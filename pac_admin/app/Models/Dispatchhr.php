<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Dispatchhr extends Model
{
    protected $table = 'dispatchhr';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'mst_admin_id',
        'mst_company_id',
        'name',
        'furigana',
        'regist_kbn',
        'gender_type',
        'birthdate',
        'age',
        'phone_no',
        'mobile_phone_no',
        'fax_no',
        'email',
        'mail_send_flg',
        'mobile_email',
        'mobile_mail_send_flg',
        'contact_method1',
        'contact_method2',
        'contact_method3',
        'contact_method4',
        'contact_method5',
        'nearest_station',
        'postal_code',
        'address1',
        'address2',
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
            'name' => 'required|max:128',
            'furigana' => 'required|max:128',
            'regist_kbn' => 'nullable|numeric',
            'gender_type' => 'nullable|numeric',
            'birthdate' => 'nullable|date',
            'age' => 'nullable|numeric',
            'phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'mobile_phone_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'fax_no' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'email' => 'nullable|email',
            'mail_send_flg' => 'nullable|numeric',
            'mobile_email' => 'nullable|email',
            'mobile_mail_send_flg' => 'nullable|numeric',
            'contact_method1' => 'nullable|numeric',
            'contact_method2' => 'nullable|numeric',
            'contact_method3' => 'nullable|numeric',
            'contact_method4' => 'nullable|numeric',
            'contact_method5' => 'nullable|numeric',
            'nearest_station' => 'nullable|max:64',
            'postal_code' => 'nullable|max:8|regex:/^[0-9-]{0,8}$/',
            'address1' => 'nullable|max:128',
            'address2' => 'nullable|max:128',
        ];
    }
    public function messages()
    {
        return [
            'postal_code.regex' => ':attribute ??????000-0000, 0000000????????????????????????????????????',
            'phone_no.regex'=>':attribute ??????0000-0000-0000????????????????????????????????????',
            'mobile_phone_no.regex'=>':attribute ??????TEL???0000-0000-0000????????????????????????????????????',
            'fax_no.regex'=>':attribute ??????0000-0000-0000????????????????????????????????????',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => '??????',
            'furigana' => '????????????',
            'regist_kbn' => '????????????',
            'gender_type' => '??????',  
            'birthdate' => '????????????',
            'age' => '??????',
            'phone_no' => '????????????',
            'mobile_phone_no' => '????????????',
            'fax_no' => 'FAX??????',
            'email' => '?????????????????????',                
            'mail_send_flg' => '??????????????????',
            'mobile_email' => '???????????????????????????',                
            'mobile_mail_send_flg' => '??????????????????',
            'contact_method' => '??????????????????',
            'nearest_station' => '?????????',
            'postal_code' => '????????????',
            'address1' => '??????1',
            'address2' => '??????2',
        ];
    }

}
