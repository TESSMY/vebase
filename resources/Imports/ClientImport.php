<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClientImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Client([
            'name' => $row['name'],
            'email' => $row['email'],
            'company_name' => $row['company_name'],
            'phone' => $row['phone'],
            'address_1' => $row['address_1'],
            'address_2' => $row['address_2'],
            'city' => $row['city'],
            'state' => $row['state'],
            'postcode' => $row['postcode'],
            'country' => $row['country'],
            'image' => null,
            'status' => Client::STATUS_ACTIVE,
        ]);
    }

    public function rules(): array
    {
        $client = new Client();
        return $client->createValidator;
    }
}
