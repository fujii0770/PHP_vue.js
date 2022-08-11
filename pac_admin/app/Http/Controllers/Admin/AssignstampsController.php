<?php

namespace App\Http\Controllers\Admin;

use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\StampUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use Session;
use App\Models\User;
use App\Models\AssignStamp;
use App\Models\CompanyStamp;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\IdAppApiUtils;
use App\Models\Company;
use GuzzleHttp\RequestOptions;


class AssignstampsController extends AdminController
{

    private $model;

    private $modelUser;

    private $companyStamp;

    public function __construct(AssignStamp $model, CompanyStamp $companyStamp, User $user)
    {
        parent::__construct();
        $this->model = $model;
        $this->modelUser = $user;

        $this->companyStamp = $companyStamp;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = \Auth::user();

        //PAC_5-1117 印面登録時　共通印割り当て編集権限と紐づいていたので、利用者編集権限に修正
        //if(!$user->can(PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE)){
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE)){
            return response()->json(['status' => false,'message' => [__('管理者情報取得処理に失敗しました。')]]);
        }


         $stamps        = $request->get('stamps');
         $mst_user_id   = $request->get('mst_user_id');
         $stamp_flg     = $request->get('stamp_flg');
         $time_stamp_permission = $request->get('time_stamp_permission');
         $state_flg     = $request->has('state_flg') ? $request->get('state_flg') : AppUtils::STATE_VALID;
         $mst_admin_id  = \Auth::user()->id;  // 管理者ID

         $itemUser      = $this->modelUser->find($mst_user_id);
         if(!$itemUser || $itemUser->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')], 'error' => $itemUser]);
        }

        if(!$stamps OR !count($stamps)){
            return response()->json(['status' => false,'message' => [__('管理者情報取得処理に失敗しました。')]]);
        }

        DB::beginTransaction();
        try{
            // 利用者状態再判定
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            $intGetCompanyStampListCount = Company::getCompanyStampCount($user->mst_company_id);
            $getCompanyConvenientStampCount = Company::getCompanyConvenientStampCount($user->mst_company_id);

            // current user all stamp
            $UserStamps = $itemUser->getStamps($mst_user_id);
            // is change current user status ?
            $boolChangeFLg = true;
            $intCurrentUserAllStamps = count($UserStamps['stampMaster'])  + count($UserStamps['stampCompany']) + count($UserStamps['stampDepartment']);
            
            if ($stamp_flg != StampUtils::CONVENIENT_STAMP) {
                Session::put('stamp_is_over',0);

                // 印面上限チェック：
                if ($company->old_contract_flg) {

                    $mst_user_count = DB::table('mst_user')->where('mst_company_id', $itemUser->mst_company_id)->where('state_flg', AppUtils::STATE_VALID)->count();
                    //旧契約形態ON　&& Standarad ：上限がイセンス契約数
                    //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
                    if ($itemUser->state_flg == AppUtils::STATE_VALID && $company->contract_edition == 0 && $intGetCompanyStampListCount + count($stamps) > $company->upper_limit) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => [sprintf(__("message.warning.stamp_limit"), $intGetCompanyStampListCount, $company->upper_limit)]]);
                    }
                    if($itemUser->state_flg != AppUtils::STATE_VALID && $company->contract_edition == 0 ){
                        $boolChangeFLg = $intGetCompanyStampListCount + count($stamps) + $intCurrentUserAllStamps > $company->upper_limit ? false :true;
                    }
                } else {
                    //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
                    //旧契約形態OFF　&& trial ：上限なし
                    if ($itemUser->state_flg == AppUtils::STATE_VALID && in_array($company->contract_edition, [0, 1, 2]) && $intGetCompanyStampListCount + count($stamps) > $company->upper_limit) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'message' => [sprintf(__("message.warning.stamp_limit"), $intGetCompanyStampListCount, $company->upper_limit)]]);
                    }
                    if($itemUser->state_flg != AppUtils::STATE_VALID && in_array($company->contract_edition, [0, 1, 2])){
                        $boolChangeFLg = $intGetCompanyStampListCount + count($stamps) + $intCurrentUserAllStamps > $company->upper_limit ? false :true;
                    }
                }
                
                if(
                    true == $boolChangeFLg && $itemUser->state_flg != AppUtils::STATE_VALID
                    && 
                    (($company->old_contract_flg && $company->contract_edition == 0)  || (!$company->old_contract_flg && in_array($company->contract_edition, [0, 1, 2])))
                ){
                    $boolChangeFLg = $company->convenient_flg == 1 &&  $getCompanyConvenientStampCount +  count($UserStamps['convenientStamp']) > $company->convenient_upper_limit ? false : true;
                }
            } else {
                // 便利印
                if ($itemUser->state_flg == AppUtils::STATE_VALID && in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1 && $getCompanyConvenientStampCount + count($stamps) > $company->convenient_upper_limit) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => [sprintf(__("message.warning.convenient_stamp_limit"), $getCompanyConvenientStampCount, $company->convenient_upper_limit)], 'convenient_stamp_is_over' => 1]);
                }
                $boolChangeFLg = false;
            }


            $updateFlg = false;
            if($company->default_stamp_flg){
                // Business以上
                // 氏名印または日付印　＋　部署印 ＋ 共通印　＝　１　（二番目追加時判定不要、状態保持）
                // 部署印申請時判定不要、バッチ回した後更新
                // 氏名印、共通印申請＋申請前氏名印、共通印、部署印数が0
                if((count($UserStamps['stampMaster']) + count($UserStamps['stampCompany']) + count($UserStamps['stampDepartment'])) == 0 && $stamp_flg != 2 && $stamp_flg != StampUtils::CONVENIENT_STAMP){
                    $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
                    // 利用者無効時(パスワード設定前含む)、有効に更新
                    if(($itemUser->state_flg == 0 || $itemUser->state_flg == 9 ) && true == $boolChangeFLg){
                        $itemUser->state_flg = 1;
                        $updateFlg = true;
                    }
                    //PAC_5-2476 ユーザ契約数を超えている場合の処理
                    if ($itemUser->state_flg == AppUtils::STATE_VALID && $company->contract_edition == 1 && $mst_user_count + 1 > $company->upper_limit){
                        $itemUser->state_flg = 0;
                        $updateFlg = true;
                    }
                }
            }else{
                // Business以上　以外
                // 氏名印または日付印　＝　１　（二番目追加時判定不要、状態保持）
                // 氏名印申請＋申請前氏名印数が0
                if(count($UserStamps['stampMaster']) == 0 && in_array($stamp_flg,[0])){
                    $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
                    // 利用者無効時(パスワード設定前含む)、有効に更新
                    if(($itemUser->state_flg == 0 || $itemUser->state_flg == 9) && true == $boolChangeFLg ){
                        $itemUser->state_flg = 1;
                        $updateFlg = true;
                    }
                    //PAC_5-2476 ユーザ契約数を超えている場合の処理
                    if ($itemUser->state_flg == AppUtils::STATE_VALID && $company->contract_edition == 1 && $mst_user_count + 1 > $company->upper_limit){
                        $itemUser->state_flg = 0;
                        $updateFlg = true;
                    }
                }
            }

            if ($stamp_flg != StampUtils::CONVENIENT_STAMP) {
                if ($company->old_contract_flg && $company->contract_edition == 0 && $intGetCompanyStampListCount + count($stamps) > $company->upper_limit) {
                    $itemUser->state_flg = $itemUser->password == "" ? 0 : 9;
                } elseif (!$company->old_contract_flg && in_array($company->contract_edition, [0, 1, 2]) && $intGetCompanyStampListCount + count($stamps) > $company->upper_limit) {
                    $itemUser->state_flg = $itemUser->password == "" ? 0 : 9;
                }
            } else {
                if (in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1 && $getCompanyConvenientStampCount + count($stamps) > $company->convenient_upper_limit) {
                    $itemUser->state_flg = $itemUser->password == "" ? 0 : 9;
                }
            }

            // 有効ユーザー数印面チェック：旧契約形態OFF && オプションフラグがON （上限：有効ユーザー上限がオプション契約数）
            if ($updateFlg && $itemUser->state_flg == AppUtils::STATE_VALID && !$company->old_contract_flg && $company->option_contract_flg) {
                $mst_user_count = DB::table('mst_user')->where('mst_company_id', $itemUser->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
                if ($mst_user_count + 1 > $company->option_contract_count) {
                    $itemUser->state_flg = $itemUser->password== "" ? 0 : 9;
                }
            }

            if($updateFlg){
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client){
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to ID App'],
                        'is_over' => 0,
                    ]);
                }
                $apiUser = [
                    "user_email" => $itemUser->email,
                    "email"=> strtolower($itemUser->email),
                    "contract_app"=> config('app.pac_contract_app'),
                    "app_env"=> config('app.pac_app_env'),
                    "contract_server"=> config('app.pac_contract_server'),
                    "user_auth"=> $itemUser->option_flg == 2 ? AppUtils::AUTH_FLG_RECEIVE : AppUtils::AUTH_FLG_USER,
                    "user_first_name"=> $itemUser->given_name,
                    "user_last_name"=> $itemUser->family_name,
                    "company_name"=> $company?$company->company_name:'',
                    "company_id"=> $company?$company->id:0,
                    "status"=> AppUtils::convertState($itemUser->state_flg),
                    "system_name"=> $company?$company->system_name:'',
                    "update_user_email"=> $user->email,
                ];

                $itemUser->save();
                if ($itemUser->state_flg==AppUtils::STATE_VALID && $itemUser->option_flg == AppUtils::USER_NORMAL && $company->contract_edition == AppUtils::CONTRACT_EDITION_TRIAL){
                    ApplicationAuthUtils::appUserUpdate($company->id,AppUtils::GW_APPLICATION_ID_FAQ_BOARD,$itemUser->id);
                }
                Log::debug("Call ID App Api to create company user");

                $result = $client->put("users",[
                    RequestOptions::JSON => $apiUser
                ]);

                if($result->getStatusCode() != 200) {
                    DB::rollBack();
                    Log::warning("Call ID App Api to create company user failed. Response Body ".$result->getBody());
                    $response = json_decode((string) $result->getBody());
                    return response()->json(['status' => false,
                        'message' => [$response->message],
                        'errors' => isset($response->errors)?$response->errors:[],
                        'is_over' => 0,
                    ]);
                }
            }

            $arrInsert = [];
            foreach($stamps as & $stamp_id){
                $arrInsert[] = [
                    'stamp_id' => $stamp_id, 'mst_user_id' => $mst_user_id, 'display_no' => 0, 'state_flg' => $state_flg,
                    'stamp_flg' => $stamp_flg, 'create_user' => $user->getFullName(), 'time_stamp_permission' => $time_stamp_permission,
                    'mst_admin_id' => $mst_admin_id];
            }
            $this->model->insert($arrInsert);

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,'is_over'=>0,'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        $UserStamps = $itemUser->getStamps($mst_user_id);

        // PAC_5-1055 BEGIN 利用者設定の部分で日付が表示されるようにしてほしい
        if (!empty($UserStamps['stampCompany'])) {
            $stampCompanys = $UserStamps['stampCompany']->toArray();
            foreach ($stampCompanys as &$stamps) {
                // 共通印時間を追加する
                if ( $stamps['stamp_company']['stamp_division'] !== 0){
                    $stamps['stamp_company']['stamp_image'] = StampUtils::companyStampWithDateArr($stamps['stamp_company'], $user->mst_company_id);
                }
            }
            $UserStamps['stampCompany'] = $stampCompanys;
        }
        // PAC_5-1055 END

        if (!empty($UserStamps['convenientStamp'])){
            $convenientStamps = $UserStamps['convenientStamp'];
            foreach ($convenientStamps as $key => $stamp){
                if ($stamp['stamp_date_flg'] !== 0){
                    $convenientStamps[$key]['stamp_image'] = StampUtils::companyStampWithDate($stamp, $user->mst_company_id);
                }
            }
            $UserStamps['convenientStamp'] = $convenientStamps;
        }

        $stampAssign = [];
        if($stamp_flg == 0)
            $stampAssign = DB::table('mst_stamp')->whereIn('id',$stamps)
                ->select('stamp_division','id')->get()->toArray();
        else if($stamp_flg == 2)
            // TODO department stamp state
            $stampAssign = DB::table('department_stamp')->whereIn('id',$stamps)
                ->select('id','pribt_type','layout','face_up1','face_up2','face_down1','face_down2','font','color')->get()->toArray();

        return response()->json(['status' => true,'message' => [__('印面割当を行いました。')],
                        'userAssign' => ['email' => $itemUser->email, 'name' => $itemUser->family_name . $itemUser->given_name],
                        'stampAssign' => $stampAssign,
                        'is_over' => 0,
                        'stamps'=> $UserStamps,
                        'item'=> $itemUser,]);

    }

    public function delete($id, Request $request){
        $user = \Auth::user();
        //PAC_5-1267 権限チェック対象を日付印参照から利用者編集へ変更
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $item = $this->model->find($id);

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $itemUser = $this->modelUser->find($item->mst_user_id);

        if($itemUser->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

//        $item->delete();
        $item->state_flg = AppUtils::STATE_INVALID;
        $item->delete_at = Carbon::now();
        $item->update_user = $user->email;
//        $item->save();

        $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
        $stamps = $itemUser->getStamps($item->mst_user_id);

        // 利用者設定画面＆共通印割当共通ロジック
        // Business以上 + 氏名印または日付印すべて削除された ＋ 共通印割当なし場合
        // 利用者を無効に更新
        $updateFlg = false;
        if($company->default_stamp_flg){
            // Business以上
            // 氏名印または日付印 ＋ 共通印　＋　部署印　＝　1
            // 削除後、ゼロ件
            if((count($stamps['stampMaster']) + count($stamps['stampCompany']) + count($stamps['stampDepartment'])) == 1){
                if($item->stamp_flg == 2){
                    // 部署印の場合、
                    // 作成中の部署印を削除する場合、なにもしない
                    // バッチ後の部署印を削除する場合、判定要
                    if($item->state_flg != 2){
                        if($itemUser->state_flg == 1){
                            $updateFlg = true;
                        }
                    }
                }else{
                    // 部署印以外の場合、
                    if($itemUser->state_flg == 1){
                        $updateFlg = true;
                    }
                }
            }
        }else{
            // Business以上 以外
            // 氏名印または日付印　＝　1 ＋　通常印を削除する場合（共通印数を０になる）
            if(count($stamps['stampMaster']) == 1 && $item->stamp_flg == 0){
                if($itemUser->state_flg == 1){
                    $updateFlg = true;
                }
            }
        }

        if($updateFlg){
            // 有効の場合、無効に更新
            if($itemUser->password){
                // パスワード設定済み
                $itemUser->state_flg = 9;
            }else{
                // パスワード未設定
                $itemUser->state_flg = 0;
            }
        }


//
        DB::beginTransaction();
        try{

            $item->save();

            if($updateFlg){
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client){
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to ID App']
                    ]);
                }
                $apiUser = [
                    "user_email" => $itemUser->email,
                    "email"=> strtolower($itemUser->email),
                    "contract_app"=> config('app.pac_contract_app'),
                    "app_env"=> config('app.pac_app_env'),
                    "contract_server"=> config('app.pac_contract_server'),
                    "user_auth"=> $itemUser->option_flg == 2 ? AppUtils::AUTH_FLG_RECEIVE : AppUtils::AUTH_FLG_USER,
                    "user_first_name"=> $itemUser->given_name,
                    "user_last_name"=> $itemUser->family_name,
                    "company_name"=> $company?$company->company_name:'',
                    "company_id"=> $company?$company->id:0,
                    "status"=> AppUtils::convertState($itemUser->state_flg),
                    "system_name"=> $company?$company->system_name:'',
                    "update_user_email"=> $user->email,
                ];

                $itemUser->save();
//

                if ($itemUser->state_flg==AppUtils::STATE_VALID && $itemUser->option_flg == AppUtils::USER_NORMAL && $company->contract_edition == AppUtils::CONTRACT_EDITION_TRIAL){
                    ApplicationAuthUtils::appUserUpdate($company->id,AppUtils::GW_APPLICATION_ID_FAQ_BOARD,$item->id);
                }
                if ($itemUser->state_flg==AppUtils::STATE_INVALID || $itemUser->state_flg == AppUtils::STATE_INVALID_NOPASSWORD){
                    DB::table('app_role_users')->where('mst_user_id',$itemUser->id)->delete();
                    DB::table('mst_application_users')->where('mst_user_id',$itemUser->id)->delete();
                }
                Log::debug("Call ID App Api to create company user");
                $apiUser['create_user_email'] = $user->email;
                $result = $client->put("users",[
                    RequestOptions::JSON => $apiUser
                ]);

                if($result->getStatusCode() != 200) {
                    DB::rollBack();
                    Log::warning("Call ID App Api to create company user failed. Response Body ".$result->getBody());
                    $response = json_decode((string) $result->getBody());
                    return response()->json(['status' => false,
                        'message' => [$response->message],
                        'errors' => isset($response->errors)?$response->errors:[]
                    ]);
                }
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        $UserStamps = $itemUser->getStamps($item->mst_user_id);

        // PAC_5-1055 BEGIN 利用者設定の部分で日付が表示されるようにしてほしい
        if (!empty($UserStamps['stampCompany'])) {
            $stampCompanys = $UserStamps['stampCompany']->toArray();
            foreach ($stampCompanys as &$stamps) {
                // 共通印時間を追加する
                if ( $stamps['stamp_company']['stamp_division'] !== 0){
                    $stamps['stamp_company']['stamp_image'] = StampUtils::companyStampWithDateArr($stamps['stamp_company'], $user->mst_company_id);
                }
            }
            $UserStamps['stampCompany'] = $stampCompanys;
        }
        // PAC_5-1055 END

        $boolStampIsOver = 0;
        // 印面上限チェック：
        if ($company->old_contract_flg) {
            //旧契約形態ON　&& Standarad ：上限がイセンス契約数
            //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
            if ($company->contract_edition == 0 && Company::getGreaterThanByCompanyLimitAndUserCount($user->mst_company_id)) {
                $boolStampIsOver = 1;
            }
        } else {
            //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
            //旧契約形態OFF　&& trial ：上限なし
            if (in_array($company->contract_edition, [0, 1, 2]) && Company::getGreaterThanByCompanyLimitAndUserCount($user->mst_company_id)) {
                $boolStampIsOver = 1;
            }
        }

        Session::put("stamp_is_over",$boolStampIsOver);
        $strMessage = '';
        if($boolStampIsOver){
            $arrCUTotal = Company::getCompanyStampLimitAndUserStampCount($user->mst_company_id);
            $strMessage = sprintf(__("message.warning.stamp_limit"),$arrCUTotal['intUserStampCount'],$arrCUTotal['intCompanyStampLimit']);
        }
        return response()->json(['status' => true,'message' => ['印面を削除しました。'],
            'stamp_flg' => $item->stamp_flg,
            'userAssign' => ['email' => $itemUser->email, 'name' => $itemUser->family_name . $itemUser->given_name],
            'stamps'=> $UserStamps,
            'is_over' => (int) $boolStampIsOver,
            'is_over_message' => $strMessage,
            'item'=> $itemUser,]);
    }

    public function getTimeStampPermission($id, Request $request){
        $user = \Auth::user();
        $stamp_asign = DB::table('mst_assign_stamp')->select('id','time_stamp_permission','mst_user_id')->where('id',$id)->first();
        $userAsignStamp = DB::table('mst_user')->where('id', $stamp_asign->mst_user_id )->where('mst_company_id',$user->mst_company_id)->first();
        if(!$userAsignStamp){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'stamp_asign' => $stamp_asign]);
    }

    public function updateTimeStampPermission($id, Request $request){
        try{
            $user = \Auth::user();
            $stamp_asign = DB::table('mst_assign_stamp')
                        ->join('mst_user','mst_assign_stamp.mst_user_id','mst_user.id')
                        ->select('mst_assign_stamp.id','mst_assign_stamp.mst_user_id')
                        ->where('mst_assign_stamp.id',$id)
                        ->where('mst_company_id',$user->mst_company_id)->first();
            if(!$stamp_asign){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            DB::table('mst_assign_stamp')
                ->where('id',$id)
                ->update([
                    'time_stamp_permission' => $request['time_stamp_permission'],
                    'update_at' => Carbon::now(),
                ]);
            return response()->json(['status' => true,'message' => ['タイムスタンプ設定を更新しました。'] ]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
        }
    }

    function checkStoreConvenientStamp (Request $request)
    {
        $user = \Auth::user();

        //PAC_5-1117 印面登録時　共通印割り当て編集権限と紐づいていたので、利用者編集権限に修正
        //if(!$user->can(PermissionUtils::PERMISSION_COMMON_SEAL_ASSIGNMENT_UPDATE)){
        if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_VIEW)){
            return response()->json(['status' => false,'message' => [__('管理者情報取得処理に失敗しました。')]]);
        }

        $stamps        = $request->get('stamps');
        $mst_user_id   = $request->get('mst_user_id');
        $stamp_flg     = $request->get('stamp_flg');

        $itemUser      = $this->modelUser->find($mst_user_id);
        if(!$itemUser || $itemUser->mst_company_id != $user->mst_company_id){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')], 'error' => $itemUser]);
        }

        if(!$stamps OR !count($stamps)){
            return response()->json(['status' => false,'message' => [__('管理者情報取得処理に失敗しました。')]]);
        }

        DB::beginTransaction();
        try{
            // 利用者状態再判定
            $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
            $getCompanyConvenientStampCount = Company::getCompanyConvenientStampCount($user->mst_company_id);

            // 便利印
            if ($stamp_flg == StampUtils::CONVENIENT_STAMP && $itemUser->state_flg == AppUtils::STATE_VALID && in_array($company->contract_edition, [0, 1, 2]) && $company->convenient_flg == 1 && $getCompanyConvenientStampCount + count($stamps) > $company->convenient_upper_limit) {
                return response()->json(['status' => false, 'message' => [sprintf(__("message.warning.convenient_stamp_limit"), $getCompanyConvenientStampCount, $company->convenient_upper_limit)], 'convenient_stamp_is_over' => 1]);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }

        return response()->json(['status' => true,'message' => [__('印面チェック完了。')]]);
    }
}
