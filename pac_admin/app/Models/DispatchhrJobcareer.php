<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispatchhrJobcareer extends Model
{
    protected $table = 'dispatchhr_jobcareer';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'dispatchhr_id',
        'work_startym',
        'work_toym',
        'company_department',
        'industry',
        'employment',
        'business_content',
        'salary',
        'retirement_reason',
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
            'work_startym'=> 'required|max:6',
            'work_toym'=> 'required|max:6',
            'company_department'=> 'required|max:256',
            'industry'=> 'required|max:128',
            'employment' => 'required|numeric',
            'business_content'=> 'required|max:512',
            'salary' => 'nullable|max:128',
            'retirement_reason' => 'nullable|max:128',

        ];
    }


}
