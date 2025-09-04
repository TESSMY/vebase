<?php

namespace Vecapital\Vebase\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ModelsExport implements FromQuery, WithHeadings, WithMapping, WithCustomQuerySize, ShouldQueue, ShouldAutoSize
{
    use Exportable;

    public $model;

    public function __construct($model)
    {
        ini_set('max_execution_time', 0);
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
        $headers = array_keys($this->model->importExport);

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
