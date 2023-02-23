<?php

namespace Vecapital\Vebase\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VeApiController extends ApiController
{
    protected $model;
    protected $tableName;
    protected $folder;

    /**
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        if (!empty($request->segments())) {
            $this->tableName = $request->segment(2);
            $class = Str::singular($request->segment(2));
            $this->model = app('App\\Models\\' . ucfirst($class));

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
        $limit = min(intval($request->get('limit', 10)), 1000);
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $models = $this->model::query();

        if (!empty($this->model::relatable)) {
            $models->with($this->model::relatable);
        }

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
        } else {
            $sortBy = $request->input('sort_by', 'latest');
            if ($sortBy === 'oldest'){
                $models->oldest();
            } elseif ($sortBy === 'latest'){
                $models->latest();
            }
        }

        return $this->respondPagination($request, $models->paginate($limit));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        if (empty($this->model::createValidator)) {
            throw new \Exception($this->model . " createValidator is empty");
        }

        $validator = Validator::make($input, $this->model::createValidator);
        if ($validator->fails()) {
            return $this->showValidationError($validator);
        }

        try {
            $model = $this->model::create($input);

            return $this->respondCreated($model);
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->respondInternalError();
        }
    }

    public function show(Request $request, $id)
    {
        $model = $this->checkRouteKey($id);

        if (!empty($model::relatable)) {
            $model->with($model::relatable);
        }
        
        return $this->respond($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->checkRouteKey($id);

        $input = $request->all();

        if (empty($this->model::create)) {
            throw new \Exception($this->model . " updateValidator is empty");
        }

        $validator = Validator::make($input, $model::updateValidator);
        if ($validator->fails()) {
            return $this->showValidationError($validator);
        }

        try {
            $model = $this->model::update($input);

            return $this->respond($model);
        } catch (\Exception $exception) {
            Log::error($exception);
            return $this->respondInternalError();
        }
    }

    public function destroy($id)
    {
        $model = $this->checkRouteKey($id);
        $model->delete();

        return $this->respond();
    }

}
