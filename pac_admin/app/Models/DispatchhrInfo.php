<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchhrInfo extends Model
{
    protected $table = 'dispatchhr_info';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dispatchhr_id',
        'dispatchhr_screenitems_id',
        'value',
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
            'value' => 'required|max:512',
        ];
    }


}
