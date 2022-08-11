<?php

namespace App\Http\Requests\API;

use App\Models\CircularUser;
use InfyOm\Generator\Request\APIRequest;

class CreateUsageSituationAPIRequest extends APIRequest
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
            'target_month' => 'required',

            'usage_situations' => 'required|array',
            "usage_situations.*.mst_company_id" => "required|integer",
            "usage_situations.*.user_total_count" => "required|integer",
            "usage_situations.*.total_name_stamp" => "required|integer",
            "usage_situations.*.total_date_stamp" => "required|integer",
            "usage_situations.*.total_common_stamp" => "required|integer",
            "usage_situations.*.total_time_stamp" => "required|integer",
            "usage_situations.*.same_domain_number" => "required|integer",
            "usage_situations.*.guest_user_total_count" => "integer",
        ];
    }
}
