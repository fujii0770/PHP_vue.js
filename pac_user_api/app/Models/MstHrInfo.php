<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class MstHrInfo
 * @package App\Models

 */
class MstHrInfo extends Model
{
    public $table = 'mst_hr_info';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    public $fillable = [
        'mst_user_id',
        'assigned_company',
        'Regulations_work_start_time',
        'Regulations_work_end_time',
        'overtime_unit',
        'break_time'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];
}
