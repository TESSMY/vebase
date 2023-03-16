<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class ClientController extends VeController
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client $client
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $client)
    {
        $client = $this->findModel($client);

        $this->authorize('view', $client);

        return view('admin.clients.show', compact('client'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $input = $request->input();

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
            DB::beginTransaction();

            $input['password'] = Hash::make($input['phone']);
            $client = Client::create($input);

            DB::commit();
            flash()->success('Successfully created client.');
            return redirect()->route('admin.clients.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error("There was an issue creating the client profile. Error: " . $exception->getMessage());
            return back()->withInput();
        }
    }

    public function update(Request $request, $client)
    {
        $client = $this->findModel($client);
        $this->authorize('update', $client);

        $input = $request->input();

        $validator = Validator::make($input, [
            'name' => 'required|min:3',
            'email' => 'required|unique:clients,email,null,' . $client->id . 'id,deleted_at,NULL',
            'company_name' => 'nullable',
            'phone' => 'required',
            'address_1' => 'required',
            'address_2' => 'nullable',
            'city' => 'required',
            'state'=> 'required',
            'postcode' => 'required',
            'country' => 'required',
        ]);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $client->update();

            DB::commit();
            flash()->success('Successfully updated client.');
            return redirect()->route('admin.clients.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error("There was an issue updating the client profile. Error: " . $exception->getMessage());
            return back()->withInput();
        }
    }
}
