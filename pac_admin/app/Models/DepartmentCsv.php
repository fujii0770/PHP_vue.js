<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DepartmentCsv extends Model
{
    protected $table = 'department_csv';

    const CREATED_AT = 'request_date';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id',
        'file_name',
        'contents',
        'request_date',
        'state',
        'contents_create_at',
        'create_user'
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

    public function rules(){
        return [             
        ];
    }
}
