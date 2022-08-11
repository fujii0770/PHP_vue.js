<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 11/13/19
 * Time: 15:45
 */

namespace App\Http\Utils;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CircularUserUtils
{
    //status
    const NOT_NOTIFY_STATUS = 0; // 未通知
    const NOTIFIED_UNREAD_STATUS = 1; // 通知済/未読
    const READ_STATUS = 2; // 既読
    const APPROVED_WITH_STAMP_STATUS = 3; // 承認(捺印あり)
    const APPROVED_WITHOUT_STAMP_STATUS = 4; // 承認(捺印なし)
    const SEND_BACK_STATUS = 5; // 差戻し
    const END_OF_REQUEST_SEND_BACK = 6; // 差戻し(未読)
    const SUBMIT_REQUEST_SEND_BACK = 7; // 差戻依頼
    const PULL_BACK_TO_USER_STATUS = 8; // 引戻し(下書き一覧に入る)
    const REVIEWING_STATUS = 10; // 窓口再承認待ち
    const NODE_COMPLETED_STATUS = 11; // ノード完了(承認ルート用)

    const SEPERATOR = '#,,,#';

    //ENV
    const ENV_AWS = 0;
    const K5_AWS = 1;

    //EDITION
    const CURRENT_EDITION = 0;
    const NEW_EDITION = 1;

    //DEL_FLG

    const NOT_DELETE = 0;
    const DELETED = 1;
    const DEFAULT_OPERATION_NOTICE_FLG = 0;
    
    // NODE_FLG
    const NODE_OTHER = 0;
    const NODE_APPROVED = 1; // 承認
    const NODE_COMPLETED = 2; // ノード完了

    // PAC_5-2352 is_skip
    const IS_SKIP_ACTION_TRUE = 1;
    const IS_SKIP_ACTION_FALSE = 0;

    //DEFAULT_COMMENT

    const DEFAULT_COMMENT = [
        'comment1' => '承認をお願いします。',
        'comment2' => '至急確認をお願いします。',
        'comment3' => '了解。',
        'comment4' => '了解しました。',
        'comment5' => '承認しました。',
        'comment6' => '差戻します。',
        'comment7' => 'いつもお世話になっております。'
    ];

    /**
     * @param $email  送信するメール
     * @param $mail_type 'viewed':閲覧通知 'updated':更新通知 'approval':承認通知 'completion':完了通知 'pullback':引戻し通知 'sendback':差戻し通知/差戻し依頼通知 'download':ダウンロード処理完了通知
     * @param $company_id このユーザーの会社ID
     * @param $env_flg 0:AWS  1:K5
     * @param $edition_flg 0:現行 1:新エディション
     * @param $server_flg  0,1,2 どのマシン
     * @return bool
     * @throws \Exception
     */
    public static function checkAllowReceivedEmail($email, $mail_type,$company_id,$env_flg,$edition_flg,$server_flg) {

        $userInfo = [];

        // ゲストユーザーではなく、現在の環境のユーザー
        if ($company_id != null && config('app.server_env') == $env_flg && config('app.edition_flg') == $edition_flg && config('app.server_flg') == $server_flg){
            $userInfo = DB::table('mst_user')
                ->select('mst_user_info.*', DB::raw('mst_user.email as email'))
                ->join('mst_user_info', 'mst_user.id', '=','mst_user_info.mst_user_id')
                ->where('mst_user.email', $email)
                ->where('mst_user.state_flg', AppUtils::STATE_VALID)
                ->first();
        }else if($company_id == null || $edition_flg == CircularUserUtils::CURRENT_EDITION){//ゲストユーザー or 現行ユーザー
            if($mail_type == 'viewed') {
                return false;
            }else {
                return true;
            }
        }else {// その他の開発環境
            $envClient = EnvApiUtils::getAuthorizeClient($env_flg,$server_flg);
            if (!$envClient) throw new \Exception('Cannot connect to Env Api');
            $response = $envClient->get("getUserInfo/".$email,[]);
            if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                $envUserInfo = json_decode($response->getBody())->data;
                if ($envUserInfo!= null && $envUserInfo->state_flg == AppUtils::STATE_VALID){
                    $userInfo = $envUserInfo;
                }
            }
        }
        // 現在のユーザーが存在しないか、ステータスが無効です
        if (!$userInfo || !$userInfo->id){
            return false;
        }
        if($mail_type == 'approval') {
            return $userInfo->approval_request_flg;
        }
        if($mail_type == 'viewed') {
            return $userInfo->browsed_notice_flg;
        }
        if($mail_type == 'updated') {
            return $userInfo->update_notice_flg;
        }
        if($mail_type == 'completion') {
            return $userInfo->completion_notice_flg;
        }
        if($mail_type == 'completion_sender') {
            return $userInfo->completion_sender_notice_flg;
        }
        if($mail_type == 'pullback') {
            return $userInfo->pullback_notice_flg;
        }
        if($mail_type == 'sendback') {
            return $userInfo->sendback_notice_flg;
        }
        if($mail_type == 'download') {
            return $userInfo->download_notice_flg;
        }

        return true;
    }

    public static function getEnvAppUrlByEnv($envFlg, $serverFlg, $editionFlg = CircularUserUtils::NEW_EDITION, $company = null)
    {
        $url = config('app.server_app_env_url')[$editionFlg . $envFlg . $serverFlg];
        if ($company && $company->login_type == AppUtils::LOGIN_TYPE_SSO && $company->url_domain_id){
            $url = rtrim(str_replace('/login', '/', $url), "/").'/'.rtrim(config('app.saml_url_prefix'), "/").'/'.$company->url_domain_id;
        }
        return $url;
    }
    
    public static function getEnvAppUrlWithoutCompany($circular_user){
        $env_app_url = null;
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $system_server_flg = config('app.server_flg');
        if ($circular_user->edition_flg == $system_edition_flg && $circular_user->mst_company_id){
            if ($circular_user->env_flg == $system_env_flg && $circular_user->server_flg == $system_server_flg){
                $circularUserCompany = DB::table('mst_company')->where('id', $circular_user->mst_company_id)->first();
                $env_app_url = CircularUserUtils::getEnvAppUrlByEnv($circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg, $circularUserCompany);
            }else{
                $client = EnvApiUtils::getAuthorizeClient($circular_user->env_flg, $circular_user->server_flg);
                
                if ($client){
                    $response = $client->get("getCompanies?ids=".$circular_user->mst_company_id,[]);
    
                    if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                        Log::warning('Cannot get Companies. Response Body '. $response->getBody());
                    }else{
                        $circularUserCompanies = json_decode((string)$response->getBody())->data;
                        if (count($circularUserCompanies)){
                            $env_app_url = CircularUserUtils::getEnvAppUrlByEnv($circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg, $circularUserCompanies[0]);
                        }
                    }
                }else{
                    Log::warning('Cannot connect to Env Api to get Companies.');
                }
            }
        }
        
        if (!$env_app_url){
            $env_app_url = CircularUserUtils::getEnvAppUrlByEnv($circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg, null);
        }
        return $env_app_url;
    }
    
    public static function getEnvAppUrlWithoutCompanies($circular_user, $mapSameEnvCompanies, $mapOtherEnvCompanies){
        $env_app_url = null;
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $system_server_flg = config('app.server_flg');
        if ($circular_user->edition_flg == $system_edition_flg && $circular_user->mst_company_id){
            if ($circular_user->env_flg == $system_env_flg && $circular_user->server_flg == $system_server_flg){
                if (isset($mapSameEnvCompanies[$circular_user->mst_company_id])){
                    $env_app_url = CircularUserUtils::getEnvAppUrlByEnv($circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg, $mapSameEnvCompanies[$circular_user->mst_company_id]);
                }
            }elseif (isset($mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id])){
                $env_app_url = CircularUserUtils::getEnvAppUrlByEnv($circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg, $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id]);                 
            }
        }
        if (!$env_app_url){
            $env_app_url = CircularUserUtils::getEnvAppUrlByEnv($circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg, null);
        }
        return $env_app_url;
    }

    /**
     * 回覧コメントセット関数 PAC_5-1462
     *
     * @param [stdClass] $user セットしたいユーザー関数
     * @param [stdClass] $info 取得したuser_info nullの場合はDEFAULT_COMMENTを代入する
     * @return [stdClass] $user
     * @todo コメント以外のuserinfo情報もこの関数でセットしたい
     */
    public static function setComment($user,$info = null){
        if(is_null($info)){
            $info = (object)self::DEFAULT_COMMENT;
        }
        $user->comment1 = $info->comment1;
        $user->comment2 = $info->comment2;
        $user->comment3 = $info->comment3;
        $user->comment4 = $info->comment4;
        $user->comment5 = $info->comment5;
        $user->comment6 = $info->comment6;
        $user->comment7 = $info->comment7;
        return $user;
    }

    public static function summaryInProgressCircular($circularId){

        $system_edition_flg = config('app.edition_flg');
        $system_env_flg = config('app.server_env');
        $system_server_flg = config('app.server_flg');

        DB::beginTransaction();
        try{
            Log::debug("Start summaryInProgressCircular for circular $circularId!");
            $circularUsers = DB::table('circular_user')->where('circular_id', $circularId)->select('email', 'title', 'parent_send_order', 'child_send_order', 'mst_company_id', 'mst_user_id')->get();
            $senderUser = DB::table('circular_user')->where('circular_id', $circularId)
                ->where('parent_send_order', 0)->where('child_send_order',0)
                ->select('name', 'email')->first();

            $strSqls = '';
            $countSql = 0;
            foreach ($circularUsers as $circularUser){
                //受信専用利用者ユーザID特殊文字変換
                $circularUser->email = str_replace("'","''",$circularUser->email);
                $circularUser->email = str_replace("\\","\\\\",$circularUser->email);
                if (trim($circularUser->title)){
                    Log::debug("No query for title $circularUser->email in circular $circularId because this email has title already!");
                }else{
                    Log::debug("Query for title $circularUser->email in circular $circularId!");
                    $mstUserId = $circularUser->mst_user_id?:0;
                    $mstCompanyId = $circularUser->mst_company_id?:0;
                    $titles = DB::table('circular as C')
                        ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
                        ->join('circular_document as D', function($join) use ($circularUser, $system_env_flg, $system_server_flg, $system_edition_flg, $mstCompanyId){
                            $join->on('C.id', '=', 'D.circular_id');
                            $join->on(function($condition) use ($circularUser, $system_env_flg, $system_server_flg, $system_edition_flg, $mstCompanyId){
                                $condition->on('confidential_flg', DB::raw('0'));
                                $condition->orOn(function($condition1) use ($circularUser, $system_env_flg, $system_server_flg, $system_edition_flg, $mstCompanyId){
                                    $condition1->on('confidential_flg', DB::raw('1'));
                                    $condition1->on('origin_edition_flg', DB::raw($system_edition_flg));
                                    $condition1->on('origin_env_flg', DB::raw($system_env_flg));
                                    $condition1->on('origin_server_flg', DB::raw($system_server_flg));
                                    $condition1->on('create_company_id', DB::raw($mstCompanyId));
                                });
                            });
                            $join->on(function($condition) use ($circularUser){
                                $condition->on('origin_document_id', DB::raw('0'));
                                $condition->orOn(function($condition1) use ($circularUser){
                                    $condition1->on('D.parent_send_order', 'U.parent_send_order');
                                });
                            });
                        })
                        ->select(DB::raw('GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
                        // 宛先に自分自身を設定していた場合の対策としてNOT EXISTS
                        ->whereRaw("((U.email = '$circularUser->email' AND NOT EXISTS (SELECT * FROM circular_user WHERE circular_id = U.circular_id AND email=U.email AND parent_send_order = 0
                    AND edition_flg = ".$system_edition_flg." AND env_flg = ".$system_env_flg." AND server_flg = ".$system_server_flg."
                    AND child_send_order = 0)) OR (C.mst_user_id = $mstUserId AND U.parent_send_order = 0 AND U.child_send_order = 0))")
                        ->where('U.edition_flg', $system_edition_flg)
                        ->where('U.env_flg', $system_env_flg)
                        ->where('U.server_flg', $system_server_flg)
                        ->where('C.id', $circularId)
                        ->where('U.parent_send_order', $circularUser->parent_send_order)
                        ->whereNotIn('C.circular_status', [CircularUtils::SAVING_STATUS, CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS])
                        ->groupBy(['C.id', 'U.parent_send_order'])->get();
                    Log::debug("Finished query title for email $circularUser->email in circular $circularId!");
                    foreach ($titles as $title){
                        $strSqls.="UPDATE circular_user SET receiver_title = '$title->file_names' where email = '$circularUser->email' and parent_send_order = $circularUser->parent_send_order and circular_id = $circularId;\n";
                        $countSql++;
                    }
                }
                if ($countSql > 100){
                    Log::debug('Flush to database in loop!');
                    DB::unprepared($strSqls);
                    $strSqls = '';
                    $countSql = 0;
                }
            }
            if ($countSql){
                Log::debug('Flush to database in loop!');
                DB::unprepared($strSqls);
            }
            DB::commit();
        }catch (Exception $ex){
            DB::rollBack();
            Log::debug('Error in summaryInProgressCircular for title!');
            Log::error($ex->getMessage().$ex->getTraceAsString());
        }
    }
}