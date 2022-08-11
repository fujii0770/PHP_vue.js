<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CircularUser
 * @package App\Models
 * @version November 20, 2019, 10:16 am UTC
 *
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
class FrmTemplateCircularUser extends Model
{
    public $table = 'frm_template_circular_user';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';


    public $fillable = [
        'frm_template_id',
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
        'create_at',
        'create_user',
        'update_at',
        'update_user',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'frm_template_id' => 'integer',
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
        'create_at' => 'datetime',
        'create_user' => 'string',
        'update_at' => 'datetime',
        'update_user' => 'string',
        'sent_date' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'frm_template_id' => 'required',
        'parent_send_order' => 'required',
        'child_send_order' => 'required',
        'email' => 'required',
        'create_at' => 'required'
    ];
}
