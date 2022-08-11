<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NoticeManagement
 * @package App\Models

 */
class NoticeManagement extends Model
{
    protected $table = 'notice_management';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'mst_notice_id',
        'mst_company_id',
        'mst_department_id',
        'mst_position_id'
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
    protected $casts = [];

    /**
     * Scope function modified query with parameter get notice
     *
     * @param $query
     * @param $data
     * @return mixed
     */
    public function scopeGetNotice($query, $data)
    {
        if (isset($data['type'])) {
            $query = $query->whereIn('type', $data['type']);
        }
        if (isset($data['mst_notice_id'])) {
            $query = $query->where('mst_notice_id', $data['mst_notice_id']);
        }

        if (!isset($data['mst_company_id']) && isset($data['mst_department_id']) && isset($data['mst_position_id'])) {
            $query = $query->where([
                'mst_department_id' => $data['mst_department_id'],
                'mst_position_id' => $data['mst_position_id']
            ]);
            // return query if is special case
            return $query;
        }
        if (isset($data['mst_company_id'])) {
            $query = $query->where('mst_company_id', $data['mst_company_id']);
        }
        if (isset($data['mst_department_id'])) {
            $query = $query->where(function ($query1) use ($data){
                $query1->whereNull('mst_department_id')
                    ->orWhere('mst_department_id', $data['mst_department_id']);
            });
        }
        if (isset($data['mst_position_id'])) {
            $query = $query->where(function ($query1) use ($data){
                $query1->whereNull('mst_position_id')
                    ->orWhere('mst_position_id', $data['mst_position_id']);
            });
        }

        return $query;
    }
}
