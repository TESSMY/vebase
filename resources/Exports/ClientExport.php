<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Client::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Company Name',
            'Phone',
            'Address 1',
            'Address 2',
            'City',
            'State',
            'Postcode',
            'Country',
            'Status',
        ];
    }

    public function map($client): array
    {
        $data = [
            $client->id,
            $client->name,
            $client->email,
            $client->company_name,
            $client->phone,
            $client->address_1,
            $client->address_2,
            $client->city,
            $client->state,
            $client->postcode,
            $client->country,
            $client->status == Client::STATUS_ACTIVE ? 'Active' : 'Disabled',
        ];
        return $data;
    }
}
