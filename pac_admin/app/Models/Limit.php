<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Limit extends Model
{
    protected $table = 'mst_limit';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id','storage_local', 'storage_box', 'storage_google','storage_dropbox','storage_onedrive','enable_any_address',
        'link_auth_flg','enable_email_thumbnail', 'receiver_permission', 'create_user','update_user',
        'environmental_selection_dialog', 'time_stamp_permission',
        'box_enabled_automatic_storage', 'box_enabled_folder_to_store', 'box_auto_save_folder_id', 'box_enabled_output_file',
        'box_enabled_automatic_delete', 'box_max_auto_delete_days', 'box_refresh_token',
        'mfa_login_timing_flg','mfa_interval_hours','use_mobile_app_flg','box_refresh_token_updated_date',
        'text_append_flg','require_print','limit_skip_flg','require_approve_flag','default_stamp_history_flg','shachihata_login_flg','with_box_login_flg',
        'limit_receive_plan_flg',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules(){
        return [
            'mst_company_id' => 'required|numeric',
            'storage_local' => 'required|numeric',
            'storage_box' => 'required|numeric',
            'storage_google' => 'required|numeric',
            'storage_dropbox' => 'required|numeric',
            'storage_onedrive' => 'required|numeric',
            'enable_any_address' => 'required|numeric',
            'link_auth_flg' => 'required|numeric',
            'enable_email_thumbnail' => 'required|numeric',
            'receiver_permission' => 'required|numeric',
            'create_user' => 'required|max:128',
            'environmental_selection_dialog' => 'required|numeric',
            'time_stamp_permission' => 'required|numeric|max:1',
            'mfa_login_timing_flg' => 'required|numeric|max:1',
            'mfa_interval_hours' => 'required|numeric|min:1|max:24',
            'use_mobile_app_flg' => 'integer|between:0,1',
            'text_append_flg' => 'integer|between:0,1',
            'require_print' => 'integer|between:0,1',
            'limit_skip_flg' => 'integer|between:0,1',
            'require_approve_flag' => 'integer|between:0,1',
            'default_stamp_history_flg' => 'integer|between:0,1',
            'shachihata_login_flg' => 'integer|between:0,1',
            'with_box_login_flg' => 'integer|between:0,1',
            'limit_receive_plan_flg' => 'integer|between:0,1',
        ];
    }
}
