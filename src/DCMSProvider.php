<?php

namespace Pveltrop\DCMS;

use App\Console\Commands\Update;
use App\Console\Commands\Crud;

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
