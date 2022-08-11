<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransferredCircularUserAPIRequest extends FormRequest
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
            'update_user' => 'required',
            'origin_circular_id' => 'required',
            'env_flg' => 'required',
            'edition_flg' => 'required',
            'server_flg' => 'required',

            'new_circular_users' => 'nullable|array',
            "new_circular_users.*.parent_send_order" => "required|integer",
            "new_circular_users.*.child_send_order" => "required|integer",
            "new_circular_users.*.env_flg" => "required|integer|in:0,1",
            "new_circular_users.*.edition_flg" => "required|integer|in:0,1",
            "new_circular_users.*.server_flg" => "required|integer",
            "new_circular_users.*.mst_company_id" => "nullable|integer",
            "new_circular_users.*.mst_company_name" => "nullable|string",
            "new_circular_users.*.mst_user_id" => "nullable|integer",
            "new_circular_users.*.email" => "required|email|max:256",
            "new_circular_users.*.name" => "nullable|string|max:128",
            "new_circular_users.*.title" => "nullable|string|max:256",
            "new_circular_users.*.return_flg" => "required|integer|in:0,1",
            "new_circular_users.*.circular_status" => "required|integer|in:0,1,2,3,4,5,6",
            "new_circular_users.*.received_date" => 'nullable',
            "new_circular_users.*.sent_date" => 'nullable',

            'update_circular_users' => 'nullable|array',
            "update_circular_users.*.parent_send_order" => "required|integer",
            "update_circular_users.*.child_send_order" => "required|integer",
            "update_circular_users.*.env_flg" => "required|integer|in:0,1",
            "update_circular_users.*.edition_flg" => "required|integer|in:0,1",
            "update_circular_users.*.server_flg" => "required|integer",
            "update_circular_users.*.mst_company_id" => "nullable|integer",
            "update_circular_users.*.mst_company_name" => "nullable|string",
            "update_circular_users.*.mst_user_id" => "nullable|integer",
            "update_circular_users.*.email" => "required|email|max:256",
            "update_circular_users.*.name" => "nullable|string|max:128",
            "update_circular_users.*.title" => "nullable|string|max:256",
            "update_circular_users.*.return_flg" => "required|integer|in:0,1",
            "update_circular_users.*.circular_status" => "required|integer|in:0,1,2,3,4,5,6",
            "update_circular_users.*.received_date" => 'nullable',
            "update_circular_users.*.sent_date" => 'nullable',

            'remove_circular_users' => 'nullable|array',
            "remove_circular_users.*.parent_send_order" => "required|integer",
            "remove_circular_users.*.child_send_order" => "required|integer",
        ];
    }
}
