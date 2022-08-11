<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Favorite
 * @package App\Models

 */
class Favorite extends Model
{
    public $table = 'favorite_route';
    
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

  
    public $fillable = [
        'mst_user_id',
        'favorite_no',
        'display_no',        
        'name',
        'email',
        'create_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'mst_user_id'   => 'integer',
        'favorite_no'   => 'integer',
        'display_no'    => 'integer',        
        'name'          => 'string',
        'email'         => 'string',
        'create_at'     => 'datetime',        
        'favorite_name' => 'string',
    ];
}
