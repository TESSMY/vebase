<?php

namespace Vecapital\Vebase\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ModelsImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
     * Sentinel for "this column was not in the row", so a legitimate null value is not
     * mistaken for a missing column.
     */
    protected const MISSING = "\0__vebase_missing__\0";

    /**
     * Rows per chunk when config('excel.chunk_size') is unset -- the config call returns null
     * there, and this method is typed `: int`, so an app without the published excel config
     * hit a TypeError before reading a single row.
     */
    public const DEFAULT_CHUNK_SIZE = 1000;

    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param array $row
     */
    public function model(array $row)
    {
        $values = [];

        foreach ($this->model->importExport as $heading => $column) {
            $key = is_array($column) ? $column['key'] : $column;

            $value = $this->valueFromRow($row, $heading, $column);

            // A spreadsheet whose headings do not match the mapping used to raise an undefined
            // key warning and then quietly write null into the column.
            if ($value === self::MISSING) {
                throw new \Exception('Missing column "'.$heading.'" in the imported file.');
            }

            if (is_array($column) && ! empty($column['from_array'])) {
                $mapped = array_search($value, $column['from_array']);
                if ($mapped === false) {
                    if (! isset($column['default'])) {
                        throw new \Exception('Value for '.$key.' incorrect: '.$value);
                    }
                    $mapped = $column['default'];
                }
                $value = $mapped;
            }

            $values[$key] = $value;
        }

        $primary = $this->model->importUniqueColumn;

        // updateOrCreate() with a null match value matches the first row with a null there,
        // so a row that carries no key must not be allowed to overwrite an unrelated record.
        if (! array_key_exists($primary, $values) || $values[$primary] === null || $values[$primary] === '') {
            throw new \Exception('Missing value for the unique column "'.$primary.'" in the imported file.');
        }

        return $this->model::updateOrCreate([$primary => $values[$primary]], $values);
    }

    public function chunkSize(): int
    {
        return (int) (config('excel.chunk_size') ?: self::DEFAULT_CHUNK_SIZE);
    }

    /**
     * Reads one mapped column out of an imported row.
     *
     * WithHeadingRow keys the row by the *heading* -- slugged by default, raw under the "none"
     * formatter -- while $importExport is written from the model's side. The array form read
     * $row[$column['value']], the model's accessor name, which is not a heading at all: a file
     * produced by ModelsExport could not be read back by ModelsImport. All three spellings are
     * accepted so existing hand-built sheets keep working.
     */
    protected function valueFromRow(array $row, $heading, $column)
    {
        $candidates = [Str::slug((string) $heading, '_'), $heading];
        $candidates[] = is_array($column) ? ($column['value'] ?? null) : $column;

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && array_key_exists($candidate, $row)) {
                return $row[$candidate];
            }
        }

        return self::MISSING;
    }
}
