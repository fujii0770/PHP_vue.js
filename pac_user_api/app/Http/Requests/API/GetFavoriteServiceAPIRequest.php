<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class GetFavoriteServiceAPIRequest extends FormRequest
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
    public function rules()
    {
        return [
            'mypage_id' => 'required|numeric'
        ];
    }
}
