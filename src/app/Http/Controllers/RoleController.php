<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\Forms\RoleForm;
use Pveltrop\DCMS\Classes\Form;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;
use Pveltrop\DCMS\Traits\DCMSController;
use Spatie\Permission\Models\Permission;
use Pveltrop\DCMS\Http\Requests\RoleRequest;

class RoleController extends Controller
{
    use DCMSController;

    public function __construct()
    {
        $this->routePrefix = 'role';
        $this->model = Role::class;
        $this->request = RoleRequest::class;
        $this->form = RoleForm::class;
        $this->responses = [
            "created" => [
                "title" => __("Role created"),
                "message" => __("Role created on __created_at__"),
            ],
            "updated" => [
                "title" => __("Role updated"),
                "message" => __("Role updated on __created_at__"),
            ],
            "confirmDelete" => [
                "title" => __("Delete role?"),
                "message" => __("Are you sure you want to delete this role?"),
            ],
            "failedDelete" => [
                "title" => __("Failed to delete"),
                "message" => __("Unable to delete this role."),
            ],
            "deleted" => [
                "title" => __("Role deleted"),
                "message" => __("Role has been deleted."),
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
        $query = Role::query();
        return (new Datatable($query))->render();
    }

    public function create()
    {
        $this->initDCMS();
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request, $this->routePrefix, $this->form, $this->responses) : null;
        return view('dcms::role.crud')->with(['form' => $form]);
    }

    public function edit(Role $role)
    {
        $this->initDCMS();
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request, $this->routePrefix, $this->form, $this->responses) : null;
        return view('dcms::role.crud')->with(['form' => $form]);
    }

    /**
     * 
     * DCMS: Execute code after a new model has been created/updated/deleted
     * 
     */

    public function afterCreateOrUpdate($request, $model)
    {
        foreach ($request['permissions'] as $x => $id) {
            $permission = Permission::find($id);
            if ($permission && !in_array($id,$model->permissions->toArray())){
                $model->givePermissionTo($permission->name);
            }
        }
    }
}
