<?php

namespace Pveltrop\DCMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function beforeValidation($request)
    {
        $request['guard_name'] = 'web';
        return $request;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => ["required", "unique:permissions,name", "string", "min:3", "max:100",],
            "route" => ["required", "string", "min:3", "max:100",],
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }
}
