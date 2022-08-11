<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Contact
 * @package App\Models

 */
class Contact extends Model
{
    public $table = 'address';
    
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

  
    public $fillable = [
        'mst_company_id',
        'mst_user_id',
        'type',
        'name',
        'email',
        'company_name',
        'position_name',
        'group_name',
        'state',
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
        'mst_company_id' => 'integer',
        'mst_user_id' => 'integer',
        'type' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'company_name' => 'string',
        'position_name' => 'string',
        'group_name' => 'string',
        'state' => 'integer',
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
        'name' => 'required',
        'email' => 'required|email',
        'group_name' => 'nullable|string|max:256'
    ];
}
