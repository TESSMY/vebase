<?php

namespace Vecapital\Vebase\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;

class ModelsExport implements FromQuery, WithHeadingRow, WithMapping, WithCustomQuerySize, ShouldQueue
{
    use Exportable;

    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function query()
    {
        return $this->model::query();
    }

    public function querySize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        $headers = array_keys($this->model->exportImport);

        return $headers;
    }

    public function map($model): array
    {
        $columns = array_values($this->model->importExport);

        $map = [];
        foreach ($columns as $column) {
            $map[] = $model[$column];
        }

        return $map;
    }
}
