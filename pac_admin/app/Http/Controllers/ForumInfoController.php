<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use App\Http\Utils\PermissionUtils;


class ForumInfoController extends Controller {

    public function __construct()
    {
        parent::__construct();
        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);

        $this->assign('use_contain', true);
    }

    public function index(){
        return $this->render('ForumInfo.index');
    }
}
