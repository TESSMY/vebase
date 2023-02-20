<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VeController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $model;
    protected $tableName;
    protected $userType;

    /**
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        $this->tableName = $request->segment(2);

        $class = Str::singular($request->segment(2));
        $this->model = app('App\\Models\\' . ucfirst($class));

        if (Auth::user()->canAccessAdmin) {
            $this->userType = 'admin';
        }
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $models = $this->model::query();

        if (!empty($search)) {
            if (empty($this->model::searchable)) {
                throw new Exception($this->model . " searchable is empty");
            }

            foreach ($this->model::searchable as $key => $value) {
                if (!Schema::hasColumn($this->tableName, $value)) {
                    throw new Exception($value . " column does not exist on " . $this->model . ' table');
                } else {
                    $models = $models->where(function($query) use ($search, $key, $value) {
                        if ($key == 0) {
                            $query->where($value, 'LIKE', '%' . $search . '%');
                        } else {
                            $query->orWhere($value, 'LIKE', '%' . $search . '%');
                        }
                    });
                }
            }
        }

        if (!empty($orderColumn)) {
            if (empty($this->model::sortable)) {
                throw new Exception($this->model . " sortable is empty");
            }
            if (!in_array($orderColumn, $this->model::sortable)) {
                throw new Exception($orderColumn . " is not in " . $this->model . " sortable");
            }

            $models = $models->orderBy($orderColumn, $orderBy);
        }

        if (!empty($this->model::relatable)) {
            $models->load($this->model::relatable);
        }

        // need to check if orderby and sortby will work together
        $sortBy = $request->input('sort_by', 'latest');
        if($sortBy === 'oldest'){
            $models->oldest();
        } elseif($sortBy === 'latest'){
            $models->latest();
        }

        return view($this->userType . '.' . $this->tableName . '.index', compact('models'));
    }

    public function create(Request $request)
    {
        return view($this->userType . '.' . $this->tableName . '.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        if (empty($this->model::create)) {
            throw new Exception($this->model . " create is empty");
        }

        $validator = Validator::make($input, $model::create);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $model::create($input);

            DB::commit();
            flash()->success('Successfully create ' .  $model);
            return redirect()->route($this->userType . '.' . $this->tableName . '.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error creating ' . $model);
            return back()->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $model = $this->model::find($id);
        abort_if(empty($model),404);

        if (!empty($model::relatable)) {
            $model->load($model::relatable);
        }
        
        return view($this->userType . '.' . $this->tableName . '.show', compact('model'));
    }

    public function edit(Request $request, $id)
    {
        $model = $this->model::find($id);
        abort_if(empty($model),404);

        return view($this->userType . '.' . $this->tableName . '.edit', compact('model'));
    }

    public function update(Request $request, $id)
    {
        $model = $this->model::find($id);
        abort_if(empty($model),404);

        $input = $request->all();

        if (empty($this->model::create)) {
            throw new Exception($this->model . " create is empty");
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
            flash()->success('Successfully create ' .  $model);
            return redirect()->route($this->userType . '.' . $this->tableName . '.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an error creating ' . $model);
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        $model = $this->model::find($id);
        abort_if(empty($model),404);

        flash()->success('Successfully deleted ' . $model);
        return redirect()->route($this->userType . '.' . $this->tableName . '.index');
    }

}
