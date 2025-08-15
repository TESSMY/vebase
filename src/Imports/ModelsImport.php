<?php

namespace Vecapital\Vebase\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Contracts\Queue\ShouldQueue;

class ModelsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts, WithChunkReading, ShouldQueue
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
//        $values = [];
//        foreach ($this->model->importExport as $key => $column) {
//            $values[$column] = $row[$column];
//        }
//        if (empty($values['name'])) {
//            dd($values);
//        }
//

        return $this->model::make([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => Hash::make('12345678'),
        ]);
    }
}
