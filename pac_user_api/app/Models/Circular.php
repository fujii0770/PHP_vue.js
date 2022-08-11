<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circular extends Model
{
    protected $table = 'circular';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_user_id',
        'access_code_flg',
        'access_code',
        'outside_access_code_flg',
        'outside_access_code',
        'hide_thumbnail_flg',
        're_notification_day',
        'circular_status',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
        'address_change_flg',
        'first_page_data',
        'env_flg',
        'edition_flg',
        'server_flg',
        'origin_circular_id',
        'current_aws_circular_id',
        'current_k5_circular_id',
        'applied_date',
        'completed_date',
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

    public function rules(){
        return [ ];
    }
}
