<?php

namespace Vecapital\Vebase\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ModelsImport implements ToModel, WithBatchInserts, WithUpserts, WithChunkReading
{
    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return $this->model->importUniqueColumn;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        $values = [];
        foreach ($this->model->importExport as $key => $column) {
            $values[$column] = $row[$key];
        }

        return new $this->model($values);
    }
}
