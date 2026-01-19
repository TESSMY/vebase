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
            $key = $column;
            if (is_array($column)) {
                $key = $column['key'];
                $value = $row[$column['value']];
                if (!empty($column['from_array'])) {
                    $value = array_search($value, $column['from_array']);
                    if ($value === false) {
                        if (!empty($column['default'])) {
                            $value = $column['default'];
                        } else {
                            throw new \Exception('Value for ' . $key . ' incorrect: ' . $row[$column['value']]);
                        }
                    }
                }
            } else {
                $value = $row[$column];
            }
            $values[$key] = $value;
        }
        $primary = $this->model->importUniqueColumn;

        return $this->model::updateOrCreate([$primary => $values[$primary]], $values);
    }

    public function chunkSize(): int
    {
        return config('excel.chunk_size');
    }
}
