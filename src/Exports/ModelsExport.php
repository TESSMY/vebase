<?php

namespace Vecapital\Vebase\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ModelsExport implements FromCollection, WithHeadingRow, WithMapping
{
    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->model::all();
    }

    public function headings(): array
    {
        $headers = array_keys($this->model->exportImport);

        return $headers;
    }

    public function map($model): array
    {
        $columns = array_values($this->model->exportImport);

        $map = [];
        foreach ($columns as $column) {
            $map[] = $model[$column];
        }

        return $map;
    }
}
