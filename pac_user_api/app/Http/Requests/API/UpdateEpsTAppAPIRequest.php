<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEpsTAppAPIRequest extends FormRequest
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
            "t_app" => "required",
            "t_app.desired_suspay_amt" => "nullable|numeric",
            "t_app.expected_amt" => "nullable|numeric",
            "t_app.form_code" => "required|string|max:20",
            "t_app.form_dtl" => "nullable|string|max:1000",
            "t_app.purpose_name" => "nullable|string|max:20",
            "t_app.target_period_from" => "required|date_format:Y-m-d",
            "t_app.target_period_to" => "nullable|date_format:Y-m-d",

            't_app_items' => 'nullable|array',
            't_app_items.*.wtsm_name' => 'required|string|max:20',
            't_app_items.*.expected_pay_date' => 'required|date_format:Y-m-d',
            't_app_items.*.unit_price' => 'nullable|numeric',
            't_app_items.*.quantity' => 'nullable|numeric',
            't_app_items.*.expected_pay_amt' => 'nullable|numeric',
            't_app_items.*.numof_ppl' => 'nullable|numeric',
            't_app_items.*.from_station' => 'nullable|string|max:50',
            't_app_items.*.to_station' => 'nullable|string|max:50',
            // Todo roundtrip_flag (in set 0, 1)
            't_app_items.*.roundtrip_flag' => 'required|numeric',
            't_app_items.*.remarks' => 'nullable|string',
            't_app_items.*.submit_method' => 'required|numeric',
            't_app_items.*.submit_other_memo' => 'nullable|string',
            't_app_items.*.nonsubmit_type' => 'nullable|numeric',
            't_app_items.*.nonsubmit_reason' => 'nullable|string',
            't_app_items.*.tax' => 'nullable|numeric',
            't_app_items.*.traffic_facility_name' => 'nullable|string',
        ];
    }

}
