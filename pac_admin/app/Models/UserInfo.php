<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserInfo extends Model
{
    protected $table = 'mst_user_info';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_user_id','mst_department_id','mst_position_id','phone_number','fax_number','postal_code','address',
        'date_stamp_config', 'api_apps','approval_request_flg', 'browsed_notice_flg','update_notice_flg', 'create_user', 'update_user',
        'mfa_type', 'email_auth_dest_flg', 'auth_email','time_stamp_permission','template_flg', 'enable_email', 'email_format',
        'rotate_angle_flg', 'default_rotate_angle', 'scheduler'
        ,'mst_department_id_1', 'mst_department_id_2', 'mst_position_id_1', 'mst_position_id_2', 'sticky_note_flg'
        ,'phone_number_extension','phone_number_mobile','sticky_note_flg'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password' ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id = "", $is_option = false){
        $rules = [
            'phone_number' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'fax_number' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'postal_code' => 'nullable|max:10|regex:/^[0-9-]{0,10}$/',
            'address' => 'nullable|max:128',
            'enable_email_browsed' => 'nullable|numeric',
            'enable_email_update' => 'nullable|numeric',
            'auth_email' => 'nullable|email|max:256',
            'mfa_type' => 'required|numeric',
            /*PAC_5-3018 S*/
            'phone_number_extension' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            'phone_number_mobile' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
            /*PAC_5-3018 E*/
        ];
        if(!$is_option){
            $rules['date_stamp_config'] ='required|numeric';
            $rules['api_apps'] ='required|numeric';
            $rules['time_stamp_permission'] ='required|numeric';
            $rules['email_auth_dest_flg'] ='required|numeric';
            $rules['enable_email'] ='required|numeric';
            $rules['email_format'] ='required|numeric';
        }
        return $rules;
    }
}
