<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateFavoriteServiceAPIRequest extends FormRequest
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
            'mypage_id' => 'required|numeric',
            'is_shachihata' => 'required|numeric',
            'service_name' => 'required|max:50',
            'logo_src' => 'required|string',
            'url' => 'required|max:2048'
        ];
    }

}
