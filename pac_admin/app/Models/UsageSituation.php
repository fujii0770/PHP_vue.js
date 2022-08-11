<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UsageSituation extends Model
{
    protected $table = 'usage_situation';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id','company_name','company_name_kana','target_month','user_total_count','total_name_stamp','total_date_stamp','total_common_stamp',
        'max_date','total_time_stamp','create_user','update_user','total_valid_stamp','timestamps_count','user_contract_count','total_option_contract_count',
        'convenient_upper_limit','total_convenient_stamp'
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
