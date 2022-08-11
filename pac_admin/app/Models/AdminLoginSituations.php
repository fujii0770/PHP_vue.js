<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLoginSituations extends Model
{
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'create_at',
        'update_at'
    ];
}
