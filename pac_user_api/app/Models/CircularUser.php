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
class CircularUser extends Model
{
    public $table = 'circular_user';
    
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';


    public $fillable = [
        'circular_id',
        'parent_send_order',
        'child_send_order',
        'env_flg',
        'edition_flg',
        'server_flg',
        'mst_company_id',
        'mst_company_name',
        'return_flg',
        'mst_user_id',
        'email',
        'name',
        'title',
        'del_flg',
        'circular_status',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
        'received_date',
        'sent_date',
        'plan_id',
        'special_site_receive_flg',
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
        'child_send_order' => 'integer',
        'env_flg' => 'integer',
        'edition_flg' => 'integer',
        'server_flg' => 'integer',
        'mst_company_id' => 'integer',
        'mst_company_name' => 'string',
        'return_flg' => 'integer',
        'mst_user_id' => 'integer',
        'email' => 'string',
        'name' => 'string',
        'title' => 'string',
        'del_flg' => 'integer',
        'circular_status' => 'integer',
        'create_at' => 'datetime',
        'create_user' => 'string',
        'update_at' => 'datetime',
        'update_user' => 'string',
        'received_date' => 'datetime',
        'sent_date' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'circular_id' => 'required',
        'parent_send_order' => 'required',
        'child_send_order' => 'required',
        'email' => 'required',
        'title' => 'required',
        'del_flg' => 'required',
        'circular_status' => 'required',
        'create_at' => 'required'
    ];
}
