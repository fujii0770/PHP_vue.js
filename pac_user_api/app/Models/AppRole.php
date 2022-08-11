<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AppRole
 * @package App\Models

 */
class AppRole extends Model
{
    //protected $table = 'app_role';
    protected $table = 'mst_application_companies'; //app_roleが作成出来たら差し替える

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        //'name',
        'mst_company_id',
        'mst_application_id',
        //'memo',
        //'is_default'
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
