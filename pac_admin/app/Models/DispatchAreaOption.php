<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchAreaOption extends Model
{
    protected $table = 'dispatcharea_option';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dispatcharea_id', 
        'dispatch_code_id',
        'status',
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
            'dispatcharea_id' => 'required|numeric',
            'dispatch_code_id' => 'required|numeric', 
            'status' => 'required|numeric', 
            'create_user' => 'required|max:128', 
            'update_user' => 'required|max:128'
        ];
    }

}
