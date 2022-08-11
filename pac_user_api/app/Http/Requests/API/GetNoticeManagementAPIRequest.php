<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetNoticeManagementAPIRequest extends FormRequest
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
     * Override automatically apply validation rules to the URL parameters
     *
     * @param null $keys
     * @return array
     */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        if (isset($data['type'])) {
            $param = $data['type'];
            $data['type'] = json_decode('[' . $param . ']', true);;
        }
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'nullable|array',
            'type.*' => 'nullable|numeric',
            'mst_notice_id' => 'nullable|numeric',
            'mst_company_id' => 'nullable|numeric',
            'mst_department_id' => 'nullable|numeric',
            'mst_position_id' => 'nullable|numeric',
            'limit' => 'nullable|numeric'
        ];
    }
}
