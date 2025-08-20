<?php

namespace Vecapital\Vebase\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Contracts\Queue\ShouldQueue;

class ModelsImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
        $values = [];
        foreach ($this->model->importExport as $key => $column) {
            $values[$column] = $row[$column];
        }

        return $this->model::updateOrCreate([$this->model->importUniqueColumn => $values[$this->model->importUniqueColumn]], $values);
    }

    public function chunkSize(): int
    {
        return config('excel.chunk_size');
    }
}
