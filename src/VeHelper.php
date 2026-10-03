<?php

namespace Vecapital\Vebase;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Vecapital\Vebase\Http\Controllers\VeApiController;
use Vecapital\Vebase\Http\Controllers\VeController;
use Vecapital\Vebase\Traits\VeModel;

class VeHelper
{
    /**
     * Longest accepted `search` term. The term becomes a LIKE pattern scanned against every
     * searchable column of every row, so an unbounded one is a cheap way to burn database time.
     */
    public const MAX_SEARCH_LENGTH = 128;

    /**
     * Upper bound for a request supplied page size, so `?limit=1000000` cannot ask the
     * database and the view layer to materialise the whole table.
     */
    public const MAX_PAGINATE_LIMIT = 100;

    /**
     * Concrete VeModel class names, resolved once per process rather than on every call.
     */
    protected static ?array $modelClasses = null;

    public static function adminRoutes()
    {
        foreach (static::veModelClasses() as $class) {
            $model = new $class();
            if (! $model->hasAdminResourceRoute()) {
                continue;
            }

            $shortName = class_basename($class);
            $overrideClass = 'App\\Http\\Controllers\\Admin\\'.$shortName.'Controller';
            $controller = class_exists($overrideClass) ? $overrideClass : VeController::class;
            $name = strtolower(Str::plural(Str::kebab($shortName)));

            if (! empty($model->importExport)) {
                if (! $model->disableImport) {
                    Route::post($name.'/import', $controller.'@import')->name($name.'.import');
                }
                if (! $model->disableExport) {
                    Route::get($name.'/export', $controller.'@export')->name($name.'.export');
                }
            }

            if (! empty($model->routesExcept)) {
                Route::resource($name, $controller)->except($model->routesExcept);
            } elseif (! empty($model->routesOnly)) {
                Route::resource($name, $controller)->only($model->routesOnly);
            } else {
                Route::resource($name, $controller);
            }
        }
    }

    public static function apiRoutes()
    {
        foreach (static::veModelClasses() as $class) {
            $model = new $class();
            if (! $model->hasApiResourceRoute()) {
                continue;
            }

            $shortName = class_basename($class);
            $overrideClass = 'App\\Http\\Controllers\\Api\\'.$shortName.'Controller';
            $controller = class_exists($overrideClass) ? $overrideClass : VeApiController::class;

            Route::apiResource(strtolower(Str::plural(Str::kebab($shortName))), $controller);
        }
    }

    /**
     * Turns a URL segment into the model class it names, or null when it names nothing.
     *
     * The segment is attacker controlled, so the result is pinned to the App\Models namespace
     * and has to be a concrete VeModel. Resolving the raw string through the container instead
     * would build whatever binding the segment happens to spell.
     *
     * Deliberately checked class-by-class rather than against veModelClasses(): this runs on
     * every request through a Ve controller, and scanning the Models directory there would
     * autoload every model in the app just to validate one name.
     */
    public static function resolveModelClass(?string $segment): ?string
    {
        if (empty($segment)) {
            return null;
        }

        $class = 'App\\Models\\'.ucfirst(Str::singular(Str::camel($segment)));

        if (! class_exists($class) || ! is_subclass_of($class, VeModel::class)) {
            return null;
        }

        return (new \ReflectionClass($class))->isInstantiable() ? $class : null;
    }

    /**
     * Normalises a request supplied search term: scalars only, trimmed and length capped.
     *
     * Returns null when there is nothing to search for. Array input would otherwise reach
     * string concatenation as "Array" and raise a fatal.
     */
    public static function sanitizeSearchTerm($value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Str::limit($value, static::MAX_SEARCH_LENGTH, '');
    }

    /**
     * Constrains $query to rows where any of $columns contains $search.
     *
     * A `relation.column` entry searches through the relation (nested relations use the usual
     * dot path, `company.owner.name`). Shared by the web and API controllers: the API copy used
     * to pass a dotted entry straight to orWhere(), which reads it as `table.column` and fails
     * with an unknown-table SQL error.
     */
    public static function applySearch($query, $columns, ?string $search)
    {
        $columns = array_filter((array) $columns, fn ($column) => is_string($column) && $column !== '');

        if ($search === null || $search === '' || empty($columns)) {
            return $query;
        }

        $pattern = '%'.$search.'%';

        return $query->where(function ($query) use ($columns, $pattern) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    $relation = Str::beforeLast($column, '.');
                    $relationColumn = Str::afterLast($column, '.');

                    $query->orWhereHas($relation, function ($q) use ($relationColumn, $pattern) {
                        $q->where($q->qualifyColumn($relationColumn), 'LIKE', $pattern);
                    });
                } else {
                    $query->orWhere($query->qualifyColumn($column), 'LIKE', $pattern);
                }
            }
        });
    }

    /**
     * Normalises a request supplied page size to a positive integer no larger than $max.
     */
    public static function sanitizeLimit($value, int $default, ?int $max = null): int
    {
        $max = $max ?? static::MAX_PAGINATE_LIMIT;

        if (! is_scalar($value) || ! is_numeric($value)) {
            return $default;
        }

        return max(1, min((int) $value, $max));
    }

    /**
     * The relations an index listing walks, taken from the model's `relation` index fields.
     *
     * table.blade.php reads these per row, so without eager loading a listing of N rows
     * issues N extra queries for every relation field.
     */
    public static function eagerLoadsFor($model): array
    {
        $relations = [];

        foreach ((array) ($model->indexFields ?? []) as $field) {
            if (! is_array($field)) {
                continue;
            }

            if (($field['type'] ?? null) === 'relation' && ! empty($field['relation']) && is_string($field['relation'])) {
                $relations[] = $field['relation'];
            }
        }

        return array_values(array_unique($relations));
    }

    /**
     * Concrete VeModel classes in the app's Models directory.
     *
     * The subclass check runs on the class name so abstract classes, and classes whose
     * constructor needs arguments, are filtered out before anything is instantiated.
     */
    public static function veModelClasses(): array
    {
        if (static::$modelClasses !== null) {
            return static::$modelClasses;
        }

        return static::$modelClasses = collect(static::getModelClasses())
            ->filter(function ($class) {
                if (! is_subclass_of($class, VeModel::class)) {
                    return false;
                }

                return (new \ReflectionClass($class))->isInstantiable();
            })
            ->values()
            ->all();
    }

    /**
     * Clears the memoised model list. Only needed by tests that declare classes at runtime.
     */
    public static function flushModelClasses(): void
    {
        static::$modelClasses = null;
    }

    /**
     * Get all model class names from the app's Models directory.
     */
    protected static function getModelClasses(): array
    {
        $namespace = app()->getNamespace();
        $modelsPath = app_path('Models');

        if (! File::isDirectory($modelsPath)) {
            return [];
        }

        return collect(File::allFiles($modelsPath))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(function ($file) use ($namespace) {
                $relativePath = str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname()
                );

                return $namespace.'Models\\'.$relativePath;
            })
            ->filter(fn ($class) => class_exists($class))
            ->values()
            ->all();
    }
}
