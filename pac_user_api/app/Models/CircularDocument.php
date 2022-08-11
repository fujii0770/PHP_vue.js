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
class CircularDocument extends Model
{
    public $table = 'circular_document';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';


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

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'circular_id' => 'integer',
        'origin_document_id' => 'integer',
        'confidential_flg' => 'integer',
        'file_name' => 'string',
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
        'confidential_flg' => 'required',
        'file_name' => 'required',
        'create_user' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function circular()
    {
        return $this->belongsTo(\App\Models\Circular::class, 'circular_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentData()
    {
        return $this->hasMany(\App\Models\DocumentDatum::class, 'circular_document_id');
    }
}
