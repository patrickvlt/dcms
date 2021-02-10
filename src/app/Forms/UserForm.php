<?php

namespace Pveltrop\DCMS\Forms;

class UserForm
{
    public function fields()
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
        ];
    }
}
