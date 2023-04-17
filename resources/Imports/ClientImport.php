<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
        $client = Client::where([['name', $row['name']], ['email', $row['email']], ['company_name', $row['company_name']], ['phone', $row['phone']]])->first();
        if (empty($client)) {
            if ($row['status'] == Client::STATUS_ACTIVE || strtolower($row['status']) == 'active') {
                $status = Client::STATUS_ACTIVE;
            } else {
                $status = Client::STATUS_DISABLED;
            }

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
                'status' => $status,
            ]);
        }
    }

    public function rules(): array
    {
        $client = new Client();
        return $client->createValidator;
    }
}
