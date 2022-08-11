<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SearchFormIssuanceListAPIRequest extends FormRequest
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
            "status" => "nullable",
            "filename" => "nullable|string",
            "userName" => "nullable|string",
            "userEmail" => "nullable|string",
            "fromdate" => "nullable|date",
            "todate" => "nullable|date",
        ];
    }
}
