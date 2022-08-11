<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class StampInfo
 * @package App\Models
 * @version November 15, 2019, 3:18 am UTC
 *
 * @property integer app_id
 * @property string stamp_image
 * @property string name
 * @property string email
 * @property string info_id
 * @property string serial
 * @property string file_name
 * @property string|\Carbon\Carbon create_at
 * @property string create_user
 * @property string|\Carbon\Carbon update_at
 * @property string update_user
 */
class StampInfo extends Model
{
    public $table = 'stamp_info';

    public $timestamps = false;
    const CREATED_AT = 'create_at';


    public $fillable = [
        'circular_document_id',
        'circular_operation_id',
        'mst_assign_stamp_id',
        'parent_send_order',
        'stamp_image',
        'name',
        'email',
        'bizcard_id',
        'env_flg',
        'server_flg',
        'edition_flg',
        'info_id',
        'file_name',
        'create_at',
        'time_stamp_permission',
        'serial',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'circular_document_id' => 'integer',
        'circular_operation_id' => 'integer',
        'mst_assign_stamp_id' => 'integer',
        'parent_send_order' => 'integer',
        'stamp_image' => 'string',
        'name' => 'string',
        'email' => 'string',
        'bizcard_id' => 'integer',
        'env_flg' => 'integer',
        'server_flg' => 'integer',
        'edition_flg' => 'integer',
        'info_id' => 'integer',
        'file_name' => 'string',
        'create_at' => 'datetime',
        'time_stamp_permission' => 'integer',
        'serial' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'circular_document_id' => 'required',
        'stamp_image' => 'required',
        'name' => 'required',
        'email' => 'required',
        'info_id' => 'required',
        'serial' => 'required',
        'file_name' => 'required'
    ];

    
}
