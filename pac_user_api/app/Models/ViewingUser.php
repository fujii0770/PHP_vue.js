<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CircularUser
 * @package App\Models
 * @version November 20, 2019, 10:16 am UTC
 *
 * @property \App\Models\Circular circular
 * @property integer circular_id
 * @property integer send_order
 * @property integer env_flg
 * @property integer mst_company_id
 * @property integer circular_no
 * @property string email
 * @property string name
 * @property string title
 * @property string text
 * @property integer circular_status
 * @property string|\Carbon\Carbon create_at
 * @property string create_user
 * @property string|\Carbon\Carbon update_at
 * @property string update_user
 */
class ViewingUser extends Model
{
    public $table = 'viewing_user';
    
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';


    public $fillable = [
        'circular_id',
        'parent_send_order',
        'mst_company_id',
        'mst_user_id',
        'del_flg',
        'memo',
        'origin_circular_url',
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
        'circular_id' => 'integer',
        'parent_send_order' => 'integer',
        'mst_company_id' => 'integer',
        'mst_user_id' => 'integer',
        'memo' => 'string',
        'origin_circular_url' => 'string',
        'del_flg' => 'integer',
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
        'circular_id' => 'required',
        'parent_send_order' => 'required',
        'del_flg' => 'required',
        'create_at' => 'required'
    ];
}
