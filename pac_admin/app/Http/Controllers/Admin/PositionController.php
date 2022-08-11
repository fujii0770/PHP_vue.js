<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Position;
use App\Http\Utils\PermissionUtils;

class PositionController extends AdminController
{

    private $model;

    public function __construct(Position $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Return list Position for master
     *
     * @return \Illuminate\Http\Response
     */
    public function getListMaster(Request $request)
    {
        $user = \Auth::user();
        $mst_company_id = $user->mst_company_id;

        $items = $this->model
                ->select('id as value', 'position_name as text')
                ->where('state',1)
                ->get();
         
        return response()->json(['status' => true, 'items' => $items]);
    }
 
}
