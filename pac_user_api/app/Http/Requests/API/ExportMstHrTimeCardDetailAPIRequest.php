<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class ExportMstHrTimeCardDetailAPIRequest extends FormRequest
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
            'export_work_list_columns' => 'required|array',
            'work_month' => 'required|date_format:Ym'
        ];
    }
}
