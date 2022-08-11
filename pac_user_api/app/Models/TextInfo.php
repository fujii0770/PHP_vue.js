<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
class TextInfo extends Model
{

    public $table = 'text_info';
    public $timestamps = false;

    public $fillable = [
        'circular_document_id',
        'circular_operation_id',
        'text',
        'name',
        'email',
        'create_at',
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
        'text' => 'string',
        'name' => 'string',
        'email' => 'string',
        'create_at' => 'datetime'
    ];
}
