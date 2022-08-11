<?php

namespace App;

use App\Http\Utils\AppUtils;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class CompanyAdmin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $table = 'mst_admin';

    protected $guard_name = 'web';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id','login_id','given_name','family_name', 'email',  'role_flg',
        'password','department_name','phone_number','state_flg','create_user','update_user',
        'email_auth_flg', 'email_auth_dest_flg', 'auth_email', 'enable_email', 'email_format',
        'menu_state_flg'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_mfa_login_at'
    ];

    public function getFullName()
    {
        return implode(' ', [$this->family_name, $this->given_name]);
    }

    public function getReallyMenuStateFlg(){
        $company=DB::table('mst_company')
            ->where('id','=',$this->mst_company_id)
            ->first();
        if (!$company){
            return 1;
        }
        if ($this->menu_state_flg==0){
            return $company->contract_edition==AppUtils::CONTRACT_EDITION_TRIAL?1:2;
        }else{
            return $this->menu_state_flg;
        }
    }
}
