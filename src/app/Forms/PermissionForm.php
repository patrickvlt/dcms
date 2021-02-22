<?php

namespace App\Forms;

use Spatie\Permission\Models\Permission;

class PermissionForm
{
    public function fields(){
        
        
        return [
            'name' => [
                'select' => [
                    'data-type' => 'slimselect',
                    'data-slimselect-auto-close' => 'true',
                    'multiple' => false,
                    'options' => [
                        'data' => $namedRoutes,
                        'value' => 'name',
                        'text' => 'name',
                    ]
                ],
            ]
        ];
    }

    public function routes()
    {
        return [
            'index' => route('dcms.portal.authorization.index'),
            'store' => route('dcms.portal.permission.store'),
            'update' => (isset(request()->route()->parameters['permission'])) ? route('dcms.portal.permission.update',request()->route()->parameters['permission']) : null,
            'destroy' => (isset(request()->route()->parameters['permission'])) ? route('dcms.portal.permission.destroy',request()->route()->parameters['permission']) : null,
        ];
    }
}
