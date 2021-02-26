<?php

namespace Pveltrop\DCMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModelRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->noCodeString = 'not_regex:/(;|")/';

        return [
            'name' => ['required', 'string', $this->noCodeString, 'max:255'],
            'seed' => ['nullable', 'boolean'],
            'amountToSeed' => [((isset($this->customRequest->seed) && $this->customRequest->seed) || is_array($this->customRequest) && $this->customRequest['seed']) ? 'required' : 'nullable', 'integer', 'min:0'],

            'responses' => ['required', 'array', 'min:3', 'max:3'],
            'responses.*' => ['required', 'array', 'min:2', 'max:2'],
            'responses.*.message' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'responses.*.url' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],

            'views' => ['required', 'array', 'min:4', 'max:4'],
            'views.*' => ['required', 'string', 'regex:/(\.)/', $this->noCodeString, 'min:1', 'max:255'],

            'columns' => ['required', 'array', 'min:1'],
            'columns.*.name' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.title' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.dataType' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.nullable' => ['nullable', 'boolean'],
            'columns.*.required' => ['nullable', 'boolean'],
            'columns.*.foreign' => ['nullable', 'boolean'],
            'columns.*.text' => ['string', 'min:1', $this->noCodeString, 'max:25'],
            'columns.*.value' => ['string', 'min:1', $this->noCodeString, 'max:25'],
            'columns.*.inputType' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.inputDataType' => ['nullable', 'string', $this->noCodeString, 'max:25'],
            'columns.*.filePondMime' => ['nullable', 'string', $this->noCodeString, 'max:25'],

            'columns.*.class' => ['string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.table' => ['string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.relation' => ['string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.method' => ['string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.onUpdate' => ['string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.onDelete' => ['string', $this->noCodeString, 'min:1', 'max:255'],

            'columns.*.seed' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'columns.*.rules' => ['nullable', 'array', 'min:0', 'max:50'],
            'columns.*.rules.*' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],

            'ktColumns' => ['required', 'array', 'min:1'],
            'ktColumns.*.name' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'ktColumns.*.enable' => ['required', 'boolean'],
            'ktColumns.*.title' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'ktColumns.*.type' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'ktColumns.*.value' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],

            'jExcelColumns' => ['array', 'min:0'],
            'jExcelColumns.*.name' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'jExcelColumns.*.enable' => ['required', 'boolean'],
            'jExcelColumns.*.title' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'jExcelColumns.*.type' => ['required', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'jExcelColumns.*.value' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],

            'jExcelResponses.*.title' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'jExcelResponses.*.message' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],
            'jExcelResponses.*.url' => ['nullable', 'string', $this->noCodeString, 'min:1', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }
}
