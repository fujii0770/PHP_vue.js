<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Department;
use App\Http\Utils\PermissionUtils;

class DepartmentController extends AdminController
{

    private $model;

    public function __construct(Department $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Return list Department for master
     *
     * @return \Illuminate\Http\Response
     */
    public function getListMaster(Request $request)
    {
        $user = \Auth::user();
        $mst_company_id = $user->mst_company_id;

        $items = $this->model
                ->select('id','parent_id' , 'department_name')
                ->where('mst_company_id',$mst_company_id)
                ->where('state',1)
                ->get();
         
        return response()->json(['status' => true, 'items' => $items]);
    }
 
}
