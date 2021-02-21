<?php

namespace App\Forms;

use Spatie\Permission\Models\Permission;

class RoleForm
{
    public function fields(){
        return [
            'name' => [
                'label' => [
                    'text' => __('Name')
                ]
            ],
            'permissions' => [
                'label' => [
                    'text' => __('Permissions')
                ],
                'select' => [
                    'data-type' => 'slimselect',
                    'data-slimselect-auto-close' => 'false',
                    'multiple' => true,
                    'options' => [
                        'data' => Permission::all(),
                        'value' => 'id',
                        'text' => 'name',
                    ]
                ],
                'small' => [
                    'text' => __('Which permissions does this role have access to?')
                ]
            ],
        ];
    }

    public function routes()
    {
        return [
            'index' => route('dcms.portal.authorization.index'),
            'store' => route('dcms.portal.role.store'),
            'update' => (isset(request()->route()->parameters['role'])) ? route('dcms.portal.role.update',request()->route()->parameters['role']) : null,
            'destroy' => (isset(request()->route()->parameters['role'])) ? route('dcms.portal.role.destroy',request()->route()->parameters['role']) : null,
        ];
    }
}
