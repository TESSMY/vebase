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
     * Used in VeController findModel()
     */
    public static bool $resourceWithTrashed = false;

    /**
     *
     * Observers that are to be used by the current model
     */
    public $observers = [];

    /**
     *
     * The fields that can be searched in the `index` functions
     * Make sure if there are a lot of fields, a composite index is used
     */
    public $searchable = [];

    /**
     *
     * If there are any files that can be uploaded / stored during creation or edit
     */
    public $files = [];

    /**
     *
     * The relations that should be loaded in the `index` or `show` functions for api
     * Make sure to add the relation in the model before adding it here or the relatable will not work
     */
    public $relatable = [];

    /**
     *
     * The fields that can be sorted in asc or desc in the `index` functions
     */
    public $sortable = [];

    /**
     *
     *  The fields that are needed for filters
     *  E.g. [
     *      'name' => 'name',
     *      'displayName' => 'Name',
     *      'where' => [['status', '=', 'active']],
     *      'class' => Category::class,
     *      'key' => 'key',
     *      'value' => 'value',
     *  ],
     */
    public $filters = [];

    /**
     *
     *  The fields that are to be shown in the `index` page
     *  E.g. id, name
     */
    public $indexFields = [];

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
    public $createFields = [];

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
    public $createValidator = [];

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
    public $updateFields = [];

    /**
     *
     *  The fields that are needed during update
     *  E.g. 'name' => 'required|min:3'
     */
    public $updateValidator = [];

    /**
     *  Register route resource except those stated
     */
    public $routesExcept = [];

    /**
     *  Register route resource except those stated
     */
    public $routesOnly = [];

    /*
     * Icon for sidebar
     */
    public $icon = '';

    /*
     * Sidebar name - if planning to override
     */
    public $sidebarName = '';

    /*
     * Sidebar order - in ascending order
     */
    public $sidebarOrder = 0;

    /*
     * Permissions needed or used - to disable, either set this to empty or override hasPolicies()
     */
    public $permissionsList = [
        'view', 'create', 'edit', 'delete'
    ];

    /**
     * Whether to include resource route for API
     */
    public $hasApiResource = false;

    /**
     * Whether or not to include resource route for Admin route
     */
    public $hasAdminResource = true;

    /**
     * @return bool
     * Register route resource for api
     */
    public function hasApiResourceRoute()
    {
        return $this->hasApiResource;
    }

    /**
     * @return bool
     *  Register route resource for admin
     */
    public function hasAdminResourceRoute()
    {
        return $this->hasAdminResource;
    }

    public function hasPolicies()
    {
        return count($this->permissionsList) > 0;
    }

    public function createValidator()
    {
        return [];
    }

    public function updateValidator()
    {
        return [];
    }
}
