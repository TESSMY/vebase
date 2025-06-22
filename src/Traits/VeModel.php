<?php

namespace Vecapital\Vebase\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

abstract class VeModel extends Model
{
    use Sortable, HasFactory;

    /**
     *
     * Observers that are to be used by the current model
     */
    public array $observers = [];

    /**
     *
     * The fields that can be searched in the `index` functions
     * Make sure if there are a lot of fields, a composite index is used
     */
    public array $searchable = [];

    /**
     *
     * If there are any files that can be uploaded / stored during creation or edit
     */
    public array $files = [];

    /**
     *
     * The relations that should be loaded in the `index` or `show` functions for api
     * Make sure to add the relation in the model before adding it here or the relatable will not work
     */
    public array $relatable = [];

    /**
     *
     * The fields that can be sorted in asc or desc in the `index` functions
     */
    public array $sortable = [];

    /**
     *
     *  The fields that are to be shown in the `index` page
     *  E.g. id, name
     */
    public array $indexFields = [];

    /**
     *
     *  The fields that are needed during creation along with its type
     *  E.g. [
     *   'required' => 'required',
     *   'type' => 'text',
     *   'name' => 'name',
     *   'displayName' => 'Name'
     *  ],
     */
    public array $createFields = [];

    /**
     *  !! This is deprecated. Please use createValidator() instead
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
     *
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
     *
     *  The fields that are needed during update
     *  E.g. 'name' => 'required|min:3'
     */
    public array $updateValidator = [];

    /**
     *  Register route resource except those stated
     */
    public array $routesExcept = [];

    /**
     *  Register route resource except those stated
     */
    public array $routesOnly = [];

    /*
     * Icon for sidebar
     */
    public String $icon = '';

    /*
     * Sidebar name - if planning to override
     */
    public String $sidebarName = '';

    /*
     * Sidebar order - in ascending order
     */
    public int $sidebarOrder = 0;

    /*
     * Permissions needed or used - to disable, either set this to empty or override hasPolicies()
     */
    public $permissionsList = [
        'view', 'create', 'edit', 'delete'
    ];

    /**
     * Whether or not to include resource route for API
     */
    public bool $hasApiResource = false;

    /**
     * Whether or not to include resource route for Admin route
     */
    public bool $hasAdminResource = true;

    /**
     * @return bool
     * Register route resource for api
     */
    public function hasApiResourceRoute(): bool
    {
        return $this->hasApiResource;
    }

    /**
     * @return bool
     *  Register route resource for admin
     */
    public function hasAdminResourceRoute(): bool
    {
        return $this->hasAdminResource;
    }

    public function hasPolicies(): bool
    {
        return count($this->permissionsList) > 0;
    }

    public function createValidator(): array
    {
        return [];
    }

    public function updateValidator(): array
    {
        return [];
    }
}
