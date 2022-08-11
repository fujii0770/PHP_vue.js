<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EpsMJournalConfig extends Model
{
    protected $table = 'eps_m_journal_config';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'id'
        ,'mst_company_id'
        ,'purpose_name'
        ,'wtsm_name'
        ,'account_name'
        ,'sub_account_name'
        ,'criteria'
        ,'remarks'
        ,'display_order'
        ,'memo'
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
             'mst_company_id'   => 'numeric'
            ,'purpose_name'     => 'required|string|max:20'
            ,'wtsm_name'        => 'required|string|max:20'
            ,'account_name'     => 'required|string|max:20'
            ,'sub_account_name' => 'max:50'
            ,'criteria' => 'JSON'
            ,'remarks' => 'max:100'
            ,'memo' => 'max:100'
            ,'version' => 'numeric'
        ];
    }

}
