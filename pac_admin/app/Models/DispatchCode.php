<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Utils\DispatchUtils;
use Illuminate\Support\Facades\Log;

class DispatchCode extends Model
{
    protected $table = 'dispatch_code';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kbn',
        'code',
        'name',          
        'order',
        'remarks',
        'del_flg',
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
            'id' => 'required|numeric', 
            'kbn' => 'required|numeric',
            'code' => 'required|numeric',
            'name' => 'required|max:512', 
            'order' => 'required|numeric',
            'remarks' => 'nullable|max:256',
            'del_flg' => 'required|numeric',
            'create_user' => 'required|max:128', 
            'update_user' => 'required|max:128'
        ];
    }
    public function getCodeAll(){
        return $this->where('del_flg', 0)
        ->get();
    }

}
