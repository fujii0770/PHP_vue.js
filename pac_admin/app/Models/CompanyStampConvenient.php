<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CompanyStampConvenient extends Model
{
    protected $table = 'mst_company_stamp_convenient';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_stamp_convenient_id','mst_company_id', 'del_flg',
        'create_user', ' update_user','serial'
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
    protected $casts = [  ];

    public function rules($id){
        return [
            'mst_stamp_convenient_id' => 'required',
            'mst_company_id' => 'required',
            'create_user' => 'required|max:128',
            'update_user' => 'nullable|max:128'
        ];
    }

    public function stampConvenient()
    {
        return $this->hasOne('App\Models\StampConvenient', 'mst_stamp_convenient_id');
    }
}
