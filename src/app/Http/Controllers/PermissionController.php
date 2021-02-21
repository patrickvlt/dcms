<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;
use Pveltrop\DCMS\Traits\DCMSController;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use DCMSController;

    public function __construct()
    {
        $this->routePrefix = 'permission';
        $this->model = Permission::class;
        $this->request = PermissionRequest::class;
        $this->form = PermissionForm::class;
        $this->responses = [
            "created" => [
                "title" => __("Permission created"),
                "message" => __("Permission created on __created_at__"),
                "url" => route('dcms.portal.permission.index')
            ],
            "updated" => [
                "title" => __("Permission updated"),
                "message" => __("Permission updated on __created_at__"),
                "url" => route('dcms.portal.permission.index')
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

    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = Permission::query();
        return (new Datatable($query))->render();
    }
}
