<?php

namespace Vecapital\Vebase\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vebase:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the UI for vebase';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // views
        $viewDirectories = scandir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views');
        foreach ($viewDirectories as $viewDirectory) {
            // skip '.' and '..' in dir
            if (strlen($viewDirectory) > 2) {
                (new Filesystem)->copyDirectory(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewDirectory, resource_path('views/admin' . DIRECTORY_SEPARATOR . $viewDirectory));
            }
        }


        // controller
        $controllers = scandir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'controllers');
        foreach ($controllers as $controller) {
            // skip '.' and '..' in dir
            if (strlen($controller) > 2) {
                copy(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller, app_path('Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR .
                'Admin' . DIRECTORY_SEPARATOR . $controller));
            }
        }

        // vue components
        $components = scandir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components');
        foreach ($components as $component) {
            // skip '.' and '..' in dir
            if (strlen($component) > 2) {
                copy(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $component, resource_path('js' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $component));
            }
        }

        $this->info('Succesfully installed UI');
    }
}