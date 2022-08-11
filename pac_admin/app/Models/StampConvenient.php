<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class StampConvenient extends Model
{
    protected $table = 'mst_stamp_convenient';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stamp_name','stamp_division', 'stamp_image', 'del_flg','height','width','stamp_date_flg','date_dpi','date_x','date_y','date_width','date_height','date_color',
        'create_user', ' update_user'
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
            'stamp_name' => 'required|max:32',
            'stamp_division' => 'required|numeric',
            'stamp_image' => 'required',
            'create_user' => 'required|max:128',
            'update_user' => 'nullable|max:128',
            'stamp_date_flg' => 'required|numeric',
        ];
    }
}
