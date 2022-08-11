<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrTimecardDetail extends Model
{
    //
    public $table = 'hr_timecard_detail';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    public $fillable = [
        'mst_company_id',
        'mst_user_id',
        'work_start_time',
        'work_end_time',
        'working_time',
        'absent_flg',
        'earlyleave_flg',
        'late_flg',
        'paid_vacation_flg',
        'sp_vacation_flg',
        'day_off_flg',
        'approval_state',
        'approval_user',
        'approval_date',
        'state',
        'memo',
        'admin_memo',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'mst_company_id' => 'integer',
        'mst_user_id' => 'integer',
        'work_start_time' => 'datetime',
        'work_end_time' => 'datetime',
        'working_time',
        'absent_flg' => 'integer',
        'earlyleave_flg' => 'integer',
        'late_flg' => 'integer',
        'paid_vacation_flg' => 'integer',
        'sp_vacation_flg' => 'integer',
        'day_off_flg' => 'integer',
        'approval_state' => 'integer',
        'approval_user' => 'string',
        'approval_date' => 'datetime',
        'state' => 'integer',
        'memo' => 'string',
        'admin_memo' => 'string',
        'create_at' => 'datetime',
        'create_user' => 'string',
        'update_at' => 'datetime',
        'update_user' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'mst_company_id' => 'required',
    ];

    /**
     * Scope function modified query with parameter get HrTimecardDetail columns
     *
     * @param $query
     * @param $data
     * @return mixed
     */
    public function scopeSelectHrTimeCardDetail($query, $data)
    {
        if (in_array('work_date', $data)) {
            $query = $query->addSelect('work_date');
        }
        if (in_array('work_start_time', $data)) {
            $query = $query->addSelect('work_start_time');
        }
        if (in_array('work_end_time', $data)) {
            $query = $query->addSelect('work_end_time');
        }
        if (in_array('break_time', $data)) {
            $query = $query->addSelect('break_time');
        }
        if (in_array('working_time', $data)) {
            $query = $query->addSelect('working_time');
        }
        if (in_array('overtime', $data)) {
            $query = $query->addSelect('overtime');
        }
        if (in_array('absent_flg', $data)) {
            $query = $query->addSelect('absent_flg');
        }
        if (in_array('late_flg', $data)) {
            $query = $query->addSelect('late_flg');
        }
        if (in_array('earlyleave_flg', $data)) {
            $query = $query->addSelect('earlyleave_flg');
        }
        if (in_array('paid_vacation_flg', $data)) {
            $query = $query->addSelect('paid_vacation_flg');
        }
        if (in_array('sp_vacation_flg', $data)) {
            $query = $query->addSelect('sp_vacation_flg');
        }
        if (in_array('day_off_flg', $data)) {
            $query = $query->addSelect('day_off_flg');
        }
        if (in_array('memo', $data)) {
            $query = $query->addSelect('memo');
        }
        if (in_array('admin_memo', $data)) {
            $query = $query->addSelect('admin_memo');
        }
        return $query;
    }

    /**
     * Scope function modified query to get HrTimecardDetail
     *
     * @param $query
     * @return mixed
     */
    public function scopeExportWorkListCSV($query) {
        $query = $query->whereNotNull('work_start_time')->orWhereNotNull('work_end_time')
                        ->orWhere('paid_vacation_flg', '<>', 0)->orWhere('sp_vacation_flg', '<>', 0)->orWhere('day_off_flg', '<>', 0)->orWhere('absent_flg', '<>', 0);
        return $query;
    }

}
