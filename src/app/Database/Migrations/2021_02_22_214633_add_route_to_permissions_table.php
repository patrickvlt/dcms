<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddRouteToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('route')->after('name')->nullable();
            });
        } catch (Throwable $th){
            if (app()->isLocal()){
                Artisan::call('migrate:rollback');
            }
            throw new \RuntimeException("Unable to add route column to permissions table. Did you run the migrations from Spaties permissions/role package? \nRun this cmd: php artisan vendor:publish --provider='Spatie\Permission\PermissionServiceProvider' --tag='migrations'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('route');
        });
    }
}
