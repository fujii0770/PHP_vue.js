<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UsageSituationDetail extends Model
{
    protected $table = 'usage_situation_detail';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'target_date','mst_company_id','company_name','stamp_contract','company_name_kana','guest_company_id','guest_company_name','guest_company_name_kana','guest_company_app_env','guest_company_contract_server','user_count_valid','user_count_activity','storage_stamp','storage_document','storage_operation_history','storage_mail','storage_sum','stamp_count','stamp_over_count','timestamp_count','timestamp_leftover_count','circular_applied_count','circular_completed_count','circular_completed_total_time','multi_comp_out','multi_comp_in','upload_count_pdf','upload_count_excel','upload_count_word','download_count_pdf'
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
        return [ ];
    }
}
