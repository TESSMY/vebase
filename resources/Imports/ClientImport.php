<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;

class ClientImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Client([
            'name' => $row[0],
            'email' => $row[1],
            'company_name' => $row[2],
            'phone' => $row[3],
            'address_1' => $row[4],
            'address_2' => $row[5],
            'city' => $row[6],
            'state' => $row[7],
            'postcode' => $row[8],
            'country' => $row[9],
            'image' => null,
            'status' => Client::STATUS_ACTIVE,
        ]);
    }
}
