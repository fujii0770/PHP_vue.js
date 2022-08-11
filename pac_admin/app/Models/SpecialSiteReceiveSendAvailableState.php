<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SpecialSiteReceiveSendAvailableState extends Model
{
    protected $table = 'special_site_receive_send_available_state';

//    const CREATED_AT = 'create_at';
//    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'is_special_site_receive_available',
        'is_special_site_send_available',
        'group_name',
        'region_name'
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
            'company_id' => 'required|numeric',
            'is_special_site_receive_available' => 'required|integer|numeric|min:0|max:1',
            'is_special_site_send_available' => 'required|integer|numeric|min:0|max:1',
        ];
    }
}
