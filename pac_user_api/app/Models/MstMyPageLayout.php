<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MstMyPageLayout
 * @package App\Models

 */
class MstMyPageLayout extends Model
{
    protected $table = 'mst_mypage_layout';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'layout_name',
        'layout_src',
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
