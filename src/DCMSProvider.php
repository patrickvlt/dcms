<?php

namespace Pveltrop\DCMS;

use Illuminate\Support\ServiceProvider;
use Pveltrop\DCMS\Console\Commands\Crud;
use Pveltrop\DCMS\Console\Commands\Update;
use Pveltrop\DCMS\Console\Commands\Publish;
use Pveltrop\DCMS\Console\Commands\Help;

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

        if ($this->app->runningInConsole()) {
            $this->commands([
                Crud::class,
                Update::class,
                Publish::class,
                Help::class,
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
