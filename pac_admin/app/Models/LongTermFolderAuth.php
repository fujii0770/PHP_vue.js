<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongTermFolderAuth extends Model
{
    protected $table = 'long_term_folder_auth';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'long_term_folder_id',
        'auth_kbn',
        'auth_link_id',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules(){
        return [ ];
    }
}

