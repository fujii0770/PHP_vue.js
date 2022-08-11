<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AdvertisementManagement
 * @package App\Models
 *
 */

class AdvertisementManagement extends Model
{
    protected $table = 'advertisement_management';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'mst_advertisement_id',
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
     * Scope function modified query with parameter get advertisement
     *
     * @param $query
     * @param $data
     * @return mixed
     */
    public function scopeGetAdvertisement ($query, $data)
    {

        if (isset($data['mst_advertisement_id'])) {
            $query = $query->where('mst_advertisement_id', $data['mst_advertisement_id']);
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
