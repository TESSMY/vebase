<?php

namespace Vecapital\Vebase;

use Illuminate\Support\Facades\Route;
use LaravelDaily\Larastarters\Console\InstallCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/courier.php' => config_path('vecapital.php'),
        ]);
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(base_path('vendor/vecapital/vebase/src/resources/views'), 'vebase');
    }

    /**
     * Register the needed routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'domain' => config('vecapital.domain', null),
            'prefix' => config('vecapital.path'),
            'middleware' => config('vecapital.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

     /**
     * Register the Invoices Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

}