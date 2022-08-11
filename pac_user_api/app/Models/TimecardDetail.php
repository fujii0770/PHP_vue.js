<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TimecardDetail extends Model
{
    protected $table = 'hr_timecard_detail';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var arrays
     */
    protected $fillable = [
       'mst_company_id'
        ,'mst_user_id'
        ,'work_start_time'
        ,'work_end_time'
        ,'late_flg'
        ,'earlyleave_flg'
        ,'paid_vacation_flg'
        ,'sp_vacation_flg'
        ,'day_off_flg'
        ,'approval_state'
        ,'approval_user'
        ,'approval_date'
        ,'state'
        ,'memo'
        ,'admin_memo'
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

    //  更新しかしないからデフォルト値いらないよね？
    protected $attributes = [
    ];

    //  これもいらないか？
    public function rules(){
        return [
        ];
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','mst_company_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','mst_user_id');
    }

    /**
     * Scope function modified query to get HrTimecardDetail
     *
     * @param $query
     * @return mixed
     */
    public function scopeExportWorkListCSV($query) {
        $query = $query->where('work_start_time', '<>', 'NULL')->orWhere('work_end_time', '<>', 'NULL')
            ->orWhere('paid_vacation_flg', '<>', 0)->orWhere('sp_vacation_flg', '<>', 0)->orWhere('day_off_flg', '<>', 0);
        return $query;
    }
}
