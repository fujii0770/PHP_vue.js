<?php

namespace App\Models;

use App\Http\Utils\DepartmentUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Department extends Model
{
    protected $table = 'mst_department';

    protected $listDepartmentName = false;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_company_id','parent_id', 'department_name', 'state', 'create_user','update_user','tree'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password' ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id){
        return [
            'mst_company_id' => 'required',
            'parent_id' => 'nullable|numeric',
            'department_name' => 'required|max:256',
            'state' => 'nullable|numeric',
            'create_user' => 'required|max:128',
            'update_user' => 'nullable|max:128',
        ];
    }
 
    public function detectFromName($listName = array(), $company_id = null){
        if(!count($listName)) return false;

        $user = \Auth::user();
        if(!$company_id){
            $company_id = $user->mst_company_id;
        }

        if(!$this->listDepartmentName){
                $listDepartmentTree = DepartmentUtils::getDepartmentTree($company_id);
                $this->listDepartmentName = \App\Http\Utils\CommonUtils::arrayKeyBy($listDepartmentTree, 'text');
        }

        $parent_id = 0;
        foreach($listName as $key => $name){
            if(!isset($this->listDepartmentName[$parent_id.'-'.$name])){
                return false; // not found
            }
         
            $departmentFound = $this->listDepartmentName[$parent_id.'-'.$name];

            // check level;       
            if($departmentFound['level'] != $key + 1) return false;

            // check parent_id
            if($key != 0 && $parent_id != $departmentFound['parent_id']) return 'false 4';

            $parent_id = $departmentFound['id'];
        }
        return $departmentFound['id'];
    }
}
