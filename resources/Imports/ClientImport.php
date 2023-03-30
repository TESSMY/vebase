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
            'code' => $row[1],
            'email' => $row[2],
            'address' => $row[3],
            'city' => $row[4],
            'state' => $row[5],
            'zip' => $row[6],
            'country' => $row[7],
            'pic_name' => $row[8],
            'pic_email' => $row[9],
            'pic_phone' => $row[10],
            'phone' => $row[11],
        ]);
    }
}