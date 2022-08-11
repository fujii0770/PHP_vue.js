<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class CircularDocument
 * @package App\Models
 * @version November 25, 2019, 3:40 am UTC
 *
 * @property \App\Models\Circular circular
 * @property \Illuminate\Database\Eloquent\Collection documentData
 * @property integer circular_id
 * @property integer origin_document_id
 * @property integer confidential_flg
 * @property string file_name
 * @property string|\Carbon\Carbon create_at
 * @property string create_user
 * @property string|\Carbon\Carbon update_at
 * @property string update_user
 */
class CircularOperationHistory extends Model
{
    public $timestamps = false;
    public $table = 'circular_operation_history';

    public $fillable = [
        'circular_id',
        'circular_document_id',
        'operation_email',
        'operation_name',
        'acceptor_email',
        'acceptor_name',
        'circular_status',
        'create_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'circular_id' => 'integer',
        'circular_document_id' => 'integer',
        'operation_email' => 'string',
        'operation_name' => 'string',
        'acceptor_email' => 'string',
        'acceptor_name' => 'string',
        'circular_status' => 'string',
        'create_at' => 'datetime',
    ];
}
