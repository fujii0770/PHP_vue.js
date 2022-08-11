<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Edition extends Model
{
    protected $table = 'mst_contract_edition';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contract_edition_name','memo','state_flg','create_user','update_user','board_flg','pdf_annotation_flg','scheduler_flg','scheduler_limit_flg'
        ,'scheduler_buy_count','caldav_flg','caldav_limit_flg','caldav_buy_count','google_flg','outlook_flg','apple_flg','file_mail_flg','file_mail_limit_flg'
        ,'file_mail_buy_count','attendance_flg','attendance_limit_flg','attendance_buy_count','faq_board_flg','faq_board_limit_flg','faq_board_buy_count','shared_scheduler_flg'
        ,'to_do_list_flg','to_do_list_limit_flg','to_do_list_buy_count','address_list_flg','address_list_limit_flg','address_list_buy_count'
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
        return [
            'contract_edition_name' => 'required',
            'memo' => 'nullable|max:100',
            'state_flg' => 'required|numeric',
        ];
    }

    public function info()
    {
        return $this->hasOne('App\Models\Company','edition_id');
    }
}
