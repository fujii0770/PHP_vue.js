<?php

namespace App\Http\Requests\API;

use InfyOm\Generator\Request\APIRequest;

class CreateUsagesDailyAPIRequest extends APIRequest
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
            'date' => 'required',

            'usages_daily' => 'required|array',
            "usages_daily.*.mst_company_id" => "required|integer",
            "usages_daily.*.new_requests" => "required|integer",
        ];
    }
}
