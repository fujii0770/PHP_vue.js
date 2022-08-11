<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HrInfo extends Model
{
    protected $table = 'mst_hr_info';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'mst_user_id'
      ,'assigned_company'
      ,'Regulations_work_start_time'
      ,'Regulations_work_end_time'
      ,'shift1_start_time'
      ,'shift1_end_time'
      ,'shift2_start_time'
      ,'shift2_end_time'
      ,'shift3_start_time'
      ,'shift3_end_time'
      ,'Overtime_unit'
      ,'break_time'
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
        'Overtime_unit' => 0,
        'break_time'    => 0,
    ];

    public function rules(){
        return [
            'assigned_company' => 'required|string|max:128',
 //           'Regulations_work_start_time' => 'required',
 //           'Regulations_work_end_time' => 'required',
            'Overtime_unit' => 'required|numeric|max:999',
            'break_time' => 'required|numeric|max:999',
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','mst_user_id');
    }

}
