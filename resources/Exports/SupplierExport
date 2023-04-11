<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupplierExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Supplier::all();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Code',
            'Contact Name',
            'Contact Number',
            'Address 1',
            'Address 2',
            'City',
            'State',
            'Postcode',
            'Country',
            'Status',
        ];
    }

    public function map($supplier): array
    {
        $data = [
            $supplier->name,
            $supplier->email,
            $supplier->code,
            $supplier->contact_name,
            $supplier->contact_number,
            $supplier->address_1,
            $supplier->address_2,
            $supplier->city,
            $supplier->state,
            $supplier->postcode,
            $supplier->country,
            $supplier->status == Supplier::STATUS_ACTIVE ? 'Active' : 'Disabled',
        ];
        return $data;
    }
}
