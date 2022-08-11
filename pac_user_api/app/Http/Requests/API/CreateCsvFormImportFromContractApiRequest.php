<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Request;

class CreateCsvFormImportFromContractApiRequest extends FormRequest
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
            'accessId' => 'required',
            'accessCode' => 'required',
            'email' => 'required',
            'frm_template_code' => 'required',
            'file' => 'required|file|max:2048'
        ];
    }

    public function attributes()
    {
        return [
            'file' => 'ファイル'
        ];
    }
}
