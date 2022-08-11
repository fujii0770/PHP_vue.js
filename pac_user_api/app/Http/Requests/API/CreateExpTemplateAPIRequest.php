<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Request;

class CreateExpTemplateAPIRequest extends FormRequest
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
        $exp_template_size_limit = (DB::table('mst_constraints')->where('mst_company_id',Request::user()->mst_company_id)->value('exp_template_size_limit')) * 1024;
        return [
            "remarks" => "max:100",      
            'file' => "required|file|max:$exp_template_size_limit"
        ];
    }

    public function attributes()
    {
        return [
            'remarks' => '備考',
            'file' => 'ファイル'
        ];
    }
}
