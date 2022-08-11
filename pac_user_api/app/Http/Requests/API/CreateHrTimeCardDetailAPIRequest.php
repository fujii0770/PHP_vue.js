<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateHrTimeCardDetailAPIRequest extends FormRequest
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
            'work_date' => 'required|date_format:Ymd',
            'work_start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'work_end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'break_time' => 'nullable|digits_between:1,3',
            'absent_flg' => 'nullable|digits:1',
            'earlyleave_flg' => 'nullable|digits:1',
            'late_flg' => 'nullable|digits:1',
            'paid_vacation_flg' => 'nullable|digits:1',
            'sp_vacation_flg' => 'nullable|digits:1',
            'day_off_flg' => 'nullable|digits:1',
            'memo' => 'nullable|string',
            'admin_memo' => 'nullable|string'
        ];
    }
}
