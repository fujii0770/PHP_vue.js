<?php

namespace App\Http\Requests\API;

use App\Models\CircularUser;
use InfyOm\Generator\Request\APIRequest;

class UpdateCircularUserAPIRequest extends APIRequest
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
            'parent_send_order' => 'required',
            'child_send_order' => 'required',
            'email' => 'required',
            'title' => 'max:256',
            'del_flg' => 'required',
            'circular_status' => 'required',
            'create_at' => 'required'
        ];
    }
}
