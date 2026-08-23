<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\VeHelper;

class VeApiController extends ApiController
{
    protected $model;

    protected $modelName;

    protected $routeName;

    protected $paginateSize = 10;

    /**
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        $this->routeName = $request->segment(2);

        // The segment is attacker controlled. resolveModelClass() only hands back concrete
        // VeModel classes the app declares, so an unmatched segment is a 404 rather than an
        // arbitrary container resolution.
        $class = VeHelper::resolveModelClass($this->routeName);
        abort_if($class === null, 404);

        $this->model = app($class);
        $this->modelName = preg_replace('/([a-z])([A-Z])/s', '$1 $2', class_basename($class));
    }

    public function findModel($id)
    {
        // getRouteKey() returns the key *value* of this empty prototype instance (null), not
        // the column name, so a model with a custom route key was silently looked up by id.
        $routeKey = $this->model->getRouteKeyName() ?: 'id';

        $model = $this->model::where($routeKey, $id)->first();
        abort_if(empty($model), 404);

        return $model;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', $this->model);

        $search = VeHelper::sanitizeSearchTerm($request->input('search'));
        $limit = VeHelper::sanitizeLimit($request->input('limit'), $this->paginateSize, self::DEFAULT_MAX_LIMIT);

        $models = $this->model::query();

        if (! empty($search) && ! empty($this->model->searchable)) {
            $models = $models->where(function ($query) use ($search) {
                foreach ($this->model->searchable as $value) {
                    $query->orWhere($value, 'LIKE', '%'.$search.'%');
                }
            });
        }

        // Every requested relation goes through the allow list. The previous code applied a
        // non-array `with` verbatim, so `?with=user.tokens` eager loaded whatever the caller
        // named regardless of what the model chose to expose.
        foreach ($this->allowedRelations($request->input('with'), $this->model) as $relation) {
            $models = $models->with($relation);
        }

        $models = $this->applyOrder($request, $models, $this->model);

        return $this->respondPagination($request, $models->paginate($limit));
    }

    public function store(Request $request)
    {
        $this->authorize('create', $this->model);

        $input = $request->all();

        $rules = $this->model->createValidator();
        if (empty($rules)) {
            throw new \Exception($this->model::class.' createValidator is empty');
        }

        $validator = Validator::make($input, $rules);
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
        $model = $this->findModel($id);
        $this->authorize('view', $model);

        // load(), not with(): with() on an already retrieved model returns a throwaway builder,
        // so the requested relations never actually reached the response.
        $relations = $this->allowedRelations($request->input('relatable'), $model);
        if (! empty($relations)) {
            $model->load($relations);
        }

        return $this->respond($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);
        $this->authorize('update', $model);

        $input = $request->all();

        $rules = $model->updateValidator();
        if (empty($rules)) {
            throw new \Exception($model::class.' updateValidator is empty');
        }

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->showValidationError($validator);
        }

        try {
            // `$this->model::update()` was a static call, which Eloquent forwards to a fresh
            // query builder with no where clause -- updating every row in the table on any
            // authorised PUT. The update has to run against the resolved instance.
            $model->update($input);

            return $this->respond($model->fresh());
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

    /**
     * Filters requested relation names down to the ones the model publishes in $relatable.
     */
    protected function allowedRelations($requested, $model): array
    {
        if (empty($requested)) {
            return [];
        }

        $allowed = (array) ($model->relatable ?? []);
        if (empty($allowed)) {
            return [];
        }

        return array_values(array_filter(
            (array) $requested,
            fn ($relation) => is_string($relation) && in_array($relation, $allowed, true)
        ));
    }

    /**
     * Applies the request ordering, restricted to the columns the model declares in $sortable.
     *
     * The direction is validated here rather than left to Builder::orderBy(), which rejects
     * anything but asc/desc with an uncaught InvalidArgumentException -- and a missing
     * `order_by` used to reach it as null.
     */
    protected function applyOrder(Request $request, $query, $model)
    {
        $orderColumn = $request->input('order_column');
        $sortable = (array) ($model->sortable ?? []);

        if (is_string($orderColumn) && in_array($orderColumn, $sortable, true)) {
            $direction = strtolower((string) $request->input('order_by', 'asc'));

            return $query->orderBy($orderColumn, in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc');
        }

        return $request->input('sort_by', 'latest') === 'oldest' ? $query->oldest() : $query->latest();
    }
}
