<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SendBackRequest extends FormRequest
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
            'send_from_id' => 'required|integer',
            'send_to_id' => 'required|integer',
            'text' => 'nullable',
        ];
    }
}
