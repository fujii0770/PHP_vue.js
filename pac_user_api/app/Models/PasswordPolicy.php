<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PasswordPolicy extends Model
{
    protected $table = 'password_policy';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id',
        'min_length',
        'validity_period',
        'enable_password',
        'set_mail_as_password',
        'character_type_limit',
        'password_mail_validity_days',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [ 
        'mst_company_id' => 'integer',
        'min_length' => 'integer',
        'validity_period' => 'integer',
        'enable_password' => 'integer',
        'set_mail_as_password' => 'integer',
        'character_type_limit' => 'integer',
        'password_mail_validity_days' => 'integer',
     ];

     public function rules(){
        return [ 
            'min_length' => 'required|integer|between:4,12',
            'validity_period' => 'required|integer|between:0,999',
            'enable_password' => 'required|integer|between:0,1',
            'character_type_limit' => 'required|integer|between:0,1',
            'set_mail_as_password' => 'required|integer|between:0,1',
            // PAC_5-1970 パスワードメールの有効期限を変更する Start
            'password_mail_validity_days' => 'required|integer|between:1,14',
            // PAC_5-1970 End
        ];
    }
}
