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

            if (Auth::user()->canAccessAdmin()) {
                $this->folder = 'admin';
            }
            $this->authorizeResource($this->model::class, $this->model);
        }
    }

    public function checkRouteKey($id) {
        if ($this->model->getRouteKey() != 'id') {
            $model = $this->model::where($this->model->getRouteKey(), '$id')->first();
            abort_if(empty($model), 404);
        } else {
            $model = $this->model::findOrFail($id);
        }
        return $model;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $models = $this->model::query();

        if (!empty($search)) {
            if (!empty($this->model::searchable)) {
                $models = $models->where(function($query) use ($search) {
                    foreach ($this->model::searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($orderColumn) && in_array($orderColumn, $this->model::sortable)) {
            $models = $models->orderBy($orderColumn, $orderBy);
        }

        // need to check if orderby and sortby will work together
        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'oldest'){
            $models->oldest();
        } elseif ($sortBy === 'latest'){
            $models->latest();
        }

        $models = $models->paginate(10)->withQueryString();

        $model = $this->model;
        $modelName = $this->modelName;
        $routeName = $this->routeName;
        $routePrefix = $this->folder;
        $compact = [
            'models', 'model', 'modelName', 'routeName', 'routePrefix', 
        ];
        
        if (View::exists($this->folder . '.' . $this->routeName . '.index')) {
            // returns view if found in app resource view folder
            return view($this->folder . '.' . $this->routeName . '.index', compact($compact));
        } elseif (file_exists(base_path('vendor/vecapital/vebase/resources/' . $this->routeName . '/index.blade.php'))) {
            // returns view found in vendor resource folder
            return View::make('vebase::' . $this->routeName . '.index', compact($compact));
        } else {
            // default vendor view
            return View::make('vebase::index', compact($compact));
        }
    }

    public function create(Request $request)
    {
        return view($this->folder . '.' . $this->routeName . '.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        if (empty($this->model::create)) {
            throw new \Exception($this->model . " create is empty");
        }

        $validator = Validator::make($input, $this->model::create);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $this->model::create($input);

            DB::commit();
            flash()->success('Successfully create ' .  $this->model);
            return redirect()->route($this->folder . '.' . $this->routeName . '.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error creating ' . $this->model);
            return back()->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $model = $this->checkRouteKey($id);

        if (!empty($model::relatable)) {
            $model->load($model::relatable);
        }
        
        return view($this->folder. '.' . $this->routeName . '.show', compact('model'));
    }

    public function edit(Request $request, $id)
    {
        $model = $this->checkRouteKey($id);

        return view($this->folder . '.' . $this->routeName . '.edit', compact('model'));
    }

    public function update(Request $request, $id)
    {
        $model = $this->checkRouteKey($id);

        $input = $request->all();

        if (empty($this->model::create)) {
            throw new \Exception($this->model . " create is empty");
        }

        $validator = Validator::make($input, $model::update);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $model::update($input);

            DB::commit();
            flash()->success('Successfully create ' .  $this->model);
            return redirect()->route($this->folder . '.' . $this->routeName . '.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error creating ' . $this->model);
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $model = $this->checkRouteKey($id);
        $model->delete();

        flash()->success('Successfully deleted ' . $this->model);
        return redirect()->route($this->folder . '.' . $this->routeName . '.index');
    }

}
