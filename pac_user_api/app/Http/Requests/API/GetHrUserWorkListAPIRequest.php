<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class GetHrUserWorkListAPIRequest extends FormRequest
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
            'working_month_from' => 'nullable|date_format:Ym',
            'working_month_to' => 'nullable|date_format:Ym',
            'submission_state' => 'nullable|numeric',
            'approval_state' => 'nullable|numeric',
			'user_name' => 'nullable'
        ];
    }

}
