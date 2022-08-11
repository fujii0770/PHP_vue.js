<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateEpsTAppItemAPIRequest extends FormRequest
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
            // update validate after done designDB
            't_app_id' => 'required|numeric',
            'wtsm_name' => 'required|string|max:20',
            'expected_pay_date' => 'required|date_format:Y-m-d',
            'unit_price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'expected_pay_amt' => 'required|numeric',
            'numof_ppl' => 'nullable|numeric',
            'from_station' => 'nullable|string|max:50',
            'to_station' => 'nullable|string|max:50',
            // Todo roundtrip_flag (in set 0, 1)
            'roundtrip_flag' => 'required|numeric',
            'remarks' => 'nullable',
        ];
    }

}
