<?php

namespace Vecapital\Vebase;

use Illuminate\Support\Facades\Route;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Str;
use Vecapital\Vebase\Http\Controllers\VeApiController;
use Vecapital\Vebase\Http\Controllers\VeController;
use Vecapital\Vebase\Traits\VeModel;

class VeHelper
{
    public static function adminRoutes()
    {
        $classes = ClassFinder::getClassesInNamespace('App\Models');

        foreach ($classes as $class) {
            $class = new $class();
            if ($class instanceof VeModel && $class->hasAdminResourceRoute()) {
                $name = (new \ReflectionClass($class))->getShortName();
                $overrideClass = 'App\\Http\\Controllers\\Admin\\' . $name . 'Controller';
                $controller = class_exists($overrideClass) ? $overrideClass : VeController::class;
                $name = Str::plural(Str::kebab($name));

                if (!empty($class->importExport)) {
                    if (!$class->disableExport) {
                        Route::post(strtolower($name) . '/import', $controller . '@import')->name(strtolower($name) . '.import');
                    }
                    if (!$class->disableImport) {
                        Route::get(strtolower($name) . '/export', $controller . '@export')->name(strtolower($name) . '.export');
                    }
                }

                if (!empty($class->routesExcept)) {
                    Route::resource(strtolower($name), $controller)->except($class->routesExcept);
                } elseif (!empty($class->routesOnly)) {
                    Route::resource(strtolower($name), $controller)->only($class->routesOnly);
                } else {
                    Route::resource(strtolower($name), $controller);
                }
            }
        }
    }

    public static function apiRoutes()
    {
        $classes = ClassFinder::getClassesInNamespace('App\Models');

        foreach ($classes as $class) {
            $class = new $class();
            if ($class instanceof VeModel && $class->hasApiResourceRoute()) {
                $name = (new \ReflectionClass($class))->getShortName();
                $overrideClass = 'App\\Http\\Controllers\\Api\\' . $name . 'Controller';
                $class = class_exists($overrideClass) ? $overrideClass : VeApiController::class;
                $name = Str::plural(Str::kebab($name));
                Route::apiResource(strtolower($name), $class);
            }
        }
    }
}
