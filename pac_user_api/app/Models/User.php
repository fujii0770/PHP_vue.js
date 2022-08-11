<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class User
 * @package App\Models
 * @version November 12, 2019, 3:45 am UTC
 *
 * @property \App\Models\MstCompany mstCompany
 * @property \Illuminate\Database\Eloquent\Collection folders
 * @property \Illuminate\Database\Eloquent\Collection mstAssignStamps
 * @property \Illuminate\Database\Eloquent\Collection mstUserInfos
 * @property integer mst_company_id
 * @property string login_id
 * @property integer system_id
 * @property string family_name
 * @property string given_name
 * @property string email
 * @property string password
 * @property integer state_flg
 * @property integer amount
 * @property string|\Carbon\Carbon create_at
 * @property string create_user
 * @property string|\Carbon\Carbon update_at
 * @property string update_user
 */
class User extends Model
{
    use SoftDeletes;

    public $table = 'mst_user';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'mst_company_id',
        'login_id',
        'system_id',
        'family_name',
        'given_name',
        'email',
        'password',
        'state_flg',
        'amount',
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
        'login_id' => 'string',
        'system_id' => 'integer',
        'family_name' => 'string',
        'given_name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'state_flg' => 'integer',
        'amount' => 'integer',
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
        'mst_company_id' => 'required',
        'login_id' => 'required',
        'system_id' => 'required',
        'family_name' => 'required',
        'given_name' => 'required',
        'email' => 'required',
        'password' => 'required',
        'state_flg' => 'required',
        'amount' => 'required',
        'create_user' => 'required'
    ];
}
