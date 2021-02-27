<?php

namespace Pveltrop\DCMS;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Pveltrop\DCMS\Http\Middleware\HasPermission;
use Pveltrop\DCMS\Console\Commands\Crud;
use Pveltrop\DCMS\Console\Commands\Update;
use Pveltrop\DCMS\Console\Commands\Datatable;
use Pveltrop\DCMS\Console\Commands\Publish;
use Pveltrop\DCMS\Console\Commands\Form;
use Pveltrop\DCMS\Console\Commands\TestCrudForm;

class DCMSProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/app/Resources/views', 'dcms');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('hasPermission', HasPermission::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Crud::class,
                TestCrudForm::class,
                Datatable::class,
                Update::class,
                Publish::class,
                Form::class,
            ]);
        }
    }

    public function register()
    {
        require_once(__DIR__ . "/app/Helpers/DCMS.php");
    }
}
