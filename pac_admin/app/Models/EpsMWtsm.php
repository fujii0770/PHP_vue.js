<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EpsMWtsm extends Model
{
    protected $table = 'eps_m_wtsm';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id'
       ,'wtsm_name'
       ,'wtsm_describe'
       ,'num_people_option'
       ,'num_people_describe'
       ,'detail_option'
       ,'detail_describe'
       ,'tax_option'
       ,'voucher_option'
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
             'wtsm_name'  => 'required|string|max:20'
            ,'wtsm_describe' => 'max:100'
            ,'num_people_describe' => 'max:100'
            ,'detail_describe' => 'max:100'
            ,'wtsmdescribe' => 'max:100'
            ,'remarks' => 'max:100'
        ];
    }

}
