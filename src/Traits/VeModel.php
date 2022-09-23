<?php

namespace App\Traits;

trait VeModel
{

    /**
     * The fields that can be searched in the `index` functions
     * Make sure if there are a lot of fields, a composite index is used
     *
     * @var array
     */
    protected array $searchable = [];

    /**
     * If there are any files that can be uploaded / stored during creation or edit
     *
     * @var array
     */
    protected array $files = [];

    abstract public function hasApiResourceRoute() : bool;

    abstract public function hasAdminResourceRoute() : bool;

    abstract public function hasDashboardResourceRoute() : bool;

    abstract public function getParentClass() : string|null;


}
