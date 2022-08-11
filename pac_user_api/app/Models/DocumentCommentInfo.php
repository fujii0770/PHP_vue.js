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
class DocumentCommentInfo extends Model
{
    public $timestamps = false;
    public $table = 'document_comment_info';

    public $fillable = [
        'circular_document_id',
        'circular_operation_id',
        'parent_send_order',
        'name',
        'email',
        'text',
        'private_flg',
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
        'parent_send_order' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'text' => 'string',
        'private_flg' => 'integer',
        'create_at' => 'datetime',
    ];
}
