<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationHistoryEnvRequest extends FormRequest
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
            'email' => 'required|string',
            'records' => 'required|array',
            'records.*.auth_flg' => 'required|integer',
            'records.*.mst_display_id' => 'required|integer',
            'records.*.mst_operation_id' => 'required|integer',
            'records.*.result' => 'required|integer',
            'records.*.detail_info' => 'required|string',
            'records.*.ip_address' => 'required|string',
            'records.*.create_at' => 'required|string',
        ];
    }
}
