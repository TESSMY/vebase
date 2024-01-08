<?php

namespace Vecapital\Vebase\Traits;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

abstract class VeModel extends Model
{
    use Sortable;

    /**
     * @var array
     * The fields that can be searched in the `index` functions
     * Make sure if there are a lot of fields, a composite index is used
     */
    public array $searchable = [];

    /**
     * @var array
     * If there are any files that can be uploaded / stored during creation or edit
     */
    public array $files = [];

    /**
     * @var array
     * The relations that should be loaded in the `index` or `show` functions for api
     * Make sure to add the relation in the model before adding it here or the relatable will not work
     */
    public array $relatable = [];

    /**
     * @var array
     * The fields that can be sorted in asc or desc in the `index` functions
     */
    public array $sortable = [];

    /**
     * @var array
     *  The fields that are to be shown in the `index` page
     *  E.g. id, name
     */
    public array $indexFields = [];

    /**
     * @var array
     *  The fields that are needed during creation along with its type
     *  E.g. [
     *   'required' => 'required',
     *   'type' => 'string',
     *   'name' => 'name',
     *   'displayName' => 'Name'
     *  ],
     */
    public array $createFields = [];

    /**
     * @var array
     *  The fields that are needed during creation
     *  E.g. [
     *      'required' => 'required',
     *      'type' => 'string',
     *      'name' => 'name',
     *      'displayName' => 'Name'
     *  ],
     */
    public array $createValidator = [];

    /**
     * @var array
     *  The fields that are needed during update along with its type
     *  E.g. [
     *      'required' => 'required',
     *      'type' => 'string',
     *      'name' => 'name',
     *      'displayName' => 'Name'
     *  ],
     */
    public array $updateFields = [];

    /**
     * @var array
     *  The fields that are needed during update
     *  E.g. 'name' => 'required|min:3'
     */
    public array $updateValidator = [];

    /**
     * @return bool
     * Register route resource for api
     */
    abstract public function hasApiResourceRoute(): bool;

    /**
     * @return bool
     *  Register route resource for admin
     */
    abstract public function hasAdminResourceRoute(): bool;

    abstract public function getParentClass(): ?string;

    public function hasPolicies(): bool
    {
        return false;
    }
}
