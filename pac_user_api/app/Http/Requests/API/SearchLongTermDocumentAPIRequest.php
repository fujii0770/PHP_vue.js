<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SearchLongTermDocumentAPIRequest extends FormRequest
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
            "documentName" => "nullable",
            "senderName" => "nullable",
            "destinationName" => "nullable",
            "departmentName" => "nullable",
            "applicationFromdate" => "nullable|date",
            "applicationTodate" => "nullable|date",
            "approvalFromdate" => "nullable|date",
            "approvalTodate" => "nullable|date",
            "fileName" => "nullable",
            "fileSize" => "nullable",
            "keyword"  => "nullable",
        ];
    }
}
