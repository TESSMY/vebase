<?php

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Facades\Route;
use Vecapital\Vebase\Http\Controllers\VeController;

$classes = ClassFinder::getClassesInNamespace('App\Models');

foreach ($classes as $class) {
    if (in_array(\Vecapital\Vebase\Traits\VeModel::class, class_uses_recursive($class))) {
        $class = new $class();
        if ($class->hasApiResourceRoute()) {
            $name = (new \ReflectionClass($class))->getShortName();
            $overrideClass = 'App\\Controllers\\' . $name . 'Controller';
            $class = class_exists($overrideClass) ? $overrideClass : VeController::class;
            Route::resource(\Illuminate\Support\Str::plural($name), $class);
        }
    }
}