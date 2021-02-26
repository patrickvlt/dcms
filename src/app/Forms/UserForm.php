<?php

namespace Pveltrop\DCMS\Forms;

use Spatie\Permission\Models\Role;

class UserForm
{
    public function fields(): array
    {
        return [
            'name' => [
                'label' => [
                    'text' => __('Name')
                ]
            ],
            'email' => [
                'label' => [
                    'text' => __('E-mail')
                ]
            ],
            'verified' => [
                'checkbox' => [
                    [
                        'text' => __('Verified'),
                        'value' => 1,
                    ]
                ],
                'small' => [
                    'text' => __('Click here to verify this user.')
                ]
            ],
            'roles' => [
                'label' => [
                    'text' => __('Roles')
                ],
                'select' => [
                    'data-type' => 'slimselect',
                    'data-slimselect-auto-close' => 'false',
                    'multiple' => true,
                    'options' => [
                        'data' => Role::all(),
                        'value' => 'id',
                        'text' => 'name',
                    ]
                ],
                'small' => [
                    'text' => __('Which roles does this user have?')
                ]
            ],
        ];
    }
}
