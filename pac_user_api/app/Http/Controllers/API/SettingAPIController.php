<?php

namespace App\Http\Controllers\API;

use App\Http\Utils\EnvApiUtils;
use http\Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Response;
use App\Http\Utils\CircularUserUtils;

class SettingAPIController extends AppBaseController
{

    private $table_name = "mst_limit";
    private $table_policy = "password_policy";
    private $table_protection = "mst_protection";

    /**
     * Get a setting for limit
     *
     * @return \Illuminate\Http\Response
     */
    public function getLimit(Request $request)
    {
        $user  = $request->user();
        $limit = DB::table($this->table_name)->where($this->table_name.'.mst_company_id',$user->mst_company_id)
                ->join('mst_company', 'mst_company.id', '=',  $this->table_name.'.mst_company_id')->first(); // 無害化処理未対応のための制限用
        return $this->sendResponse($limit, 'Limit retrieved successfully');
    }
    
    public function getPasswordPolicy(Request $request)
    {
        $user  = $request->user();
        $query = DB::table($this->table_policy)->where('mst_company_id',$user->mst_company_id);
        $policy = $query->first();
        if(!$policy){
            DB::table($this->table_policy)->insert([
                'mst_company_id'=>$user->mst_company_id,
                'min_length'=>4,
                // PAC_5-1970 パスワードメールの有効期限を変更する Start
                'password_mail_validity_days'=>7,
                // PAC_5-1970 End
                'enable_password'=>1,
                'validity_period'=>0,
                'create_at'=>Carbon::now()
            ]);
            $policy = $query->first();
        }

        return $this->sendResponse($policy, 'Password policy retrieved successfully');
    }

    public function getDetailCompany(Request $request){

        $intCompanyID = 0;
        if(isset($request['usingHash']) && $request['usingHash']) {
            if ($request['current_edition_flg'] == config('app.edition_flg') && $request['current_env_flg'] == config('app.server_env') && $request['current_server_flg'] == config('app.server_flg') ){
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company = DB::table('mst_company')->where('id', $request['current_circular_user']->mst_company_id)->first();
                } else {
                    $company = DB::table('mst_company')->where('id', $request['current_viewing_user']->mst_company_id)->first();
                }
                if($company && isset($company->id)){
                    $intCompanyID = $company->id;
                }

                if(!$company){
                    $company = new \stdClass();
                    $company->esigned_flg = 0;
                    $company->stamp_flg = 0;
                    $company->certificate_flg = 0;
                    $company->sanitizing_flg = 0;
                    $company->bizcard_flg = 0;
                    $company->pdf_annotation_flg = 0;
                    $company->confidential_flg = 0;
                    $company->is_together_send = 0;
                }

            /*PAC_5-2191:現行ユーザーの場合に長期保管機能を強制的にOFFにする
                Start
            */
            //現行ユーザーか判定
            }else if ($request['current_edition_flg'] == CircularUserUtils::CURRENT_EDITION){
                /*PAC_5-2191:現行ユーザーの場合に各フラグを固定する
                    以下フラグは他と合わせる
                    esigned_flg・esigned_flg・certificate_flg・sanitizing_flg・bizcard_flg・pdf_annotation_flg
                */
                $company = new \stdClass();
                $company->esigned_flg = 0;
                $company->stamp_flg = 0;
                $company->certificate_flg = 0;
                $company->sanitizing_flg = 0;
                $company->bizcard_flg = 0;
                $company->pdf_annotation_flg = 0;
                //PAC_5-2191：長期保管フラグを強制的にOFFにする
                $company->long_term_storage_flg = 0;
                $company->is_together_send = 0;
                Log::info('長期保管フラグ:'.var_export($company->long_term_storage_flg,true));
            /*PAC_5-2191:現行ユーザーの場合に長期保管機能を強制的にOFFにする
                End
            */
            }else{
                $company = new \stdClass();
                $company->esigned_flg = 0;
                $company->stamp_flg = 0;
                $company->certificate_flg = 0;
                $company->confidential_flg = 0;
                $company->is_together_send = 0;

                //他環境処理を呼び出し
                $envClient = EnvApiUtils::getAuthorizeClient($request['current_env_flg'], $request['current_server_flg']);
                if (!$envClient) throw new \Exception('Cannot connect to Env Api');
                if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                    $company_id = $request['current_circular_user']->mst_company_id;
                } else {
                    $company_id = $request['current_viewing_user']->mst_company_id;
                }

                $response = $envClient->get("getCompany/$company_id", []);
                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                    $envCompany = json_decode($response->getBody())->data;
                    if ($envCompany){
                        $company->long_term_storage_flg = $envCompany->long_term_storage_flg;
                        $company->esigned_flg = $envCompany->esigned_flg;
                        $company->stamp_flg = $envCompany->stamp_flg;
                        $company->long_term_folder_flg = $envCompany->long_term_folder_flg;
                        $company->company_name = $envCompany->company_name;
                    }
                }else {
                    Log::warning('Cannot getCompany from other env');
                    Log::warning($response->getBody());
                }

                // 回覧完了日時
                $finishedDateKey = $request->get('finishedDate');
                // 当月
                if (!$finishedDateKey) {
                    $finishedDate = '';
                } else {
                    $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
                }

                // 環境を異にした場合、元会社の長期保存フラッグを取得します。
                $circular_user = DB::table("circular_user$finishedDate")->where('circular_id', $request['current_circular'])
                    ->where('parent_send_order', 0)
                    ->where('child_send_order', 0)
                    ->first();

                $mst_company = DB::table('mst_company')->where('id', $circular_user->mst_company_id)->first();
                if($mst_company){
                $company->sanitizing_flg = $mst_company->sanitizing_flg;
                $company->bizcard_flg = $mst_company->bizcard_flg;
                $company->pdf_annotation_flg = $mst_company->pdf_annotation_flg;
				$company->is_together_send = $mst_company->is_together_send;
                }else{
                    $company->sanitizing_flg = 0;
                    $company->bizcard_flg = 0;
                    $company->pdf_annotation_flg = 0;
					$company->is_together_send = 0;
            }
            }
        }else{
            $user  = $request->user();
            if (is_object($user)){
                $intCompanyID = $user->mst_company_id;
                $company = DB::table('mst_company')->where('id', $user->mst_company_id)->first();
                if(!$company){
                    $company = new \stdClass();
                    $company->esigned_flg = 0;
                    $company->stamp_flg = 0;
                    $company->certificate_flg = 0;
                    $company->sanitizing_flg = 0;
                    $company->bizcard_flg = 0;
                    $company->pdf_annotation_flg = 0;
                    $company->confidential_flg = 0;
                    $company->is_together_send = 0;
                }
            }else{
                $company = new \stdClass();
                $company->esigned_flg = 0;
                $company->stamp_flg = 0;
                $company->certificate_flg = 0;
                $company->sanitizing_flg = 0;
                $company->bizcard_flg = 0;
                $company->pdf_annotation_flg = 0;
                $company->confidential_flg = 0;
                $company->is_together_send = 0;
            }
        }
        $company->default_stamp_history_flg = 0;
        if(!empty($intCompanyID)){
            $objLimit = DB::table("mst_limit")->where("mst_company_id",$intCompanyID)->first();
            $company->default_stamp_history_flg = $objLimit->default_stamp_history_flg ?? 0;
        }

        return $this->sendResponse($company,'get company true');

    }

    /**
     * 名刺機能ON/OFFを取得
     * @param Request $request
     * @return mixed
     */
    public function getBizcardFlg(Request $request){
        try {
            Log::debug('getBizcardFlg Request Parameter: ' . json_encode($request->all()));
            $bizcard_flg = 0;

            // クロス環境判定
            $env_flg = $request->filled('current_env_flg') ? $request->input('current_env_flg') : config('app.server_env');
            $server_flg = $request->filled('current_server_flg') ? $request->input('current_server_flg') : config('app.server_flg');
            $edition_flg = $request->filled('current_edition_flg') ? $request->input('current_edition_flg') : config('app.edition_flg');

            if ((($env_flg != config('app.server_env')) || ($server_flg != config('app.server_flg'))) && $edition_flg != 0) {
                Log::debug('他環境の会社情報取得');
                // 他環境の場合、他環境のapiを呼び出す。
                $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
                if (!$envClient){
                    throw new \Exception('Cannot connect to other server Api');
                }
                $mst_company_id = $request->input('current_circular_user')->mst_company_id;
                $response = $envClient->get('setting/getBizcardFlgById/' . $mst_company_id);
                if (!$response) {
                    return $this->sendError('名刺フラグの取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::debug('getStatusCode：'. $response->getStatusCode());
                    Log::error($response->getBody());
                    throw new \Exception('Cannot get bizcard Flg');
                }
                $resData = json_decode((string)$response->getBody());
                $bizcard_flg = $resData->data->bizcard_flg;
            } else {
                Log::debug('同一環境の会社情報取得');
                // 捺印するユーザの情報を取得する
                $user = $request->user();
                $circular_user = $request['current_circular_user'];
                $mst_company_id = null;
                if ($user != null && isset($user->mst_company_id)) {
                    // ログイン済みユーザ
                    Log::debug('ログインユーザの情報取得');
                    $mst_company_id = $user->mst_company_id;
                } else if ($circular_user != null && isset($circular_user->mst_company_id)) {
                    // 未ログインかつゲストユーザでない(回覧メールから文書表示)
                    Log::debug('未ログインかつゲストユーザでないユーザの情報取得');
                    $mst_company_id = $circular_user->mst_company_id;
                }
                // mst_company_idがnullでなければ、bizcard_flgを取得
                if ($mst_company_id != null) {
                    $bizcard_flg = DB::table('mst_company')->where('id', $mst_company_id)->value('bizcard_flg');
                }
            }

            return $this->sendResponse([
                'bizcard_flg' => $bizcard_flg,
            ], '名刺フラグの取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 環境またぎ時に名刺機能ON/OFFを取得
     * @param $mst_company_id
     * @return mixed
     */
    public function getBizcardFlgById($mst_company_id) {
        Log::debug('getBizcardFlgById mst_company_id: ' . $mst_company_id);
        $bizcard_flg = DB::table('mst_company')->where('id', $mst_company_id)->value('bizcard_flg');

        return $this->sendResponse([
            'bizcard_flg' => $bizcard_flg,
        ], '名刺フラグの取得処理に成功しました。');
    }

    public function getProtection(Request $request){
        $user  = $request->user();
        $query = DB::table($this->table_protection)->where('mst_company_id',$user->mst_company_id);
        $protection = $query->first();
        if (!$protection) {
            DB::table($this->table_protection)->insert([
                'mst_company_id' => $user->mst_company_id,
                'protection_setting_change_flg' => 0,
                'destination_change_flg' => 0,
                'enable_email_thumbnail' => 0,
                'access_code_protection' => 1,
                'create_at' => Carbon::now(),
            ]);
            $protection = $query->first();
        }

        return $this->sendResponse($protection, 'Protection retrieved successfully');
    }

    /**
     * PAC_5-1075 BEGIN
     * 携帯アプリ状態
     * 現在携帯アプリ側から取得する方法が現在ないので、こちらを携帯アプリ側で取得できるような
     * APIを作成していただきたい。
     * @param Request $request
     * @return mixed
     */
    public function getPhoneAppFlg(Request $request)
    {
        try {
            $company_id = $request->company_id;
            if (!$company_id) {
                return $this->sendError('リクエストパラメータが不足しています。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $phone_app_flg = DB::table('mst_company')
                ->select('phone_app_flg')
                ->where('id', $company_id)->first();
            if (!$phone_app_flg){
                return $this->sendError('対象企業存在しません', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $this->sendResponse($phone_app_flg, '携帯アプリ利用状態取得成功しました。');
        } catch (\Exception $ex) {

        }
    }

    /**
     * PAC_5-1398 添付ファイル機能
     * 回覧申請者企業の添付ファイル機能
     * @param Request $request
     * @return int attachment_flg 回覧申請者の添付ファイル機能
     */
    public function getCreateCircularCompany(Request $request){
        try{
            $circular_id = $request->get('circular_id');
            $finished_Date = $request->get('finishedDate','') ;
            if ($finished_Date){
                $finished_moth = Carbon::now()->subMonthsWithNoOverflow($finished_Date)->format('Ym');
            }else{
                $finished_moth = '';
            }
            $createAttachmentFlg = DB::table('mst_company')
                ->join("circular$finished_moth as circular",function ($query) use($circular_id){
                    $query->where('circular.id',$circular_id);
                })
                ->join('mst_user',function ($query){
                    $query->on('mst_user.id','circular.mst_user_id');
                    $query->on('mst_user.mst_company_id','mst_company.id');
                })->value('attachment_flg');

            return $this->sendResponse(['attachment_flg'=>$createAttachmentFlg], __('message.success.create_circular_company'));
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.create_circular_company'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PAC_5-1790  get my company max_document_size config
     * @param Request $request company_id
     * @return mixed
     */
    public function getMyCompanyConstraintsMaxDocumentSize(Request $request,$intCompanyID,$intCircularId){
        try{
            if(empty($intCompanyID) && empty($intCircularId)){
                return $this->sendError('not find this company config!', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            // no login user  find current circular user's company_ID
            if(empty($intCompanyID)){
                $objCircularUser = DB::table('circular_user')
                    ->where("circular_id",$intCircularId)
                    ->first();
                $intCompanyID = !empty($objCircularUser) && isset($objCircularUser->mst_company_id) ? $objCircularUser->mst_company_id : false;
            }
            if(!$intCompanyID){
                return $this->sendError('not find this company config!', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $intMaxDocumentSize = DB::table('mst_constraints')
                ->where("mst_company_id",$intCompanyID)
                ->value('max_document_size');
            return $this->sendResponse(['max_document_size'=>$intMaxDocumentSize], "find this company config");
        }catch (\Exception $ex){
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('not find this company config!', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
}
