<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SettingFrmTemplateAPIRequest extends FormRequest
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
            "templateSetting.frm_default_name" => ['required','max:100','regex:/^[^\/\:\*?\>\"\<\>\|]*$/'],
            "templateSetting.to_email_addr_imp" => "nullable|email|max:128",
            "templateSetting.to_email_name_imp" => "nullable|max:128",
            "templateSetting.frm_imp_cols" => 'nullable|json',
            "editItem.frm_template_edit_flg" => "required|integer|in:0,1,2",
            "editItem.frm_template_access_flg" => "required|integer|in:0,1,2",
            "editItem.remarks" => "nullable|max:100",
            "stamps" => "nullable|array",
            "stamps.*.stamp_flg" => "required|integer",
            "stamps.*.stamp_assign_id" => "required|integer",
            "stamps.*.stamp_deg" => "required|integer",
            "stamps.*.stamp_left" => "required|numeric",
            "stamps.*.stamp_page" => "required|integer",
            "stamps.*.stamp_top" => "required|numeric",
            "stamps.*.stamp_date" => "required|string",
        ];
    }

    public function attributes()
    {
        return [
            'templateSetting.frm_default_name' => '明細名',
            'templateSetting.to_email_addr_imp' => '送信先メールアドレス_IMP項目名',
            'templateSetting.to_email_name_imp' => '送信先名_IMP項目名',
            'editItem.frm_template_edit_flg' => '編集権限',
            'editItem.frm_template_access_flg' => 'アクセスフラグ',
            'editItem.remarks' => '備考',
        ];
    }
}
