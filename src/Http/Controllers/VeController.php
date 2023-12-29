<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

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
        if (!empty($request->segments())) {
            $this->routeName = $request->segment(2);
            $name = Str::singular(Str::camel($this->routeName));
            $this->model = app('App\\Models\\' . ucfirst($name));
            $this->modelName = preg_replace('/([a-z])([A-Z])/s','$1 $2', ucFirst($name));

            $this->folder = Str::singular($request->segment(1));
        }
    }

    public function findModel($id) 
    {
        $routeKey = $this->model->getRouteKey() ?? 'id';
        $model = $this->model::where($routeKey, $id)->first();
        abort_if(empty($model), 404);
        
        return $model;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', $this->model);

        $search = $request->input('search');
        $limit = $request->input('limit') ?? 10;
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $models = $this->model::query();

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $models = $models->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
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
        
        if (View::exists($this->folder . '.' . $this->routeName . '.index')) {
            // returns view if found in app resource view folder
            return view($this->folder . '.' . $this->routeName . '.index', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/' . $this->routeName . '/index.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::' . $this->routeName . '.index', $compact);
        } else {
            // default vendor view
            return View::make('vebase::index', $compact);
        }
    }

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

        if (View::exists($this->folder . '.' . $this->routeName . '.create')) {
            // returns view if found in app resource view folder
            return view($this->folder . '.' . $this->routeName . '.create', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/' . $this->routeName . '/create.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::' . $this->routeName . '.create', $compact);
        } else {
            // default vendor view
            return View::make('vebase::create', $compact);
        }
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->model);
        
        $input = $request->all();

        if (!empty($this->model->createValidator)) {
            $validator = Validator::make($input, $this->model->createValidator);
            if ($validator->fails()) {
                flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
                return back()->withInput($request->input())->withErrors($validator);
            }
        }


        try {
            DB::beginTransaction();

            if (!empty($this->model->files)) {
                foreach ($this->model->files as $file) {
                    if ($request->hasFile($file)) {
                        $input[$file] = Storage::url($request->file($file)->store($this->modelName . '/' . time()));
                    }
                }
            }

            $created = $this->model::create($input);

            if (method_exists($this, 'storeAfter')) {
                $this->storeAfter($request, $created);
            }

            DB::commit();
            flash()->success('Successfully created ' .  strtolower($this->modelName));

            if (!empty($this->create_redirect_route)) {
                if (!empty($this->create_redirect_object)) {
                    return redirect()->route($this->create_redirect_route, [$created]);
                }
                return redirect()->route($this->create_redirect_route);
            }
            
            return redirect()->route($this->folder . '.' . $this->routeName . '.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error creating ' . strtolower($this->modelName));
            return back()->withInput();
        }
    }

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
        
        if (View::exists($this->folder . '.' . $this->routeName . '.show')) {
            // returns view if found in app resource view folder
            return view($this->folder . '.' . $this->routeName . '.show', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/' . $this->routeName . '/show.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::' . $this->routeName . '.show', $compact);
        } else {
            // default vendor view
            return View::make('vebase::show', $compact);
        }
    }

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

        if (View::exists($this->folder . '.' . $this->routeName . '.edit')) {
            // returns view if found in app resource view folder
            return view($this->folder . '.' . $this->routeName . '.edit', $compact);
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/' . $this->routeName . '/edit.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::' . $this->routeName . '.edit', $compact);
        } else {
            // default vendor view
            return View::make('vebase::edit', $compact);
        }
    }

    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);
        $this->authorize('update', $model);

        $input = $request->all();

        if (!empty($this->model->updateValidator())) {
            $validator = Validator::make($input, $model->updateValidator());
            if ($validator->fails()) {
                flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
                return back()->withInput($request->input())->withErrors($validator);
            }
        }


        try {
            DB::beginTransaction();

            if (!empty($this->model->files)) {
                foreach ($this->model->files as $file) {
                    if ($request->hasFile($file)) {
                        if (!empty($model[$file])) {
                            $path = $model[$file];
                            if (config('filesystems.default') == 'public') {
                                $initialPath = config('filesystems.disks.public.url');
                                $path = substr($path, strlen($initialPath));
                            }
                            Storage::delete($path);
                        }
                        $input[$file] = Storage::url($request->file($file)->store($this->modelName . '/' . md5($model->id)));
                    }
                }
            }

            $model->update($input);

            if (method_exists($this, 'updateAfter')) {
                $this->updateAfter($request, $model);
            }

            DB::commit();
            flash()->success('Successfully updated ' .  strtolower($this->modelName) . '. ID: ' . $model->id);
            return redirect()->route($this->folder . '.' . $this->routeName . '.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error updating ' . strtolower($this->modelName));
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $model = $this->findModel($id);
        $this->authorize('delete', $model);

        $model->delete();

        flash()->success('Successfully deleted ' . $this->modelName);
        return redirect()->route($this->folder . '.' . $this->routeName . '.index');
    }

}
