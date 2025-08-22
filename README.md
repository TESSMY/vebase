

# VE Base

VE Base is built on top of Laravel framework to allow for minimal development effort and provide a highly customizable and modular development. This source code is only to be used by is authors and is not to be used for any purposes. Any unauthorized usage is strictly prohibited.

---

## Issues with composer
1. Host key verification failed.\
   Run `ssh -T git@bitbucket.org`


2. Permission denied (publickey).\
   Generate ssh and add key to bitbucket


---

## Installation
Add this line in composer.json

Bitbucket
```
"repositories": [
   ...,
   {
      "type": "vcs",
      "url":  "git@bitbucket.org:pixelafy/vebase.git"
   }
],
```

Github
```
"repositories": [
   ...,
   {
      "type": "vcs",
      "url":  "git@github.com:TESSMY/vebase.git"
   }
],
```

Run the command to install the package
```
composer install vecapital/vebase
```

If there are updates to this package run the following command
```
composer update vecapital/vebase
```


---

## Basic Usage

Naming of models MUST be following laravel conventions - singular for Model names.

For base models that need routing, the model should extend the VeModel.

---

## Using VeController

To perform additional actions after the default creation, updating, deletion - use observers.

[Laravel Observer Documentation](https://laravel.com/docs/10.x/eloquent#observers)

---

## Overriding VeController

To override the VeController, just create a controller to extend the VeController.

This also allows you to add new functions but you will need to manually register those routes.
```
<?php

use Vecapital\Vebase\Http\Controllers\VeController;

class InvoiceController extends VeController
{
   ...
}

```

---

## Declaring observers
To declare observers, you can do so in the model
```
class User {
    ...

    public $observers = [
        UserObserver::class,
    ];

    ...
}
```
---

## Using the default form
If you wish to use the default form, you can do so as shown below by calling `@include('vebase::form')` in your blade file
```
@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
       @include('vebase::form')
    </div>
@endsection
```
---

## Using the default table
If you wish to use the default table, you can do so as shown below by calling `@include('vebase::common.table')` in your blade file
```
@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
       @include('vebase::common.table')
    </div>
    <div class="container-fluid">
       @include('vebase::common.paginator') // Note: this needs to be include for pagination of the table to work
    </div>
@endsection
```
---

## Model Route Resource
You do not need to register routes unless it is a custom route. You can enable the route resource by doing so in the model as shown below by either returning `true` or `false`
```
class User {
    ...

    public function hasApiResourceRoute(): bool
    {
        return false;
    }

    public function hasAdminResourceRoute(): bool
    {
        return false;
    }

    public function hasDashboardResourceRoute(): bool
    {
        return false;
    }

    public function getParentClass(): string|null
    {
        return null;
    }

    ...
}
```
---

## Indexfield for `$indexFields`
```
public $indexFields = [
    [
        'displayName' => '',
        'columnName' => 'image',
        'type' => 'image'
    ],
    [
        'displayName' => 'ID',
        'columnName' => 'id',
    ],
    [
        'displayName' => 'Created By',
        'columnName' => 'created_by', // foreign key column
        'type' => 'relation',
        'relation' => 'createdBy', // relation must follow exactly as defined in the model
        'relatedColumnName' => 'name', // relation column name
    ],
    [
        'displayName' => 'Status',
        'columnName' => 'status_text', // this can be appended fields or col from the backend
        'type' => 'span'
        'class' => 'status_btn', // you can put css class here or put the css into the appended fields
    ],
    [
        'displayName' => 'Selling Price',
        'columnName' => 'selling_price', 
        'type' => 'decimal' // format value to the decimal point
        'decimal' => '2' // decimal point
    ],
    [
        'displayName' => 'Selling Price',
        'columnName' => 'selling_price', 
        'type' => 'decimal_with_currency'
        'currency' => 'SGD' // can specify your own currency
    ],
    [
       'displayName' => 'Selling Price',
        'columnName' => 'selling_price', 
        'type' => 'dollar_decimal' // display with the '$' in front of the value
        'decimal' => '2' // decimal point
    ],
    [
        'displayName' => 'HTML',
        'columnName' => 'html', 
        'type' => 'html' // html type
        'html' => '<div>Hi</div>' // write your own html if you need
    ],
    [
        'displayName' => 'Actions',
        'columnName' => 'edit',
    ],
    [
        'displayName' => 'Actions',
        'columnName' => 'show',
    ],
    [
        'displayName' => 'Actions',
        'columnName' => 'show_and_edit',
    ],
];
```
---

## Overriding index table
Below are the option to override the index table. The order in which the files take precendence is shown below.
1. index.blade.php
2. index-header.php
3. index-table-head.blade.php
4. index-table-th.blade.php
5. index-table-body.blade.php
6. index-table-tr.blade.php
---

## Overriding views
Below are the option to override the views. By default the views should display if the `Model Route Resource` is set to true.
1. index.blade.php
2. create.blade.php
3. show.blade.php
4. edit.blade.php

---

## ShowFields for `$showFields`
```
public $showFields = [
    [
        'displayName' => 'Code',
        'columnName' => 'code',
    ],
    [
        'displayName' => 'Name',
        'columnName' => 'name',
    ],
    [
        'displayName' => 'Total Products',
        'columnName' => 'total_products',
    ],
];
```
---

## Form input examples for `$createFields` & `$updateFields`
```
public $createFields = [
    [
        'size' => 'col-md-6',
        'required' => 'true',
        'inputType' => 'textarea',
        'name' => 'textarea',
        'displayName' => 'textarea',
        'placeholder' => 'placeholder',
        'rows' => 5,
    ],
    [
        'size' => 'col-md-6',
        'required' => 'true',
        'inputType' => 'color',
        'name' => 'color',
        'displayName' => 'color',
        'placeholder' => 'placeholder',
    ],
    [
        'size' => 'col-md-2',
        'required' => 'true',
        'inputType' => 'file',
        'name' => 'file',
        'displayName' => 'file',
        'placeholder' => 'placeholder',
    ],
    [
        'size' => 'col-md-2',
        'required' => 'true',
        'inputType' => 'text',
        'name' => 'text',
        'displayName' => 'text',
        'placeholder' => 'placeholder',
    ],
    [
        'size' => 'col-md-2',
        'type' => 'readonly',
        'inputType' => 'text',
        'name' => 'text',
        'displayName' => 'text',
        'placeholder' => 'readonly',
    ],
    [
        'size' => 'col-md-2',
        'type' => 'disabled',
        'inputType' => 'text',
        'name' => 'text',
        'displayName' => 'text',
        'placeholder' => 'disabled',
    ],
    [
        'size' => 'col-md-2',
        'type' => '',
        'inputType' => 'number',
        'name' => 'number',
        'displayName' => 'number',
        'placeholder' => 'number',
        'min' => '0',
        'max' => '10',
        'step' => '0.5',
    ],
    [
        'size' => 'col-md-12',
        'type' => '',
        'inputType' => 'range',
        'name' => 'range',
        'displayName' => 'range',
        'placeholder' => 'range',
        'min' => '0',
        'max' => '10',
        'step' => '0.5',
    ],
    [
        'size' => 'col-md-4',
        'required' => 'true',
        'inputType' => 'email',
        'name' => 'email',
        'displayName' => 'email',
        'placeholder' => 'placeholder',
    ],
    [
        'size' => 'col-md-4',
        'required' => 'true',
        'inputType' => 'password',
        'name' => 'password',
        'displayName' => 'password',
        'placeholder' => 'placeholder',
    ],
    [
        'size' => 'col-md-4',
        'required' => 'true',
        'inputType' => 'select',
        'name' => 'name',
        'displayName' => 'Name',
        'options' => [
            'a' => '1',
            'b' => '2',
            'c' => '3',
        ],
    ],
    [
        'size' => 'col-md-2',
        'type' => '',
        'inputType' => 'radio',
        'name' => 'name',
        'displayName' => 'Name',
        'displayValue' => 'true',
        'value' => 'true',
        'id' => 'radio1',
    ],
    [
        'size' => 'col-md-2',
        'type' => '',
        'inputType' => 'radio',
        'multipleInput' => 'true',
        'displayName' => 'Name',
        'displayValue' => 'false',
        'value' => 'true',
        'options' => [
            [
                'type' => '',
                'inputType' => 'radio',
                'name' => 'name2',
                'displayName' => 'Name',
                'displayValue' => 'abc',
                'value' => 'abc',
                'id' => 'radio2',
            ],
            [
                'type' => '',
                'inputType' => 'radio',
                'name' => 'name2',
                'displayName' => 'Name',
                'displayValue' => '123',
                'value' => '123',
                'id' => 'radio3',
            ]
        ]
    ],
    [
        'size' => 'col-md-2',
        'type' => '',
        'inputType' => 'checkbox',
        'switchType' => 'true',
        'multipleInput' => 'true',
        'displayName' => 'Name',
        'displayValue' => 'false',
        'value' => 'true',
        'options' => [
            [
                'type' => '',
                'inputType' => 'checkbox',
                'name' => 'switch1',
                'displayName' => 'Name',
                'displayValue' => 'abc',
                'value' => 'abc',
                'id' => 'switch1',
            ],
            [
                'type' => '',
                'inputType' => 'checkbox',
                'name' => 'switch2',
                'displayName' => 'Name',
                'displayValue' => '123',
                'value' => '123',
                'id' => 'switch2',
            ]
        ]
    ],
    [
        'size' => 'col-md-2',
        'type' => '',
        'inputType' => 'checkbox',
        'name' => 'name',
        'displayName' => 'checkbox 1',
        'displayValue' => 'true',
        'value' => 'true',
        'id' => 'checkbox'
    ],
    [
        'size' => 'col-md-2',
        'type' => '',
        'inputType' => 'checkbox',
        'multipleInput' => 'true',
        'displayName' => 'checkbox 2',
        'displayValue' => 'false',
        'value' => 'true',
        'options' => [
            [
                'type' => '',
                'inputType' => 'checkbox',
                'name' => 'checkbox1',
                'displayName' => 'Name',
                'displayValue' => 'abc',
                'value' => 'abc',
                'id' => 'checkbox1',
            ],
            [
                'type' => '',
                'inputType' => 'checkbox',
                'name' => 'checkbox2',
                'displayName' => 'Name',
                'displayValue' => '123',
                'value' => '123',
                'id' => 'checkbox2',
            ]
        ]
    ],
    [
        'size' => 'col-md-3',
        'required' => 'true',
        'inputType' => 'month',
        'name' => 'month',
        'displayName' => 'month',
        'placeholder' => 'month',
    ],
    [
        'size' => 'col-md-3',
        'required' => 'true',
        'inputType' => 'date',
        'name' => 'date',
        'displayName' => 'date',
        'placeholder' => 'date',
    ],
    [
        'size' => 'col-md-3',
        'required' => 'true',
        'inputType' => 'time',
        'name' => 'time',
        'displayName' => 'time',
        'placeholder' => 'time',
    ],
    [
        'size' => 'col-md-3',
        'required' => 'true',
        'inputType' => 'week',
        'name' => 'week',
        'displayName' => 'week',
        'placeholder' => 'week',
    ],
    [
        'size' => 'col-md-3',
        'required' => 'true',
        'inputType' => 'url',
        'name' => 'url',
        'displayName' => 'url',
        'placeholder' => 'url',
    ],
    [
        'size' => 'col-md-3',
        'type' => 'required',
        'inputType' => 'countryselect',
        'name' => 'country',
        'dataName' => 'name',
        'displayName' => 'Country',
    ],
    [
        'size' => 'col-md-3',
        'type' => 'required',
        'inputType' => 'tagging',
        'name' => 'name',
        'displayName' => 'displayName',
        'trackBy' => 'code' ,
        'label' => 'tagName',
        'allowAddNewTag' => 'true',
        'options' => [
            [
                'tagName' => 'TEST1',
                'code' => 'code1',
            ],
            [
                'tagName' => 'TEST22',
                'code' => 'code2',
            ]
        ],
    ]
];
```