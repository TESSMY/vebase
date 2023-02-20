<?php

namespace Vecapital\Vebase\Traits;

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

    /**
     * The relations that should be loaded in the `index` or `show` functions
     * Make sure to add the relation in the model before adding it here or the relatable will not work
     * @var array
     */
    protected array $relatable = [];

    /**
     * The fields that can be sorted in asc or desc in the `index` functions
     * @var array
     */
    protected array $sortable = [];

    /**
     * The fields that are needed during creation
     * E.g. 'email' => 'required|unique:users,email,' . $this->id . ',id,deleted_at,NULL',
     *
     * @var array
     */
    protected array $create = [];

    /**
     * The fields that are needed during update
     * E.g. 'name' => 'required|min:3'
     *
     * @var array
     */
    protected array $update = [];

    abstract public function hasApiResourceRoute() : bool;

    abstract public function hasAdminResourceRoute() : bool;

    abstract public function hasDashboardResourceRoute() : bool;

    abstract public function getParentClass() : string|null;


}
