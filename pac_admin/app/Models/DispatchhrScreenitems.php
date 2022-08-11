<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchhrScreenitems extends Model
{
    protected $table = 'dispatchhr_screenitems';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'dispatch_template_id',
        'row',
        'col',
        'remarks',
        'type',
        'code_flg',
        'dispatch_code_kbn',
        'regist_table_kbn',
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
            'row' => 'required|numeric',
            'col' => 'required|numeric',
            'remarks' => 'required|max:256',
            'type' => 'required|max:20',
            'code_flg' => 'nullable|numeric',
            'dispatch_code_kbn' => 'nullable|numeric',
            'regist_table_kbn' => 'nullable|numeric',
        ];
    }
    public function getMaxId(){
        return $this->max('id');
    }

}
