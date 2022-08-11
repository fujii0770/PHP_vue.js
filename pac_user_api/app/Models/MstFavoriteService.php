<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MstFavoriteService
 * @package App\Models

 */
class MstFavoriteService extends Model
{
    protected $table = 'mst_favorite_service';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'service_name',
        'logo_src',
        'url'
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
    protected $casts = [];

}
