<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Request;


class CreateFrmTemplateAPIRequest extends FormRequest
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
        $template_size_limit = (DB::table('mst_constraints')->where('mst_company_id',Request::user()->mst_company_id)->value('template_size_limit')) * 1024;
        return [
            "frm_template_code"=> [
//                'required',
                'max:15',
//                'string',
                // Rule::unique('frm_template')->where('mst_company_id',Request::user()->mst_company_id)
            ],
            "remarks" => "max:100",        
            "frm_template_edit_flg" => "required|integer|in:0,1,2",
            "frm_template_access_flg" => "required|integer|in:0,1,2",
            "frm_type_flg" => "required|integer|in:0,1",
            'file' => "required|file|max:$template_size_limit"
        ];
    }

    public function attributes()
    {
        return [
            'remarks' => '備考',
            'frm_template_edit_flg' => '編集権限',
            'frm_template_access_flg' => 'アクセスフラグ',
            'frm_type_flg' => '明細種別',
            'file' => 'ファイル',
        ];

    }
    public function messages()
    {
        return [
            // 'frm_template_code.unique' => '帳票テンプレートコードはすでに使われています。'
        ];
    }
}
