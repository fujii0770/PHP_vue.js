<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EpsMPurpose extends Model
{
    protected $table = 'eps_m_purpose';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id'
       ,'purpose_name'
       ,'describe'
       ,'remarks'
       ,'display_order'
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
             'purpose_name'  => 'required|string|max:20'
            ,'describe' => 'max:100'
            ,'remarks' => 'max:100'
        ];
    }

}
