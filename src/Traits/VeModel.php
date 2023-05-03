<?php

namespace Vecapital\Vebase\Traits;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

abstract class VeModel extends Model
{
    use Sortable;
    
    /**
     * The fields that can be searched in the `index` functions
     * Make sure if there are a lot of fields, a composite index is used
     *
     */
    public $searchable = [];

    /**
     * If there are any files that can be uploaded / stored during creation or edit
     *
     */
    public $files = [];

    /**
     * The relations that should be loaded in the `index` or `show` functions for api
     * Make sure to add the relation in the model before adding it here or the relatable will not work
     */
    public $relatable = [];

    /**
     * The fields that can be sorted in asc or desc in the `index` functions
     */
    public $sortable = [];

    /**
     * The fields that are to be shown in the `index` page
     * E.g. id, name
     */
    public $indexFields = [];

    /**
     * The fields that are needed during creation along with its type
     * E.g. [
     *  'required' => 'required',
     *  'type' => 'string',
     *  'name' => 'name',
     *  'displayName' => 'Name'
     * ],
     *
     */
    public $createFields = [];

    /**
     * The fields that are needed during creation
     * E.g. [
     *     'required' => 'required',
     *     'type' => 'string',
     *     'name' => 'name',
     *     'displayName' => 'Name'
     * ],
     *
     */
    public $createValidator = [];

    /**
     * The fields that are needed during update along with its type
     * E.g. [
     *     'required' => 'required',
     *     'type' => 'string',
     *     'name' => 'name',
     *     'displayName' => 'Name'
     * ],
     *
     */
    public $updateFields = [];

    /**
     * The fields that are needed during update
     * E.g. 'name' => 'required|min:3'
     *
     */
    public $updateValidator = [];

    abstract public function hasApiResourceRoute() : bool;

    abstract public function hasAdminResourceRoute() : bool;

    abstract public function hasDashboardResourceRoute() : bool;

    abstract public function getParentClass() : string|null;


}
