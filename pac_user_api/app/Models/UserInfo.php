<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class User
 * @package App\Models
 * @version November 12, 2019, 3:45 am UTC
 *
 * @property \App\Models\MstCompany mstCompany
 * @property \Illuminate\Database\Eloquent\Collection folders
 * @property \Illuminate\Database\Eloquent\Collection mstAssignStamps
 * @property \Illuminate\Database\Eloquent\Collection mstUserInfos
 * @property integer mst_company_id
 * @property string login_id
 * @property integer system_id
 * @property string family_name
 * @property string given_name
 * @property string email
 * @property string password
 * @property integer state_flg
 * @property integer amount
 * @property string|\Carbon\Carbon create_at
 * @property string create_user
 * @property string|\Carbon\Carbon update_at
 * @property string update_user
 */
class UserInfo extends Model
{
    use SoftDeletes;

    public $table = 'mst_user_info';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'id',
        'mst_user_id',
        'mst_department_id',
        'mst_position_id',
        // PAC_5-1599 追加部署と役職 Start
        'mst_department_id_1',
        'mst_position_id_1',
        'mst_department_id_2',
        'mst_position_id_2',
        // PAC_5-1599 End
        'date_stamp_config',
        'api_apps',
        'approval_request_flg',
        'browsed_notice_flg',
        'update_notice_flg',
        'mfa_type',
        'email_auth_dest_flg',
        'completion_notice_flg',
        'page_display_first',
        'circular_info_first',
        'time_stamp_permission',
        'operation_notice_flg',
        'address',
        'auth_email',
        'fax_number',
        'phone_number',
        'postal_code',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
        'last_stamp_id',
        'user_profile_data',
        'sticky_note_flg',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'mst_user_id' => 'integer',
        'mst_department_id' => 'integer',
        'mst_position_id' => 'integer',
        // PAC_5-1599 追加部署と役職 Start
        'mst_department_id_1' => 'integer',
        'mst_position_id_1' => 'integer',
        'mst_department_id_2' => 'integer',
        'mst_position_id_2' => 'integer',
        // PAC_5-1599 End
        'date_stamp_config' => 'integer',
        'api_apps' => 'integer',
        'approval_request_flg' => 'integer',
        'browsed_notice_flg' => 'integer',
        'update_notice_flg' => 'integer',
        'mfa_type' => 'integer',
        'email_auth_dest_flg' => 'integer',
        'completion_notice_flg' => 'integer',
        'page_display_first' => 'string',
        'circular_info_first' => 'string',
        'time_stamp_permission' => 'integer',
        'operation_notice_flg' => 'integer',
        'address' => 'string',
        'auth_email' => 'string',
        'fax_number' => 'string',
        'phone_number' => 'string',
        'postal_code' => 'string',
        'create_at' => 'datetime',
        'create_user' => 'string',
        'update_at' => 'datetime',
        'update_user' => 'string',
        'last_stamp_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id' => 'integer|max:9223372036854775807',
        'mst_user_id' => 'integer|required|max:9223372036854775807',
        'mst_department_id' => 'nullable|integer|max:9223372036854775807',
        'mst_position_id' => 'nullable|integer|max:9223372036854775807',
        // PAC_5-1599 追加部署と役職 Start
        'mst_department_id_1'=> 'nullable|integer|max:9223372036854775807',
        'mst_position_id_1'=> 'nullable|integer|max:9223372036854775807',
        'mst_department_id_2'=> 'nullable|integer|max:9223372036854775807',
        'mst_position_id_2'=> 'nullable|integer|max:9223372036854775807',
        // PAC_5-1599 End
        'date_stamp_config' => 'boolean|required',
        'api_apps' => 'boolean|required',
        'approval_request_flg' => 'boolean|required',
        'browsed_notice_flg' => 'boolean|required',
        'update_notice_flg' => 'boolean|required',
        'mfa_type' => 'integer|required|max:2147483647',
        'email_auth_dest_flg' => 'boolean|required',
        'completion_notice_flg' => 'boolean|required',
        'page_display_first' => 'nullable|string',
        'circular_info_first' => 'nullable|string',
        'time_stamp_permission' => 'integer|required',
        'operation_notice_flg' => 'integer|required',
        'address' => 'nullable|string|max:256',
        'auth_email' => 'nullable|string|max:256',
        'create_at' => 'date|required',
        'update_at' => 'nullable|date',
        'create_user' => 'nullable|string|max:128',
        'update_user' => 'nullable|string|max:128',
        'fax_number' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
        'phone_number' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
        'postal_code' => 'nullable|max:10|regex:/^[0-9-]{0,10}$/',
        'last_stamp_id' => 'nullable|integer',
        'user_profile_data' => 'nullable|string',
        'phone_number_extension' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
        'phone_number_mobile' => 'nullable|max:15|regex:/^[0-9-]{0,15}$/',
    ];
}
