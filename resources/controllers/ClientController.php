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

        $validator = Validator::make($input, [
            'name' => 'required|min:3',
            'email' => 'required|unique:clients,email,null,id,deleted_at,NULL',
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

            $input['password'] = Hash::make($input['phone']);
            $client = Client::create($input);

            DB::commit();
            flash()->success('Successfully created client.');
            return redirect()->route('admin.clients.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an issue creating client profile.');
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

            Client::update($input);

            DB::commit();
            flash()->success('Successfully created client.');
            return redirect()->route('admin.clients.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error('There was an issue creating client profile.');
            return back()->withInput();
        }
    }
}
