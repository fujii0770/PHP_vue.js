<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHrTimeCardDetailAPIRequest extends FormRequest
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
            'work_date' => 'nullable|date_format:Ymd',
            'late_flg' => 'nullable|digits:1',
            'earlyleave_flg' => 'nullable|digits:1',
            'paid_vacation_flg' => 'nullable|digits:1',
            'absent_flg' => 'nullable|digits:1',
            'sp_vacation_flg' => 'nullable|digits:1',
            'day_off_flg' => 'nullable|digits:1',
            'approval_state' => 'nullable|digits:1',
            'state' => 'nullable|digits:1',
            'work_start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'work_end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'approval_date' => 'nullable|date_format:Y-m-d H:i:s',
            'approval_user' => 'nullable|string|max:128',
            'memo' => 'nullable|string',
            'admin_memo' => 'nullable|string',
        ];
    }
}
