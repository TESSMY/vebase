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
use Vecapital\Vebase\Exports\ModelsExport;
use Vecapital\Vebase\Imports\ModelsImport;


class VeController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $model;

    protected $modelName;

    protected $routeName;

    protected $folder;

    /**
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        if (! empty($request->segments())) {
            $this->routeName = $request->segment(2);
            $name = Str::singular(Str::camel($this->routeName));
            $this->model = app('App\\Models\\'.ucfirst($name));
            $this->modelName = preg_replace('/([a-z])([A-Z])/s', '$1 $2', ucfirst($name));

            $this->folder = Str::singular($request->segment(1));
        }
    }

    public function findModel($id)
    {
        $routeKey = $this->model->getRouteKeyName() ?? 'id';

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

        $search = $request->input('search');
        $limit = $request->input('limit') ?? 10;
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');
        $trashed = $request->input('trashed');

        $models = $this->model::query();

        if (!empty($filters = $this->model->filters)) {
            foreach ($filters as $filter) {
                $name = $filter['name'];
                $value = $request->input($name);
                if (!is_null($value)) {
                    $models->where($name, $value);
                }
            }
        }

        if ($trashed && in_array(SoftDeletes::class, class_uses_recursive($this->model)) && $this->model::$resourceWithTrashed) {
            $models = $models->withTrashed();
        }

        if (! empty($search)) {
            if (! empty($this->model->searchable)) {
                $models = $models->where(function ($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%'.$search.'%');
                    }
                });
            }
        }

        if (method_exists($this, 'indexFilter')) {
            $models = $this->indexFilter($request, $models);
        }

        $models = $models->sortable()->latest()->paginate($limit)->withQueryString();

        $compact = [
            'routeModel' => Str::singular($this->routeName),
            'models' => $models,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ];

        if (View::exists($this->folder.'.'.$this->routeName.'.index')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.index', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/'.$this->routeName.'/index.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.index', $compact);
        } else {
            // default vendor view
            return View::make('vebase::index', $compact);
        }
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
        ];

        if (View::exists($this->folder.'.'.$this->routeName.'.create')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.create', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/'.$this->routeName.'/create.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.create', $compact);
        } else {
            // default vendor view
            return View::make('vebase::create', $compact);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->model);

        $input = $request->all();

        if (! empty($this->model->createValidator) || ! empty($this->model->createValidator())) {
            $validator = Validator::make($input, $this->model->createValidator() ?? $this->model->createValidator);
            if ($validator->fails()) {
                flash('Error: '.implode(' ', $validator->errors()->all()))->error();

                return back()->withInput($request->input())->withErrors($validator);
            }
        }

        try {
            DB::beginTransaction();

            if (! empty($this->model->files)) {
                foreach ($this->model->files as $file) {
                    if ($request->hasFile($file)) {
                        if (is_array($request->file($file))) {
                            $files = [];
                            foreach ($request->file($file) as $item) {
                                $files[] = Storage::url($item->store(strtolower(Str::snake($this->modelName)) . '/' . time()));
                            }
                            $input[$file] = $files;
                        } else {
                            $input[$file] = Storage::url($request->file($file)->store(strtolower(Str::snake($this->modelName)) . '/' . time()));
                        }
                    }
                }
            }

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
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error creating ' . strtolower($this->modelName) . '. Error: ' . $exception->getMessage());

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
        ];

        if (View::exists($this->folder.'.'.$this->routeName.'.show')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.show', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/'.$this->routeName.'/show.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.show', $compact);
        } else {
            // default vendor view
            return View::make('vebase::show', $compact);
        }
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
        ];

        if (View::exists($this->folder.'.'.$this->routeName.'.edit')) {
            // returns view if found in app resource view folder
            return view($this->folder.'.'.$this->routeName.'.edit', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/'.$this->routeName.'/edit.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::'.$this->routeName.'.edit', $compact);
        } else {
            // default vendor view
            return View::make('vebase::edit', $compact);
        }
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

        $input = $request->all();

        if (!empty($model->updateValidator) || ! empty($model->updateValidator())) {
            $validator = Validator::make($input, $model->updateValidator() ?? $model->updateValidator);
            if ($validator->fails()) {
                flash('Error: '.implode(' ', $validator->errors()->all()))->error();

                return back()->withInput($request->input())->withErrors($validator);
            }
        }

        try {
            DB::beginTransaction();

            if (! empty($this->model->files)) {
                foreach ($this->model->files as $file) {
                    if ($request->hasFile($file)) {

                        // Handle deletion first
                        if (! empty($model[$file])) {
                            if (is_array($model[$file])) {
                                $files = [];
                                foreach ($model[$file] as $item) {
                                    $path = $item;
                                    if (config('filesystems.default') == 'public') {
                                        $initialPath = config('filesystems.disks.public.url');
                                        $path = substr($path, strlen($initialPath));
                                    }
                                    Storage::delete($path);
                                }
                            } else {
                                $path = $model[$file];
                                if (config('filesystems.default') == 'public') {
                                    $initialPath = config('filesystems.disks.public.url');
                                    $path = substr($path, strlen($initialPath));
                                }
                                Storage::delete($path);
                            }
                        }
                        // Actually store the files now
                        if (is_array($request->file($file))) {
                            $files = [];
                            foreach ($request->file($file) as $item) {
                                $files[] = Storage::url($item->store(strtolower(Str::snake($this->modelName)) . '/' . md5($model->id)));
                            }
                            $input[$file] = $files;
                        } else {
                            $input[$file] = Storage::url($request->file($file)->store(strtolower(Str::snake($this->modelName)) . '/' . md5($model->id)));
                        }

                    }
                }
            }

            if (method_exists($this, 'updateInput')) {
                $input = $this->updateInput($input);
            }

            $model->update($input);

            if (method_exists($this, 'updateAfter')) {
                $this->updateAfter($request, $model);
            }

            DB::commit();
            flash()->success('Successfully updated '.strtolower($this->modelName).'. ID: '.$model->id);

            return redirect()->route($this->folder.'.'.$this->routeName.'.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error updating ' . strtolower($this->modelName) . '. Error: ' . $exception->getMessage());

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

        $model->delete();

        flash()->success('Successfully deleted '.$this->modelName);

        return redirect()->route($this->folder.'.'.$this->routeName.'.index');
    }

    public function export()
    {
        if (Auth::user()->hasPermissionTo('export-'. $this->modelName)) {
            abort(401);
        }

        return Excel::download(new ModelsExport($this->model), $this->modelName . '-' . now()->toDateString() . '.xlsx');
    }

    public function import(request $request)
    {
        if (Auth::user()->hasPermissionTo('import-'. $this->modelName)) {
            abort(401);
        }

        Excel::import(new ModelsImport($this->model), request()->file('import_file'));

        return redirect()->route($this->folder.'.'.$this->routeName.'.index')->with('success', 'All good!');
    }
}
