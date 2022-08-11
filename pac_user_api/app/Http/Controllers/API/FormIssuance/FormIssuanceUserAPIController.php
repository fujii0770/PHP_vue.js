<?php

namespace App\Http\Controllers\API\FormIssuance;

use App\Http\Requests\API\ClearFrmTemplateCircularUserRequest;
use App\Http\Requests\API\ClearFrmTemplateViewingUserRequest;
use App\Http\Requests\API\CreateChildFrmTemplateCircularUserAPIRequest;
use App\Http\Requests\API\CreateFrmTemplateCircularUserAPIRequest;
use App\Http\Requests\API\SendBackRequest;
use App\Http\Requests\API\SendNotifyContinueRequest;
use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Requests\API\UpdateFrmTemplateCircularUserAPIRequest;
use App\Http\Requests\API\UpdateFrmTemplateViewingUserAPIRequest;
use App\Http\Requests\API\UpdateMultipleCircularUserAPIRequest;
use App\Http\Requests\API\UpdateTransferredCircularUserAPIRequest;
use App\Http\Requests\API\UpdateTransferredStatusAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use App\Http\Delegate\EnvApiDelegate;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Mail\SendAccessCodeNoticeMail;
use App\Mail\SendCircularUserMail;
use App\Mail\SendMailInitPassword;
use App\Mail\SendCircularPullBackMail;
use App\Models\CircularUser;
use App\Jobs\SendNotification;
use App\Jobs\PushNotify;
use App\Models\FrmTemplateCircularUser;
use App\Repositories\FrmTemplateCircularUserRepository;
use App\Repositories\CompanyRepository;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use Response;
use Image;
use App\Http\Utils\MailUtils;
use Symfony\Component\VarDumper\Cloner\Data;
use App\Models\CircularUserRoutes;
use App\Http\Utils\TemplateRouteUtils;

/**
 * Class FormIssuanceUserAPIController
 * @package App\Http\Controllers\API
 */

class FormIssuanceUserAPIController extends AppBaseController
{
    /** @var  FrmTemplateCircularUserRepository */
    private $frmTemplateCircularUserRepository;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    public function __construct(FrmTemplateCircularUserRepository $frmTemplateCircularUserRepository, CompanyRepository $companyRepository)
    {
        $this->frmTemplateCircularUserRepository = $frmTemplateCircularUserRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Display a listing of the CircularUser.
     * GET|HEAD /circularUsers
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $frmTemplateCircularUsers = $this->frmTemplateCircularUserRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($frmTemplateCircularUsers->toArray(), 'Frm Template Circular Users retrieved successfully');
    }

    public function getSavedCircularUsers($templateId, Request $request) {
        try{
            $savedCircular = false;
            $savedCircularUsers = DB::table('frm_template_circular_user')
                ->where('frm_template_id', $templateId)
                ->get()
                ->toArray();
            if (count($savedCircularUsers) > 0) {
                $savedCircular = true;
            }
            return $this->sendResponse(['savedCircularUsers' => $savedCircularUsers, 'savedCircular' => $savedCircular], 'get saved circular users done');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧ユーザ取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSavedViewingUsers($templateId, Request $request) {
        try{
            $savedViewing = false;
            $savedViewingUsers = DB::table('frm_template_viewing_user as vu')
                ->join('mst_user as U', 'vu.mst_user_id', 'U.id')
                ->select(DB::raw('vu.frm_template_id, vu.parent_send_order, vu.mst_user_id, U.email, CONCAT(U.family_name, " ", U.given_name) as name'))
                ->where('frm_template_id', $templateId)
                ->get()
                ->toArray();
            if (count($savedViewingUsers) > 0) {
                $savedViewing = true;
            }
            return $this->sendResponse(['savedViewingUsers' => $savedViewingUsers, 'savedViewing' => $savedViewing], 'get saved viewing users done');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('閲覧ユーザー取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store($frm_template_id, CreateFrmTemplateCircularUserAPIRequest $request) {
        try {
            $login_user = $request->user();

            $system_env_flg     = config('app.server_env');
            $system_edition_flg = config('app.edition_flg');
            $system_server_flg = config('app.server_flg');

            $users = $request['users'];
            $frm_template_id = $request['frm_template_id'];
            $rets = [];

            /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ get only last circular user */
            $last_circular_user = DB::table('frm_template_circular_user')
                ->where('frm_template_id', $frm_template_id)
                ->orderBy('parent_send_order', 'desc')
                ->orderBy('child_send_order', 'desc')
                ->first();

            DB::beginTransaction();
            if($last_circular_user) {
                $parent_send_order = intval($last_circular_user->parent_send_order);
                $child_send_order = intval($last_circular_user->child_send_order);

                $old_company_id     = $last_circular_user->mst_company_id;
                $old_env_flg        = $last_circular_user->env_flg;
                $old_edition_flg    = $last_circular_user->edition_flg;
                $old_server_flg    = $last_circular_user->server_flg;
                $old_company_key    = "$old_company_id-$old_env_flg-$old_edition_flg-$old_server_flg";
            }else{
                $old_company_key = null;
                $parent_send_order = 0;
                $child_send_order = 0;
            }

            /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ get all user by email one time */
            $emails = [];
            foreach ($users as $user) {
                $env_flg        = isset($user['env_flg'])?$user['env_flg']:$system_env_flg;
                $edition_flg    = isset($user['edition_flg'])?$user['edition_flg']:$system_edition_flg;
                $server_flg     = isset($user['server_flg'])?$user['server_flg']:$system_server_flg;
                if ($edition_flg == $system_edition_flg && $env_flg == $system_env_flg && $server_flg == $system_server_flg){
                    $emails[] = $user['email'];
                }
            }
            $dbUsers = DB::table('mst_user')->whereIn('email', $emails)->where('state_flg', AppUtils::STATE_VALID)->select('email', 'id')->get();
            $mapDBUsers = [];
            foreach ($dbUsers as $user) {
                $mapDBUsers[$user->email] = $user;
            }

            // 合議のroute id
            $old_template_route_id = -1;

            /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ support insert multiple circular user */
            foreach ($users as $user) {
                $user_company_id = isset($user['company_id'])?$user['company_id']:-1;

                $env_flg        = isset($user['env_flg'])?$user['env_flg']:$system_env_flg;
                $edition_flg    = isset($user['edition_flg'])?$user['edition_flg']:$system_edition_flg;
                $server_flg     = isset($user['server_flg'])?$user['server_flg']:$system_server_flg;
                $user_company_key   = "$user_company_id-$env_flg-$edition_flg-$server_flg";
                $template_route_id = isset($user['template_rotes_id'])?$user['template_rotes_id']:-1;

                if (!$old_company_key){
                    $old_company_key   = "$user_company_id-$env_flg-$edition_flg-$server_flg";
                }

                $received_date = null;
                if(isset($user['is_maker']) && $user['is_maker']) {
                    $parent_send_order = 0;
                    $child_send_order = 0;
                }else{
                    if($user_company_id == -1 || $user_company_key != $old_company_key) {
                        $parent_send_order += 1;
                        $child_send_order   = 1;
                    }else{
                        // $template_route_id == -1 非合議
                        if($template_route_id == -1 || $template_route_id != $old_template_route_id){
                        $child_send_order += 1;
                    }
                    }
                    $old_company_key = $user_company_key;
                    $old_template_route_id = $template_route_id;
                }
                if ($parent_send_order === 0 && $child_send_order === 0){
                    $received_date = Carbon::now();
                }
                $mst_user_id = null;
                /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ set db user id  */
                if ($edition_flg == $system_edition_flg && $env_flg == $system_env_flg && $server_flg == $system_server_flg && isset($mapDBUsers[$user['email']])){
                    $mst_user_id = $mapDBUsers[$user['email']]->id;
                }else {
                    //新エディション側の回覧ユーザー
                    if($edition_flg == $system_edition_flg){
                        //本環境の文書データを取得する
                        $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
                        if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                        $response = $envClient->get("getUserInfo/" . $user['email'], []);
                        if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $envUserInfo = json_decode($response->getBody())->data;
                            if ($envUserInfo) {
                                $mst_user_id = $envUserInfo->mst_user_id;
                            }
                        } else {
                            Log::warning('Cannot get Env UserInfo from other env');
                            Log::warning($response->getBody());
                        }
                    }else{
                        //現行エディション側の無効回覧ユーザー制約
                        $client = IdAppApiUtils::getAuthorizeClient();
                        if (!$client){
                            //TODO message
                            return response()->json(['status' => false,
                                'message' => ['Cannot connect to ID App']
                            ]);
                        }
                        $response = $client->post("users/checkEmail",[
                            RequestOptions::JSON => ['email' => $user['email'] ,'contract_app' => $edition_flg ,'app_env' => $env_flg]
                        ]);
                        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                            $result = json_decode((string) $response->getBody());
                            if($result->data == []){
                                DB::rollBack();
                                return $this->sendError('無効なパソコン決裁Cloud利用者がルートに含まれています。お気に入りを再度作成し直してください。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        } else {
                            DB::rollBack();
                            return $this->sendError('回覧ユーザー登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                }
                Log::debug("Insert frm template circular user for circular $frm_template_id: email - ".$user['email'].", parent_send_order - $parent_send_order, child_send_order - $child_send_order");
                $circular_user_params = [
                    'frm_template_id'=> $frm_template_id,
                    'parent_send_order'=> $parent_send_order,
                    'child_send_order'=> $child_send_order,
                    'env_flg'=> $env_flg,
                    'edition_flg'=> $edition_flg,
                    'server_flg'=> $server_flg,
                    'mst_company_id'=> $user_company_id==-1?null:$user_company_id,
                    'mst_company_name'=> $user_company_id==-1?null:(isset($user['company_name'])?$user['company_name']:null),
                    'mst_user_id' => $mst_user_id,
                    'name'=> isset($user['name'])?$user['name']:($user['family_name'].' '.$user['given_name']),
                    'email'=> $user['email'],
                    'return_flg' => 1,
                    'create_at' => Carbon::now(),
                    'create_user' => $login_user->email,
                    'update_at' => Carbon::now(),
                    'update_user' => $login_user->email,
                ];
                $circular_user = $this->frmTemplateCircularUserRepository->create($circular_user_params);
                $arr_circular_user = $circular_user->toArray();

//                // 合議の場合
//                if($template_route_id !== -1){
//                    $mode = $user['template_mode'];
//                    $wait = $user['template_wait'];
//                    $score = $user['template_score'];
//                    $detail = $user['template_detail'];
//
//                    $circular_user_routes = CircularUserRoutes::where('circular_id', $circular_id)->where('child_send_order', $child_send_order)->first();
//                    if(!$circular_user_routes){
//                        $circular_user_routes = new CircularUserRoutes();
//                        $circular_user_routes->circular_id = $circular_id;
//                        $circular_user_routes->child_send_order = $child_send_order;
//                        $circular_user_routes->mode = $mode;
//                        $circular_user_routes->wait = $wait;
//                        $circular_user_routes->score = $score;
//                        $circular_user_routes->detail = $detail;
//                        $circular_user_routes->state = 1;
//                        $circular_user_routes->create_at = Carbon::now();
//                        $circular_user_routes->create_user = $login_user->email;
//                        $circular_user_routes->update_at = Carbon::now();
//                        $circular_user_routes->update_user = $login_user->email;
//                        $circular_user_routes->save();
//            }
//                    $arr_circular_user["user_routes_id"] = $circular_user_routes->id; // 合議 route id
//                    $arr_circular_user["detail"] = $circular_user_routes->detail;
//                    $arr_circular_user["mode"] = $circular_user_routes->mode;
//                    $arr_circular_user["wait"] = $circular_user_routes->wait;
//                    $arr_circular_user["score"] = $circular_user_routes->score;
//                }

                array_push($rets, $arr_circular_user);
            }
            DB::commit();
            return $this->sendResponse($rets,'回覧ユーザー登録処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧ユーザー登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param CreateChildFrmTemplateCircularUserAPIRequest $request
     * @return mixed
     */
    public function storeChildren($frm_template_id, CreateChildFrmTemplateCircularUserAPIRequest $request) {
        try {
            $login_user = $request->user();
            $email  = $request->get('email', null);
            if(!$login_user || !$login_user->id) {
                $login_user = $request['user'];
            }

            DB::beginTransaction();

            $parent_circular_user = DB::table('frm_template_circular_user')
                ->where('id', $request['parent_id'])
                ->first();

            $old_circular_user = DB::table('frm_template_circular_user')
                ->where('frm_template_id', $frm_template_id)
                ->where('mst_company_id', $parent_circular_user->mst_company_id)
                ->where('parent_send_order', $parent_circular_user->parent_send_order)
                ->orderBy('child_send_order', 'desc')
                ->first();

            $parent_send_order = 0;
            $child_send_order = 0;

            $new_user_id = null;
            $new_user = null;
            if($old_circular_user && $old_circular_user->id) {

                $parent_send_order = intval($old_circular_user->parent_send_order);
                $child_send_order = intval($old_circular_user->child_send_order) + 1;

                $db_user = DB::table('mst_user')->where('email', $request['email'])->where('state_flg', AppUtils::STATE_VALID)->first();

                $user_company_id = null;

                if ($db_user && $db_user->id && $old_circular_user->edition_flg == config('app.edition_flg') && $old_circular_user->env_flg == config('app.server_env') && $old_circular_user->server_flg == config('app.server_flg')) {
                    $user_company_id = $db_user->mst_company_id;
                    $new_user_id = $db_user->id;
                }
                $old_company_id = $old_circular_user->mst_company_id;

                if ($old_company_id != $user_company_id) {
                    //call id app

                    $client = IdAppApiUtils::getAuthorizeClient();
                    if (!$client){
                        return response()->json(['status' => false,
                            'message' => ['Cannot connect to ID App']
                        ]);
                    }

                    $result =  $client->post("users/checkEmail",[
                        RequestOptions::JSON => ['email' => $email]
                    ]);

                    $resData = json_decode((string)$result->getBody());
                    $id_app_users = $resData->data;
                    foreach ($id_app_users as $user) {
                        if($old_circular_user->mst_company_id == $user->company_id && $old_circular_user->edition_flg == $user->edition_flg && $old_circular_user->env_flg == $user->env_flg && $old_circular_user->server_flg == $user->server_flg) {
                            $new_user = $user;
                        }
                    }

                    if($new_user) {
                        Log::debug('Frm Template Circular user in pacId');
                        $circular_user_params = [
                            'frm_template_id'=> $old_circular_user->frm_template_id,
                            'parent_send_order'=> $parent_send_order,
                            'child_send_order'=> $child_send_order,
                            'env_flg'=> $new_user->env_flg,
                            'edition_flg'=> $new_user->edition_flg,
                            'server_flg'=> $new_user->server_flg,
                            'mst_company_id'=> $new_user->company_id,
                            'mst_company_name'=> $new_user->company_name,
                            'mst_user_id'=> $new_user->id,
                            'name'=> $new_user->name,
                            'email'=> $new_user->email,
                            'return_flg' => 0,
                            'create_at' => Carbon::now(),
                            'create_user' => $login_user->email,
                        ];

                        $circular_user = $this->frmTemplateCircularUserRepository->create($circular_user_params);

//                        if ($old_circular_user->edition_flg == config('app.edition_flg') && ($old_circular_user->env_flg != config('app.server_env') || $old_circular_user->server_flg != config('app.server_flg'))){
//                            Log::debug('sync new circular to other application');
//
//                            $circular = DB::table('circular')->where('id', $old_circular_user->circular_id)->select('env_flg', 'edition_flg', 'server_flg')->first();
//
//                            $transferredCircularUser = ['origin_circular_id' => $old_circular_user->circular_id,
//                                'env_flg' => $circular->env_flg,
//                                'edition_flg' => $circular->edition_flg,
//                                'server_flg' => $circular->server_flg,
//                                'update_user' => $login_user->email,
//                                'new_circular_users' => [[
//                                    "email" => $circular_user->email,
//                                    "parent_send_order" => $circular_user->parent_send_order,
//                                    "child_send_order" => $circular_user->child_send_order,
//                                    "env_flg" => $circular_user->env_flg,
//                                    "edition_flg" => $circular_user->edition_flg,
//                                    "server_flg" => $circular_user->server_flg,
//                                    "mst_company_id" => $circular_user->mst_company_id,
//                                    "mst_company_name" => $circular_user->mst_company_name,
//                                    "mst_user_id" => $circular_user->mst_user_id,
//                                    "name" => $circular_user->name,
//                                    "return_flg" => $circular_user->return_flg,
//                                    "circular_status" => $circular_user->circular_status,
//                                    "title" => $circular_user->title,
//                                    "origin_circular_url" => CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->circular_id) . CircularUtils::encryptOutsideAccessCode($circular_user->id),
//                                ]]
//                            ];
//
//                            $envClient = EnvApiUtils::getAuthorizeClient($old_circular_user->env_flg, $old_circular_user->server_flg);
//                            if (!$envClient){
//                                //TODO message
//                                throw new \Exception('Cannot connect to Env Api');
//                            }
//
//                            $response = $envClient->put("circularUsers/updatesTransferred",[
//                                RequestOptions::JSON => $transferredCircularUser
//                            ]);
//                            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
//                                Log::error('Cannot store circular user');
//                                Log::error($response->getBody());
//                                throw new \Exception('Cannot store circular user');
//                            }
//                        }
                        DB::commit();
                        return $this->sendResponse($circular_user->toArray(),'Create child circular user successfully');
                    }
                    //PAC_5-1243 社外宛先登録時のメッセージを修正
                    return $this->sendError($request['email'].': 承認者は社外のユーザーは追加できません', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                }
            }

            $circular_user_params = [
                'frm_template_id'=> $old_circular_user->frm_template_id,
                'parent_send_order'=> $parent_send_order,
                'child_send_order'=> $child_send_order,
                'env_flg'=> config('app.server_env'),
                'edition_flg'=> config('app.edition_flg'),
                'server_flg'=> config('app.server_flg'),
                'mst_company_id'=> $old_circular_user->mst_company_id,
                'mst_company_name'=> $old_circular_user->mst_company_name,
                'mst_user_id'=> $new_user_id,
                'name'=> $request['name'],
                'email'=> $request['email'],
                'return_flg' => 0,
                'create_at' => Carbon::now(),
                'create_user' => $login_user->email,
            ];
            $circular_user = $this->frmTemplateCircularUserRepository->create($circular_user_params);

//            if ($old_circular_user->edition_flg == config('app.edition_flg') && ($old_circular_user->env_flg != config('app.server_env') || $old_circular_user->server_flg != config('app.server_flg'))){
//                Log::debug('sync new circular to other application');
//
//                $circular = DB::table('circular')->where('id', $old_circular_user->circular_id)->select('env_flg', 'edition_flg', 'server_flg')->first();
//
//                $transferredCircularUser = ['origin_circular_id' => $old_circular_user->circular_id,
//                    'env_flg' => $circular->env_flg,
//                    'edition_flg' => $circular->edition_flg,
//                    'server_flg' => $circular->server_flg,
//                    'update_user' => $login_user->email,
//                    'new_circular_users' => [[
//                        "email" => $circular_user->email,
//                        "parent_send_order" => $circular_user->parent_send_order,
//                        "child_send_order" => $circular_user->child_send_order,
//                        "env_flg" => $circular_user->env_flg,
//                        "edition_flg" => $circular_user->edition_flg,
//                        "server_flg" => $circular_user->server_flg,
//                        "mst_company_id" => $circular_user->mst_company_id,
//                        "mst_company_name" => $circular_user->mst_company_name,
//                        "mst_user_id" => $circular_user->mst_user_id,
//                        "name" => $circular_user->name,
//                        "return_flg" => $circular_user->return_flg,
//                        "circular_status" => $circular_user->circular_status,
//                        "title" => $circular_user->title,
//                        "origin_circular_url" => CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->circular_id) . CircularUtils::encryptOutsideAccessCode($circular_user->id),
//                    ]]
//                ];
//
//                $envClient = EnvApiUtils::getAuthorizeClient($old_circular_user->env_flg, $old_circular_user->server_flg);
//                if (!$envClient){
//                    //TODO message
//                    throw new \Exception('Cannot connect to Env Api');
//                }
//
//                $response = $envClient->put("circularUsers/updatesTransferred",[
//                    RequestOptions::JSON => $transferredCircularUser
//                ]);
//                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
//                    Log::error('Cannot store circular user');
//                    Log::error($response->getBody());
//                    throw new \Exception('Cannot store circular user');
//                }
//            }
            DB::commit();
            return $this->sendResponse($circular_user->toArray(),'Create child circular user successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * Display the specified CircularUser.
     * GET|HEAD /circularUsers/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var FrmTemplateCircularUser $frmTemplateCircularUser */
        $frmTemplateCircularUser = $this->frmTemplateCircularUserRepository->find($id);
        if (empty($frmTemplateCircularUser)) {
            return $this->sendError('Frm Template Circular User not found');
        }

        return $this->sendResponse($frmTemplateCircularUser->toArray(), 'Frm Template Circular User retrieved successfully');
    }

    /**
     * Update the specified CircularUser in storage.
     * PUT/PATCH /circularUsers/{id}
     *
     * @param int $id
     * @param UpdateFrmTemplateCircularUserAPIRequest $request
     *
     * @return Response
     */
    public function update($frm_template_id,$id, UpdateFrmTemplateCircularUserAPIRequest $request)
    {
        $input = $request->all();
        $login_user = $request->User();
        $input['frm_template_id'] = $frm_template_id;
        if(array_key_exists('usingHash', $input)) {
            unset($input['usingHash']);
        }
        /** @var FrmTemplateCircularUser $frmTemplateCircularUser */
        $frmTemplateCircularUser = $this->frmTemplateCircularUserRepository->find($id);

        if (empty($frmTemplateCircularUser)) {
            return $this->sendError('ユーザーが見つかりません。');
        }
        $frmTemplateCircularUser = $this->frmTemplateCircularUserRepository->update($input, $id);

        return $this->sendResponse($frmTemplateCircularUser->toArray(), '回覧ユーザーの更新処理に成功しました。');
    }

    public function addViewuser(UpdateFrmTemplateViewingUserAPIRequest $request)
    {
        try{
            $input = $request->all();
            $frm_template_id = $input['frm_template_id'];
            $parent_send_order = $input['parent_send_order'];
            $mst_company_id = $input['company_id'];
            $mst_user_id = $input['mst_user_id'];
            $create_at = Carbon::now();
            $create_user = $input['create_user'];
            $update_at = Carbon::now();
            $update_user = $input['update_user'];

            DB::table('frm_template_viewing_user')->insert([
                'frm_template_id'=> $frm_template_id,
                'parent_send_order' => $parent_send_order,
                'mst_company_id' => $mst_company_id,
                'mst_user_id'=> $mst_user_id,
                'create_at'=> Carbon::now(),
                'create_user'=> $create_user,
                'update_at' => Carbon::now(),
                'update_user' => $update_user
            ]);

            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function removeViewuser(ClearFrmTemplateViewingUserRequest $request)
    {
        try{
            $input = $request->all();
            $frm_template_id = $input['frm_template_id'];
            $email = $input['email'];

            $uid = DB::table('mst_user')
                ->select('id')
                ->where('email', $email)
                ->first();

            DB::table('frm_template_viewing_user')
                ->where('frm_template_id', $frm_template_id)
                ->where('mst_user_id', $uid->id)
                ->delete();

            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


//    /**
//     * Update the multiple CircularUser of circular in storage.
//     * PUT /circularUsers/updates
//     *
//     * @param UpdateMultipleCircularUserAPIRequest $request
//     *
//     * @return Response
//     */
//    public function updates(UpdateMultipleCircularUserAPIRequest $request, $circular_id)
//    {
//        try {
//            $input = $request->all();
//            $login_user = $request->user();
//
//            if(!$login_user || !$login_user->id) {
//                $login_user = $request['user'];
//            }
//
//            $circularUsers = $input['circular_users'];
//
//            DB::beginTransaction();
//            $otherEnv = config('app.server_env');
//            $otherServer = config('app.server_flg');
//
//            $validCircularUserIds = [];
//            foreach ($circularUsers as $circularUser) {
//                if (isset($circularUser['id'])) {
//                    $validCircularUserIds[] = $circularUser['id'];
//                }
//            }
//
//            if (count($validCircularUserIds)){
//                $validCircularUserIds = DB::table('circular_user')->where('circular_id', $circular_id)->whereIn('id', $validCircularUserIds)->pluck('id')->toArray();
//            }
//
//            $updateCircularUsers = [];
//            foreach ($circularUsers as $circularUser) {
//                if (isset($circularUser['id']) && $circularUser['id'] && in_array($circularUser['id'], $validCircularUserIds)){
//                    $id = $circularUser['id'];
//                    unset($circularUser['id']);
//                    if(!$circularUser['title']){
//                        $circularUser['title'] = '';
//                    }
//                    // PAC_5-2011  重複したユーザのステータスを正確に取りたい 承認者B（1回目）は「承認（捺印あり）」承認者B（2回目）は「承認（捺印なし）」としたい
//                    // $circularUser['stamp_flg'] = 0     承認（捺印なし   $circularUser['stamp_flg'] = Number (>1)   捺印あり  ;
//                    $circularUser['update_at'] = Carbon::now();
//                    $circularUser['update_user'] = $login_user->email;
//                    unset($circularUser['text']);
//                    if ($circularUser['edition_flg'] == config('app.edition_flg')
//                        && ($circularUser['env_flg'] != config('app.server_env') || $circularUser['server_flg'] != config('app.server_flg'))
//                        && $circularUser['circular_status'] != CircularUserUtils::NOT_NOTIFY_STATUS){
//                        $otherEnv = $circularUser['env_flg'];
//                        $otherServer = $circularUser['server_flg'];
//                    }
//                    $updatedCircularUser = $this->circularUserRepository->update($circularUser, $id);
//                    $updateCircularUsers[] = [
//                        "email" => $updatedCircularUser->email,
//                        "parent_send_order" => $updatedCircularUser->parent_send_order,
//                        "child_send_order" => $updatedCircularUser->child_send_order,
//                        "env_flg" => $updatedCircularUser->env_flg,
//                        "edition_flg" => $updatedCircularUser->edition_flg,
//                        "server_flg" => $updatedCircularUser->server_flg,
//                        "mst_company_id" => $updatedCircularUser->mst_company_id,
//                        "mst_company_name" => $updatedCircularUser->mst_company_name,
//                        "mst_user_id" => $updatedCircularUser->mst_user_id,
//                        "name" => $updatedCircularUser->name,
//                        "return_flg" => $updatedCircularUser->return_flg,
//                        "circular_status" => $updatedCircularUser->circular_status,
//                        "received_date" => $updatedCircularUser->received_date,
//                        "sent_date" => $updatedCircularUser->sent_date,
//                        "title" => $updatedCircularUser->title,
//                        "origin_circular_url" => CircularUtils::generateApprovalUrl($updatedCircularUser->email, $updatedCircularUser->edition_flg, $updatedCircularUser->env_flg, $updatedCircularUser->server_flg, $updatedCircularUser->circular_id) . CircularUtils::encryptOutsideAccessCode($id),
//                    ];
//                }
//
//            }
//            if (($otherEnv != config('app.server_env') || $otherServer != config('app.server_flg')) && count($updateCircularUsers)){
//                Log::debug('sync new circular to other application');
//
//                $circular = DB::table('circular')->where('id', $circular_id)->select('env_flg', 'edition_flg', 'server_flg')->first();
//
//                $transferredCircularUser = ['origin_circular_id' => $circular_id,
//                    'env_flg' => $circular->env_flg,
//                    'edition_flg' => $circular->edition_flg,
//                    'server_flg' => $circular->server_flg,
//                    'update_user' => $login_user->email,
//                    'update_circular_users' => $updateCircularUsers
//                ];
//
//                $envClient = EnvApiUtils::getAuthorizeClient($otherEnv,$otherServer);
//                if (!$envClient){
//                    //TODO message
//                    throw new \Exception('Cannot connect to Env Api');
//                }
//
//                $response = $envClient->put("circularUsers/updatesTransferred",[
//                    RequestOptions::JSON => $transferredCircularUser
//                ]);
//                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
//                    Log::error('Cannot store circular user');
//                    Log::error($response->getBody());
//                    throw new \Exception('Cannot store circular user');
//                }
//            }
//
//            DB::commit();
//            return $this->sendSuccess('回覧ユーザーの更新処理に成功しました。');
//
//        }catch (\Exception $ex) {
//            DB::rollBack();
//            Log::error($ex->getMessage().$ex->getTraceAsString());
//            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }

//    /**
//     * Update the multiple transferred CircularUser of circular in storage.
//     * PUT /circularUsers/updatesTransferred
//     *
//     * @param UpdateTransferredCircularUserAPIRequest $request
//     *
//     * @return Response
//     */
//    public function updatesTransferred(UpdateTransferredCircularUserAPIRequest $request)
//    {
//        try {
//            $update_user = $request['update_user'];
//
//            DB::beginTransaction();
//
//            $existCircular = DB::table('circular')->where('origin_circular_id', $request['origin_circular_id'])
//                ->where('env_flg', $request['env_flg'])
//                ->where('edition_flg', $request['edition_flg'])
//                ->where('server_flg', $request['server_flg'])
//                ->first();
//            if ($existCircular){
//                $circular_id = $existCircular->id;
//                if (isset($request['new_circular_users']) && $request['new_circular_users']){
//                    $newCircularUsers = [];
//                    foreach($request['new_circular_users'] as $new_circular_user){
//                        $newCircularUser = $new_circular_user;
//                        $newCircularUser['circular_id'] = $circular_id;
//                        $newCircularUser['title'] = isset($new_circular_user['title']) && $new_circular_user['title']?$new_circular_user['title']:'';
//                        $newCircularUser['del_flg'] = 0;
//                        $newCircularUser['create_at'] = Carbon::now();
//                        $newCircularUser['create_user'] = $update_user;
//                        $newCircularUsers[] = $newCircularUser;
//                    }
//
//                    if (count($newCircularUsers)){
//                        DB::table('circular_user')->insert($newCircularUsers);
//                    }
//                }
//                if (isset($request['remove_circular_users']) && $request['remove_circular_users']){
//                    foreach($request['remove_circular_users'] as $remove_circular_user){
//                        // TODO improve performance
//                        DB::table('circular_user')->where('circular_status', CircularUserUtils::NOT_NOTIFY_STATUS)
//                            ->where('circular_id', $circular_id)
//                            ->where('parent_send_order', $remove_circular_user['parent_send_order'])
//                            ->where('child_send_order', $remove_circular_user['child_send_order'])
//                            ->delete();
//
//                        $this->reBuildCircularUserAfterDelete($circular_id, $remove_circular_user['parent_send_order'], $remove_circular_user['child_send_order']);
//                    }
//                }
//                if (isset($request['update_circular_users']) && $request['update_circular_users']){
//                    foreach($request['update_circular_users'] as $update_circular_user){
//                        // TODO improve performance
//                        $updateCircularUser = $update_circular_user;
//                        $updateCircularUser['title'] = isset($update_circular_user['title']) && $update_circular_user['title']?$update_circular_user['title']:'';
//                        $updateCircularUser['update_at'] = Carbon::now();
//                        $updateCircularUser['update_user'] = $update_user;
//                        if (isset($updateCircularUser['received_date']) && $updateCircularUser['received_date']){
//                            $updateCircularUser['received_date'] = Carbon::parse($updateCircularUser['received_date'])->format('Y-m-d H:i:s');
//                        }
//                        if (isset($updateCircularUser['sent_date']) && $updateCircularUser['sent_date']){
//                            $updateCircularUser['sent_date'] = Carbon::parse($updateCircularUser['sent_date'])->format('Y-m-d H:i:s');
//                        }
//                        DB::table('circular_user')->where('circular_id', $circular_id)->where('parent_send_order', $update_circular_user['parent_send_order'])
//                            ->where('child_send_order', $update_circular_user['child_send_order'])->update($updateCircularUser);
//                    }
//                }
//            }else{
//                $this->sendError('Circular invalid', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
//            }
//
//            DB::commit();
//            return $this->sendSuccess('回覧ユーザーの更新処理に成功しました。');
//
//        }catch (\Exception $ex) {
//            DB::rollBack();
//            Log::error($ex->getMessage().$ex->getTraceAsString());
//            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }

    private function reBuildCircularUserAfterDelete($frm_template_id, $deletedParentSendOrder, $deletedChildSendOrder){
        DB::table('frm_template_circular_user')
            ->where('frm_template_id', $frm_template_id)
            ->where('parent_send_order', $deletedParentSendOrder)
            ->where('child_send_order','>', $deletedChildSendOrder)
            ->update([
                'child_send_order' => DB::raw( 'child_send_order - 1')
            ]);

        if($deletedParentSendOrder > 0 && $deletedChildSendOrder == 1) {
            $count = DB::table('frm_template_circular_user')
                ->where('frm_template_id', $frm_template_id)
                ->where('parent_send_order', '=', $deletedParentSendOrder)
                ->count();
            if ($count === 0){
                $previousCircularUser = DB::table('frm_template_circular_user')
                    ->where('frm_template_id', $frm_template_id)
                    ->where('parent_send_order', '=', $deletedParentSendOrder - 1)
                    ->orderByDesc('child_send_order')
                    ->first();
                $nextCircularUser = DB::table('frm_template_circular_user')
                    ->where('frm_template_id', $frm_template_id)
                    ->where('parent_send_order', '=', $deletedParentSendOrder + 1)
                    ->orderBy('child_send_order')
                    ->first();
                if ($previousCircularUser && $nextCircularUser
                    && $previousCircularUser->edition_flg === $nextCircularUser->edition_flg
                    && $previousCircularUser->env_flg === $nextCircularUser->env_flg
                    && $previousCircularUser->server_flg === $nextCircularUser->server_flg
                    && $previousCircularUser->mst_company_id === $nextCircularUser->mst_company_id
                    && $previousCircularUser->mst_company_id  !== null){
                    // The previous and next circular user is same company, make appending
                    DB::table('frm_template_circular_user')
                        ->where('frm_template_id', $frm_template_id)
                        ->where('parent_send_order', '=', $deletedParentSendOrder + 1)
                        ->update([
                            'child_send_order' => DB::raw('child_send_order + '.$previousCircularUser->child_send_order)
                        ]);
                    DB::table('frm_template_circular_user')
                        ->where('frm_template_id', $frm_template_id)
                        ->where('parent_send_order', '>', $deletedParentSendOrder)
                        ->update([
                            'parent_send_order' => DB::raw('parent_send_order - 2')
                        ]);
                }else{
                    DB::table('frm_template_circular_user')
                        ->where('frm_template_id', $frm_template_id)
                        ->where('parent_send_order', '>', $deletedParentSendOrder)
                        ->update([
                            'parent_send_order' => DB::raw('parent_send_order - 1')
                        ]);
                }
            }
        }
    }

    /**
     * Remove the specified CircularUser from storage.
     * DELETE /circularUsers/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($circular_id, $id, Request $request)
    {
        try {
            DB::beginTransaction();
            /** @var FrmTemplateCircularUser $frmTemplateCircularUser */
            $frmTemplateCircularUser = $this->frmTemplateCircularUserRepository->find($id);

            if (empty($frmTemplateCircularUser)) {
                DB::rollBack();
                return $this->sendError('ユーザーが見つかりません。');
            }

            $frmTemplateCircularUser->delete();

            $this->reBuildCircularUserAfterDelete($frmTemplateCircularUser->frm_template_id, $frmTemplateCircularUser->parent_send_order, $frmTemplateCircularUser->child_send_order);

//            if ($frmTemplateCircularUser->edition_flg == config('app.edition_flg')
//                && ($frmTemplateCircularUser->env_flg != config('app.server_env') || $frmTemplateCircularUser->server_flg != config('app.server_flg'))){
//                Log::debug('sync new circular to other application: check current_circular_user');
//                if (isset($request['current_circular_user'])){
//                    $currentCircularUser = $request['current_circular_user'];
//                    if ($currentCircularUser->edition_flg == config('app.edition_flg')
//                        && ($currentCircularUser->env_flg != config('app.server_env') || $currentCircularUser->server_flg != config('app.server_flg'))){
//                        Log::debug('sync new circular to other application');
//
//                        $circular = DB::table('circular')->select('edition_flg', 'env_flg', 'server_flg')->where('id',  $request['current_circular'])->first();
//                        $transferredCircularUser = ['origin_circular_id' => $request['current_circular'],
//                            'env_flg' => $circular->env_flg,
//                            'edition_flg' => $circular->edition_flg,
//                            'server_flg' => $circular->server_flg,
//                            'update_user' => $request['current_email'],
//                            'remove_circular_users' => [[
//                                "parent_send_order" => $frmTemplateCircularUser->parent_send_order,
//                                "child_send_order" => $frmTemplateCircularUser->child_send_order,
//                            ]]
//                        ];
//
//                        $envClient = EnvApiUtils::getAuthorizeClient($frmTemplateCircularUser->env_flg,$frmTemplateCircularUser->server_flg);
//                        if (!$envClient){
//                            //TODO message
//                            throw new \Exception('Cannot connect to Env Api');
//                        }
//
//                        $response = $envClient->put("circularUsers/updatesTransferred",[
//                            RequestOptions::JSON => $transferredCircularUser
//                        ]);
//                        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
//                            Log::error('Cannot store circular user');
//                            Log::error($response->getBody());
//                            throw new \Exception('Cannot store circular user');
//                        }
//                    }
//                }
//            }
            DB::commit();
            $circular_users = DB::table('frm_template_circular_user')
                ->where('frm_template_id', $frmTemplateCircularUser->frm_template_id)
                ->orderBy('parent_send_order')
                ->orderBy('child_send_order')
                ->get();

            return $this->sendResponse($circular_users, '回覧ユーザーの削除処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove all CircularUsers from storage.
     * DELETE /circularUsers/deletes
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function clear($frm_template_id,ClearFrmTemplateCircularUserRequest $request)
    {
        try {

            DB::table('frm_template_circular_user')
                ->where('frm_template_id', $frm_template_id)
                ->where('child_send_order', '!=', 0)
                ->delete();

            // 合議の場合
//            DB::table('circular_user_routes')
//                ->where('circular_id', $circular_id)
//                ->delete();

            return $this->sendSuccess('削除処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateReturnflg($frm_template_id, $id, Request $request) {
        try {
            $return_flg = $request['returnFlg'];
            $login_user = $request->user();

            if(!$login_user || !$login_user->id) {
                $login_user = $request['user'];
            }
            DB::beginTransaction();

            DB::table('frm_template_circular_user')
                ->where('id', $id)
                ->update([
                    'return_flg' => $return_flg?:0,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                ]);
//            $circularUser = DB::table('frm_template_circular_user')
//                ->where('id', $id)->first();
//            if ($circularUser->edition_flg == config('app.edition_flg') && ($circularUser->env_flg != config('app.server_env') || $circularUser->server_flg != config('app.server_flg')) && $circularUser->circular_status != CircularUserUtils::NOT_NOTIFY_STATUS){
//                $updateCircularUsers[] = [
//                    "email" => $circularUser->email,
//                    "parent_send_order" => $circularUser->parent_send_order,
//                    "child_send_order" => $circularUser->child_send_order,
//                    "env_flg" => $circularUser->env_flg,
//                    "edition_flg" => $circularUser->edition_flg,
//                    "server_flg" => $circularUser->server_flg,
//                    "mst_company_id" => $circularUser->mst_company_id,
//                    "mst_user_id" => $circularUser->mst_user_id,
//                    "name" => $circularUser->name,
//                    "return_flg" => $circularUser->return_flg,
//                    "origin_circular_url" => CircularUtils::generateApprovalUrl($circularUser->email, $circularUser->edition_flg, $circularUser->env_flg, $circularUser->server_flg, $circularUser->circular_id) . CircularUtils::encryptOutsideAccessCode($circularUser->id),
//                ];
//
//                $transferredCircularUser = ['origin_circular_id' => $circularUser->circular_id,
//                    'env_flg' => config('app.server_env'),
//                    'edition_flg' => config('app.edition_flg'),
//                    'server_flg' => config('app.server_flg'),
//                    'update_user' => $login_user->email,
//                    'update_circular_users' => $updateCircularUsers
//                ];
//
//                $envClient = EnvApiUtils::getAuthorizeClient($circularUser->env_flg, $circularUser->server_flg);
//                if (!$envClient){
//                    //TODO message
//                    throw new \Exception('Cannot connect to Env Api');
//                }
//
//                $response = $envClient->put("circularUsers/updatesTransferred",[
//                    RequestOptions::JSON => $transferredCircularUser
//                ]);
//                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
//                    Log::error('Cannot store circular user');
//                    Log::error($response->getBody());
//                    throw new \Exception('Cannot store circular user');
//                }
//            }

            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendSuccess('');
    }


//    public function getUserView(Request $request) {
//
//        Log::debug("値チェック".$request->get("circular_id"));
//        $email=str_replace([' ','  '], '+', $request->get("email"));
//        Log::debug("値チェック".$email);
//        $creater_id= DB::table('circular')->where('id', $request->get("circular_id"))->value("mst_user_id");
//        $mst_company_id = DB::table('mst_user')->where('id', $creater_id)->value("mst_company_id");
//        $user_id = DB::table('mst_user')->where('email', $email)->where('mst_company_id',$mst_company_id)->value("id");
//        Log::debug("値チェック(user_id)".$user_id);
//        if($user_id){
//            $mail=array();
//            $name=array();
//            $i=0;
//            $mst_user_ids=DB::table('viewing_user')->where('circular_id', $request->get("circular_id"))->get();
//            foreach($mst_user_ids as $mst_user_id){
//                $mst_users=DB::table('mst_user')->where('id',$mst_user_id->mst_user_id)->get();
//                foreach($mst_users as $mst_user){
//                    $mail[$i]=$mst_user->email;
//                    $name[$i]="$mst_user->family_name"."$mst_user->given_name";
//                    $i++;
//
//                }
//
//            }
//            $i=$i-1;
//
//            return   ['email' => $mail, 'name' => $name, 'i' => $i ];
//        }
//
//    }

//    public function deleteUserView(Request $request) {
//
//        $email=str_replace([' ','  '], '+', $request->get("email"));
//        $id=DB::table('mst_user')->where('email',$email)->value("id");
//        DB::table('viewing_user')->where('circular_id', $request->get("circular_id"))->where('mst_user_id',$id)->delete();
//
//    }
}
