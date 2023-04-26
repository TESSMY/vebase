<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class TaxController extends VeController
{
    public function store(Request $request)
    {
        $this->authorize('create', $this->model);
        
        $input = $request->all();

        if (empty($this->model->createValidator)) {
            flash('Error: ' . $this->modelName . " create is empty")->error();
            return back()->withInput($request->input());
        }

        $validator = Validator::make($input, $this->model->createValidator);
        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {

            if (!empty($input['is_default'])) {
                Tax::where('is_default', true)->update(['is_default' => false, 'updated_at' => now()]);
            } 
            if (!empty($input['is_default_2'])) {
                Tax::where('is_default_2', true)->update(['is_default_2' => false, 'updated_at' => now()]);
            }

            $model = $this->model::create($input);

            flash()->success('Successfully create ' .  $this->modelName);
            
            return redirect()->route($this->folder . '.' . $this->routeName . '.index');
        } catch (\Exception $exception) {
            Log::error($exception);
            flash()->error('There was an error creating ' . $this->modelName);
            return back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $model = $this->findModel($id);
        $this->authorize('update', $model);

        $input = $request->all();

        if (empty($this->model->updateValidator())) {
            flash('Error:  updateValidator is empty')->error();
            return back()->withInput($request->input());
        }

        $validator = Validator::make($input, $model->updateValidator());
        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }


        try {
            if (!empty($input['is_default'])) {
                Tax::where('is_default', true)->update(['is_default' => false, 'updated_at' => now()]);
            } 
            if (!empty($input['is_default_2'])) {
                Tax::where('is_default_2', true)->update(['is_default_2' => false, 'updated_at' => now()]);
            }

            if (empty($input['is_default'])) {
                $input['is_default'] = false;
            }
            if (empty($input['is_default_2'])) {
                $input['is_default_2'] = false;
            }
        
            $model->update($input);

            flash()->success('Successfully updated ' .  $this->modelName);
            return redirect()->route($this->folder . '.' . $this->routeName . '.index');
        } catch (\Exception $exception) {
            Log::error($exception);
            flash()->error('There was an error creating ' . $this->modelName);
            return back()->withInput();
        }
    }
}
