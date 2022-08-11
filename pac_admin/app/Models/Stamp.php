<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Stamp extends Model
{
    protected $table = 'mst_stamp';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stamp_name','stamp_division', 'font', 'stamp_image','serial','width','height','date_width', 'date_height', 'date_x', 'date_y', 'create_at'
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
            'stamp_name' => 'required',
            'stamp_division' => 'required|numeric',
            'font' => 'required|numeric',
            'stamp_image' => 'required',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'date_width' => 'nullable|numeric',
            'date_height' => 'nullable|numeric',
            'date_x' => 'nullable|numeric',
            'date_y' => 'nullable|numeric'
        ];
    }
}
