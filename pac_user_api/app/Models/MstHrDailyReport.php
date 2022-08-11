<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class MstHrDailyReport
 * @package App\Models

 */
class MstHrDailyReport extends Model
{
    public $table = 'mst_hr_daily_report';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    public $fillable = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [

    ];
}
