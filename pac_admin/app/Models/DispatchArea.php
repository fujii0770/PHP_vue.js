<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchArea extends Model
{
    protected $table = 'dispatcharea';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'dispatcharea_agency_id',
        'department',
        'position',
        'department_date',
        'postal_code',
        'address1',
        'address2',
        'main_phone_no',
        'mobile_phone_no',
        'fax_no',
        'email',
        'responsible_name',
        'responsible_phone_no',
        'commander_name',
        'commander_phone_no',
        'troubles_name',
        'troubles_phone_no',
        'dispatcharea_holiday',
        'dispatcharea_holiday_other',
        'welfare_kbn',
        'welfare_other',
        'separate_clause',
        'remarks',
        'fraction_type',
        'memo',
        'manager_office_name',
        'manager_name',
        'caution',
        'status_kbn',
        'evaluation',
        'del_flg',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id = 'null'){
        return [
            'department' => 'nullable|max:128',
            'position' => 'nullable|max:128',
            'department_date' => 'nullable|date',
            'postal_code' => 'nullable|max:8|regex:/^[0-9-]{0,8}$/',
            'address1' => 'nullable|max:128',
            'address2' => 'nullable|max:128',
            'main_phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'mobile_phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'fax_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'email' => 'nullable|max:256|email',
            'responsible_name' => 'nullable|max:128',
            'responsible_phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'commander_name' => 'nullable|max:128',
            'commander_phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'troubles_name' => 'nullable|max:128',
            'troubles_phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'dispatcharea_holiday' => 'nullable|numeric',
            'dispatcharea_holiday_other' => 'nullable|max:128',
            'welfare_kbn' => 'nullable|numeric',
            'welfare_other' => 'nullable|max:128',
            'separate_clause' => 'nullable|max:256',
            'remarks' => 'nullable|max:256',
            'fraction_type' => 'nullable|numeric',
            'memo' => 'nullable|max:256',
            'manager_office_name' => 'nullable|max:256',
            'manager_name' => 'nullable|max:128',
            'caution' => 'nullable|max:256',
            'status_kbn' => 'nullable|numeric',
            'evaluation' => 'nullable|max:256',
            
        ];
    }
    public function messages()
    {
        return [
            'postal_code.regex' => ':attribute は、000-0000, 0000000形式で入力してください。',
            'main_phone_no.regex'=>':attribute は、0000-0000-0000形式で入力してください。',
            'mobile_phone_no.regex'=>':attribute は、TEL　0000-0000-0000形式で入力してください。',
            'fax_no.regex'=>':attribute は、0000-0000-0000形式で入力してください。',
            'responsible_phone_no.regex'=>':attribute は、0000-0000-0000形式で入力してください。',
            'commander_phone_no.regex'=>':attribute は、0000-0000-0000形式で入力してください。',
            'troubles_phone_no.regex'=>':attribute は、0000-0000-0000形式で入力してください。',
        ];
    }
    public function attributes(): array
    {
        return [
            'department' => '部署名',
            'position' => '部署長役職',
            'department_date' => '部署抵触日',
            'postal_code' => '郵便番号',
            'address1' => '住所1',
            'address2' => '住所2',
            'main_phone_no' => '代表電話番号',
            'mobile_phone_no' => '携帯電話番号',
            'fax_no' => 'FAX番号',
            'email' => 'メールアドレス',
            'responsible_name' => '派遣先責任者',
            'responsible_phone_no' => '派遣先責任者電話番号',
            'commander_name' => '指揮命令者',
            'commander_phone_no' => '指揮命令者電話番号',
            'troubles_name' => '苦情申出先',
            'troubles_phone_no' => '苦情申出先電話番号',
            'dispatcharea_holiday' => '派遣時の休日',
            'dispatcharea_holiday_other' => '派遣時の休日（その他）',
            'welfare_kbn' => '派遣時の福利厚生',
            'welfare_other' => '派遣時の福利厚生（その他）',
            'separate_clause' => '別条項',
            'remarks' => '備考',
            'fraction_type' => '1円未満端数処理',
            'memo' => 'その他メモ',
            'manager_office_name' => '担当事業所',
            'manager_name' => '担当者',
            'caution' => '注意事項',
            'status_kbn' => '取引ステータス',
            'evaluation' => '派遣先からの評価',          
        ];
    }
}
