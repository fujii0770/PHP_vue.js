<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\UserInfo;
use App\Http\Utils\PermissionUtils;

class UserInfoController extends AdminController
{

    private $model;

    public function __construct(UserInfo $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->model->select('id', 'email', 'state_flg', 'phone_number', 'given_name','family_name', 'department_name')->find($id);
        return response()->json(['status' => true, 'info' => $item]);
    }
}
