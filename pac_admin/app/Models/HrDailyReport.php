<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HrDailyReport extends Model
{
    protected $table = 'mst_hr_daily_report';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'id'
      ,'mst_user_id'
      ,'report_date'
      ,'daily_report'
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

    protected $attributes = [
        //'_flg' => 0,
    ];

    public function rules(){
        return [
             'report_date'  => 'required|date'
            ,'daily_report' => 'string|max:512'
        ];
    }

}
