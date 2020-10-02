<?php

$requestContent = '<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class '.$model.'Request extends FormRequest
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

    /**
     * Modify request before it gets validated
     *
     * @return array
     */

    public function beforeValidation()
    {
        $request = request()->all();

        // Modify all requests
        $request["foo"] = "bar";
        // Modify store request
        if (FormMethod() == "POST"){
        }
        // Modify update request
        else if (FormMethod() == "PUT"){
        }

        return $request;
    }

    /**
     * Modify request after it has been validated, before the data gets stored
     *
     * @return array
     */

    public function afterValidation($request)
    {
        // Modify all requests
        $request["foo"] = "bar";
        // Modify store request
        if (FormMethod() == "POST"){
        }
        // Modify update request
        else if (FormMethod() == "PUT"){
        }

        return $request;
    }

    /**
     *
     * DCMS: Place validation for file uploads here, refer to the Laravel documentation. You can still use messages() to return custom messages.
     *
     */

    public function uploadRules()
    {
        return [
            // "logo.*" => ["nullable","mimes:jpeg, jpg, png, jpg, gif, svg, webp", "max:2000"],
            // "sheet.*" => ["nullable","mimes:octet-stream, vnd.ms-excel, msexcel, x-msexcel, x-excel, x-dos_ms_excel, xls, x-xls, , vnd.openxmlformats-officedocument.spreadsheetml.sheet", "max:2000"],
        ];
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        '.$reqEntries.'
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }
}';
