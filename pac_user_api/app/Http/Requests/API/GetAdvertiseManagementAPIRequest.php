<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class GetAdvertiseManagementAPIRequest extends FormRequest
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
        return [
            'mst_advertisement_id' => 'nullable|numeric',
            'mst_company_id' => 'nullable|numeric',
            'mst_department_id' => 'nullable|numeric',
            'mst_position_id' => 'nullable|numeric'
        ];
    }

}
