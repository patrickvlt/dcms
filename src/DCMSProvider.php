<?php

namespace Pveltrop\DCMS;

use Illuminate\Support\ServiceProvider;
use Pveltrop\DCMS\Console\Commands\Crud;
use Pveltrop\DCMS\Console\Commands\Update;
use Pveltrop\DCMS\Console\Commands\Publish;

class DCMSProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->publishes([
            __DIR__.'/app/Resources/js' => resource_path('js/dcms'),
            __DIR__.'/app/Resources/sass' => resource_path('sass/dcms')
        ], 'resources');

        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // php artisan vendor:publish --tag=resources --force

        if ($this->app->runningInConsole()) {
            $this->commands([
                Crud::class,
                Update::class,
                Publish::class,
            ]);
        }
    }

    public function register()
    {
        $helpers = glob(__DIR__ . "/app/Helpers/*.{php}", GLOB_BRACE);
        foreach ($helpers as $file) {
            require_once($file);
        }
    }
}
