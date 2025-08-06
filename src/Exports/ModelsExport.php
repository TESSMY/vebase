<?php

namespace Vecapital\Vebase\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ModelsExport implements FromCollection
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
}
