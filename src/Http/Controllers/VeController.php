<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Vecapital\Vebase\Exports\ModelsExport;
use Vecapital\Vebase\Imports\ModelsImport;
use Vecapital\Vebase\VeHelper;


class VeController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $model;

    protected $modelName;

    protected $routeName;

    protected $folder;

    protected $paginateSize = 10;

    /**
     * Largest page size a request may ask for. Without a ceiling `?limit=1000000` hydrates
     * the whole table into memory and renders a row for each.
     */
    protected int $maxPaginateSize = VeHelper::MAX_PAGINATE_LIMIT;

    /**
     * Largest accepted import upload, in kilobytes.
     */
    protected int $maxImportSize = 10240;

    /**
     * Restricts store()/update() input to the names the model declares in $createFields /
     * $updateFields, on top of Eloquent's own $fillable.
     *
     * Off by default because controllers commonly submit fields that never appear in the
     * generated form. Turn it on for any model using `$guarded = []`, where $request->all()
     * otherwise lets a caller write any column it can name.
     */
    protected bool $restrictInputToFields = false;

    /**
     * Optional redirect targets honoured after a successful store()/update().
     *
     * Declared rather than set dynamically -- reading an undeclared property raises an
     * "Undefined property" warning on every write request.
     */
    protected $create_redirect_route;

    protected $create_redirect_object;

    protected $update_redirect_route;

    protected $update_redirect_object;

    /**
     * Request keys never flashed back to the session when a write is rejected.
     *
     * A failed user form used to flash the whole request -- the submitted password included --
     * into the session store in plain text, and the next page read it back out.
     */
    protected array $dontFlash = ['password', 'password_confirmation', 'current_password'];

    /**
     * Extensions refused for any `$files` upload, whatever the model's own rules allow.
     *
     * Uploads are stored on the default disk -- usually the public one, served from the app's
     * own origin -- under a name whose extension is guessed from the content. An SVG or HTML
     * upload therefore became a same-origin page that runs script for whoever opens its URL.
     * Models should still validate their uploads; this closes the gap when one does not.
     */
    protected array $blockedUploadExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar',
        'html', 'htm', 'shtml', 'xhtml', 'xht', 'svg', 'svgz', 'xml', 'xsl', 'js', 'mjs',
        'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'sh', 'bat', 'cmd', 'exe', 'htaccess',
    ];

    /**
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        $this->routeName = $request->segment(2);

        // The segment is attacker controlled. resolveModelClass() only hands back concrete
        // VeModel classes the app declares, so an unmatched segment is a 404 rather than an
        // arbitrary container resolution driven by the URL.
        //
        // The 404 itself waits for callAction(): the router (and `artisan route:list`) builds
        // controllers just to read their middleware, and aborting here made every console
        // command that does so die with a NotFoundHttpException.
        $class = VeHelper::resolveModelClass($this->routeName);
        if ($class === null) {
            return;
        }

        $this->model = app($class);
        $this->modelName = preg_replace('/([a-z])([A-Z])/s', '$1 $2', class_basename($class));
        $this->folder = Str::singular($request->segment(1));
    }

    public function callAction($method, $parameters)
    {
        abort_if($this->model === null, 404);

        return parent::callAction($method, $parameters);
    }

    public function findModel($id)
    {
        $routeKey = $this->model->getRouteKeyName() ?: 'id';

        if ($this->model::$resourceWithTrashed && in_array(SoftDeletes::class, class_uses_recursive($this->model))) {
            $model = $this->model::withTrashed()->where($routeKey, $id)->first();
        } else {
            $model = $this->model::where($routeKey, $id)->first();
        }
        abort_if(empty($model), 404);

        return $model;
    }


    /**
     * @param Request $request
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', $this->model);

        $search = VeHelper::sanitizeSearchTerm($request->input('search'));
        // A subclass that raises $paginateSize past the cap means to allow that many, so the
        // ceiling never sits below the controller's own default.
        $limit = VeHelper::sanitizeLimit(
            $request->input('limit'),
            $this->paginateSize,
            max($this->maxPaginateSize, $this->paginateSize)
        );
        $trashed = $request->input('trashed');

        $models = $this->model::query();

        if (method_exists($this, 'indexFilter')) {
            $models = $this->indexFilter($request, $models) ?? $models;
        }

        if (! empty($filters = $this->model->filters)) {
            foreach ($filters as $filter) {
                $name = $filter['name'];
                $value = $request->input($name);
                // Array input reaches the grammar as a single binding and blows up the query;
                // only a scalar can meaningfully match an equality filter.
                if (is_null($value) || ! is_scalar($value)) {
                    continue;
                }

                if (! empty($filter['relation'])) {
                    // A filter on a related model (e.g. a user's roles). Without this the
                    // value was matched against a column of the same name on this table,
                    // which does not exist, so controllers stripped the parameter from the
                    // request to dodge the SQL error -- and the filter then vanished from the
                    // pagination links and from the selected option.
                    $column = $filter['relationColumn'] ?? $filter['key'] ?? 'id';
                    $models->whereHas($filter['relation'], function ($q) use ($column, $value) {
                        $q->where($q->qualifyColumn($column), $value);
                    });
                } else {
                    $models->where($name, $value);
                }
            }
        }

        if ($trashed && in_array(SoftDeletes::class, class_uses_recursive($this->model)) && $this->model::$resourceWithTrashed) {
            $models = $models->withTrashed();
        }

        $models = VeHelper::applySearch($models, $this->model->searchable ?? [], $search);

        // The index table walks `relation` index fields per row, so without this a listing of
        // N rows issues N extra queries for each of them.
        if (! empty($eagerLoads = VeHelper::eagerLoadsFor($this->model))) {
            $models = $models->with($eagerLoads);
        }

        $models = $this->applyDefaultOrder($this->applySort($request, $models))->paginate($limit)->withQueryString();

        $compact = [
            'routeModel' => Str::singular($this->routeName),
            'models' => $models,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
            'limit' => $limit,
        ];

        if (View::exists($this->folder.'.'.$this->routeName.'.index')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.index', $compact);
        } elseif (View::exists('vebase::'.$this->routeName.'.index')) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.index', $compact);
        } else {
            // default vendor view
            return View::make('vebase::index', $compact);
        }
    }

    /**
     * Applies the request sort, restricted to the columns the model declares in $sortable.
     *
     * ColumnSortable reads `sort` straight off the request rather than from the argument, and
     * treats any value containing a `.` as `relation.column`. The left half is handed to
     * Builder::getRelation(), which calls it as a method on a new model instance, and
     * Model::__call forwards unknown methods to the query builder -- so an unchecked value
     * reaches zero-argument builder methods such as truncate(). Anything not on the allow list
     * is stripped from the request before sortable() ever reads it.
     */
    protected function applySort(Request $request, $query)
    {
        $sort = $request->input('sort');
        $sortable = $this->model->sortable ?? [];

        if (! empty($sort) && (! is_array($sortable) || ! in_array($sort, $sortable, true))) {
            Log::warning('Rejected sort parameter for '.$this->model::class.': '.(is_string($sort) ? $sort : gettype($sort)));

            // scopeSortable() resolves the parameters through request(), which is this same
            // instance, so the value has to be removed from every input source it reads.
            foreach (['sort', 'direction'] as $key) {
                $request->query->remove($key);
                $request->request->remove($key);

                if ($request->isJson()) {
                    $request->json()->remove($key);
                }
            }
        }

        return $query->sortable();
    }

    /**
     * Newest first, as the tie-breaker after any requested sort.
     *
     * latest() orders by a bare `created_at`: that is ambiguous once ColumnSortable joins a
     * related table for a `relation.column` sort, and missing outright on models without
     * timestamps -- both an SQL error on the index page. The column is qualified, and models
     * without a created-at column fall back to their key.
     */
    protected function applyDefaultOrder($query)
    {
        $createdAt = $this->model->usesTimestamps() ? $this->model->getCreatedAtColumn() : null;

        return $query->orderByDesc($this->model->qualifyColumn($createdAt ?: $this->model->getKeyName()));
    }

    /**
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', $this->model);

        $compact = [
            'routeModel' => Str::singular($this->routeName),
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ] + $this->createFields();

        if (View::exists($this->folder.'.'.$this->routeName.'.create')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.create', $compact);
        } elseif (View::exists('vebase::'.$this->routeName.'.create')) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.create', $compact);
        } else {
            // default vendor view
            return View::make('vebase::create', $compact);
        }
    }

    public function createFields() : array
    {
        return [];
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->model);

        $input = $this->inputFor($request, $this->model->createFields ?? []);

        if ($rejected = $this->rejectInvalidInput($request, $input, $this->model->createValidator())) {
            return $rejected;
        }

        $stored = [];

        try {
            DB::beginTransaction();

            $input = $this->storeUploadedFiles($request, $input, strtolower(Str::snake($this->modelName)).'/'.time(), $stored);

            if (method_exists($this, 'storeInput')) {
                $input = $this->storeInput($input);
            }

            $created = $this->model::create($input);

            if (method_exists($this, 'storeAfter')) {
                $this->storeAfter($request, $created);
            }

            DB::commit();
            flash()->success('Successfully created '.strtolower($this->modelName));

            if (!empty($this->create_redirect_route)) {
                if (!empty($this->create_redirect_object)) {
                    return redirect()->route($this->create_redirect_route, [$this->create_redirect_object]);
                }

                return redirect()->route($this->create_redirect_route);
            }

            return redirect()->route($this->folder.'.'.$this->routeName.'.index');
        } catch (HttpException | AuthorizationException $exception) {
            // Guards in storeInput/storeAfter (or anything they call) surface abort()/authorize()
            // as these exception types. Roll the write back but let the framework render the
            // proper 401/403 -- do not flatten to a flashed 302, which would look like a
            // validation error and hide the refusal.
            DB::rollBack();
            $this->deleteFiles($stored);
            throw $exception;
        } catch (\Exception $exception) {
            DB::rollBack();
            // The rollback cannot reach the disk: without this every failed create left its
            // uploads behind with nothing referencing them.
            $this->deleteFiles($stored);
            Log::error($exception);
            flash()->error($this->failureMessage('creating', $exception));

            return back()->withInput($this->flashableInput($request));
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     * @throws AuthorizationException
     */
    public function show(Request $request, $id)
    {
        $routeModel = Str::singular(Str::camel($this->routeName));
        $$routeModel = $this->findModel($id);
        $this->authorize('view', $$routeModel);

        $compact = [
            'routeModel' => $routeModel,
            $routeModel => $$routeModel,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ] + $this->showFields($$routeModel);

        if (View::exists($this->folder.'.'.$this->routeName.'.show')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.show', $compact);
        } elseif (View::exists('vebase::'.$this->routeName.'.show')) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.show', $compact);
        } else {
            // default vendor view
            return View::make('vebase::show', $compact);
        }
    }

    public function showFields($model) : array
    {
        return [];
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     * @throws AuthorizationException
     */
    public function edit(Request $request, $id)
    {
        $routeModel = Str::singular(Str::camel($this->routeName));
        $$routeModel = $this->findModel($id);
        $this->authorize('update', $$routeModel);

        $compact = [
            'routeModel' => $routeModel,
            $routeModel => $$routeModel,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ] + $this->editFields($request, $$routeModel);

        if (View::exists($this->folder.'.'.$this->routeName.'.edit')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.edit', $compact);
        } elseif (View::exists('vebase::'.$this->routeName.'.edit')) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.edit', $compact);
        } else {
            // default vendor view
            return View::make('vebase::edit', $compact);
        }
    }

    public function editFields($request, $model) : array
    {
        return [];
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);
        $this->authorize('update', $model);

        $input = $this->inputFor($request, $this->model->updateFields ?? []);

        if ($rejected = $this->rejectInvalidInput($request, $input, $model->updateValidator())) {
            return $rejected;
        }

        $stored = [];

        try {
            DB::beginTransaction();

            // Replacing a file: the old one is only removed once the new value is committed.
            // It used to be deleted up front, so any failure later in the write -- a hook's
            // guard, a constraint violation -- rolled the row back to point at a file that
            // no longer existed.
            $replaced = $this->replacedFilePaths($request, $model);
            $input = $this->storeUploadedFiles($request, $input, strtolower(Str::snake($this->modelName)).'/'.md5((string) $model->getKey()), $stored);

            if (method_exists($this, 'updateInput')) {
                $input = $this->updateInput($input);
            }

            $model->update($input);

            if (method_exists($this, 'updateAfter')) {
                $this->updateAfter($request, $model);
            }

            DB::commit();
            $this->deleteFiles($replaced);
            flash()->success('Successfully updated '.strtolower($this->modelName).'. ID: '.$model->getKey());

            if (!empty($this->update_redirect_route)) {
                if (!empty($this->update_redirect_object)) {
                    return redirect()->route($this->update_redirect_route, [$this->update_redirect_object]);
                }

                return redirect()->route($this->update_redirect_route);
            }

            return redirect()->route($this->folder.'.'.$this->routeName.'.index');
        } catch (HttpException | AuthorizationException $exception) {
            // See store(): keep abort()/authorize() from hook methods as 401/403 rather than
            // a flashed 302. The rollback still runs so no partial write survives.
            DB::rollBack();
            $this->deleteFiles($stored);
            throw $exception;
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->deleteFiles($stored);
            Log::error($exception);
            flash()->error($this->failureMessage('updating', $exception));

            return back()->withInput($this->flashableInput($request));
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $model = $this->findModel($id);
        $this->authorize('delete', $model);

        DB::beginTransaction();
        try {
            if (method_exists($this, 'deleteBefore')) {
                $this->deleteBefore($model);
            }

            $model->delete();

            if (method_exists($this, 'deleteAfter')) {
                $this->deleteAfter();
            }
            DB::commit();
        } catch (HttpException | AuthorizationException $exception) {
            // See store(): keep abort()/authorize() from deleteBefore/deleteAfter as 401/403.
            DB::rollBack();
            throw $exception;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error($this->failureMessage('deleting', $exception));

            return back();
        }

        flash()->success('Successfully deleted '.$this->modelName);

        return redirect()->route($this->folder.'.'.$this->routeName.'.index');
    }

    public function export(Request $request)
    {
        $this->authorizePermission('export');

        return Excel::download(new ModelsExport($this->model), $this->modelName . '-' . now()->toDateString() . '.xlsx');
    }

    public function import(Request $request)
    {
        $this->authorizePermission('import');

        // The upload went straight into the spreadsheet reader unchecked: a missing field was a
        // TypeError, and any file of any size and type was parsed.
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:'.$this->maxImportSize],
        ]);

        try {
            // One transaction for the whole file. Rows are written chunk by chunk, so a bad row
            // halfway down used to leave everything above it imported while the user was told
            // the import failed -- re-running it then duplicated or re-applied those rows.
            DB::transaction(fn () => Excel::import(new ModelsImport($this->model), $request->file('import_file')));
        } catch (\Exception $exception) {
            Log::error($exception);
            flash()->error($this->failureMessage('importing', $exception));

            return back();
        }

        // flash(), like every other outcome here. `->with('success')` wrote a session key the
        // flash partial never reads, so a successful import showed no confirmation at all.
        flash()->success('Successfully imported '.Str::plural(strtolower($this->modelName)).'.');

        return redirect()->route($this->folder.'.'.$this->routeName.'.index');
    }

    /**
     * Aborts unless the current user holds the `<action>-<model>` permission.
     *
     * can() rather than hasPermissionTo(): the latter raises PermissionDoesNotExist for a
     * permission the app never registered, turning a denial into a 500. A guest reached it as
     * a null dereference. Missing authorisation is a 403 -- 401 means "not authenticated yet".
     */
    protected function authorizePermission(string $action): void
    {
        $user = Auth::user();
        $permission = $action.'-'.Str::kebab(strtolower($this->modelName));

        abort_if($user === null, 401);
        abort_if(! $user->can($permission), 403);
    }

    /**
     * The input array a write should operate on.
     *
     * Framework-internal keys never belong in a model write. When $restrictInputToFields is on,
     * the input is further narrowed to the field names the model declares.
     */
    protected function inputFor(Request $request, array $fields): array
    {
        $input = $request->except(['_token', '_method']);

        if (! $this->restrictInputToFields) {
            return $input;
        }

        $allowed = [];
        foreach ($fields as $field) {
            if (is_array($field) && ! empty($field['name'])) {
                $allowed[] = $field['name'];
            }
        }

        return empty($allowed) ? $input : array_intersect_key($input, array_flip($allowed));
    }

    /**
     * Validates a write's input, plus the upload extension block list. Returns the redirect
     * back to the form when anything fails, or null when the write may go ahead.
     */
    protected function rejectInvalidInput(Request $request, array $input, $rules): ?RedirectResponse
    {
        $validator = Validator::make($input, empty($rules) ? [] : $rules);

        $validator->after(function ($validator) use ($request) {
            foreach ($this->blockedUploadErrors($request) as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });

        if (! $validator->fails()) {
            return null;
        }

        flash('Error: '.implode(' ', $validator->errors()->all()))->error();

        return back()->withInput($this->flashableInput($request))->withErrors($validator);
    }

    /**
     * The request input that may be flashed back to the form, without $dontFlash keys.
     */
    protected function flashableInput(Request $request): array
    {
        return Arr::except($request->input(), $this->dontFlash);
    }

    /**
     * One error per `$files` field carrying an upload whose extension is on the block list.
     *
     * Both the content-guessed extension -- which store() puts on the stored name -- and the
     * client's own are checked.
     */
    protected function blockedUploadErrors(Request $request): array
    {
        $errors = [];
        $blocked = array_map('strtolower', $this->blockedUploadExtensions);

        foreach ((array) ($this->model->files ?? []) as $file) {
            if (! $request->hasFile($file)) {
                continue;
            }

            foreach (Arr::flatten(Arr::wrap($request->file($file))) as $upload) {
                $extensions = [
                    strtolower((string) $upload->guessExtension()),
                    strtolower((string) $upload->getClientOriginalExtension()),
                ];

                if (array_intersect($extensions, $blocked)) {
                    $errors[$file] = 'The '.str_replace('_', ' ', $file).' file type is not allowed.';
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Stores any uploaded files listed in $model->files under $directory and returns the input
     * with those keys replaced by the resulting public URLs.
     *
     * Each disk path written is appended to $stored, so a write that fails afterwards can
     * remove them again.
     */
    protected function storeUploadedFiles(Request $request, array $input, string $directory, array &$stored = []): array
    {
        foreach ((array) ($this->model->files ?? []) as $file) {
            if (! $request->hasFile($file)) {
                continue;
            }

            $uploaded = $request->file($file);

            if (is_array($uploaded)) {
                $urls = [];
                foreach ($uploaded as $item) {
                    $urls[] = Storage::url($stored[] = $item->store($directory));
                }
                $input[$file] = $urls;
            } else {
                $input[$file] = Storage::url($stored[] = $uploaded->store($directory));
            }
        }

        return $input;
    }

    /**
     * Disk paths of the files $model currently references for each upload being replaced.
     */
    protected function replacedFilePaths(Request $request, $model): array
    {
        $paths = [];

        foreach ((array) ($this->model->files ?? []) as $file) {
            if (! $request->hasFile($file) || empty($model[$file])) {
                continue;
            }

            foreach ((array) $model[$file] as $item) {
                if (is_string($item) && ($path = $this->storagePathFromUrl($item))) {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
    }

    /**
     * Best-effort removal of stored files. A file that cannot be removed is logged rather than
     * allowed to fail a write that has already been committed or rolled back.
     */
    protected function deleteFiles(array $paths): void
    {
        $paths = array_values(array_filter($paths, fn ($path) => is_string($path) && $path !== ''));

        if (empty($paths)) {
            return;
        }

        try {
            Storage::delete($paths);
        } catch (\Throwable $exception) {
            Log::warning($exception);
        }
    }

    /**
     * Maps a stored public URL back to the disk-relative path it was written to.
     *
     * The previous logic only stripped a prefix when the default disk was literally named
     * "public", and compared a `Storage::url()` result against the configured base URL, so on
     * every other disk Storage::delete() was handed a full URL and quietly deleted nothing --
     * replaced files accumulated forever. Deriving the prefix from the same call that built
     * the URL keeps the two in step whatever the disk is.
     */
    protected function storagePathFromUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            $prefix = rtrim(Storage::url(''), '/');
        } catch (\Throwable) {
            // Driver cannot build URLs; the stored value is already a relative path.
            $prefix = '';
        }

        if ($prefix !== '' && str_starts_with($value, $prefix)) {
            $value = substr($value, strlen($prefix));
        }

        $value = ltrim(rawurldecode($value), '/');

        // A column an operator can write reaching a filesystem delete has to stay inside the
        // disk root, whatever Flysystem would have made of the traversal itself.
        if ($value === '' || str_contains($value, '..')) {
            return null;
        }

        return $value;
    }

    /**
     * A user-facing failure message. Exception text can carry SQL fragments, table names and
     * absolute paths, so it is only surfaced when the app is in debug mode; the full exception
     * is logged either way.
     */
    protected function failureMessage(string $action, \Throwable $exception): string
    {
        $message = 'There was an error '.$action.' '.strtolower($this->modelName).'.';

        return config('app.debug') ? $message.' Error: '.$exception->getMessage() : $message;
    }
}
