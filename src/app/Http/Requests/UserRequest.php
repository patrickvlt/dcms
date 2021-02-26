<?php

namespace Pveltrop\DCMS\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $randomPassword = Hash::make(RandomString(10));
        $request['password'] = $randomPassword;
        $request['password_confirmation'] = $randomPassword;
        return $request;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (preg_match('/user.edit/', CurrentRoute()) || preg_match('/user.update/', CurrentRoute())) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'verified' => ['required', 'boolean'],
            ];
        } else {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ];
        }
    }
}
