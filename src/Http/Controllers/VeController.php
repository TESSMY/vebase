<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class VeController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $model;

    /**
     * creates the model from the request path
     */
    public function __construct(Request $request)
    {
        $class = \Illuminate\Support\Str::singular($request->segment(2));
        $this->model = app('App\\Models\\' . ucfirst($class));  
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $model = $this->model::query();

        if (!empty($search)) {
            if (empty($model::searchable)) {
                throw new Exception("Model searchable is empty");
            } else {
                foreach ($model::searchable as $key => $value) {
                    $model = $model->where(function($query) use ($search, $value) {
                        $query->where($value, 'LIKE', '%' . $search . '%')
                            ->orWhere('email', 'LIKE', '%' . $search . '%');
                    });
                }
            }
        }

        $sortBy = $request->input('sort_by', 'latest');
        if($sortBy === 'oldest'){
            $model->oldest();
        } elseif($sortBy === 'latest'){
            $model->latest();
        }
    }

    public function create()
    {
        // 
    }

    public function store(Request $request)
    {
        // 
    }

    public function show(Request $request, $id)
    {
        $model = $this->model::find($id);
        abort_if(empty($model),404);
        
        dd($model->toArray());
    }

    public function edit(Request $request, $id)
    {
        // 
    }

    public function update(Request $request, $id)
    {
        // 
    }

    public function destroy($id)
    {
        // 
    }

}
