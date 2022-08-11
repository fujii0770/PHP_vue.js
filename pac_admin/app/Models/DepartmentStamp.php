<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DepartmentStamp extends Model
{
    protected $table = 'department_stamp';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pribt_type',
        'layout',
        'serial',
        'face_up1',
        'face_up2',
        'face_down1',
        'face_down2',
        'font',
        'color',
        'stamp_image',
        'width',
        'height',
        'real_width',
        'real_height',
        'date_x',
        'date_y', 
        'date_width',
        'date_height',
        'state',
        'create_at',
        'update_at'
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
            'pribt_type' => 'required|string|max:32',
            'layout' => 'required|string|max:32',
            'face_up1' => 'nullable|string|max:32',
            'face_up2' => 'nullable|string|max:32',
            'face_down1' => 'nullable|string|max:32',
            'face_down2' => 'nullable|string|max:32',
            'font'=> 'required|string|max:32',
            'color'=> 'required|string|max:32',
            'stamp_image'=> 'nullable|string',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'real_width' => 'nullable|numeric',
            'real_height' => 'nullable|numeric',
            'date_x' => 'nullable|numeric',
            'date_y' => 'nullable|numeric',
            'date_width' => 'nullable|numeric',
            'date_height' => 'nullable|numeric',
            'state' => 'required|numeric',
        ];
    }
}
