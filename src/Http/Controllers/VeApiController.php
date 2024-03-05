<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VeApiController extends ApiController
{
    protected $model;

    protected $modelName;

    protected $routeName;

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
        $with = $request->input('with');
        $limit = min(intval($request->get('limit', 10)), 1000);
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $models = $this->model::query();

        if (! empty($search)) {
            if (! empty($this->model->searchable)) {
                $models = $models->where(function ($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%'.$search.'%');
                    }
                });
            }
        }

        if (! empty($with)) {
            if (is_array($with)) {
                foreach ($with as $relatable) {
                    if (in_array($relatable, $this->model->relatable)) {
                        $models = $models->with($relatable);
                    }
                }
            } else {
                $models = $models->with($with);
            }
        }

        if (! empty($orderColumn) && in_array($orderColumn, $this->model->sortable)) {
            $models = $models->orderBy($orderColumn, $orderBy);
        } else {
            $sortBy = $request->input('sort_by', 'latest');
            if ($sortBy === 'oldest') {
                $models->oldest();
            } elseif ($sortBy === 'latest') {
                $models->latest();
            }
        }

        return $this->respondPagination($request, $models->paginate($limit));
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->model);

        $input = $request->all();

        if (empty($this->model->createValidator)) {
            throw new \Exception($this->model.' createValidator is empty');
        }

        $validator = Validator::make($input, $this->model->createValidator);
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
        $input = $request->input();
        $model = $this->findModel($id);
        $this->authorize('view', $model);

        if (! empty($input['relatable'])) {
            foreach ($input['relatable'] as $relatable) {
                if (in_array($relatable, $model->relatable)) {
                    $model->with($relatable);
                }
            }
        }

        return $this->respond($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);
        $this->authorize('update', $model);

        $input = $request->all();

        if (empty($this->model->updateValidator())) {
            throw new \Exception($this->model.' updateValidator is empty');
        }

        $validator = Validator::make($input, $model->updateValidator());
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
        $model = $this->findModel($id);
        $this->authorize('delete', $model);

        $model->delete();

        return $this->respond();
    }
}
