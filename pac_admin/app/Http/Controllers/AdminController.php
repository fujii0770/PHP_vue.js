<?php

namespace App\Http\Controllers;

use App\Models\Company;
use DB;
use Hash;
use Session;


class AdminController extends Controller {

    public function __construct()
    {
        parent::__construct();
        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);

        $this->assign('use_contain', true);
    }

    public function hideSideBar(){
        $this->assign('show_sidebar', false);
    }

    public function home(){
        $user = \Auth::user();
//        // システム管理者の場合、利用状況画面に遷移
//        if($user->hasRole(PermissionUtils::ROLE_SHACHIHATA_ADMIN)){
//            return redirect()->route('Reports.Usage.Show');
//        }
//		// 企業管理者の場合、利用状況画面に遷移
//        if($user->can(PermissionUtils::PERMISSION_USAGE_SITUATION_VIEW)) {
//			return redirect()->route('Reports.Usage.Show');
//		}
//        // 権限がない、パスワード設定画面に遷移
//        return redirect()->route('settingadmin.changePassword');
//        return redirect()->route('home.index');

        //　sidebarとpadding不要
        $boolStampOver = 0;
        if(!empty($user->mst_company_id)){
            $company = Company::where('id', $user->mst_company_id)->first();
            if($company->contract_edition != 3){
                $boolStampOver = (int) Company::stampIsOverByCount($company->id);
                
            }
        }
        Session::put("stamp_is_over",$boolStampOver);

        $this->assign('use_contain', false);
        $this->setMetaTitle('ホームページ');
        return $this->render('home.index');
    }
}
