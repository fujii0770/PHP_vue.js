<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class HrTimeCard
 * @package App\Models

 */
class HrTimeCard extends Model
{
    public $table = 'hr_timecard';

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

    /**
     * Scope function modified query with parameter get notice
     *
     * @param $query
     * @param $data
     * @return mixed
     */
    public function scopeGetHrWorkList($query, $data)
    {
        if (isset($data['working_month_from'])) {
            $query = $query->where('working_month', '>=', $data['working_month_from']);
        }
        if (isset($data['working_month_to'])) {
            $query = $query->where('working_month', '<=', $data['working_month_to']);
        }
        if (isset($data['submission_state'])) {
            $query = $query->where('submission_state', $data['submission_state']);
        }
        if (isset($data['approval_state'])) {
            $query = $query->where('approval_state', $data['approval_state']);
        }
        return $query;
    }


}
