<?php

namespace Pveltrop\DCMS;

use Illuminate\Support\ServiceProvider;
use Pveltrop\DCMS\Console\Commands\Crud;
use Pveltrop\DCMS\Console\Commands\Update;
use Pveltrop\DCMS\Console\Commands\Datatable;
use Pveltrop\DCMS\Console\Commands\Publish;
use Pveltrop\DCMS\Console\Commands\Help;

class DCMSProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/app/Database/Migrations' => base_path('database/migrations')
        ], 'resources');

        $this->loadViewsFrom(__DIR__.'/app/Resources/views','dcms');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Crud::class,
                Datatable::class,
                Update::class,
                Publish::class,
                Help::class,
            ]);
        }
    }

    public function register()
    {
        require_once(__DIR__ . "/app/Helpers/DCMS.php");
    }
}
