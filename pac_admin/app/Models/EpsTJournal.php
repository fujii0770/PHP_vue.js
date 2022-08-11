<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EpsTJournal extends Model
{
    protected $table = 'eps_t_journal';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'mst_company_id'
        ,'eps_t_app_id'
        ,'eps_t_app_item_id'
        ,'eps_app_item_bno'
        ,'rec_date'
        ,'debit_rec_dept'
        ,'debit_account'
        ,'debit_subaccount'
        ,'debit_amount'
        ,'debit_tax_div'
        ,'debit_tax_rate'
        ,'debit_tax'
        ,'credit_rec_dept'
        ,'credit_account'
        ,'credit_subaccount'
        ,'credit_amount'
        ,'credit_tax_div'
        ,'credit_tax_rate'
        ,'credit_tax'
        ,'remarks'
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
            ,'eps_t_app_id'     => 'numeric'
            ,'eps_t_app_item_id'=> 'numeric'
            ,'eps_app_item_bno' => 'numeric'
            ,'rec_date'         => 'date'
            ,'debit_rec_dept'   => 'string|max:20'
            ,'debit_account'    => 'string|max:20'
            ,'debit_subaccount' => 'string|max:1000'
            ,'debit_amount'     => 'numeric'
            ,'debit_tax_div'    => 'numeric'
            ,'debit_tax_rate'   => 'numeric'
            ,'debit_tax'        => 'numeric'
            ,'credit_rec_dept'   => 'string|max:20'
            ,'credit_account'    => 'string|max:20'
            ,'credit_subaccount' => 'string|max:1000'
            ,'credit_amount'     => 'numeric'
            ,'credit_tax_div'    => 'numeric'
            ,'credit_tax_rate'   => 'numeric'
            ,'credit_tax'        => 'numeric'
            ,'version'           => 'numeric'
            ,'remarks'           => 'string|max:1000'
        ];
    }

}
