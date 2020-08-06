<?php

namespace Pveltrop\DCMS;

use Console\Commands\Update;
use Console\Commands\Crud;

use Illuminate\Support\ServiceProvider;

class DCMSProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Crud::class,
                Update::class,
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
