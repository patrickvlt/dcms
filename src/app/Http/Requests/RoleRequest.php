<?php

namespace Pveltrop\DCMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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

    public function afterValidation($request)
    {
        // attach permissions to role
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => ["required", "string", "min:3", "max:100",],
            "permissions.*" => ["int", "exists:permissions,id"],
            "permissions" => ["array", "min:1", "max:5"]
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }
}
