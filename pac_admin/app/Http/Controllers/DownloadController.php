<?php

namespace App\Http\Controllers;

use App\Http\Utils\AppUtils;
use App\Jobs\DownloadJob;

use App\Models\NewTimeCard;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Utils\DownloadControllerUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\PermissionUtils;

use App\Models\Company;

/**
 * 管理者画面におけるダウンロード
 */
class DownloadController extends Controller
{
    /**
     * 利用者・印面登録状況
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stampRegisterState(Request $request){
        try{
            $user = \Auth::user();

            // 利用者・印面登録状況_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_STAMP_REGISTER_STATE] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getStampRegisterState', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_STAMP_REGISTER_STATE] . Carbon::now()->format('YmdHis') .'.csv'])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 捺印台帳CSV出力
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stampLedger(Request $request){
        try{
            $user           = \Auth::user();
            $select_month   = $request->get('select_month');

            if(!$select_month){
                return response()->json(['status' => false,'message' => [__('対象月を指定してください')]]);
            }

            // stampyyyy-MM_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_STAMP_LEDGER] . $select_month . '_' . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getStampLedger', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_STAMP_LEDGER]. $select_month . '_' . Carbon::now()->format('YmdHis') .'.csv'])]
            ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 利用者登録状況
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userRegistrationStatus(Request $request){
        try{
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }

            // users_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_USER_REGISTRATION] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getUserRegistrationStatus', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

     /**
     * 管理者・利用者操作履歴
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request){

        try{

            $user = \Auth::user();
            $type = $request->get('type','');

            if($type == 'admin'){
                if(!$user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_HISTORY_VIEW)){
                    $this->raiseWarning(__('message.warning.not_permission_access'));
                    return redirect()->route('home');
                }
            }else if($type == 'user'){
                if(!$user->can(PermissionUtils::PERMISSION_USER_HISTORY_VIEW)){
                    $this->raiseWarning(__('message.warning.not_permission_access'));
                    return redirect()->route('home');
                }
            }

            // 利用者履歴 or 管理者履歴
            $dl_type = $type == 'user' ? DownloadControllerUtils::TYPE_USER_HISTORY : DownloadControllerUtils::TYPE_ADMIN_HISTORY;
            // adminlog_yyyyMMDDHHmmss.csv or userlog_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[$dl_type] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getHistory', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 利用者設定
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userSetting(Request $request){
        try{
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }

            // users_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_USER_SETTING] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getUserSetting', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 共通アドレス帳
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addressCommon(Request $request){
        try{
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_USER_SETTINGS_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }

            // address_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_ADDRESS_COMMON] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getAddressCommon', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 部署
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function department(Request $request){
        try{
            $user = $request->user();

            // 部署_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_DEPARTMENT] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getDepertment', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_DEPARTMENT] . Carbon::now()->format('YmdHis') .'.csv'])]
            ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 役職
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function position(Request $request){
        try{
            $user = $request->user();

            // position_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_POSITION] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getPosition', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 打刻履歴CSV出力
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function timeCard(Request $request){

        $targetMonth = str_replace(['年', '月'], ['-', ''], $request->targetMonth);
        $formArr = [
            'userId' => $request->userId,
            'targetMonth' => $targetMonth
        ];

        try{
            $user = $request->user();
            // 無害化するかを確認
            $is_sanitizing = Company::where('id', $user->mst_company_id)->first()->sanitizing_flg;
            // Job登録
            if($is_sanitizing){
                if(is_array($request->userId)){
                    foreach ($request->userId as $item){
                        $user_name = DB::table('mst_user')
                            ->where('id', $item)
                            ->selectRaw('CONCAT(family_name,\' \',given_name) as user_name')
                            ->value('user_name', '');
                        $user_name = preg_replace('/[\x00-\x1F\x7F]/', '', $user_name);
                        $user_name = preg_replace('/\.|\\\|\\/|\:|\*|\?|\"|\<|\>|\|/', '', $user_name);
                        $user_name = preg_replace('/[\\|\/|\r|\n|\t|\f]/', '', $user_name);
                        // 一人分の打刻履歴ダウンロード
                        $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') . '_' . $user_name . '.csv';
                        // Job登録
                        $request->merge(['userId' => $item]);
                        $result = DownloadUtils::downloadRequest(
                            $user, 'App\Http\Utils\DownloadControllerUtils', 'getTimeCard', $file_name,
                            $user, $targetMonth, $request->all()
                        );
                        if(!($result === true)){
                            return response()->json(['status' => false,
                                'message' => [__('message.false.download_request.download_ordered_sanitizing', ['attribute' => $result])]]);
                        }
                    }
                }else{
                    $user_name = DB::table('mst_user')
                        ->where('id', $request->userId)
                        ->selectRaw('CONCAT(family_name,\' \',given_name) as user_name')
                        ->value('user_name', '');
                    $user_name = preg_replace('/[\x00-\x1F\x7F]/', '', $user_name);
                    $user_name = preg_replace('/\.|\\\|\\/|\:|\*|\?|\"|\<|\>|\|/', '', $user_name);
                    $user_name = preg_replace('/[\\|\/|\r|\n|\t|\f]/', '', $user_name);
                    // 一人分の打刻履歴ダウンロード
                    $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') . '_' . $user_name . '.csv';
                    // Job登録
                    $result = DownloadUtils::downloadRequest(
                        $user, 'App\Http\Utils\DownloadControllerUtils', 'getTimeCard', $file_name,
                        $user, $targetMonth, $request->all()
                    );
                    if(!($result === true)){
                        return response()->json(['status' => false,
                            'message' => [__('message.false.download_request.download_ordered_sanitizing', ['attribute' => $result])]]);
                    }
                }
                
                return response()->json([
                    'status' => true,
                    'message' =>    [__("message.success.download_request.download_ordered_sanitizing"
                    )]
                ]);
            }
            if(is_array($request->userId) && count($request->userId) > 1) {
                // 数人の打刻履歴ダウンロード
                $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') .'.zip';
                // Job登録
                $result = DownloadUtils::downloadRequest(
                    $user, 'App\Http\Utils\DownloadControllerUtils', 'getTimeCardManage', $file_name,
                    $user, $targetMonth, $request->all()
                );
            }else {
                $user_name = DB::table('mst_user')
                    ->where('id', $request->userId)
                    ->selectRaw('CONCAT(family_name,\' \',given_name) as user_name')
                    ->value('user_name', '');
                $user_name = preg_replace('/[\x00-\x1F\x7F]/', '', $user_name);
                $user_name = preg_replace('/\.|\\\|\\/|\:|\*|\?|\"|\<|\>|\|/', '', $user_name);
                $user_name = preg_replace('/[\\|\/|\r|\n|\t|\f]/', '', $user_name);
                // 一人分の打刻履歴ダウンロード
                $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') . '_' . $user_name . '.csv';
                // Job登録
                $result = DownloadUtils::downloadRequest(
                    $user, 'App\Http\Utils\DownloadControllerUtils', 'getTimeCard', $file_name,
                    $user, $targetMonth, $request->all()
                );
            }

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => $file_name])]
            ]);
        }catch(Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * グループウェア専用利用者 CSV取込
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionUserSetting(Request $request){
        try {
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_OPTION_USERS_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }

            // users_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_OPTION_USER] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getOptionUserSetting', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => $file_name])]
            ]);


        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }


    /**
     * 受信専用利用者 CSV取込
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiveUserSetting(Request $request){
        try {
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_RECEIVE_USERS_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }

            // users_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_RECEIVE_USER] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getReceiveUserSetting', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => $file_name])]
            ]);


        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 回覧一覧
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function circulars(Request $request){
        try{
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_CIRCULATION_LIST_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }
            $company = Company::where('id', $user->mst_company_id)->select('circular_list_csv')->first();
            if (!$company || (isset($company->circular_list_csv) && $company->circular_list_csv == 0)) {
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_CIRCULARS] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getCirculars', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 利用者ファイル容量CSV出力
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diskUsages(Request $request){
        try{
            $user = \Auth::user();

            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_USER_DISKUSAGE] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getDiskUsage', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_USER_DISKUSAGE] . Carbon::now()->format('YmdHis') .'.csv'])]
            ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * ホスト利用者ファイル容量CSV出力
     *
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diskHostUsages(Request $request){
        try{
            $user = \Auth::user();

            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_USER_DISKUSAGE] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getHostDiskUsage', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_USER_DISKUSAGE] . Carbon::now()->format('YmdHis') .'.csv'])]
            ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * 承認ルートのCSV出力
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function templateRoute(Request $request){
        try{
            $user = $request->user();
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TEMPLATE_ROUTE] . Carbon::now()->format('YmdHis') .'.csv';
            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getTemplateRoute', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                    'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                    ['attribute' => $file_name])]
            ]);
        }catch(\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    public function expenseMFormAdv(Request $request){
        try{
            $user = \Auth::user();

            if(!$user->can(PermissionUtils::PERMISSION_STYLE_EXPENSE_SETTING_VIEW)){
                return response()->json([
                    'status' => false,
                    'message' => [__("message.warning.not_permission_access")]
                ]);
            }
            // users_yyyyMMDDHHmmss.csv
            $file_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_EXPENSE_M_FORM_ADV] . Carbon::now()->format('YmdHis') .'.csv';

            // Job登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\DownloadControllerUtils', 'getExpenseMFormAdv', $file_name,
                $user, $request->all()
            );

            if(!($result === true)){
                return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $result])]]);
            }

            return response()->json([
                'status' => true,
                'message' =>    [__("message.success.download_request.download_ordered",
                                ['attribute' => $file_name])]
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false,
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }
}
