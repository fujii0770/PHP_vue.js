<?php

namespace App\Models;

use Eloquent as Model;

class CircularAttachment extends Model
{
    public $table = 'circular_attachment';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    public $fillable = [
      'circular_id',
        'confidential_flg',
        'file_name',
        'file_size',
        'create_user_id',
        'create_company_id',
        'edition_flg',
        'env_flg',
        'server_flg',
        'apply_user_id',
        'name',
        'title',
        'server_url',
        'status',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
    ];


    public $casts = [
        'id' => 'integer',
        'circular_id' => 'integer',
        'confidential_flg' => 'integer',
        'file_name' => 'string',
        'file_size' => 'string',
        'create_user_id' => 'integer',
        'create_company_id' => 'integer',
        'server_url' => 'string',
        'status' => 'integer',
        'create_at' =>'datetime',
        'create_user' => 'string',
        'update_at' =>'datetime',
        'update_user' => 'string'
    ];

    public $rules = [
        'circular_id' => 'required',
        'confidential_flg' => 'required',
        'file_name' => 'required',
        'create_user_id' => 'required',
        'create_company_id' => 'required',
        'create_user' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function circular()
    {
        return $this->belongsTo(Circular::class, 'circular_id');
    }
}
