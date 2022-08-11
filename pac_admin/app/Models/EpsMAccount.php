<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EpsMAccount extends Model
{
    protected $table = 'eps_m_account';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id'
       ,'account_name'
       ,'remarks'
       ,'display_order'
       ,'deleted_at'
       ,'version'
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

    protected $attributes = [
        //'_flg' => 0,
    ];

    public function rules(){
        return [
             'account_name'  => 'required|string|max:20'
            ,'remarks' => 'max:100'
        ];
    }

}
