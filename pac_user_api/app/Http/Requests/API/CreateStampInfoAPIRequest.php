<?php

namespace App\Http\Requests\API;

use App\Models\StampInfo;
use InfyOm\Generator\Request\APIRequest;

class CreateStampInfoAPIRequest extends APIRequest
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
            'stamp_image' => 'required',
            'name' => 'required',
            'email' => 'required',
            'file_name' => 'required'
        ];
    }
}
