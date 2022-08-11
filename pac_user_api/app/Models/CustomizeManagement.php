<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class CustomizeManagement
 * @package App\Models
 *
 */

class CustomizeManagement extends Model
{
    protected $table = 'customize_management';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'mst_customize_id',
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
     * Scope function modified query with parameter get customize
     *
     * @param $query
     * @param $data
     * @return mixed
     */
    public function scopeGetCustomize ($query, $data)
    {
        if (isset($data['mst_customize_id'])) {
            $query = $query->where('mst_customize_id', $data['mst_customize_id']);
        }

        if (isset($data['mst_company_id'])) {
            $query = $query->where('mst_company_id', $data['mst_company_id']);
        }

        if (!isset($data['mst_company_id']) && isset($data['mst_department_id']) && isset($data['mst_position_id'])) {
            $query = $query->where([
                'mst_department_id' => $data['mst_department_id'],
                'mst_position_id' => $data['mst_position_id']
            ]);
        } else {
            $query = $query->where(function ($query1) use ($data){
                $query1->orWhere(function ($query2) {
                    $query2->whereNull('mst_department_id')
                        ->whereNull('mst_position_id');
                });
                if (isset($data['mst_department_id'])) {
                    $query1->orWhere(function ($query2) use ($data) {
                        $query2->where('mst_department_id', $data['mst_department_id'])
                            ->whereNull('mst_position_id');
                    });
                }
                if (isset($data['mst_position_id'])) {
                    $query1->orWhere(function ($query2) use ($data) {
                        $query2->whereNull('mst_department_id')
                            ->where('mst_position_id', $data['mst_position_id']);
                    });
                }
            });
        }

        if (isset($data['location_type'])) {
            if (!is_array($data['location_type'])) $data['location_type'] = explode(',', $data['location_type']);
            $query = $query->whereIn('location_type', $data['location_type']);
        }

        return $query;
    }

}
