<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NoticeReadManagement
 * @package App\Models

 */
class NoticeReadManagement extends Model
{
    protected $table = 'notice_read_management';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'notice_management_id',
        'mst_user_id',
        'is_read',
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
}
