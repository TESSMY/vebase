<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SupplierImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $supplier = Supplier::where([['name', $row['name']], ['email', $row['email']], ['code', $row['code']], ['contact_name', $row['contact_name']], ['contact_number', $row['contact_number']]])->first();
        if (empty($supplier)) {
            if ($row['status'] == Supplier::STATUS_ACTIVE || strtolower($row['status']) == 'active') {
                $status = Supplier::STATUS_ACTIVE;
            } else {
                $status = Supplier::STATUS_DISABLED;
            }

            return new Supplier([
                'name' => $row['name'],
                'email' => $row['email'],
                'code' => $row['code'],
                'contact_name' => $row['contact_name'],
                'contact_number' => $row['contact_number'],
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
        $supplier = new Supplier();
        return $supplier->createValidator;
    }
}
