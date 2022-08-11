<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrWorkHours extends Model
{
    protected $table = 'hr_working_hours';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id'
        ,'mst_company_id'
        ,'definition_name'
        ,'work_form_kbn'
        ,'regulations_work_start_time'
        ,'regulations_work_end_time'
        ,'shift1_start_time'
        ,'shift1_end_time'
        ,'shift2_start_time'
        ,'shift2_end_time'
        ,'shift3_start_time'
        ,'shift3_end_time'
        ,'overtime_unit'
        ,'break_time'
        ,'create_at'
        ,'create_user'
        ,'update_at'
        ,'update_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public function rules(){
        return [
            'mst_company_id' => 'required',
            'work_form_kbn' => 'required',
        ];
    }


}
