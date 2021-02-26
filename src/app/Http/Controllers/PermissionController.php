<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;
use Pveltrop\DCMS\Traits\DCMSController;
use Spatie\Permission\Models\Permission;
use Pveltrop\DCMS\Http\Requests\PermissionRequest;

class PermissionController extends Controller
{
    use DCMSController;

    public function __construct()
    {
        $this->routePrefix = 'permission';
        $this->model = Permission::class;
        $this->request = PermissionRequest::class;
        $this->responses = [
            "created" => [
                "title" => __("Permission created"),
                "message" => __("Permission created on __created_at__"),
            ],
            "updated" => [
                "title" => __("Permission updated"),
                "message" => __("Permission updated on __created_at__"),
            ],
            "confirmDelete" => [
                "title" => __("Delete permission?"),
                "message" => __("Are you sure you want to delete this permission?"),
            ],
            "failedDelete" => [
                "title" => __("Failed to delete"),
                "message" => __("Unable to delete this permission."),
            ],
            "deleted" => [
                "title" => __("Permission deleted"),
                "message" => __("Permission has been deleted."),
            ],
        ];
        $this->views = [
            "index" => "index",
            "show" => "show",
            "edit" => "crud",
            "create" => "crud"
        ];
    }

    public function getRoutes()
    {
        $namedRoutes = [];
        foreach (app()->routes->getRoutes() as $key => $route) {
            if (isset($route->action['as']) && !preg_match('/ignition/m', $route->action['as'])) {
                $routeObj = (object) $route->action['as'];
                $routeObj->name = $route->action['as'];
                $namedRoutes[] = $routeObj;
            }
        }
        return collect($namedRoutes);
    }

    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = Permission::query();
        return (new Datatable($query))->render();
    }

    public function create()
    {
        $this->initDCMS();

        $namedRoutes = $this->getRoutes();
        $useSelect = true;

        return view('dcms::permission.crud')->with([
            'namedRoutes' => $namedRoutes,
            'useSelect' => $useSelect
        ]);
    }

    public function edit(Permission $permission)
    {
        $this->initDCMS();
        $useSelect = false;

        $namedRoutes = $this->getRoutes();
        if ($namedRoutes->contains($permission->route)) {
            $useSelect = true;
        }

        return view('dcms::permission.crud')->with([
            'namedRoutes' => $namedRoutes,
            'useSelect' => $useSelect
        ]);
    }
}
