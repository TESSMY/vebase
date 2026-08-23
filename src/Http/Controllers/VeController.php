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
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        $this->routeName = $request->segment(2);

        // The segment is attacker controlled. resolveModelClass() only hands back concrete
        // VeModel classes the app declares, so an unmatched segment is a 404 rather than an
        // arbitrary container resolution driven by the URL.
        $class = VeHelper::resolveModelClass($this->routeName);
        abort_if($class === null, 404);

        $this->model = app($class);
        $this->modelName = preg_replace('/([a-z])([A-Z])/s', '$1 $2', class_basename($class));
        $this->folder = Str::singular($request->segment(1));
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
                if (! is_null($value) && is_scalar($value)) {
                    $models->where($name, $value);
                }
            }
        }

        if ($trashed && in_array(SoftDeletes::class, class_uses_recursive($this->model)) && $this->model::$resourceWithTrashed) {
            $models = $models->withTrashed();
        }

        if (! empty($search) && ! empty($this->model->searchable)) {
            $models = $models->where(function ($query) use ($search) {
                foreach ($this->model->searchable as $value) {
                    if (str_contains($value, '.')) {
                        [$relation, $relationColumn] = explode('.', $value, 2);

                        $query->orWhereHas($relation, function ($q) use ($relationColumn, $search) {
                            $q->where($relationColumn, 'LIKE', '%'.$search.'%');
                        });
                    } else {
                        $query->orWhere($value, 'LIKE', '%'.$search.'%');
                    }
                }
            });
        }

        // The index table walks `relation` index fields per row, so without this a listing of
        // N rows issues N extra queries for each of them.
        if (! empty($eagerLoads = VeHelper::eagerLoadsFor($this->model))) {
            $models = $models->with($eagerLoads);
        }

        $models = $this->applySort($request, $models)->latest()->paginate($limit)->withQueryString();

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

        if (!empty($this->model->createValidator())) {
            $validator = Validator::make($input, $this->model->createValidator());
            if ($validator->fails()) {
                flash('Error: '.implode(' ', $validator->errors()->all()))->error();

                return back()->withInput($request->input())->withErrors($validator);
            }
        }

        try {
            DB::beginTransaction();

            $input = $this->storeUploadedFiles($request, $input, strtolower(Str::snake($this->modelName)).'/'.time());

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
            throw $exception;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error($this->failureMessage('creating', $exception));

            return back()->withInput();
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

        if (!empty($model->updateValidator())) {
            $validator = Validator::make($input, $model->updateValidator());
            if ($validator->fails()) {
                flash('Error: '.implode(' ', $validator->errors()->all()))->error();

                return back()->withInput($request->input())->withErrors($validator);
            }
        }

        try {
            DB::beginTransaction();

            // Replacing a file: drop what is already there, then store the new upload.
            $this->deleteStoredFiles($request, $model);
            $input = $this->storeUploadedFiles($request, $input, strtolower(Str::snake($this->modelName)).'/'.md5((string) $model->id));

            if (method_exists($this, 'updateInput')) {
                $input = $this->updateInput($input);
            }

            $model->update($input);

            if (method_exists($this, 'updateAfter')) {
                $this->updateAfter($request, $model);
            }

            DB::commit();
            flash()->success('Successfully updated '.strtolower($this->modelName).'. ID: '.$model->id);

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
            throw $exception;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error($this->failureMessage('updating', $exception));

            return back()->withInput();
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
            Excel::import(new ModelsImport($this->model), $request->file('import_file'));
        } catch (\Exception $exception) {
            Log::error($exception);
            flash()->error($this->failureMessage('importing', $exception));

            return back();
        }

        return redirect()->route($this->folder.'.'.$this->routeName.'.index')->with('success', 'All good!');
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
     * Stores any uploaded files listed in $model->files under $directory and returns the input
     * with those keys replaced by the resulting public URLs.
     */
    protected function storeUploadedFiles(Request $request, array $input, string $directory): array
    {
        foreach ((array) ($this->model->files ?? []) as $file) {
            if (! $request->hasFile($file)) {
                continue;
            }

            $uploaded = $request->file($file);

            if (is_array($uploaded)) {
                $stored = [];
                foreach ($uploaded as $item) {
                    $stored[] = Storage::url($item->store($directory));
                }
                $input[$file] = $stored;
            } else {
                $input[$file] = Storage::url($uploaded->store($directory));
            }
        }

        return $input;
    }

    /**
     * Removes the files currently referenced by $model for each upload being replaced.
     */
    protected function deleteStoredFiles(Request $request, $model): void
    {
        foreach ((array) ($this->model->files ?? []) as $file) {
            if (! $request->hasFile($file) || empty($model[$file])) {
                continue;
            }

            foreach ((array) $model[$file] as $item) {
                if (! is_string($item)) {
                    continue;
                }

                if ($path = $this->storagePathFromUrl($item)) {
                    Storage::delete($path);
                }
            }
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
