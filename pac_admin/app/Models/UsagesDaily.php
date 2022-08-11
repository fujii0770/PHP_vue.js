<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsagesDaily extends Model
{
    protected $table = 'usages_daily';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id',
        'company_name',
        'company_name_kana',
        'date',
        'new_requests',
        'guest_company_id',
        'guest_company_name',
        'guest_company_name_kana',
        'guest_company_app_env',
        'guest_company_contract_server',
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
