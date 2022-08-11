<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FavoriteService
 * @package App\Models

 */
class MyPage extends Model
{
    protected $table = 'mypage';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_mypage_layout_id',
        'page_name',
        'layout'
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
