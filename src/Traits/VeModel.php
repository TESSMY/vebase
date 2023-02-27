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
    public array $searchable = [];

    /**
     * If there are any files that can be uploaded / stored during creation or edit
     *
     * @var array
     */
    public array $files = [];

    /**
     * The relations that should be loaded in the `index` or `show` functions for api
     * Make sure to add the relation in the model before adding it here or the relatable will not work
     * @var array
     */
    public array $relatable = [];

    /**
     * The fields that can be sorted in asc or desc in the `index` functions
     * @var array
     */
    public array $sortable = [];

    /**
     * The fields that are to be shown in the `index` page
     * E.g. id, name
     * @var array
     */
    public array $indexFields = [];

    /**
     * The fields that are needed during creation along with its type
     * E.g. [
     *  'required' => 'required',
     *  'type' => 'string',
     *  'name' => 'name',
     *  'displayName' => 'Name'
     * ],
     *
     * @var array
     */
    public array $createFields = [];

    /**
     * The fields that are needed during creation
     * E.g. [
     *     'required' => 'required',
     *     'type' => 'string',
     *     'name' => 'name',
     *     'displayName' => 'Name'
     * ],
     *
     * @var array
     */
    public array $createValidator = [];

    /**
     * The fields that are needed during update along with its type
     * E.g. [
     *     'required' => 'required',
     *     'type' => 'string',
     *     'name' => 'name',
     *     'displayName' => 'Name'
     * ],
     *
     * @var array
     */
    public array $updateFields = [];

    /**
     * The fields that are needed during update
     * E.g. 'name' => 'required|min:3'
     *
     * @var array
     */
    public array $updateValidator = [];

    abstract public function hasApiResourceRoute() : bool;

    abstract public function hasAdminResourceRoute() : bool;

    abstract public function hasDashboardResourceRoute() : bool;

    abstract public function getParentClass() : string|null;


}
