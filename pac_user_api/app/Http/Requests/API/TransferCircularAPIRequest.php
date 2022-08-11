<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class TransferCircularAPIRequest extends FormRequest
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
            "circular_id"=> "required|integer",
            "mst_user_id"=> "required|integer",
            "env_flg" => "required|integer|in:0,1",
            "edition_flg" => "required|integer|in:0,1",
            "server_flg" => "required|integer",
            "address_change_flg" => "required|integer|in:0,1",
            "access_code_flg" => "required|integer|in:0,1",
            "current_k5_circular_id" => "nullable",
            "current_aws_circular_id" => "nullable",
            "access_code" => "nullable",
            "hide_thumbnail_flg" => "required|integer|in:0,1",
            "re_notification_day" => "nullable",
            "first_page_data" => "required",
            "circular_status" => "required|integer|in:1,2,3,4,5,6,7,8,9",
            'create_user' => "required",
            'applied_date' => 'nullable',

            "circular_users" => "required|array|min:1",
            "circular_users.*.parent_send_order" => "required|integer",
            "circular_users.*.child_send_order" => "required|integer",
            "circular_users.*.title" => "nullable|string|max:256",
            "circular_users.*.text" => "nullable|string",
            "circular_users.*.env_flg" => "required|integer|in:0,1",
            "circular_users.*.edition_flg" => "required|integer|in:0,1",
            "circular_users.*.server_flg" => "required|integer",
            "circular_users.*.mst_company_id" => "nullable|integer",
            "circular_users.*.mst_user_id" => "nullable|integer",
            "circular_users.*.email" => "required|email|max:256",
            "circular_users.*.name" => "nullable|string|max:128",
            "circular_users.*.view_url" => "nullable|string|max:516",
            "circular_users.*.circular_status" => "required|integer|in:0,1,2,3,4,5,6,11",
            "circular_users.*.received_date" => 'nullable',
            "circular_users.*.sent_date" => 'nullable',
            "circular_users.*.plan_id" => 'nullable',

            "circular_documents" => "required|array|min:1",
            "circular_documents.*.origin_document_id" => "required|integer",
            "circular_documents.*.env_flg" => "required|integer|in:0,1",
            "circular_documents.*.edition_flg" => "required|integer|in:0,1",
            "circular_documents.*.server_flg" => "required|integer",
            "circular_documents.*.parent_send_order" => "required|integer",
            "circular_documents.*.create_company_id" => "required|integer",
            "circular_documents.*.create_user_id" => "required|integer",
            "circular_documents.*.document_no" => "required|integer",
            "circular_documents.*.confidential_flg" => "required|integer|in:0,1",
            "circular_documents.*.file_name" => "required|string|max:256",
            "circular_documents.*.file_size" => "required|integer",

            "document_datas" => "nullable|array",
            "document_datas.*.circular_document_id" => "required|integer",
            "document_datas.*.file_data" => "required",

            "stamp_infos" => "nullable|array",
            "stamp_infos.*.circular_document_id" => "required|integer",
            "stamp_infos.*.stamp_image" => "required",
            "stamp_infos.*.name" => "required|string|max:128",
            "stamp_infos.*.email" => "required|string|max:256",
            "stamp_infos.*.info_id" => "required|string|max:128",
            "stamp_infos.*.serial" => "required|string|max:128",
            "stamp_infos.*.file_name" => "required|string|max:256",
            "stamp_infos.*.create_at" => "required|date",
            "stamp_infos.*.time_stamp_permission" => "required|integer|in:0,1",

            "text_infos" => "nullable|array",
            "text_infos.*.circular_document_id" => "required",
            "text_infos.*.text" => "required",
            "text_infos.*.name" => "required|string|max:128",
            "text_infos.*.email" => "required|string|max:256",
            "text_infos.*.create_at" => "required|date",

            "time_stamp_infos" => "nullable|array",
            "time_stamp_infos.*.circular_document_id" => "required|integer",
            "time_stamp_infos.*.mst_company_id" => "nullable|integer",
            "time_stamp_infos.*.mst_user_id" => "nullable|integer",
            "time_stamp_infos.*.create_at" => "required|date",
        ];
    }
}

