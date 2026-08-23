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

    /**
     * Ceiling for the export's execution time, in seconds. Overridable via config so a slow
     * export can be given room without handing an unauthenticated-in-effect endpoint the
     * ability to pin a PHP worker forever, which `max_execution_time = 0` did.
     */
    public const DEFAULT_TIMEOUT = 300;

    public $model;

    /**
     * Cached column map, so map() does not re-read and re-walk $importExport for every row.
     */
    protected array $columns;

    public function __construct($model)
    {
        $this->model = $model;
        $this->columns = array_values($model->importExport);

        @ini_set('max_execution_time', (string) config('vebase.export_timeout', self::DEFAULT_TIMEOUT));
    }

    public function query()
    {
        return $this->model::query();
    }

    /**
     * Total rows to export.
     *
     * This was hardcoded to 1000, which is the number of rows the chunked reader was told
     * existed -- so any table with more than 1000 rows silently exported only the first 1000.
     */
    public function querySize(): int
    {
        return $this->query()->count();
    }

    public function headings(): array
    {
        return array_keys($this->model->importExport);
    }

    public function map($model): array
    {
        $map = [];

        foreach ($this->columns as $column) {
            // The plain-string branch used to assign $column itself, so a simple
            // 'Name' => 'name' mapping wrote the literal string "name" into every row
            // instead of the record's value.
            $map[] = is_array($column)
                ? $model->{$column['value']}
                : $model->{$column};
        }

        return $map;
    }
}
