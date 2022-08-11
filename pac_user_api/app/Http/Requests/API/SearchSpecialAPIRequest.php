<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SearchSpecialAPIRequest extends FormRequest
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
            "group_name" => "nullable|string",
            "region_name" => "nullable|string",
        ];
    }
}
