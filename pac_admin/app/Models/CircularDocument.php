<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CircularDocument
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
class CircularDocument extends Model
{
    public $table = 'circular_document';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'circular_id',
        'origin_document_id',
        'confidential_flg',
        'file_name',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
    ];
}
