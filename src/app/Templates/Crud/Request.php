<?php

return '<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class ' . $this->model . 'Request extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ' . $reqEntries . '
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }
}';
