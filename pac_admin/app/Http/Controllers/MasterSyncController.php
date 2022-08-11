<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\PermissionUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AdminController;
use DB;
use App\Models\MasterSync;
use Illuminate\Support\Facades\Storage;

class MasterSyncController extends AdminController
{

    private $model;

    public function __construct(MasterSync $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        $mst_admin_id = \Auth::user()->id;
        $failure_message="";
        $searchResults = GwAppApiUtils::getCompanyAppSearch($user['email'], $user['mst_company_id']);
        if ($searchResults === false){
            $failure_message = "大変申し訳ございません。アクセス集中の為、データの取得、または更新に失敗しました。</br> お手数をおかけしますが、時間を置いてから再度お試しください。";
        }
        $syncdata = array();
        $department = array();
        $department_data = array();
        $position = array();
        $position_data = array();
        $mstuser_data = array();
        $MstUser = array();
        $syncMstAdminUserRequestList = array();
        $adminRequest = array();

        $department = DB::table('mst_department')->where('mst_company_id', $user['mst_company_id'])->get();
        $position = DB::table('mst_position')->where('mst_company_id', $user['mst_company_id'])->get();
        $MstUser = DB::table('mst_user')
                      ->select('mst_user_info.*', 'mst_user.*')
                      ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                      ->where('mst_user.mst_company_id', $user['mst_company_id'])
                      //PAC_5-1671 ADD START
                      ->whereIn('mst_user.state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_VALID])
                      //PAC_5-1671 ADD END
                      ->get();

        // adminRequest
        $syncdata['adminRequest'] = array("portalCompanyId" => $user['mst_company_id'], "portalEmail" => $user['email'],
                                          "editionFlg" => config('app.pac_contract_app'), "envFlg" => config('app.pac_app_env'), "serverFlg" => config('app.pac_contract_server'));

        // syncMstAdminUserRequestList
        $syncdata['syncMstAdminUserRequestList'][0] = array("id" => $mst_admin_id, "portalEmail" => $user['email'], "stateFlg" => $user["state_flg"]);

        // syncMstDepartmentRequestList
        foreach($department as $key => $val) {
            $syncdata['syncMstDepartmentRequestList'][$key]['id'] = $val->id;
            $syncdata['syncMstDepartmentRequestList'][$key]['departmentName'] = $val->department_name;
            $syncdata['syncMstDepartmentRequestList'][$key]['parentId'] = $val->parent_id;
        }

        // syncMstPositionRequestList
        foreach($position as $key => $val) {
            $syncdata['syncMstPositionRequestList'][$key]['id'] = $val->id;
            $syncdata['syncMstPositionRequestList'][$key]['positionName'] = $val->position_name;
        }

        // syncMstUserRequestList
        foreach($MstUser as $key => $val) {
            // null対応
            if (($val->phone_number == "") || ($val->phone_number == null)) {
                $val->phone_number = '-';
            }
            if (($val->fax_number == "") || ($val->fax_number == null)) {
                $val->fax_number = '-';
            }
            if (($val->postal_code == "") || ($val->postal_code == null)) {
                $val->postal_code = '-';
            }
            if (($val->address == "") || ($val->address == null)) {
                $val->address = '-';
            }

            $syncdata['syncMstUserRequestList'][$key]['id'] = $val->id;
            $syncdata['syncMstUserRequestList'][$key]['address'] = $val->address;
            $syncdata['syncMstUserRequestList'][$key]['email'] = $val->email;
            $syncdata['syncMstUserRequestList'][$key]['familyName'] = $val->family_name;
            $syncdata['syncMstUserRequestList'][$key]['faxNumber'] = $val->fax_number;
            $syncdata['syncMstUserRequestList'][$key]['fullName'] = $val->family_name . ' ' . $val->given_name;
            $syncdata['syncMstUserRequestList'][$key]['givenName'] = $val->given_name;
            $syncdata['syncMstUserRequestList'][$key]['mstDepartmentId'] = $val->mst_department_id;
            $syncdata['syncMstUserRequestList'][$key]['mstPositionId'] = $val->mst_position_id;
            $syncdata['syncMstUserRequestList'][$key]['phoneNumber'] = $val->phone_number;
            $syncdata['syncMstUserRequestList'][$key]['postalCode'] = $val->postal_code;
            $syncdata['syncMstUserRequestList'][$key]['stateFlg'] = $val->state_flg;
            $syncdata['syncMstUserRequestList'][$key]['notificationEmail'] = $val->notification_email;
            $syncdata['syncMstUserRequestList'][$key]['optionFlg'] = $val->option_flg;
        }

        $syncdata = json_encode($syncdata,JSON_UNESCAPED_UNICODE);

        $this->assign('syncdata', $syncdata);
        $this->assign('failure_message',$failure_message);
        $this->setMetaTitle("マスタ同期設定");
        return $this->render('SettingGroupware.MasterSync');
    }
}