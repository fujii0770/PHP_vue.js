<?php

namespace App\Http\Requests\API;

use InfyOm\Generator\Request\APIRequest;

class CreateUsagesRangeAPIRequest extends APIRequest
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
            'usages_range' => 'required|array',
            "usages_range.*.mst_company_id" => "required|integer",
//            "usages_range.*.mst_user_id" => "required|integer",
            "usages_range.*.disk_usage" => "required|integer",
        ];
    }
}
