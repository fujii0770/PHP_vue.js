<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransferredStatusAPIRequest extends FormRequest
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
            "current_k5_circular_id" => "nullable",
            "current_aws_circular_id" => "nullable",
            "parent_send_order" => "required|integer",
            "child_send_order" => "required|integer",
            "sendback_child_send_order" => "nullable|integer",
            "sendback_parent_send_order" => "nullable|integer",
            "circular_env_flg" => "nullable|in:0,1",
            "circular_edition_flg" => "nullable|in:0,1",
            "circular_server_flg" => "nullable",
            "title" => "nullable|string|max:256",
            "text" => "nullable|string",
            "view_url" => "nullable|string|max:516",
            "circular_status" => "required|integer|in:0,1,2,3,4,5,6,7,8",

            "circular_documents" => "nullable|array",
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
            "stamp_infos.*.serial" => "required|string|max:32",
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

