<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchhrTemplate extends Model
{
    protected $table = 'dispatchhr_template';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tabno',
        'order',
        'remarks',
        'create_at',
        'create_user',
        'update_at',
        'update_user'
        
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

    public function rules($id = 'null'){
        return [
            'tabno' => 'required|numeric',
            'order' => 'required|numeric',
            'remarks' => 'required|max:256',
        ];
    }


}
