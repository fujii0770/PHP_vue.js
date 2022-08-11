<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Delegate\EnvApiDelegate;
use App\Http\Requests\API\ClearCircularUserRequest;
use App\Http\Requests\API\CreateChildCircularUserAPIRequest;
use App\Http\Requests\API\CreateCircularUserAPIRequest;
use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Requests\API\SendBackRequest;
use App\Http\Requests\API\SendNotifyContinueRequest;
use App\Http\Requests\API\UpdateCircularUserAPIRequest;
use App\Http\Requests\API\UpdateMultipleCircularUserAPIRequest;
use App\Http\Requests\API\UpdateTransferredCircularUserAPIRequest;
use App\Http\Requests\API\UpdateTransferredStatusAPIRequest;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\CircularOperationHistoryUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\ExpenseUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\SpecialApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\TemplateRouteUtils;
use App\Jobs\PushNotify;
use App\Jobs\SendAllUserCircular;
use App\Jobs\SendNotification;
use App\Models\CircularUser;
use App\Models\CircularUserRoutes;
use App\Repositories\CircularUserRepository;
use App\Repositories\CompanyRepository;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Image;
use Response;
use Session;
use App\Http\Utils\OperationsHistoryUtils;
use App\Mail\SendAccessCodeNoticeMail;
use App\Mail\SendCircularUserMail;
use App\Mail\SendMailInitPassword;
use App\Mail\SendCircularPullBackMail;
use App\Models\Circular;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Class CircularUserController
 * @package App\Http\Controllers\API
 */

class CircularUserAPIController extends AppBaseController
{
    /** @var  CircularUserRepository */
    private $circularUserRepository;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    public function __construct(CircularUserRepository $circularUserRepo, CompanyRepository $companyRepository)
    {
        $this->circularUserRepository = $circularUserRepo;
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
        $circularUsers = $this->circularUserRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($circularUsers->toArray(), 'Circular Users retrieved successfully');
    }

    public function store($circular_id, CreateCircularUserAPIRequest $request) {
        try {
            $login_user = $request->user();

            $system_env_flg     = config('app.server_env');
            $system_edition_flg = config('app.edition_flg');
            $system_server_flg = config('app.server_flg');

            $users = $request['users'];
            $rets = [];

            /* PAC_5-974 【速度改善】アドレス帳で指定した宛先の反映速度改善②③ get only last circular user */
            $last_circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->orderBy('parent_send_order', 'desc')
                ->orderBy('child_send_order', 'desc')
                ->first();
            $company=DB::table('mst_company')
                ->where('id','=',$request->user()->mst_company_id)
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
            // PAC_5-1698
            $is_plan = (count($users)>1 && isset($users[0]['is_plan']) && $users[0]['is_plan'] == 1) && $company->user_plan_flg;
            $plan_id = 0;
            $is_update_parent = false;
            if ($is_plan) {
                /*PAC_5-2820 S*/
                if (count($users) > 30) {
                    return $this->sendError('最大30人しか一つ合議に設定できません。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                /*PAC_5-2820 E*/
                $plan_id = DB::table('circular_user_plan')->insertGetId([
                    'circular_id' => $circular_id,
                    'child_send_order' => $parent_send_order + 1,
                    'state' => 1,
                    'create_at' => Carbon::now(),
                    'create_user' => $login_user->email,
                    'update_at' => Carbon::now(),
                    'update_user' => $login_user->email,
                    'mode' => 1,
                    'score' => count($users)
                ]);
                $child_send_order_plan = $child_send_order + 1;
            }

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
                        $is_update_parent = true;
                    }else{
                        // $template_route_id == -1 非合議
                        if($template_route_id == -1 || $template_route_id != $old_template_route_id){
                            if ($is_plan) {
                                if ($is_update_parent) {
                                    $child_send_order;
                                } else {
                                $child_send_order = $child_send_order_plan;
                                }
                            } else {
                        $child_send_order += 1;
                    }
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
                Log::debug("Insert circular user for circular $circular_id: email - ".$user['email'].", parent_send_order - $parent_send_order, child_send_order - $child_send_order");
                $circular_user_params = [
                    'circular_id'=> $circular_id,
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
                    'title'=> $last_circular_user ? $last_circular_user->title: '',
                    'del_flg'=> CircularUserUtils::NOT_DELETE,
                    'return_flg' => 1,
                    'circular_status'=> CircularUserUtils::NOT_NOTIFY_STATUS,
                    'create_at' => Carbon::now(),
                    'create_user' => $login_user->email,
                    'update_at' => Carbon::now(),
                    'update_user' => $login_user->email,
                    'received_date' => $received_date,
                    'plan_id' => $plan_id,
                ];
                $circular_user = $this->circularUserRepository->create($circular_user_params);
                $arr_circular_user = $circular_user->toArray();

                // 合議の場合
                if($template_route_id !== -1){
                    $mode = $user['template_mode'];
                    $wait = $user['template_wait'];
                    $score = $user['template_score'];
                    $detail = $user['template_detail'];

                    $circular_user_routes = CircularUserRoutes::where('circular_id', $circular_id)->where('child_send_order', $child_send_order)->first();
                    if(!$circular_user_routes){
                        $circular_user_routes = new CircularUserRoutes();
                        $circular_user_routes->circular_id = $circular_id;
                        $circular_user_routes->child_send_order = $child_send_order;
                        $circular_user_routes->mode = $mode;
                        $circular_user_routes->wait = $wait;
                        $circular_user_routes->score = $score;
                        $circular_user_routes->detail = $detail;
                        $circular_user_routes->state = 1;
                        $circular_user_routes->create_at = Carbon::now();
                        $circular_user_routes->create_user = $login_user->email;
                        $circular_user_routes->update_at = Carbon::now();
                        $circular_user_routes->update_user = $login_user->email;
                        $circular_user_routes->save();
            }
                    $arr_circular_user["user_routes_id"] = $circular_user_routes->id; // 合議 route id
                    $arr_circular_user["detail"] = $circular_user_routes->detail;
                    $arr_circular_user["mode"] = $circular_user_routes->mode;
                    $arr_circular_user["wait"] = $circular_user_routes->wait;
                    $arr_circular_user["score"] = $circular_user_routes->score;
                }

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
     * @param CreateChildCircularUserAPIRequest $request
     * @return mixed
     */
    public function storeChildren($circular_id, CreateChildCircularUserAPIRequest $request) {
        try {
            $login_user = $request->user();
            $email  = $request->get('email', null);
            if(!$login_user || !$login_user->id) {
                $login_user = $request['user'];
            }
            DB::beginTransaction();

            $parent_circular_user = DB::table('circular_user')
                ->where('id', $request['parent_id'])
                ->first();

            $old_circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('mst_company_id', $parent_circular_user->mst_company_id)
                ->where('parent_send_order', $parent_circular_user->parent_send_order)
                ->orderBy('child_send_order', 'desc')
                ->first();
            $special_site_receive_flg = 0;
            $circular = DB::table('circular')->where('id', $circular_id)->select('special_site_flg')->first();
            if ($circular && $circular->special_site_flg && $old_circular_user && $old_circular_user->special_site_receive_flg == 1) {
                $special_site_receive_flg = 1;
            }
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
                    if ($db_user->option_flg == AppUtils::USER_RECEIVE && $old_circular_user->mst_company_id != $db_user->mst_company_id){
                        return $this->sendError($request['email'].': 受信専用利用者は社内文書しか受信できないです。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                    }else{
                    $user_company_id = $db_user->mst_company_id;
                    $new_user_id = $db_user->id;
                }
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
                        if ($user->user_auth == AppUtils::AUTH_FLG_RECEIVE){
                            return $this->sendError($request['email'].': 受信専用利用者は社内文書しか受信できないです。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                        }
                        if($old_circular_user->mst_company_id == $user->company_id && $old_circular_user->edition_flg == $user->edition_flg && $old_circular_user->env_flg == $user->env_flg && $old_circular_user->server_flg == $user->server_flg) {
                            $new_user = $user;
                        }
                    }

                    if($new_user) {
                        Log::debug('Circular user in pacId');
                        $circular_user_params = [
                            'circular_id'=> $old_circular_user->circular_id,
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
                            'title'=> $old_circular_user->title,
                            'del_flg'=> CircularUserUtils::NOT_DELETE,
                            'return_flg' => 0,
                            'circular_status'=> CircularUserUtils::NOT_NOTIFY_STATUS,
                            'create_at' => Carbon::now(),
                            'create_user' => $login_user->email,
                            'special_site_receive_flg' => $special_site_receive_flg,
                        ];

                        $circular_user = $this->circularUserRepository->create($circular_user_params);

                        if ($old_circular_user->edition_flg == config('app.edition_flg') &&
                            ($old_circular_user->env_flg != config('app.server_env') || $old_circular_user->server_flg != config('app.server_flg'))){
                            Log::debug('sync new circular to other application');

                            $circular = DB::table('circular')->where('id', $old_circular_user->circular_id)->select('env_flg', 'edition_flg', 'server_flg')->first();

                            $transferredCircularUser = ['origin_circular_id' => $old_circular_user->circular_id,
                                'env_flg' => $circular->env_flg,
                                'edition_flg' => $circular->edition_flg,
                                'server_flg' => $circular->server_flg,
                                'update_user' => $login_user->email,
                                'new_circular_users' => [[
                                    "email" => $circular_user->email,
                                    "parent_send_order" => $circular_user->parent_send_order,
                                    "child_send_order" => $circular_user->child_send_order,
                                    "env_flg" => $circular_user->env_flg,
                                    "edition_flg" => $circular_user->edition_flg,
                                    "server_flg" => $circular_user->server_flg,
                                    "mst_company_id" => $circular_user->mst_company_id,
                                    "mst_company_name" => $circular_user->mst_company_name,
                                    "mst_user_id" => $circular_user->mst_user_id,
                                    "name" => $circular_user->name,
                                    "return_flg" => $circular_user->return_flg,
                                    "circular_status" => $circular_user->circular_status,
                                    "title" => $circular_user->title,
                                    "origin_circular_url" => CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->circular_id) . CircularUtils::encryptOutsideAccessCode($circular_user->id),
                                    "special_site_receive_flg" => $special_site_receive_flg,
                                ]]
                            ];

                            $envClient = EnvApiUtils::getAuthorizeClient($old_circular_user->env_flg, $old_circular_user->server_flg);
                            if (!$envClient){
                                //TODO message
                                throw new \Exception('Cannot connect to Env Api');
                            }

                            $response = $envClient->put("circularUsers/updatesTransferred",[
                                RequestOptions::JSON => $transferredCircularUser
                            ]);
                            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                                Log::error('Cannot store circular user');
                                Log::error($response->getBody());
                                throw new \Exception('Cannot store circular user');
                            }
                        }
                        DB::commit();
                        return $this->sendResponse($circular_user->toArray(),'Create child circular user successfully');
                    }
                    //PAC_5-1243 社外宛先登録時のメッセージを修正
                    return $this->sendError($request['email'].': 承認者は社外のユーザーは追加できません', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                }
            }
            $circular_user_params = [
                'circular_id'=> $old_circular_user->circular_id,
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
                'title'=> $old_circular_user->title,
                'del_flg'=> CircularUserUtils::NOT_DELETE,
                'return_flg' => 0,
                'circular_status'=> CircularUserUtils::NOT_NOTIFY_STATUS,
                'create_at' => Carbon::now(),
                'create_user' => $login_user->email,
                "special_site_receive_flg" => $special_site_receive_flg,
            ];
            $circular_user = $this->circularUserRepository->create($circular_user_params);

            if ($old_circular_user->edition_flg == config('app.edition_flg') && ($old_circular_user->env_flg != config('app.server_env') || $old_circular_user->server_flg != config('app.server_flg'))){
                Log::debug('sync new circular to other application');

                $circular = DB::table('circular')->where('id', $old_circular_user->circular_id)->select('env_flg', 'edition_flg', 'server_flg')->first();

                $transferredCircularUser = ['origin_circular_id' => $old_circular_user->circular_id,
                    'env_flg' => $circular->env_flg,
                    'edition_flg' => $circular->edition_flg,
                    'server_flg' => $circular->server_flg,
                    'update_user' => $login_user->email,
                    'new_circular_users' => [[
                        "email" => $circular_user->email,
                        "parent_send_order" => $circular_user->parent_send_order,
                        "child_send_order" => $circular_user->child_send_order,
                        "env_flg" => $circular_user->env_flg,
                        "edition_flg" => $circular_user->edition_flg,
                        "server_flg" => $circular_user->server_flg,
                        "mst_company_id" => $circular_user->mst_company_id,
                        "mst_company_name" => $circular_user->mst_company_name,
                        "mst_user_id" => $circular_user->mst_user_id,
                        "name" => $circular_user->name,
                        "return_flg" => $circular_user->return_flg,
                        "circular_status" => $circular_user->circular_status,
                        "title" => $circular_user->title,
                        "origin_circular_url" => CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->circular_id) . CircularUtils::encryptOutsideAccessCode($circular_user->id),
                        "special_site_receive_flg" => $special_site_receive_flg,
                    ]]
                ];

                $envClient = EnvApiUtils::getAuthorizeClient($old_circular_user->env_flg, $old_circular_user->server_flg);
                if (!$envClient){
                    //TODO message
                    throw new \Exception('Cannot connect to Env Api');
                }

                $response = $envClient->put("circularUsers/updatesTransferred",[
                    RequestOptions::JSON => $transferredCircularUser
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::error('Cannot store circular user');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot store circular user');
                }
            }
            DB::commit();
            return $this->sendResponse($circular_user->toArray(),'Create child circular user successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function sendNotifyFirst($circular_id, Request $request)
    {
        $login_user = $request->user();
        $input = $request->all();
        if(!empty($input['isSendAllUser'])){
            $currentUser = DB::table("circular_user")
                ->where("parent_send_order",0)
                ->where("child_send_order",0)
                ->where("circular_id",$circular_id)
                ->first();
            if(empty($currentUser)){
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
            }
            $login_user = (new \App\User())->find($currentUser->mst_user_id);
        }

        // PAC_5-1973 ログインパスワードの変更画面から利用者としてログインできるように修正 Start
        if (isset($input['title']) && is_string($input['title'])) $input['title'] = preg_replace('/[\t]/', '', $input['title']);
        // PAC_5-1973
        $validator = Validator::make($input, [
            'hide_thumbnail_flg' => 'nullable|boolean',
            're_notification_day' => 'nullable|date',
            'address_change_flg' => 'nullable|boolean',
            'access_code_flg' => 'nullable|boolean',
            'access_code' => "nullable|string|max:10",
            'title' => "nullable|string|max:256",
            'text_append_flg' => 'nullable|boolean',
            'require_print' => 'nullable|boolean',
            'text' => 'nullable|string|max:500'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(!$input['mail_to'] && !$input['specialFile']){
            return $this->sendError("回覧先を省略することはできません");
        }
        if(!isset($input['require_print']) || is_null($input['require_print'])){
            $companyProtection = DB::table('mst_protection')
                ->where('mst_company_id','=',$login_user->mst_company_id)
                ->first();
            $input['require_print']=$companyProtection->require_print;
        }

        $company = DB::table('mst_company')->where('id', $login_user->mst_company_id)->first();
        $isFromGuestCompany = $company->guest_company_flg;
        $canApplyDocument = $company->guest_document_application;

        if ($isFromGuestCompany && !$canApplyDocument){
            return $this->sendError("Do not allow to apply circular");
        }
        $lgwan_parent_order = -1;
        $lgwan_env_flg = -1;
        $lgwan_server_flg = -1;

        DB::beginTransaction();
        try {
            //特設サイト、回覧ユーザー登録
            if(isset($input['specialFile']) && $input['specialFile']){
                $templateInfo = DB::table('special_site_circular')->where('circular_id',$circular_id)->first();
                if($templateInfo){
                    $circularQuery = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->get();
                    $mst_company_id = DB::table('mst_user')
                        ->where('id', $input['user'])
                        ->get()
                        ->toArray();
                    $mst_company_id = $mst_company_id[0]->mst_company_id;
                    // SRS-018 回覧開始
                    $client = SpecialApiUtils::getAuthorizeClient();
                    if (!$client) {
                        Log::error(__('message.false.auth_client'));
                    }
                    $response = $client->post("/sp/api/get-circular-users", [
                        RequestOptions::JSON => [
                            'company_id' => $mst_company_id,
                            "env_flg"=>config('app.server_env'),
                            "edition_flg"=>config('app.edition_flg'),
                            "server_flg"=>config('app.server_flg'),
                            "entered_circular_token"=> $templateInfo->circular_token,
                        ]
                    ]);
                    $response_dencode = json_decode($response->getBody(),true);  //配列へ
                    if ($response->getStatusCode() == 200) {
                        $response_body = json_decode($response->getBody(),true);  //配列へ
                        $circular_infos = $response_body['result']['circular_info'];
                    } else {
                        Log::error('Api storeBoard companyId:' . $mst_company_id);
                        Log::error($response_dencode);
                        return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
                    }

                    $newCircularUser['circular_id'] = $input['circular_id'];
                    $newCircularUser['env_flg'] = $templateInfo->receive_env_flg;
                    $newCircularUser['edition_flg'] = $templateInfo->receive_edition_flg;
                    $newCircularUser['server_flg'] = $templateInfo->receive_server_flg;
                    $newCircularUser['title'] = ' ';
                    $newCircularUser['circular_status'] = AppUtils::MAIL_STATE_WAIT;
                    $newCircularUser['del_flg'] = 0;
                    $newCircularUser['create_at'] = Carbon::now();
                    $newCircularUser['create_user'] = $login_user->email;
                    $newCircularUser['return_flg'] = 1;

                    $child_send_order = 0;
                    $parent_send_order = 0;
                    foreach ($circularQuery as $circular){
                        if($parent_send_order < $circular->parent_send_order){
                            $parent_send_order = $circular->parent_send_order;
                        }
                        // ゲストユーザー存在の場合、所属環境変更
                        if (is_null($circular->mst_company_id)) {
                            DB::table('circular_user')->where('id', $circular->id)->update([
                                'edition_flg' => $templateInfo->receive_edition_flg,
                                'env_flg' => $templateInfo->receive_env_flg,
                                'server_flg' => $templateInfo->receive_server_flg,
                            ]);
                    }
                    }
                    $parent_send_order++;
                    $newCircularUser['parent_send_order'] = $parent_send_order;
                    $lgwan_parent_order = $parent_send_order;
                    foreach ($circular_infos as $circular_info) {
                        $mst_user_id = "";
                        // ユーザー情報取得
                        $envClient = EnvApiUtils::getAuthorizeClient($templateInfo->receive_env_flg, $templateInfo->receive_server_flg);
                        $lgwan_env_flg = $templateInfo->receive_env_flg;
                        $lgwan_server_flg = $templateInfo->receive_server_flg;
                        if (!$envClient) throw new \Exception('Cannot connect to Env Api');

                        $response = $envClient->get("getUserInfo/" . $circular_info['email'], []);
                        if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            $envUserInfo = json_decode($response->getBody())->data;
                            if ($envUserInfo) {
                                $mst_user_id = $envUserInfo->mst_user_id;
                            }
                        } else {
                            Log::warning('Cannot get Env UserInfo from other env');
                            Log::warning($response->getBody());
                        }

                        // 統合ID側からユーザー情報取得
                        $client = IdAppApiUtils::getAuthorizeClient();
                        if (!$client) {
                            return response()->json(['status' => false,
                                'message' => ['Cannot connect to ID App']
                            ]);
                        }

                        $result = $client->post("users/checkEmail", [
                            RequestOptions::JSON => ['email' => $circular_info['email']]
                        ]);

                        $resData = json_decode((string)$result->getBody());

                        $child_send_order++;
                        $newCircularUser['mst_user_id'] = $mst_user_id;
                        $newCircularUser['special_site_receive_flg'] = 1; //受取側
                        $newCircularUser['mst_company_id'] = $resData->data[0]->company_id;
                        $newCircularUser['mst_company_name'] = $resData->data[0]->company_name;
                        $newCircularUser['name'] = $resData->data[0]->name;
                        $newCircularUser['email'] = $circular_info['email'];
                        $newCircularUser['child_send_order'] = $child_send_order;

                        DB::table('circular_user')->insert($newCircularUser);
                    }
                }
            }

            $viewingUsers = [];
            $mst_constraints = DB::table('mst_constraints')->select('max_viwer_count')->where('mst_company_id', $login_user->mst_company_id)->first();
            if (count($request['userViews']) > $mst_constraints->max_viwer_count){
                return $this->sendError('閲覧ユーザーに設定できるのは'.$mst_constraints->max_viwer_count.'名までです');
            }
            // current circular  PAC_5-2354  ゲストユーザーから引戻しを行いその文書を申請するとタブが増えている
            $objCurrentCircular = DB::table('circular')->where("id",$circular_id)->first();
            foreach($request['userViews'] as $user){
                $viewingUsers[] = [ 'circular_id' => $circular_id,
                    'parent_send_order' => $user['parent_send_order'],
                    'mst_company_id' => $user['company_id'],
                    'mst_user_id' => $user['mst_user_id'],
                    'memo' => !CommonUtils::isNullOrEmpty($user['memo']) ? $user['memo'] : '',
                    'del_flg' => $user['del_flg'],
                    'create_user' => $user['create_user'],
                    'update_user' => $user['update_user'],
                    'create_at' => Carbon::now()
                ];
            }
            if (count($viewingUsers)){
                DB::table('viewing_user')->insert($viewingUsers);
            }
            DB::table('circular')->where('id', $circular_id)->update([
                'address_change_flg' => $input['address_change_flg'],
                'access_code_flg' => $input['access_code_flg'],
                'access_code' => $input['access_code'],
				'outside_access_code_flg' => $input['outside_access_code_flg'],
				'outside_access_code' => $input['outside_access_code'],
                'hide_thumbnail_flg' => $input['hide_thumbnail_flg'],
                're_notification_day' => $input['re_notification_day'],
                'applied_date' => Carbon::now(),
                'circular_status' =>  CircularUtils::CIRCULATING_STATUS,
                'final_updated_date' => Carbon::now(),
                'text_append_flg' => !empty($input['text_append_flg'])?$input['text_append_flg']:null,
                'require_print' => $input['require_print']
            ]);
            $circular = DB::table('circular')->where('id', $circular_id)->first();

            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->update(['title'=> $input['title']?:' ']);
            if(isset($input['operationNotice']) && $input['operationNotice']){
                DB::table('mst_user_info')
                ->where('mst_user_id',$login_user->id)
                ->update(['operation_notice_flg' => CircularUserUtils::DEFAULT_OPERATION_NOTICE_FLG]);
            }
            //PAC_5-1398　添付ファイル機能　件名を更新
            DB::table('circular_attachment')
                ->where('circular_id',$circular_id)
                ->update(['title'=> $input['title']?:' ']);

            $circular_users = DB::table('circular_user')
                ->where('circular_id',$circular_id)
                ->where('circular_status', CircularUserUtils::NOT_NOTIFY_STATUS)
                ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                ->orderBy('parent_send_order', 'asc')
                ->orderBy('child_send_order', 'asc')->get();
            $system_env_flg     = config('app.server_env');
            $system_edition_flg = config('app.edition_flg');
            $system_server_flg  = config('app.server_flg');

            $guest_users = $circular_users->filter(function ($circular_user, $key) {
                return $circular_user->mst_company_id == null;
            })->all();
            $db_guest_users = [];
            foreach ($guest_users as $key => $guest_user) {
                $db_guest_user['circular_id'] = $guest_user->circular_id;
                $db_guest_user['create_user_id'] = $login_user->id;
                $db_guest_user['email'] = $guest_user->email;
                $db_guest_user['create_company_id'] = $login_user->mst_company_id;
                $db_guest_user['name'] = $guest_user->name;
                $db_guest_user['create_at'] = Carbon::now();
                $db_guest_user['create_user'] = $guest_user->create_user;
                $db_guest_users[] = $db_guest_user;
            }
            DB::table('guest_user')->insert($db_guest_users);

            if($isFromGuestCompany) {
                $host_company_id = $company->mst_company_id;
                $host_company_env = $company->host_app_env;
                $host_company_server = $company->host_contract_server;

                $hasExternalCompany = false;
                $lastCircularUser = null;

                foreach($circular_users as $circular_user) {
                    if (($circular_user->mst_company_id != $host_company_id
                            || $circular_user->edition_flg != $system_edition_flg
                            || $circular_user->env_flg != $host_company_env
                            || $circular_user->server_flg != $host_company_server)
                        && ($circular_user->mst_company_id != $login_user->mst_company_id
                            || $circular_user->edition_flg != $system_edition_flg
                            || $circular_user->env_flg != $system_env_flg
                            || $circular_user->server_flg != $system_server_flg)){
                        $hasExternalCompany = true;
                        break;
                    }
                    $lastCircularUser = $circular_user;
                }
                if($hasExternalCompany
                    || $lastCircularUser->mst_company_id != $host_company_id
                    || $lastCircularUser->edition_flg != $system_edition_flg
                    || $lastCircularUser->env_flg != $host_company_env
                    || $lastCircularUser->server_flg != $host_company_server) {
                    DB::rollBack();
                    return $this->sendError('回覧先の設定が誤っています', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            if($input['addToContactsFlg']){
                $contacts = DB::table('address')
                    ->where('mst_user_id', $login_user->id)
                    ->select(DB::raw('CONCAT(name, email) as name, id'))
                    ->pluck('id','name');
                $arrInsert = [];
                foreach($circular_users as $circular_user){
                    //$circular_user->mst_company_id ==null AND
                    if(!isset($contacts[$circular_user->name.$circular_user->email])){
                        $arrInsert[] = [
                            'name' => $circular_user->name,
                            'email' => $circular_user->email,
                            'mst_company_id' => $login_user->mst_company_id,
                            'mst_user_id' => $login_user->id,
                            'type' => ContactUtils::TYPE_PERSONAL,
                            'state' => ContactUtils::STATE_ENABLE,
                            'create_at' => Carbon::now(),
                            'create_user' => $login_user->email,
                            'update_at' => Carbon::now(),
                            'update_user' => $login_user->email
                        ];
                    }
                }
                DB::table('address')->insert($arrInsert);
            }
            //  経費精算t_appにcircular_idおよびstatus登録
            $check=DB::table('eps_t_app')
                    ->where('circular_id', $circular_id)
                    ->first();
            if(!($check==null)){

                DB::table('eps_t_app')
                    ->where('mst_company_id', $login_user->mst_company_id)
                    ->where('circular_id', $circular_id)
                    ->update([
                        'status' => CircularUtils::CIRCULATING_STATUS,
                        'filing_date' => Carbon::now(),
                        'update_user' => $login_user->email,
                        'update_at' => Carbon::now(),
                    ]);
            }

            // 第一ノード取得
            $circular_user_first = $circular_users->first();
            if($circular_user_first && $circular_user_first->id) {
                // 同レベルの取得
                $circular_users_first = $circular_users->where('parent_send_order', $circular_user_first->parent_send_order)
                    ->where('child_send_order', $circular_user_first->child_send_order);

                $author =  DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', 0)
                    ->where('child_send_order', 0);

				// 件名・メッセージ入力した場合
				if($input['text']){
					$circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
						'circular_id'=> $circular_id,
						'circular_document_id' => $input['circular_document_id'],
						'operation_email' => $login_user->email,
						'operation_name' => $login_user->getFullName(),
						'acceptor_email'=> '',
						'acceptor_name'=> '',
						'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS,
						'create_at' => Carbon::now(),
					]);
					DB::table('document_comment_info')->insert([
						'circular_document_id' => $input['circular_document_id'],
						'circular_operation_id' => $circular_operation_history_id,
						'parent_send_order' => 0,
						'name'=> $login_user->getFullName(),
						'email'=> $login_user->email,
						'text'=> $input['text'],
						'private_flg'=> CircularOperationHistoryUtils::DOCUMENT_COMMENT_PRIVATE,
						'create_at' => Carbon::now(),
					]);
				}

                // PAC_5-539 承認履歴情報登録
                foreach($circular_users_first as $key => $circular_user) {
				DB::table('circular_operation_history')->insert([
					'circular_id'=> $circular_id,
					'operation_email' => $login_user->email,
					'operation_name' => $login_user->getFullName(),
					'acceptor_email'=> $circular_user ? $circular_user->email : '',
					'acceptor_name'=> $circular_user ? $circular_user->name : '',
					'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS,
					'create_at' => Carbon::now(),
				]);
                }

                //insert table mail_text
                DB::table('mail_text')
                    ->insert([
                        'text'=> $input['text'] ? $input['text'] : '',
                        'circular_user_id' => $author->first()->id,
                        'create_at' => Carbon::now()
                    ]);
                //update circular_status
                $author->update([
                    'circular_status' => CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                    'sent_date' => Carbon::now(),
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                ]);

                foreach($circular_users_first as $key => $circular_user) {
                DB::table('circular_user')
                    ->where('id', $circular_user->id)
                    ->update([
                        'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                        'received_date' => Carbon::now(),
                        'update_at'=> Carbon::now(),
                        'update_user'=> $login_user->email,
                    ]);
                }

                // check to copy document
                foreach($circular_users_first as $key => $circular_user) {
                    if ($circular_user->parent_send_order != $login_user->parent_send_order && (!isset($input['specialFile']) || !$input['specialFile'])){
                        // PAC_5-2354  ゲストユーザーから引戻しを行いその文書を申請するとタブが増えている
                        // Delete the circular that should have been deleted during withdrawal to prevent duplicate copying of the circular
                        if($objCurrentCircular->circular_status == CircularUtils::RETRACTION_STATUS ){
                            DB::beginTransaction();
                            try{
                                $objCurrentCircularOtherDocument = DB::table("circular_document")
                                    ->where("circular_id",$circular_id)
                                    ->where("parent_send_order",$circular_user->parent_send_order)
                                    ->where("origin_document_id",'>',0)->first();

                                if(!empty($objCurrentCircularOtherDocument)){
                                    DB::table('document_data')
                                        ->where('circular_document_id', $objCurrentCircularOtherDocument->id)->limit(1)
                                        ->delete();
                                    DB::table('circular_document')->where('id', $objCurrentCircularOtherDocument->id)->limit(1)->delete();
                                }
                                DB::commit();
                            }catch (\Exception $ex) {
                                DB::rollBack();
                                Log::warning($ex->getMessage().$ex->getTraceAsString());
                                return $this->sendError($ex->getMessage(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        }
                        $this->sendDocument2OtherCompany($circular, 0, 0, $circular_user);
                    }

                }

                DB::commit();

                CircularUserUtils::summaryInProgressCircular($circular_id);
                // 回覧文書データ連携
                $original_circular_id = 0;
                if(isset($input['specialFile']) && $input['specialFile']){
                    $this->sendDocumentToLGWAN($circular, 0, $lgwan_parent_order, $lgwan_env_flg, $lgwan_server_flg);
                    $client = SpecialApiUtils::getAuthorizeClient();
                    if (!$client) {
                        Log::error(__('message.false.auth_client'));
                        return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    //操作履歴
                    $circular_operation_history = DB::table('circular_operation_history')
                        ->where('circular_id', $circular_id)
                        ->select(['operation_email', 'operation_name', 'acceptor_email', 'acceptor_name', 'circular_status', 'create_at'])
                        ->orderBy('id')
                        ->get()
                        ->toArray();
                    //ゲストユーザー
                    $guest_users = DB::table('guest_user')
                        ->where('circular_id', $circular_id)
                        ->select(['create_user_id', 'create_user_id', 'email', 'create_company_id', 'name', 'create_at', 'create_user', 'update_at'])
                        ->orderBy('id')
                        ->get()
                        ->toArray();
                    $response = $client->post("/sp/api/start-circular", [
                        RequestOptions::JSON => [
                            'company_id' => $mst_company_id,
                            'env_flg' => config('app.server_env'),
                            'edition_flg' => config('app.edition_flg'),
                            'server_flg' => config('app.server_flg'),
                            'entered_circular_token' => $templateInfo->circular_token,
                            'template_info' => [
                                'template_file_id' => $templateInfo->special_template_id,
                                'company_id' => $templateInfo->receive_mst_company_id,
                                'env_flg' => $templateInfo->receive_env_flg,
                                'edition_flg' => $templateInfo->receive_edition_flg,
                                'server_flg' => $templateInfo->receive_server_flg,
                                'circular_id' => $circular_id,
                            ],
                            'circular_operation_history' => $circular_operation_history,
                            'circular_text' => $input['text'],
                            'guest_user' => $guest_users,
                        ]
                    ]);
                    $response_dencode = json_decode($response->getBody(),true);  //配列へ
                    if ($response->getStatusCode() == 200) {
                        $response_body = json_decode($response->getBody(), true);  //配列へ
                        $original_circular_id = $response_body['result']['circular_id'];
                        $original_circular_users = $response_body['result']['circular_users'];
                        DB::table('circular')->where('id', $circular_id)->update([
                            'edition_flg' => config('app.edition_flg'),
                            'env_flg' => $lgwan_env_flg,
                            'server_flg' => $lgwan_server_flg,
                            'origin_circular_id' => $original_circular_id,
                        ]);
                        foreach ($original_circular_users as $original_circular_user){
                            DB::table('circular_user')
                                ->where('circular_id', $circular_id)
                                ->where('parent_send_order', $original_circular_user['parent_send_order'])
                                ->where('child_send_order', $original_circular_user['child_send_order'])
                                ->update([
                                'origin_circular_url' => CircularUtils::generateApprovalUrl($original_circular_user['email'], $original_circular_user['edition_flg'], $original_circular_user['env_flg'],
                                        $original_circular_user['server_flg'], $original_circular_id, true) . CircularUtils::encryptOutsideAccessCode($original_circular_user['id']),
                            ]);
                        }
                        DB::table('document_data')->where('circular_document_id', $input['circular_document_id'])->delete();
                    } else {
                        Log::error('Api storeBoard companyId:' . $mst_company_id);
                        Log::error($response_dencode);
                        return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }

                CircularUserUtils::summaryInProgressCircular($circular_id);
                try {
                    foreach($circular_users_first as $key => $circular_user) {
                    if(CircularUserUtils::checkAllowReceivedEmail($circular_user->email, 'approval',$circular_user->mst_company_id,$circular_user->env_flg,$circular_user->edition_flg,$circular_user->server_flg)) {
                        $data = [];
                        $allDocument = DB::table('circular_document')
                            ->where('circular_id', $circular_id)
                            ->orderBy('id')->get();
                        $hasConfidenceFiles = false;
                        $ConfidenceFilesInfo = array();
                        foreach($allDocument as $document){
                            if($document->confidential_flg){
                                $hasConfidenceFiles = true;
                                $ConfidenceFileInfo = [
                                    'origin_edition_flg' => $document->origin_edition_flg,
                                    'origin_env_flg'     => $document->origin_env_flg,
                                    'origin_server_flg'  => $document->origin_server_flg,
                                    'create_company_id'  => $document->create_company_id,
                                ];
                                $ConfidenceFilesInfo[] = $ConfidenceFileInfo;
                            }
                        }
                        // hide_thumbnail_flg 0:表示 1:非表示
                        if (!$circular->hide_thumbnail_flg) {
                            // thumbnail表示
                            $canSeePreview = false;
                            $firstDocument = $allDocument[0];
                            if ($firstDocument && $firstDocument->confidential_flg
                                && $firstDocument->origin_edition_flg == $circular_user->edition_flg
                                && $firstDocument->origin_env_flg == $circular_user->env_flg
                                && $firstDocument->origin_server_flg == $circular_user->server_flg
                                && $firstDocument->create_company_id == $circular_user->mst_company_id){
                                // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                $canSeePreview = true;
                            }else if ($firstDocument && !$firstDocument->confidential_flg){
                                // 一ページ目が社外秘ではない
                                $canSeePreview = true;
                            }

                            if($canSeePreview && $circular->first_page_data){
                                // 一ページ目表示
                                $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else{
                                // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                $data['image_path'] = public_path()."/images/no-preview.png";
                            }
                        }else{
                            // thumbnail非表示
                            $data['image_path'] = '';
                        }
                        $filenames = DB::table('circular_document')
                            ->where('circular_id', $circular_id)
                            ->where(function($query) use ($circular_user,$circular){

                                $query->where(function ($query0) use ($circular_user,$circular){
                                    // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                    $query0->where('confidential_flg', 0);
                                    $query0->where(function ($query01) use ($circular_user,$circular){
                                        // 回覧終了時：origin_document_id＝0のレコード
                                        $query01->where('origin_document_id', 0);
                                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                        $query01->orWhere('parent_send_order', $circular_user->parent_send_order);
                                        if ($circular->special_site_flg){
                                            $query01->orWhere('parent_send_order', 0);
                                        }
                                    });
                                });
                                $query->orWhere(function($query1) use ($circular_user){
                                    // 社外秘：origin_document_idが-1固定
                                    // 同社メンバー参照可
                                    $query1->where('confidential_flg', 1);
                                    $query1->where('origin_edition_flg', $circular_user->edition_flg);
                                    $query1->where('origin_env_flg', $circular_user->env_flg);
                                    $query1->where('origin_server_flg', $circular_user->server_flg);
                                    $query1->where('create_company_id', $circular_user->mst_company_id);
                                });
                            })
                            ->pluck('file_name');

                        $title = $input['title'];
                        if (!trim($title)) {
                            $title = $filenames->toArray()[0];
                        }

                        $data['creator_name'] = $login_user->getFullName();
                        $data['receiver_name'] = $circular_user->name;
                        $data['mail_name'] = $title;
                        $data['circular_id'] = $circular_id;
                        $data['filenames'] = $filenames;
                        if(count($filenames)){
                            $data['filenamestext'] = '';
                            foreach($filenames as $filename){
                                if ($data['filenamestext'] == '') {
                                    $data['filenamestext'] = $filename;
                                    continue;
                                }
                                $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                            }
                        }else{
                            $data['filenamestext'] = '';
                        }
                        $data['text'] = $input['text'];
                        $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                        //特設サイトの場合
                        if ((isset($input['specialFile']) && $input['specialFile'])) {

                        }
                        $is_move_to_lgwan = (isset($input['specialFile']) && $input['specialFile']) ? true : false;
                        $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg,
                            $is_move_to_lgwan ? $original_circular_id : $circular_id, $is_move_to_lgwan);
                        // check to use SAMl Login URL or not
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($circular_user);
                        // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                        // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                        $data['hide_circular_approval_url'] = false;
                        if($hasConfidenceFiles){
                            // 社外秘文書ある、
                            foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                                if($ConfidenceFileInfo['origin_edition_flg'] == $circular_user->edition_flg
                                && $ConfidenceFileInfo['origin_env_flg'] == $circular_user->env_flg
                                && $ConfidenceFileInfo['origin_server_flg'] == $circular_user->server_flg
                                && $ConfidenceFileInfo['create_company_id'] == $circular_user->mst_company_id){
                                    $data['hide_circular_approval_url'] = true;
                                }
                            }
                        }

                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                        }else{
                            $data['circular_approval_url_text'] = '';
                        }

                        //利用者:回覧文書が届いています
                        MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                            $circular_user->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'],
                            // パラメータ
                            json_encode($data,JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $login_user->getFullName()]),
                            // メールボディ
                            trans('mail.circular_user_template.body', $data)
                        );

                        // PAC_5-445 アクセスコードが設定されている場合、アクセスコード通知メール（MAPP0012）を次の宛先に送信する。
                        // 次の宛先が社内の場合
						$author_user = DB::table('circular_user')
							->where('circular_id',$circular_id)
							->where('parent_send_order',0)
							->where('child_send_order',0)->first();

                        //data push notify
                        if (config('app.enable_push_notification') && $circular_user->edition_flg == $system_edition_flg){
                            $badgenumber = $this->getNotifyBadgeNumber($circular_user->email, $circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg);
                            $dataNotify = new PushNotify("Shachihata Cloud", "回覧文書が届いています", $circular_user->email, $circular_user->env_flg, $circular_user->server_flg, 1, $badgenumber);
                            Log::debug('sendNotifyFirst');
                            $this->dispatch(new SendNotification($dataNotify));
                        }

                        // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            if ($circular->access_code_flg == CircularUtils::ACCESS_CODE_VALID
                                    && $author_user->mst_company_id == $circular_user->mst_company_id
                                    && $author_user->edition_flg == $circular_user->edition_flg
                                    && $author_user->env_flg == $circular_user->env_flg
                                    && $author_user->server_flg == $circular_user->server_flg) {
                                $access_data['title'] = $title;
                                $access_data['access_code'] = $circular->access_code;

                                MailUtils::InsertMailSendResume(
                                    // 送信先メールアドレス
                                    $circular_user->email,
                                    // メールテンプレート
                                    MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                    // パラメータ
                                    json_encode($access_data,JSON_UNESCAPED_UNICODE),
                                    // タイプ
                                    AppUtils::MAIL_TYPE_USER,
                                    // 件名
                                    trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                    // メールボディ
                                    trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                );
                            }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                && ($author_user->mst_company_id != $circular_user->mst_company_id
                                    || $author_user->edition_flg != $circular_user->edition_flg
                                    || $author_user->env_flg != $circular_user->env_flg
                                    || $author_user->server_flg != $circular_user->server_flg)) {
                                // 次の宛先が社外の場合
                                $access_data['title'] = $title;
                                $access_data['access_code'] = $circular->outside_access_code;
                                MailUtils::InsertMailSendResume(
                                    // 送信先メールアドレス
                                    $circular_user->email,
                                    // メールテンプレート
                                    MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                    // パラメータ
                                    json_encode($access_data,JSON_UNESCAPED_UNICODE),
                                    // タイプ
                                    AppUtils::MAIL_TYPE_USER,
                                    // 件名
                                    trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                    // メールボディ
                                    trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                );
                            }
                        }
                    }
                    }
                }catch (\Exception $e) {
                    Log::error($e->getMessage().$e->getTraceAsString());
                    return $this->sendError($e->getMessage(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }else{
                DB::commit();
            }

            return $this->sendSuccess('通知メールを送信しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function sendDocument2OtherCompany($circular, $currentParentSendOrder, $currentChildSendOrder, $nextCircularUser, $senderUser = null, $isSendbackCircular = false){
        $hasUpdateTransferredDocumentStatus = false; // 新エディションの場合、他のアプリケーションに文書更新要否フラグ
        $hasUpdateTransferredDocumentEnv = null; // 新エディションの場合、他のアプリケーションに文書更新要、連携用環境フラグ
        $hasUpdateTransferredDocumentServer = null; // 新エディションの場合、他のアプリケーションに文書更新要、連携用サーバーID
        $hasUpdateCurrentAWSDocumentStatus = false; // 現行AWSのAPIアクセス要否フラグ
        $hasUpdateCurrentK5DocumentStatus = false; // 現行K5のAPIアクセス要否フラグ
        $nextEditionFlag = $nextCircularUser->edition_flg; // 次の回覧者のエディションフラグ
        $nextEnvFlag = $nextCircularUser->env_flg; // 次の回覧者の環境フラグ
        $nextServerFlag = $nextCircularUser->server_flg; // 次の回覧者のサーバーID
        $nextParentSendOrder = $nextCircularUser->parent_send_order; // 次の回覧者の親回覧番号

        Log::debug('Copy document for other company!');
        $this->copyDocument($circular->id, $currentParentSendOrder, $nextParentSendOrder);
        // 次の回覧者エディションと環境判断
        if ($nextEditionFlag == config('app.edition_flg')){
            Log::debug('Next circular user is belong of the same edition!');
            if ($nextEnvFlag == config('app.server_env') && $nextServerFlag == config('app.server_flg')){
                Log::debug('Next circular user is belong of the same application, copy document!');
            }else{
                if ($isSendbackCircular){
                    Log::debug('Next circular user is belong of the original application, update status to original application!');
                    $hasUpdateTransferredDocumentStatus = true;
                    $hasUpdateTransferredDocumentEnv = $nextEnvFlag;
                    $hasUpdateTransferredDocumentServer = $nextServerFlag;
                }else{
                    Log::debug('Next circular user is belong of the other application, send to other application!');
                    $this->sendDocument($circular, $currentParentSendOrder, $nextCircularUser);
                }
            }
        }else{
            if (!$isSendbackCircular){
                Log::debug('Next circular user is belong of the other edition, sending document!');
                $this->sendDocument2OtherEdition($circular, $currentParentSendOrder, $currentChildSendOrder, $nextCircularUser );
            }else{
                // PAC_5-78 差戻し後、現行エディションへの再申請
                Log::debug('次の環境が現行エディション、差戻し後再申請時に、現行エディションの回覧状態変更');
                $this->ReapplyWithOtherEdition($circular, $nextCircularUser);
            }
        }
        // 現在の回覧者エディションと環境判断
        if ($senderUser && $senderUser->edition_flg != config('app.edition_flg')){
            if ($senderUser->env_flg == EnvApiUtils::ENV_FLG_AWS){
                Log::debug('Current circular user is belong of the other edition, update document status to current AWS!');
                $hasUpdateCurrentAWSDocumentStatus = true;
            }else{
                Log::debug('Current circular user is belong of the other edition, update document status to current K5!');
                $hasUpdateCurrentK5DocumentStatus = true;
            }
        }else if ($senderUser){
            if ($senderUser->env_flg == config('app.server_env') && $senderUser->server_flg == config('app.server_flg')){
                Log::debug('Sender circular user is belong of the same application, do nothing!');
            }else{
                Log::debug('Next circular user is belong of the original application, update status to original application!');
                $hasUpdateTransferredDocumentStatus = true;
                $hasUpdateTransferredDocumentEnv = $senderUser->env_flg;
                $hasUpdateTransferredDocumentServer = $senderUser->server_flg;
            }
        }
        return ['hasUpdateTransferredDocumentStatus' => $hasUpdateTransferredDocumentStatus,
            'hasUpdateTransferredDocumentEnv' => $hasUpdateTransferredDocumentEnv,
            'hasUpdateTransferredDocumentServer' => $hasUpdateTransferredDocumentServer,
            'hasUpdateCurrentAWSDocumentStatus' => $hasUpdateCurrentAWSDocumentStatus,
            'hasUpdateCurrentK5DocumentStatus' => $hasUpdateCurrentK5DocumentStatus];
    }

    private function sendDocument2OtherEdition($circular, $currentParentSendOrder, $currentChildSendOrder, $nextCircularUser){
        $transferredCircular = ["documentId"=> $circular->id,
            "env_flg" => $circular->env_flg,
            "edition_flg" => $circular->edition_flg,
            "server_flg" => $circular->server_flg];

        $sender_user = DB::table('circular_user')
            ->where('circular_id', $circular->id)
            ->where('parent_send_order', $currentParentSendOrder)
            ->where('child_send_order', $currentChildSendOrder)
            ->first();
        $firstCircularUser = null;
        if ($currentParentSendOrder == 0){
            $firstCircularUser = $sender_user;
        }else{
            $firstCircularUser = DB::table('circular_user')
                ->where('circular_id', $circular->id)
                ->where('parent_send_order', 0)
                ->where('child_send_order', 0)
                ->first();
        }
        $requestsCircularUser = null;
        if ($sender_user){
            $mailText = DB::table('mail_text')
                ->where('circular_user_id',$sender_user->id)
                ->orderBy('id', 'desc')->first();
            $requestsCircularUser = [  'message' => $mailText->text,
                'subject' => $sender_user->title,
                'deadline' => $circular->re_notification_day,
                'options' =>$circular->hide_thumbnail_flg
            ];
        }
        // 社外アクセスコード追加のため、判断処理追加
        $passcode = '';
        if ($nextCircularUser->parent_send_order === 0) {
            $passcode == $circular->access_code;
        } else {
            $passcode == $circular->outside_access_code;
        }

        $transferredCircularUser = [
            "parent_send_order" => $nextCircularUser->parent_send_order,
            "child_send_order" => $nextCircularUser->child_send_order,
            "comment" => '',
            "passcode" => $passcode?$passcode:null,
            "email" => $nextCircularUser->email,
            "name" => $nextCircularUser->name?:$nextCircularUser->email,
            "state" => 0,
            "score" => ($nextCircularUser->child_send_order <= $currentParentSendOrder)?1:0,
            "view_url" => CircularUtils::generateApprovalUrl($nextCircularUser->email, $nextCircularUser->edition_flg, $nextCircularUser->env_flg, $nextCircularUser->server_flg, $circular->id) . CircularUtils::encryptOutsideAccessCode($nextCircularUser->id),
        ];

        $transferredCircular['accepters_info'] = $transferredCircularUser;
        $transferredCircular['requests_info'] = $requestsCircularUser;

        //get circular_document
        $cDocument = DB::table('circular_document')->where('circular_id', $circular->id)
            ->where('parent_send_order', $nextCircularUser->parent_send_order)
            ->first();

        $transferredDocument = [
            "owner" => $cDocument->create_user_id,
            "owner_name" => $firstCircularUser->name,
            "owner_email" => $cDocument->create_user,
            "revision" => $nextCircularUser->parent_send_order,
            "name" => $cDocument->file_name
        ];
        $transferredCircular['documents_info'] = $transferredDocument;

        $result = EnvApiUtils::getEditionAuthorizeClient($nextCircularUser->env_flg, $circular, $nextCircularUser);
        if (!$result['status']){
            //TODO message
            throw new \Exception('Cannot connect to Edition Api: '.$result['message']);
        }else{
            $editionClient = $result['client'];
            $transferredCircular['apikey'] = $result['token'];

            $response = $editionClient->post("saveDocument",[
                RequestOptions::JSON => $transferredCircular
            ]);
            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                Log::error('Cannot transfer the circular');
                Log::error($response->getBody());
                throw new \Exception('Cannot transfer the circular');
            }else{
                $result = json_decode((string) $response->getBody());
                if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED){
                    return 1;
                }else{
                    Log::error('Cannot transfer the circular');
                    Log::error($response->getBody());
                    Log::error(json_encode($transferredCircular));
                    throw new \Exception('Cannot transfer the circular');
                }
            }
        }
    }

    private function copyDocument($circularId, $currentParentSendOrder, $nextParentSendOrder){
        //get circular_document
        $cDocuments = DB::table('circular_document')->where('circular_id', $circularId)
            ->where('parent_send_order', $currentParentSendOrder)
            ->where('confidential_flg', 0)
            ->get();
        // copy document
        $copiedCDocuments = [];
        $publicDocumentIds = [];
        foreach ($cDocuments as $cDocument){
            $copiedCDocuments[] = ['circular_id' => $circularId,
                'origin_env_flg' => $cDocument->origin_env_flg,
                'origin_edition_flg' => $cDocument->origin_edition_flg,
                'origin_server_flg' => $cDocument->origin_server_flg,
                'origin_document_id' => $cDocument->id,
                'parent_send_order' => $nextParentSendOrder,
                'create_company_id' => $cDocument->create_company_id,
                'create_user_id' => $cDocument->create_user_id,
                'confidential_flg' => $cDocument->confidential_flg,
                'file_name' => $cDocument->file_name,
                'create_user' => $cDocument->create_user,
                'create_at' => $cDocument->create_at,
                'update_at' => $cDocument->update_at,
                'update_user' => $cDocument->update_user,
                'document_no' => $cDocument->document_no,
                'file_size' => $cDocument->file_size,
            ];
            if (!$cDocument->confidential_flg){
                $publicDocumentIds[] = $cDocument->id;
            }
        }
        if (count($copiedCDocuments)){
            DB::table('circular_document')->insert($copiedCDocuments);
        }

        // get new document
        $cDocuments = DB::table('circular_document')->where('circular_id', $circularId)
            ->where('parent_send_order', $nextParentSendOrder)
            ->select(['id', 'origin_document_id'])
            ->get();
        $mapCDocuments = array();
        foreach ($cDocuments as $cDocument){
            $mapCDocuments[$cDocument->origin_document_id] = $cDocument->id;
        }

        // copy document data
        if (count($publicDocumentIds)){
            $cDocumentDatas = DB::table('document_data')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $copiedDocumentDatas = [];
            foreach ($cDocumentDatas as $cDocumentData){
                $copiedDocumentDatas[] = [
                    'circular_document_id' => $mapCDocuments[$cDocumentData->circular_document_id],
                    'create_user' => $cDocumentData->create_user,
                    'create_at' => $cDocumentData->create_at,
                    'update_at' => $cDocumentData->update_at,
                    'update_user' => $cDocumentData->update_user,
                    'file_data' => $cDocumentData->file_data,
                ];
            }
            if (count($copiedDocumentDatas)){
                DB::table('document_data')->insert($copiedDocumentDatas);
            }
            // コピーcircular_operation_history
			$operationHistories = array();
			$circularOperationHistories = DB::table('circular_operation_history')->whereIn('circular_document_id', $publicDocumentIds)->get();
            foreach ($circularOperationHistories as $circularOperationHistory){
				$circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
					'circular_id'=> $circularOperationHistory->circular_id,
					'circular_document_id' => $mapCDocuments[$circularOperationHistory->circular_document_id],
					'operation_email' => $circularOperationHistory->operation_email,
					'operation_name' => $circularOperationHistory->operation_name,
					'acceptor_email'=> $circularOperationHistory->acceptor_email,
					'acceptor_name'=> $circularOperationHistory->acceptor_name,
					'circular_status'=> $circularOperationHistory->circular_status,
					'create_at' => $circularOperationHistory->create_at,
				]);
				$operationHistories[$circularOperationHistory->id] = $circular_operation_history_id;
			}

            // copy text info
            $cDocumentTexts = DB::table('text_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $copiedDocumentTexts = [];
            foreach ($cDocumentTexts as $cDocumentText){
                $copiedDocumentTexts[] = [
                    'circular_document_id' => $mapCDocuments[$cDocumentText->circular_document_id],
                    'circular_operation_id' => $operationHistories[$cDocumentText->circular_operation_id],
                    'name' => $cDocumentText->name,
                    'create_at' => $cDocumentText->create_at,
                    'email' => $cDocumentText->email,
                    'text' => $cDocumentText->text,
                ];
            }
            if (count($copiedDocumentTexts)){
                DB::table('text_info')->insert($copiedDocumentTexts);
            }

            // copy stamp info
            $cDocumentStamps = DB::table('stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $copiedDocumentStamps = [];
            foreach ($cDocumentStamps as $cDocumentStamp){
                $copiedDocumentStamps[] = [
                    'circular_document_id' => $mapCDocuments[$cDocumentStamp->circular_document_id],
					'circular_operation_id' => $operationHistories[$cDocumentStamp->circular_operation_id],
                    'parent_send_order' => $cDocumentStamp->parent_send_order,
                    'name' => $cDocumentStamp->name,
                    'create_at' => $cDocumentStamp->create_at,
                    'email' => $cDocumentStamp->email,
                    'stamp_image' => $cDocumentStamp->stamp_image,
                    'info_id' => $cDocumentStamp->info_id,
                    'serial' => $cDocumentStamp->serial,
                    'file_name' => $cDocumentStamp->file_name,
                    'time_stamp_permission' => $cDocumentStamp->time_stamp_permission,
                    'mst_assign_stamp_id' => $cDocumentStamp->mst_assign_stamp_id,
                    'bizcard_id' => $cDocumentStamp->bizcard_id,
                    'env_flg' => $cDocumentStamp->env_flg,
                    'server_flg' => $cDocumentStamp->server_flg,
                    'edition_flg' => $cDocumentStamp->edition_flg,
                ];
            }
            if (count($copiedDocumentStamps)){
                DB::table('stamp_info')->insert($copiedDocumentStamps);
            }

			// PAC_5-368 document_comment_info copy
			$cDocumentComments = DB::table('document_comment_info')->whereIn('circular_document_id', $publicDocumentIds)->get();
			$copiedDocumentComments = [];
			foreach ($cDocumentComments as $cDocumentComment){
				$copiedDocumentComments[] = [
					'circular_document_id' => $mapCDocuments[$cDocumentComment->circular_document_id],
					'circular_operation_id' => $operationHistories[$cDocumentComment->circular_operation_id],
					'parent_send_order' => $cDocumentComment->parent_send_order,
					'name' => $cDocumentComment->name,
					'email' => $cDocumentComment->email,
					'text' => $cDocumentComment->text,
					'private_flg' => $cDocumentComment->private_flg,
					'create_at' => $cDocumentComment->create_at,
				];
			}
			if (count($copiedDocumentComments)){
				DB::table('document_comment_info')->insert($copiedDocumentComments);
			}

            // sticky_notes copy
            $csticky_notes = DB::table('sticky_notes')->whereIn('document_id', $publicDocumentIds)->get();
            $copiedStickyNotes = [];
            foreach ($csticky_notes as $csticky_note){
                $copiedStickyNotes[] = [
                    'circular_id' => $csticky_note->circular_id,
                    'document_id' => $mapCDocuments[$csticky_note->document_id],
                    'note_format' => $csticky_note->note_format,
                    'note_text' => $csticky_note->note_text,
                    'page_num' => $csticky_note->page_num,
                    'top' => $csticky_note->top,
                    'left' => $csticky_note->left,
                    'edition_flg' => $csticky_note->edition_flg,
                    'env_flg' => $csticky_note->env_flg,
                    'server_flg' => $csticky_note->server_flg,
                    'operator_email' => $csticky_note->operator_email,
                    'operator_name' => $csticky_note->operator_name,
                    'create_at' => $csticky_note->create_at,
                    'update_at' => $csticky_note->update_at,
                ];
            }
            if (count($copiedStickyNotes)){
                DB::table('sticky_notes')->insert($copiedStickyNotes);
            }

            // copy timestamp info
            $cDocumentTimestamps = DB::table('time_stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $copiedDocumentTimestamps = [];
            foreach ($cDocumentTimestamps as $cDocumentTimestamp){
                $copiedDocumentTimestamps[] = [
                    'circular_document_id' => $mapCDocuments[$cDocumentTimestamp->circular_document_id],
                    'app_env' => $cDocumentTimestamp->app_env,
                    'contract_server' => $cDocumentTimestamp->contract_server,
                    'mst_user_id' => $cDocumentTimestamp->mst_user_id,
                    'create_at' => $cDocumentTimestamp->create_at,
                    'mst_company_id' => $cDocumentTimestamp->mst_company_id,
                ];
            }
            if (count($copiedDocumentTimestamps)){
                DB::table('time_stamp_info')->insert($copiedDocumentTimestamps);
            }
        }
    }

    private function sendDocument($circular, $currentParentSendOrder, $nextCircularUser){
        $nextParentSendOrder = $nextCircularUser->parent_send_order;
        $transferredCircular = ["circular_id"=> $circular->id,
            "mst_user_id"=> $circular->mst_user_id,
            "env_flg" => $circular->env_flg,
            "edition_flg" => $circular->edition_flg,
            "server_flg" => $circular->server_flg,
            "current_aws_circular_id" => $circular->current_aws_circular_id,
            "current_k5_circular_id" => $circular->current_k5_circular_id,
            "address_change_flg" => $circular->address_change_flg,
            "access_code_flg" => $circular->access_code_flg,
            "access_code" => $circular->access_code ?: '',
            "outside_access_code_flg" => $circular->outside_access_code_flg,
            "outside_access_code" => $circular->outside_access_code ?: '',
            "hide_thumbnail_flg" => $circular->hide_thumbnail_flg,
            "re_notification_day" => $circular->re_notification_day,
            "first_page_data" => $circular->first_page_data,
            "circular_status" => $circular->circular_status,
            "applied_date" => $circular->applied_date,
            'create_user' => $circular->create_user,
            'special_site_flg' => $circular->special_site_flg,
        ];

        $circular_users = DB::table('circular_user')
            ->leftJoin('mail_text', function($join){
                $join->on('circular_user.id', '=', 'mail_text.circular_user_id');
                $join->on('mail_text.id', '=', DB::raw("(select max(id) from mail_text WHERE mail_text.circular_user_id = circular_user.id)"));
            })
            ->select('circular_user.*', 'mail_text.text as text')
            ->where('circular_id', $circular->id)
            ->orderBy('parent_send_order', 'asc')
            ->orderBy('child_send_order', 'asc')->get();
        $transferredCircularUsers = [];
        foreach ($circular_users as $circular_user){
            if (($circular_user->parent_send_order == 0 && $circular_user->child_send_order == 0)
                || ($circular_user->parent_send_order != 0 && $circular_user->child_send_order == 1)
                || ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->env_flg == $nextCircularUser->env_flg && $circular_user->server_flg == $nextCircularUser->server_flg)){
                $transferredCircularUser = [
                    "parent_send_order" => $circular_user->parent_send_order,
                    "child_send_order" => $circular_user->child_send_order,
                    "title" => $circular_user->title,
                    "text" => $circular_user->text,
                    "env_flg" => $circular_user->env_flg,
                    "edition_flg" => $circular_user->edition_flg,
                    "server_flg" => $circular_user->server_flg,
                    "mst_company_id" => $circular_user->mst_company_id,
                    "mst_user_id" => $circular_user->mst_user_id,
                    "email" => $circular_user->email,
                    "name" => $circular_user->name,
                    "circular_status" => $circular_user->circular_status,
                    "return_flg" => $circular_user->return_flg,
                    "received_date" => $circular_user->received_date,
                    "sent_date" => $circular_user->sent_date,
                    "view_url" => CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular->id) . CircularUtils::encryptOutsideAccessCode($circular_user->id),
                    "plan_id"=>$circular_user->plan_id,
                ];
                $transferredCircularUsers[] = $transferredCircularUser;
            }
        }
        $transferredCircular['circular_users'] = $transferredCircularUsers;

        //get circular_document
        $cDocuments = DB::table('circular_document')->where('circular_id', $circular->id)
            ->where('parent_send_order', $currentParentSendOrder)
            ->get();

        $transferredDocuments = [];
        $publicDocumentIds = [];
        foreach ($cDocuments as $cDocument){
            $transferredDocuments[] = [
                "origin_document_id" => $cDocument->id,
                "env_flg" => $cDocument->origin_env_flg,
                "edition_flg" => $cDocument->origin_edition_flg,
                "server_flg" => $cDocument->origin_server_flg,
                "parent_send_order" => $nextParentSendOrder,
                "create_company_id" => $cDocument->create_company_id,
                "create_user_id" => $cDocument->create_user_id,
                "document_no" => $cDocument->document_no,
                "confidential_flg" => $cDocument->confidential_flg,
                "file_name" => $cDocument->file_name,
                "file_size" => $cDocument->file_size,
            ];
            if (!$cDocument->confidential_flg){
                $publicDocumentIds[] = $cDocument->id;
            }
        }
        $transferredCircular['circular_documents'] = $transferredDocuments;

        if (false && count($publicDocumentIds)){
            // transfer document data
            $cDocumentDatas = DB::table('document_data')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentDatas = [];
            foreach ($cDocumentDatas as $cDocumentData){
                $transferredDocumentDatas[] = [
                    'circular_document_id' => $cDocumentData->circular_document_id,
                    'file_data' => $cDocumentData->file_data,
                ];
            }
            if (count($transferredDocumentDatas)){
                $transferredCircular['document_datas'] = $transferredDocumentDatas;
            }

            // transfer text info
            $cDocumentTexts = DB::table('text_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentTexts = [];
            foreach ($cDocumentTexts as $cDocumentText){
                $transferredDocumentTexts[] = [
                    "circular_document_id" => $cDocumentText->circular_document_id,
                    "text" => $cDocumentText->text,
                    "name" => $cDocumentText->name,
                    "email" => $cDocumentText->email,
                    "create_at" => $cDocumentText->create_at
                ];
            }
            if (count($transferredDocumentTexts)){
                $transferredCircular['text_infos'] = $transferredDocumentTexts;
            }

            // transfer stamp info
            $cDocumentStamps = DB::table('stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentStamps = [];
            foreach ($cDocumentStamps as $cDocumentStamp){
                $transferredDocumentStamps[] = [
                    "circular_document_id" => $cDocumentStamp->circular_document_id,
                    "parent_send_order" => $cDocumentStamp->parent_send_order,
                    "stamp_image" => $cDocumentStamp->stamp_image,
                    "name" => $cDocumentStamp->name,
                    "email" => $cDocumentStamp->email,
                    "info_id" => $cDocumentStamp->info_id,
                    'serial' => $cDocumentStamp->serial,
                    "file_name" => $cDocumentStamp->file_name,
                    "create_at" => $cDocumentStamp->create_at,
                    'time_stamp_permission' => $cDocumentStamp->time_stamp_permission,
                    'mst_assign_stamp_id' => $cDocumentStamp->mst_assign_stamp_id,
                ];
            }
            if (count($transferredDocumentStamps)){
                $transferredCircular['stamp_infos'] = $transferredDocumentStamps;
            }

			// PAC_5-368 transfer document_comment_info
			$cDocumentComments = DB::table('document_comment_info')->whereIn('circular_document_id', $publicDocumentIds)
				->get();
			$transferredDocumentComments = [];
			foreach ($cDocumentComments as $cDocumentComment){
				$transferredDocumentComments[] = [
					"circular_document_id" => $cDocumentComment->circular_document_id,
					'parent_send_order' => $cDocumentComment->parent_send_order,
					"name" => $cDocumentComment->name,
					"email" => $cDocumentComment->email,
					"text" => $cDocumentComment->text,
					"private_flg" => $cDocumentComment->private_flg,
					"create_at" => $cDocumentComment->create_at,
				];
			}
			if (count($transferredDocumentComments)){
				$transferredCircular['comment_infos'] = $transferredDocumentComments;
			}

            // transfer time stamp info
            $cDocumentTimestamps = DB::table('time_stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentTimestamps = [];
            foreach ($cDocumentTimestamps as $cDocumentTimestamp){
                $transferredDocumentTimestamps[] = [
                    "circular_document_id" => $cDocumentTimestamp->circular_document_id,
                    "mst_user_id" => $cDocumentTimestamp->mst_user_id,
                    "mst_company_id" => $cDocumentTimestamp->mst_company_id,
                    'app_env' => $cDocumentTimestamp->app_env,
                    'contract_server' => $cDocumentTimestamp->contract_server,
                    "create_at" => $cDocumentTimestamp->create_at,
                ];
            }
            if (count($transferredDocumentTimestamps)){
                $transferredCircular['stamp_time_infos'] = $transferredDocumentTimestamps;
            }
        }
        $envClient = EnvApiUtils::getAuthorizeClient($nextCircularUser->env_flg, $nextCircularUser->server_flg);
        if (!$envClient){
            //TODO message
            throw new \Exception('Cannot connect to Env Api');
        }

        $response = $envClient->post("storeCircular",[
            RequestOptions::JSON => $transferredCircular
        ]);
        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
            Log::error('Cannot transfer the circular');
            Log::error($response->getBody());
            throw new \Exception('Cannot transfer the circular');
        }
    }

    private function sendDocumentToLGWAN($circular, $currentParentSendOrder, $nextParentSendOrder,$env_flg,$server_flg){
        $transferredCircular = ["circular_id"=> $circular->id,
            "mst_user_id"=> $circular->mst_user_id,
            "env_flg" => $circular->env_flg,
            "edition_flg" => $circular->edition_flg,
            "server_flg" => $circular->server_flg,
            "current_aws_circular_id" => $circular->current_aws_circular_id,
            "current_k5_circular_id" => $circular->current_k5_circular_id,
            "address_change_flg" => $circular->address_change_flg,
            "access_code_flg" => $circular->access_code_flg,
            "access_code" => $circular->access_code ?: '',
            "outside_access_code_flg" => $circular->outside_access_code_flg,
            "outside_access_code" => $circular->outside_access_code ?: '',
            "hide_thumbnail_flg" => $circular->hide_thumbnail_flg,
            "re_notification_day" => $circular->re_notification_day,
            "first_page_data" => $circular->first_page_data,
            "circular_status" => $circular->circular_status,
            "applied_date" => $circular->applied_date,
            'create_user' => $circular->create_user,
            'special_site_flg' => $circular->special_site_flg,
            'require_print' => $circular->require_print,
        ];

        $circular_users = DB::table('circular_user')
            ->leftJoin('mail_text', function($join){
                $join->on('circular_user.id', '=', 'mail_text.circular_user_id');
                $join->on('mail_text.id', '=', DB::raw("(select max(id) from mail_text WHERE mail_text.circular_user_id = circular_user.id)"));
            })
            ->select('circular_user.*', 'mail_text.text as text')
            ->where('circular_id', $circular->id)
            ->orderBy('parent_send_order', 'asc')
            ->orderBy('child_send_order', 'asc')->get();
        $transferredCircularUsers = [];
        foreach ($circular_users as $circular_user){

                $transferredCircularUser = [
                    "parent_send_order" => $circular_user->parent_send_order,
                    "child_send_order" => $circular_user->child_send_order,
                    "title" => $circular_user->title,
                    "text" => $circular_user->text,
                    "env_flg" => $circular_user->env_flg,
                    "edition_flg" => $circular_user->edition_flg,
                    "server_flg" => $circular_user->server_flg,
                    "mst_company_id" => $circular_user->mst_company_id,
                    "mst_user_id" => $circular_user->mst_user_id,
                    "email" => $circular_user->email,
                    "name" => $circular_user->name,
                    "circular_status" => $circular_user->circular_status,
                    "return_flg" => $circular_user->return_flg,
                    "received_date" => $circular_user->received_date,
                    "sent_date" => $circular_user->sent_date,
                    "special_site_receive_flg" => $circular_user->special_site_receive_flg,
                    "mst_company_name" => $circular_user->mst_company_name,
                    "view_url" => CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular->id) . CircularUtils::encryptOutsideAccessCode($circular_user->id),
                ];
                $transferredCircularUsers[] = $transferredCircularUser;
        }
        $transferredCircular['circular_users'] = $transferredCircularUsers;

        //get circular_document
        $cDocuments = DB::table('circular_document')->where('circular_id', $circular->id)
            ->where('parent_send_order', $currentParentSendOrder)
            ->get();

        $transferredDocuments = [];
        $publicDocumentIds = [];
        foreach ($cDocuments as $cDocument){
            $transferredDocuments[] = [
                "origin_document_id" => $cDocument->id,
                "env_flg" => $cDocument->origin_env_flg,
                "edition_flg" => $cDocument->origin_edition_flg,
                "server_flg" => $cDocument->origin_server_flg,
                "parent_send_order" => $nextParentSendOrder,
                "create_company_id" => $cDocument->create_company_id,
                "create_user_id" => $cDocument->create_user_id,
                "document_no" => $cDocument->document_no,
                "confidential_flg" => $cDocument->confidential_flg,
                "file_name" => $cDocument->file_name,
                "file_size" => $cDocument->file_size,
            ];
            if (!$cDocument->confidential_flg){
                $publicDocumentIds[] = $cDocument->id;
            }
        }
        $transferredCircular['circular_documents'] = $transferredDocuments;

        if (false && count($publicDocumentIds)){
            // transfer document data
            $cDocumentDatas = DB::table('document_data')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentDatas = [];
            foreach ($cDocumentDatas as $cDocumentData){
                $transferredDocumentDatas[] = [
                    'circular_document_id' => $cDocumentData->circular_document_id,
                    'file_data' => $cDocumentData->file_data,
                ];
            }
            if (count($transferredDocumentDatas)){
                $transferredCircular['document_datas'] = $transferredDocumentDatas;
            }

            // transfer text info
            $cDocumentTexts = DB::table('text_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentTexts = [];
            foreach ($cDocumentTexts as $cDocumentText){
                $transferredDocumentTexts[] = [
                    "circular_document_id" => $cDocumentText->circular_document_id,
                    "text" => $cDocumentText->text,
                    "name" => $cDocumentText->name,
                    "email" => $cDocumentText->email,
                    "create_at" => $cDocumentText->create_at
                ];
            }
            if (count($transferredDocumentTexts)){
                $transferredCircular['text_infos'] = $transferredDocumentTexts;
            }

            // transfer stamp info
            $cDocumentStamps = DB::table('stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentStamps = [];
            foreach ($cDocumentStamps as $cDocumentStamp){
                $transferredDocumentStamps[] = [
                    "circular_document_id" => $cDocumentStamp->circular_document_id,
                    "parent_send_order" => $cDocumentStamp->parent_send_order,
                    "stamp_image" => $cDocumentStamp->stamp_image,
                    "name" => $cDocumentStamp->name,
                    "email" => $cDocumentStamp->email,
                    "info_id" => $cDocumentStamp->info_id,
                    'serial' => $cDocumentStamp->serial,
                    "file_name" => $cDocumentStamp->file_name,
                    "create_at" => $cDocumentStamp->create_at,
                    'time_stamp_permission' => $cDocumentStamp->time_stamp_permission,
                    'mst_assign_stamp_id' => $cDocumentStamp->mst_assign_stamp_id,
                ];
            }
            if (count($transferredDocumentStamps)){
                $transferredCircular['stamp_infos'] = $transferredDocumentStamps;
            }

            // PAC_5-368 transfer document_comment_info
            $cDocumentComments = DB::table('document_comment_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentComments = [];
            foreach ($cDocumentComments as $cDocumentComment){
                $transferredDocumentComments[] = [
                    "circular_document_id" => $cDocumentComment->circular_document_id,
                    'parent_send_order' => $cDocumentComment->parent_send_order,
                    "name" => $cDocumentComment->name,
                    "email" => $cDocumentComment->email,
                    "text" => $cDocumentComment->text,
                    "private_flg" => $cDocumentComment->private_flg,
                    "create_at" => $cDocumentComment->create_at,
                ];
            }
            if (count($transferredDocumentComments)){
                $transferredCircular['comment_infos'] = $transferredDocumentComments;
            }

            // transfer time stamp info
            $cDocumentTimestamps = DB::table('time_stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentTimestamps = [];
            foreach ($cDocumentTimestamps as $cDocumentTimestamp){
                $transferredDocumentTimestamps[] = [
                    "circular_document_id" => $cDocumentTimestamp->circular_document_id,
                    "mst_user_id" => $cDocumentTimestamp->mst_user_id,
                    "mst_company_id" => $cDocumentTimestamp->mst_company_id,
                    'app_env' => $cDocumentTimestamp->app_env,
                    'contract_server' => $cDocumentTimestamp->contract_server,
                    "create_at" => $cDocumentTimestamp->create_at,
                ];
            }
            if (count($transferredDocumentTimestamps)){
                $transferredCircular['stamp_time_infos'] = $transferredDocumentTimestamps;
            }
        }
        $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
        if (!$envClient){
            //TODO message
            throw new \Exception('Cannot connect to Env Api');
        }

        $response = $envClient->post("storeCircular",[
            RequestOptions::JSON => $transferredCircular
        ]);
        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
            Log::error('Cannot transfer the circular');
            Log::error($response->getBody());
            throw new \Exception('Cannot transfer the circular');
        }
    }

    private function sendUpdateDocumentStatus2OtherEdition($circular, $currentCircularUser, $toEnv, $isCompleted = false, $isSendbackCircular = false, $nextCircularUser = null){
        $requestsTransferredCircular = ["document_id"=> $circular->origin_circular_id?:$circular->id,
            "env_flg" => $circular->env_flg,
            "edition_flg" => $circular->edition_flg,
            "server_flg" => $circular->server_flg,
            "parent_send_order" => $currentCircularUser->parent_send_order,
            "child_send_order" => $currentCircularUser->child_send_order,
            "comment" => '',
            "requests_status" => $isCompleted?1:0,
            "accepters_status" => $currentCircularUser->circular_status==CircularUserUtils::APPROVED_WITH_STAMP_STATUS?2:3];

        $result = EnvApiUtils::getEditionAuthorizeClient($toEnv, $circular, $currentCircularUser);
        if (!$result['status']){
            //TODO message
            throw new \Exception('Cannot connect to Edition Api: '.$result['message']);
        }else{
            $editionClient = $result['client'];
            $token = $result['token'];
            $requestsTransferredCircular['apikey'] = $token;

            $response = $editionClient->post("updateDocument",[
                RequestOptions::JSON => $requestsTransferredCircular
            ]);

            Log::debug('requestsTransferredCircular: '.json_encode($requestsTransferredCircular));
            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                Log::error('Cannot transfer the circular');
                Log::error($response->getBody());
                throw new \Exception('Cannot transfer the circular');
            }else{
                $result = json_decode((string) $response->getBody());
                if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED){

                }else{
                    Log::error('Cannot transfer the circular');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot transfer the circular');
                }
            }
        }
        if ($isSendbackCircular && $nextCircularUser->edition_flg != config('app.edition_flg')){
            $requestsTransferredCircular = ["document_id"=> $circular->origin_circular_id?:$circular->id,
                "env_flg" => $circular->env_flg,
                "edition_flg" => $circular->edition_flg,
                "server_flg" => $circular->server_flg,
                "parent_send_order" => $nextCircularUser->parent_send_order,
                "child_send_order" => $nextCircularUser->child_send_order,
                "comment" => '',
                "requests_status" => 0,
                "accepters_status" => 0];

            $result = EnvApiUtils::getEditionAuthorizeClient($nextCircularUser->env_flg, $circular, $nextCircularUser);
            if (!$result['status']){
                //TODO message
                throw new \Exception('Cannot connect to Edition Api: '.$result['message']);
            }else{
                $editionClient = $result['client'];
                $token = $result['token'];
                $requestsTransferredCircular['apikey'] = $token;

                $response = $editionClient->post("updateDocument",[
                    RequestOptions::JSON => $requestsTransferredCircular
                ]);

                Log::debug('requestsTransferredCircular: '.json_encode($requestsTransferredCircular));
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::error('Cannot transfer the circular');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot transfer the circular');
                }else{
                    $result = json_decode((string) $response->getBody());
                    if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED){
                    }else{
                        Log::error('Cannot transfer the circular');
                        Log::error($response->getBody());
                        throw new \Exception('Cannot transfer the circular');
                    }
                }
            }
        }
    }

    private function sendBackWithOtherEdition($circular, $currentCircularUser, $nextCircularUser, $text){
        if ($currentCircularUser->edition_flg != config('app.edition_flg')){
            $requestsTransferredCircular = ["document_id"=> $circular->origin_circular_id?:$circular->id,
                "env_flg" => $circular->env_flg,
                "edition_flg" => $circular->edition_flg,
                "server_flg" => $circular->server_flg,
                "parent_send_order" => $currentCircularUser->parent_send_order,
                "child_send_order" => $currentCircularUser->child_send_order,
                "comment" => '',
                "requests_status" => 8,
                "accepters_status" => 8];

            $result = EnvApiUtils::getEditionAuthorizeClient($currentCircularUser->env_flg, $circular, $currentCircularUser);
            if (!$result['status']){
                //TODO message
                throw new \Exception('Cannot connect to Edition Api: '.$result['message']);
            }else{
                $editionClient = $result['client'];
                $token = $result['token'];
                $requestsTransferredCircular['apikey'] = $token;

                $response = $editionClient->post("updateDocument",[
                    RequestOptions::JSON => $requestsTransferredCircular
                ]);

                Log::debug('requestsTransferredCircular: '.json_encode($requestsTransferredCircular));
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::error('Cannot transfer the circular');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot transfer the circular');
                }else{
                    $result = json_decode((string) $response->getBody());
                    if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED){


                    }else{
                        Log::error('Cannot transfer the circular');
                        Log::error($response->getBody());
                        throw new \Exception('Cannot transfer the circular');
                    }
                }
            }
        }

        if ($nextCircularUser->edition_flg != config('app.edition_flg')){
            $acceptTransferredCircular = ["document_id"=> $circular->origin_circular_id?:$circular->id,
                "env_flg" => $circular->env_flg,
                "edition_flg" => $circular->edition_flg,
                "server_flg" => $circular->server_flg,
                "parent_send_order" => $nextCircularUser->parent_send_order,
                "child_send_order" => $nextCircularUser->child_send_order,
                "comment" => $text?:"",
                "requests_status" => 8,
                "accepters_status" => 0];
            $result = EnvApiUtils::getEditionAuthorizeClient($nextCircularUser->env_flg, $circular, $nextCircularUser);
            if (!$result['status']){
                //TODO message
                throw new \Exception('Cannot connect to Edition Api: '.$result['message']);
            }else{
                $editionClient = $result['client'];
                $token = $result['token'];
                $acceptTransferredCircular['apikey'] = $token;

                $response = $editionClient->post("updateDocument",[
                    RequestOptions::JSON => $acceptTransferredCircular
                ]);

                Log::debug('acceptTransferredCircular: '.json_encode($acceptTransferredCircular));
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::error('Cannot transfer the circular');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot transfer the circular');
                }else{
                    $result = json_decode((string) $response->getBody());
                    if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED){
                    }else{
                        Log::error('Cannot transfer the circular');
                        Log::error($response->getBody());
                        throw new \Exception('Cannot transfer the circular');
                    }
                }
            }
        }
    }

    private function sendUpdateTransferredDocumentStatusForSendback($circular, $currentParentSendOrder, $currentChildSendOrder, $toParentSendOrder, $toChildSendOrder, $title, $text, $toEnv, $toServer, $sendDocument = true)
	{
		$circular_user = DB::table('circular_user')
			->where('circular_id', $circular->id)
			->where('parent_send_order', $currentParentSendOrder)
			->where('child_send_order', $currentChildSendOrder)->first();

        $sendChildSendOrder = ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->env_flg == $toEnv && $circular_user->server_flg == $toServer)?$currentChildSendOrder:($currentParentSendOrder?1:0);
        $circular_status = $circular_user->return_send_back == 1 && in_array($circular_user->circular_status, [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::NODE_COMPLETED_STATUS]) ? CircularUserUtils::SEND_BACK_STATUS : $circular_user->circular_status;
		$transferredCircular = ["circular_id" => $circular->id,
			"current_k5_circular_id" => $circular->current_k5_circular_id,
			"current_aws_circular_id" => $circular->current_aws_circular_id,
			"parent_send_order" => $currentParentSendOrder,
			"child_send_order" => $sendChildSendOrder,
			'sendback_parent_send_order' => $toParentSendOrder,
			'sendback_child_send_order' => $toChildSendOrder,
			"circular_env_flg" => $circular->env_flg,
			"circular_edition_flg" => $circular->edition_flg,
            "circular_server_flg" => $circular->server_flg,
			"title" => $title,
			"text" => $text,
			"circular_status" => $circular_status];

		if ($sendDocument) {
			//get circular_document
			$cDocuments = DB::table('circular_document')->where('circular_id', $circular->id)
				->where('parent_send_order', $currentParentSendOrder)
				->get();

			$transferredDocuments = [];
			$publicDocumentIds = [];
			foreach ($cDocuments as $cDocument) {
				$transferredDocuments[] = [
					"origin_document_id" => $cDocument->id,
					"env_flg" => $cDocument->origin_env_flg,
					"edition_flg" => $cDocument->origin_edition_flg,
                    "server_flg" => $cDocument->origin_server_flg,
					"parent_send_order" => $currentParentSendOrder,
					"create_company_id" => $cDocument->create_company_id,
					"create_user_id" => $cDocument->create_user_id,
					"document_no" => $cDocument->document_no,
					"confidential_flg" => $cDocument->confidential_flg,
					"file_name" => $cDocument->file_name,
					"file_size" => $cDocument->file_size,
				];
				if (!$cDocument->confidential_flg) {
					$publicDocumentIds[] = $cDocument->id;
				}
			}
			$transferredCircular['circular_documents'] = $transferredDocuments;
			// add if false to no send document data
			if (false && count($publicDocumentIds)) {
				// transfer document data
				$cDocumentDatas = DB::table('document_data')->whereIn('circular_document_id', $publicDocumentIds)
					->get();
				$transferredDocumentDatas = [];
				foreach ($cDocumentDatas as $cDocumentData) {
					$transferredDocumentDatas[] = [
						'circular_document_id' => $cDocumentData->circular_document_id,
						'file_data' => $cDocumentData->file_data,
					];
				}
				if (count($transferredDocumentDatas)) {
					$transferredCircular['document_datas'] = $transferredDocumentDatas;
				}

				// transfer text info
				$cDocumentTexts = DB::table('text_info')->whereIn('circular_document_id', $publicDocumentIds)
					->get();
				$transferredDocumentTexts = [];
				foreach ($cDocumentTexts as $cDocumentText) {
					$transferredDocumentTexts[] = [
						"circular_document_id" => $cDocumentText->circular_document_id,
						"text" => $cDocumentText->text,
						"name" => $cDocumentText->name,
						"email" => $cDocumentText->email,
						"create_at" => $cDocumentText->create_at
					];
				}
				if (count($transferredDocumentTexts)) {
					$transferredCircular['text_infos'] = $transferredDocumentTexts;
				}

				// PAC_5-368 transfer document_comment_info
				$cDocumentComments = DB::table('document_comment_info')->whereIn('circular_document_id', $publicDocumentIds)
					->get();
				$transferredDocumentComments = [];
				foreach ($cDocumentComments as $cDocumentComment) {
					$transferredDocumentComments[] = [
						"circular_document_id" => $cDocumentComment->circular_operation_id,
						"name" => $cDocumentComment->name,
						"email" => $cDocumentComment->email,
						"text" => $cDocumentComment->text,
						'private_flg' => $cDocumentComment->private_flg,
						"create_at" => $cDocumentComment->create_at,
					];
				}
				if (count($transferredDocumentComments)) {
					$transferredCircular['comment_infos'] = $transferredDocumentComments;
				}

				// transfer timestamp info
				$cDocumentTimestamps = DB::table('time_stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
					->get();
				$transferredDocumentTimestamps = [];
				foreach ($cDocumentTimestamps as $cDocumentTimestamp) {
					$transferredDocumentTimestamps[] = [
						"circular_document_id" => $cDocumentTimestamp->circular_document_id,
						"mst_company_id" => $cDocumentTimestamp->mst_company_id,
						"mst_user_id" => $cDocumentTimestamp->mst_user_id,
						'app_env' => $cDocumentTimestamp->app_env,
                        'contract_server' => $cDocumentTimestamp->contract_server,
						"create_at" => $cDocumentTimestamp->create_at,
					];
					// transfer stamp info
					$cDocumentStamps = DB::table('stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
						->get();
					$transferredDocumentStamps = [];
					foreach ($cDocumentStamps as $cDocumentStamp) {
						$transferredDocumentStamps[] = [
							"circular_document_id" => $cDocumentStamp->circular_document_id,
                            "parent_send_order" => $cDocumentStamp->parent_send_order,
							"stamp_image" => $cDocumentStamp->stamp_image,
							"name" => $cDocumentStamp->name,
							"email" => $cDocumentStamp->email,
							"info_id" => $cDocumentStamp->info_id,
							'serial' => $cDocumentStamp->serial,
							"file_name" => $cDocumentStamp->file_name,
							"create_at" => $cDocumentStamp->create_at,
							'time_stamp_permission' => $cDocumentStamp->time_stamp_permission,
                            'mst_assign_stamp_id' => $cDocumentStamp->mst_assign_stamp_id,
						];
					}
					if (count($transferredDocumentStamps)) {
						$transferredCircular['stamp_infos'] = $transferredDocumentStamps;
					}

			// PAC_5-368 transfer document_comment_info
			$cDocumentComments = DB::table('document_comment_info')->whereIn('circular_document_id', $publicDocumentIds)
				->get();
			$transferredDocumentComments = [];
			foreach ($cDocumentComments as $cDocumentComment){
				$transferredDocumentComments[] = [
					"circular_document_id" => $cDocumentComment->circular_operation_id,
					'parent_send_order' => $cDocumentComment->parent_send_order,
					"name" => $cDocumentComment->name,
					"email" => $cDocumentComment->email,
					"text" => $cDocumentComment->text,
					'private_flg' => $cDocumentComment->private_flg,
					"create_at" => $cDocumentComment->create_at,
				];
			}
			if (count($transferredDocumentComments)){
				$transferredCircular['comment_infos'] = $transferredDocumentComments;
			}

            // transfer timestamp info
            $cDocumentTimestamps = DB::table('time_stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                ->get();
            $transferredDocumentTimestamps = [];
            foreach ($cDocumentTimestamps as $cDocumentTimestamp){
                $transferredDocumentTimestamps[] = [
                    "circular_document_id" => $cDocumentTimestamp->circular_document_id,
                    "mst_company_id" => $cDocumentTimestamp->mst_company_id,
                    "mst_user_id" => $cDocumentTimestamp->mst_user_id,
                    'app_env' => $cDocumentTimestamp->app_env,
                    'contract_server' => $cDocumentTimestamp->contract_server,
                    "create_at" => $cDocumentTimestamp->create_at,
                ];
            }
            if (count($transferredDocumentTimestamps)){
                $transferredCircular['time_stamp_infos'] = $transferredDocumentTimestamps;
            }
        }
                $envClient = EnvApiUtils::getAuthorizeClient($toEnv,$toServer);
        if (!$envClient){
            //TODO message
            throw new \Exception('Cannot connect to Env Api');
        }
        $response = $envClient->post("updateStatus",[
            RequestOptions::JSON => $transferredCircular
        ]);
        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
            Log::error('Cannot transfer the circular');
            Log::error($response->getBody());
            throw new \Exception('Cannot transfer the circular');
        }
    }

            $envClient = EnvApiUtils::getAuthorizeClient($toEnv,$toServer);
			if (!$envClient) {
				//TODO message
				throw new \Exception('Cannot connect to Env Api');
			}
			$response = $envClient->post("updateStatus", [
				RequestOptions::JSON => $transferredCircular
			]);
			if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
				Log::error('Cannot transfer the circular');
				Log::error($response->getBody());
				throw new \Exception('Cannot transfer the circular');
			}
		}
	}

    /**
     * 差出環境回覧状態変更の場合、他環境への状態変更（次の回覧者は他会社ユーザーではなく）
     * @param $circular 回覧情報
     * @param $currentParentSendOrder 現在の回覧者の親コード
     * @param $currentChildSendOrder 現在の回覧者の子コード
     * @param $title 件名
     * @param $text コメント
     * @param $toEnv 現在の回覧者のenv
     * @param $toServer 現在の回覧者のserver
     * @param $sendDocument document送信要否
     * @param $justApproveRequestSendback
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendUpdateTransferredDocumentStatus($circular, $currentParentSendOrder, $currentChildSendOrder, $title, $text, $toEnv, $toServer, $sendDocument = true, $justApproveRequestSendback = false){
        $circular_user = DB::table('circular_user')
            ->where('circular_id', $circular->id)
            ->where('parent_send_order', $currentParentSendOrder)
            ->where('child_send_order', $currentChildSendOrder)->first();

        $sendChildSendOrder = ($circular_user->edition_flg == config('app.edition_flg') && (($circular_user->env_flg == $toEnv && $circular_user->server_flg == $toServer) || $circular_user->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS))?$currentChildSendOrder:($currentParentSendOrder?1:0);
        $sendCircularId = ($circular->edition_flg == config('app.edition_flg') && $circular->env_flg == config('app.server_env') && $circular->server_flg == config('app.server_flg'))?$circular->id:$circular->origin_circular_id;

        $transferredCircular = [
            "circular_id"=> $sendCircularId,
            "current_k5_circular_id" => $circular->current_k5_circular_id,
            "current_aws_circular_id" => $circular->current_aws_circular_id,
            "parent_send_order"=> $currentParentSendOrder,
            "child_send_order" => $sendChildSendOrder,
            "circular_env_flg" => $circular->env_flg,
            "circular_edition_flg" => $circular->edition_flg,
            "circular_server_flg" => $circular->server_flg,
            "title" => $title,
            "text" => $text,
            "circular_status" => $justApproveRequestSendback?CircularUserUtils::END_OF_REQUEST_SEND_BACK:$circular_user->circular_status,
            "is_skip" => $circular_user->is_skip,
        ];


        if ($sendDocument){
            //get circular_document
            $cDocuments = DB::table('circular_document')->where('circular_id', $circular->id)
                ->where('parent_send_order', $currentParentSendOrder)
                ->get();

            $transferredDocuments = [];
            $publicDocumentIds = [];
            foreach ($cDocuments as $cDocument){
                $transferredDocuments[] = [
                    "origin_document_id" => $cDocument->id,
                    "env_flg" => $cDocument->origin_env_flg,
                    "edition_flg" => $cDocument->origin_edition_flg,
                    "server_flg" => $cDocument->origin_server_flg,
                    "parent_send_order" => $currentParentSendOrder,
                    "create_company_id" => $cDocument->create_company_id,
                    "create_user_id" => $cDocument->create_user_id,
                    "document_no" => $cDocument->document_no,
                    "confidential_flg" => $cDocument->confidential_flg,
                    "file_name" => $cDocument->file_name,
                    "file_size" => $cDocument->file_size,
                ];
                if (!$cDocument->confidential_flg){
                    $publicDocumentIds[] = $cDocument->id;
                }
            }
            $transferredCircular['circular_documents'] = $transferredDocuments;

            // add if false to no send document data
            if (false && count($publicDocumentIds)){
                // transfer document data
                $cDocumentDatas = DB::table('document_data')->whereIn('circular_document_id', $publicDocumentIds)
                    ->get();
                $transferredDocumentDatas = [];
                foreach ($cDocumentDatas as $cDocumentData){
                    $transferredDocumentDatas[] = [
                        'circular_document_id' => $cDocumentData->circular_document_id,
                        'file_data' => $cDocumentData->file_data,
                    ];
                }
                if (count($transferredDocumentDatas)){
                    $transferredCircular['document_datas'] = $transferredDocumentDatas;
                }

                // transfer text info
                $cDocumentTexts = DB::table('text_info')->whereIn('circular_document_id', $publicDocumentIds)
                    ->get();
                $transferredDocumentTexts = [];
                foreach ($cDocumentTexts as $cDocumentText){
                    $transferredDocumentTexts[] = [
                        "circular_document_id" => $cDocumentText->circular_document_id,
                        "text" => $cDocumentText->text,
                        "name" => $cDocumentText->name,
                        "email" => $cDocumentText->email,
                        "create_at" => $cDocumentText->create_at
                    ];
                }
                if (count($transferredDocumentTexts)){
                    $transferredCircular['text_infos'] = $transferredDocumentTexts;
                }

                // transfer stamp info
                $cDocumentStamps = DB::table('stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                    ->get();
                $transferredDocumentStamps = [];
                foreach ($cDocumentStamps as $cDocumentStamp){
                    $transferredDocumentStamps[] = [
                        "circular_document_id" => $cDocumentStamp->circular_document_id,
                        "parent_send_order" => $cDocumentStamp->parent_send_order,
                        "stamp_image" => $cDocumentStamp->stamp_image,
                        "name" => $cDocumentStamp->name,
                        "email" => $cDocumentStamp->email,
                        "info_id" => $cDocumentStamp->info_id,
                        'serial' => $cDocumentStamp->serial,
                        "file_name" => $cDocumentStamp->file_name,
                        "create_at" => $cDocumentStamp->create_at,
                        'time_stamp_permission' => $cDocumentStamp->time_stamp_permission,
                        'mst_assign_stamp_id' => $cDocumentStamp->mst_assign_stamp_id,
                    ];
                }
                if (count($transferredDocumentStamps)){
                    $transferredCircular['stamp_infos'] = $transferredDocumentStamps;
                }

				// PAC_5-368 transfer comment info
				$cDocumentComments = DB::table('document_comment_info')->whereIn('circular_document_id', $publicDocumentIds)
					->get();
				$transferredDocumentComments = [];
				foreach ($cDocumentComments as $cDocumentComment){
					$transferredDocumentComments[] = [
						"circular_document_id" => $cDocumentComment->circular_document_id,
						"circular_operation_id" => $cDocumentComment->circular_operation_id,
						'parent_send_order' => $cDocumentComment->parent_send_order,
						"name" => $cDocumentComment->name,
						"email" => $cDocumentComment->email,
						"text" => $cDocumentComment->text,
						'private_flg' => $cDocumentComment->private_flg,
						"create_at" => $cDocumentComment->create_at,
					];
				}
				if (count($transferredDocumentComments)){
					$transferredCircular['comment_infos'] = $transferredDocumentComments;
				}

                // transfer timestamp info
                $cDocumentTimestamps = DB::table('time_stamp_info')->whereIn('circular_document_id', $publicDocumentIds)
                    ->get();
                $transferredDocumentTimestamps = [];
                foreach ($cDocumentTimestamps as $cDocumentTimestamp){
                    $transferredDocumentTimestamps[] = [
                        "circular_document_id" => $cDocumentTimestamp->circular_document_id,
                        "mst_company_id" => $cDocumentTimestamp->mst_company_id,
                        "mst_user_id" => $cDocumentTimestamp->mst_user_id,
                        "app_env" => $cDocumentTimestamp->app_env,
                        "contract_server" => $cDocumentTimestamp->contract_server,
                        "create_at" => $cDocumentTimestamp->create_at,
                    ];
                }
                if (count($transferredDocumentTimestamps)){
                    $transferredCircular['time_stamp_infos'] = $transferredDocumentTimestamps;
                }
            }
        }

        $envClient = EnvApiUtils::getAuthorizeClient($toEnv,$toServer);
        if (!$envClient){
            //TODO message
            throw new \Exception('Cannot connect to Env Api');
        }
        $response = $envClient->post("updateStatus",[
            RequestOptions::JSON => $transferredCircular
        ]);
        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
            Log::error('Cannot transfer the circular');
            Log::error($response->getBody());
            throw new \Exception('Cannot transfer the circular');
        }
    }

    /**
     * 差戻し後、現行エディションへの再申請
     * @param $circular
     * @param $nextCircularUser
     * @throws \Exception
     */
    private function ReapplyWithOtherEdition($circular, $nextCircularUser)
    {
        $requestsTransferredCircular = ["document_id" => $circular->origin_circular_id ?: $circular->id,
            "env_flg" => $circular->env_flg,
            "edition_flg" => $circular->edition_flg,
            "server_flg" => $circular->server_flg,
            "parent_send_order" => $nextCircularUser->parent_send_order,
            "child_send_order" => $nextCircularUser->child_send_order,
            "comment" => '',
            "requests_status" => 0,
            "accepters_status" => 0];

        $result = EnvApiUtils::getEditionAuthorizeClient($nextCircularUser->env_flg, $circular, $nextCircularUser);
        if (!$result['status']) {
            //TODO message
            throw new \Exception('Cannot connect to Edition Api: ' . $result['message']);
        } else {
            $editionClient = $result['client'];
            $token = $result['token'];
            $requestsTransferredCircular['apikey'] = $token;

            $response = $editionClient->post("updateDocument", [
                RequestOptions::JSON => $requestsTransferredCircular
            ]);

            Log::debug('requestsTransferredCircular: ' . json_encode($requestsTransferredCircular));
            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::error('Cannot transfer the circular');
                Log::error($response->getBody());
                throw new \Exception('Cannot transfer the circular');
            } else {
                $result = json_decode((string)$response->getBody());
                if ($result->status == StatusCodeUtils::HTTP_OK || $result->status == StatusCodeUtils::HTTP_CREATED) {
                } else {
                    Log::error('Cannot transfer the circular');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot transfer the circular');
                }
            }
        }
    }
    /**
     * @param SendNotifyContinueRequest $request
     * @return mixed
     */
    public function sendNotifyContinue($circular_id, SendNotifyContinueRequest $request)
    {
        $mailDatas = [];
		$noticeMailDatas = [];
        $is_circular_completed = false;// 回覧完成フラグ
		try {
            $login_user = $request->user();
			$edition_flg = config('app.edition_flg');
			$env_flg = config('app.server_env');
            $server_flg = config('app.server_flg');
			if($request['usingHash']) {
				$login_user = $request['user'];
                $user_name = is_null($request['current_name']) ? '' : $request['current_name'];
				$edition_flg = $request['current_circular_user']->edition_flg;
				$env_flg = $request['current_circular_user']->env_flg;
                $server_flg = $request['current_circular_user']->server_flg;
			}else{
				$user_name = $login_user->getFullName();
			}

            $input = $request->all();

            $objSkipUser = null;
            // Is Skip User Or Not
            $boolSikpCurrentHandler = !empty($input['skipCurrentHandler']);
            // wait send skip's mail user
            $arrSendSkipMailUser = [];
            if($boolSikpCurrentHandler){
                $objSkipUser = DB::table("circular_user")->where("id",$input['sender_id'])->first();
                if(empty($objSkipUser) || in_array($objSkipUser->circular_status,[CircularUserUtils::APPROVED_WITH_STAMP_STATUS,CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS])){

                    return $this->sendError(__('message.false.skip_handler_error'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $objFIndCompanyISSkip = DB::table("mst_company")->where("id",$login_user->mst_company_id)->where('skip_flg',1)->first();
                if(empty($objFIndCompanyISSkip)){

                    return $this->sendError(__('message.false.skip_handler_error'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                //  この前、まだ承認操作してない
                $arrBeforeNode = DB::table('circular_user')
                    ->where('circular_id',$circular_id)
                    // 1 2 8 10
                    ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) < "
                        .str_pad($objSkipUser->parent_send_order,3,'0', STR_PAD_LEFT)
                        .str_pad($objSkipUser->child_send_order,3,'0', STR_PAD_LEFT))
                    ->orderBy('parent_send_order', 'asc')
                    ->orderBy('child_send_order', 'asc')
                    ->get()->toArray();

                $arrSendSkipMailUser = DB::table('circular_user')
                    ->where('circular_id',$circular_id)
                    // 1 2 8 10
                    ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->where('parent_send_order',$objSkipUser->parent_send_order)
                    ->where('child_send_order',$objSkipUser->child_send_order)
                    ->get();

                if(count($arrBeforeNode) > 0 ){
                    return $this->sendError(__('message.false.skip_before_node_has_handler'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                // current handler user's env
                $edition_flg = $objSkipUser->edition_flg;
                $env_flg = $objSkipUser->env_flg;
                $server_flg = $objSkipUser->server_flg;
            }

            DB::beginTransaction();
            //get circular
            $circular = DB::table('circular')
                ->where('id', $circular_id)
                ->first();

            //set circular status
            $isSendbackCircular = $circular->circular_status == CircularUtils::SEND_BACK_STATUS;

            $circular->circular_status = CircularUtils::CIRCULATING_STATUS;

            $author_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('parent_send_order', 0)
                ->where('child_send_order', 0)
                ->first();

            // PAC_5-1973 ログインパスワードの変更画面から利用者としてログインできるように修正 Start
            if (isset($input['title']) && is_string($input['title'])) $input['title'] = preg_replace('/[\t]/', '', $input['title']);
            // PAC_5-1973
            // PAC_5-183 差戻された文書を再申請するとき件名を変更したい
			if(isset($input['title']) && !empty($input['title']) && $author_user->title != $input['title']){
				DB::table('circular_user')
					->where('circular_id',$circular_id)
					->update([
						'title' => $input['title'],
						'update_at'=> Carbon::now(),
						'update_user'=> $login_user->email,
					]);

                //PAC_5-1398　添付ファイル機能　件名を更新
                DB::table('circular_attachment')
                    ->where('circular_id',$circular_id)
                    ->update(['title'=> $input['title']]);
			}

            $system_env_flg     = config('app.server_env');
            $system_edition_flg = config('app.edition_flg');
            $system_server_flg = config('app.server_flg');

            if($request['usingHash']) {
                $sender_user = $request['current_circular_user'];
                $request['sender_id'] = $sender_user->id;
            }else if(!empty($objSkipUser)){
                // PAC_5-2352
                $sender_user = $objSkipUser;
                $request['sender_id'] = $sender_user->id;
            }else{
                $sender_user = DB::table('circular_user')
                    ->where('email', $login_user->email)
                    ->where('circular_id', $circular_id)
                    ->where('env_flg', $system_env_flg)
                    ->where('edition_flg', $system_edition_flg)
                    ->where('server_flg', $system_server_flg)
                    ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->first();
                $request['sender_id'] = $sender_user->id;
            }

            // ウィンドウノード
            $windowCircularUser = null;
                if (isset($request['userViews'])){
                    if ($sender_user->edition_flg == $system_edition_flg){
                        if (count($request['userViews']) > 1){
                            return $this->sendError("設定できるユーザーは1名のみです");
                        }
                        $viewingUsers = [];
                        //PAC_5-1455　現段階では承認者の追加人数は1名のみだが、拡張を考えてforeach文で構成する。
                        foreach($request['userViews'] as $user){
							$operation_user = DB::table('circular_user')
								->where('circular_id', $circular_id)
								->where('parent_send_order', $user['parent_send_order'])
								->where('email', $user['create_user'])
								->first();
                            $vUser = [
                                'circular_id' => $user['circular_id'],
                                'parent_send_order' => $user['parent_send_order'],
                                'mst_company_id' => $user['company_id'],
                                'memo' => !CommonUtils::isNullOrEmpty($user['memo']) ? $user['memo'] : '',
                                'del_flg' => $user['del_flg'],
                                'create_user' => $user['create_user'],
                                'update_user' => $user['update_user'],
                                'origin_circular_url' => CircularUtils::generateApprovalUrl($user['email'], $sender_user->edition_flg, $sender_user->env_flg, $sender_user->server_flg, $circular_id) . CircularUtils::encryptOutsideAccessCode($operation_user?$operation_user->id:''),
                                'create_at' => Carbon::now()->toDateTimeString()
                            ];
                            if ($sender_user->env_flg != $system_env_flg || $sender_user->server_flg != $system_server_flg){
                                $vUser['email'] = $user['email'];
                            }else{
                                $dbUser = DB::table('mst_user')->where('email', $user['email'])->where('state_flg', AppUtils::STATE_VALID)->select('email', 'id')->first();
                                $vUser['mst_user_id'] = $dbUser->id;
                            }
                            $viewingUsers[] = $vUser;
                        }
                        if (count($viewingUsers)){
                            if ($sender_user->env_flg != $system_env_flg || $sender_user->server_flg != $system_server_flg){
                                $envClient = EnvApiUtils::getAuthorizeClient($sender_user->env_flg,$sender_user->server_flg);
                                if (!$envClient){
                                    //TODO message
                                    throw new \Exception('Cannot connect to Env Api');
                                }
                                Log::info('connect env client');
                                $response = $envClient->get("getViewingUser",[
                                    RequestOptions::JSON =>[ 'circular_id' => $viewingUsers[0]['circular_id'], 'email' => $viewingUsers[0]['email'],]
                                ]);

                                if($response->getStatusCode() == \Illuminate\Http\Response::HTTP_NOT_FOUND) {
                                    $response = $envClient->post("add-viewing-user",[
                                        RequestOptions::JSON => $viewingUsers[0]
                                    ]);
                                    if($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                                        Log::error($response->getBody());
                                        throw new \Exception('Cannot store viewing user');
                                    }
                                }else if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                                    Log::error($response->getBody());
                                    throw new \Exception('Cannot get viewing user');
                                }
                            }else{
                                foreach($viewingUsers as $index => $Vuser){
                                    $exitVuser = DB::table('viewing_user')->where('circular_id',$Vuser['circular_id'])->where('mst_user_id',$Vuser['mst_user_id'])->exists();
                                    if($exitVuser) unset($viewingUsers[$index]);
                                }
                                if(count($viewingUsers))DB::table('viewing_user')->insert($viewingUsers);

                            }
                        }
                    }
                }
             //PAC_5-1806 「最終承認者から直接社外に送る」にチェックを入れなくても最終承認者から直接社外へ回覧が送られる
            if ($sender_user->parent_send_order != 0 && $sender_user->child_send_order == 1){
                $windowCircularUser = $sender_user;
            }else{
                $windowCircularUser = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $sender_user->parent_send_order)
                    ->where('child_send_order', $sender_user->parent_send_order?1:0)
                    ->first();
            }

            // 現在の回覧者状態が「窓口再承認待ち」状態ではなく場合、該当回覧者以降回覧状態が「未通知」に変更
            if ($sender_user->circular_status != CircularUserUtils::REVIEWING_STATUS){
                DB::table('circular_user')
                    ->where('circular_id',$circular_id)
                    ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) > "
                        .str_pad($sender_user->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($sender_user->child_send_order,3,'0', STR_PAD_LEFT))
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        "received_date" => null,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $login_user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER
                    ]);
            }
            //PAC_5-1698  合議
            $is_plan = $sender_user->plan_id > 0;
            $next_isPlan = false;
            $plan_circular_completed = false;
            $now_plan_circular_completed = false;
            // 次の回覧者
            $circular_user = null;
            $template_circular_completed = false;
            $next_order_circular_users = null; // 次のノードを続けるかどうか
            if (isset($input["isTemplateCircular"]) && $input["isTemplateCircular"]) {
                //
                $arrWaitSkipHandlerUser = [];
                // 合議の場合、次の回覧者
                // 次のノード
                $next_orders = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $sender_user->parent_send_order)
                    ->where('child_send_order', $sender_user->child_send_order + 1)
                    ->get();

                // 現在のノード 回覧者情報の取得
                $send_user_orders = DB::table('circular_user')
                    ->leftjoin('circular_user_routes', function($query){
                        $query->on('circular_user.circular_id', '=', 'circular_user_routes.circular_id');
                        $query->on('circular_user.child_send_order', '=', 'circular_user_routes.child_send_order');
                    })
                    ->where('circular_user.circular_id', $circular_id)
                    //->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->where('circular_user.parent_send_order', $sender_user->parent_send_order)
                    ->where('circular_user.child_send_order', $sender_user->child_send_order)
                    ->select('circular_user.circular_status', 'circular_user_routes.mode', 'circular_user_routes.wait', 'circular_user_routes.score', 'circular_user.id')
                    ->get();

                // 未承認人数
                $user_uncompleted = 0;
                // 承認済み人数
                $user_completed = 0;
                foreach($send_user_orders as $send_user_order){
                    // 1,2,8,10
                    if($send_user_order->id != $request['sender_id'] && in_array($send_user_order->circular_status, [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])){
                        $user_uncompleted ++;
                        $arrWaitSkipHandlerUser[] = $send_user_order->id;
                    }else{
                        $user_completed ++;
                    }
                }

                // 次のノード ない
                if(!count($next_orders)){
                    if($user_uncompleted === 0){
                        $template_circular_completed = true;
                    }else{
                        $route_mode = $send_user_orders[0]->mode;
                        $route_wait = $send_user_orders[0]->wait; // 1:待つ 0:待たない
                        $route_score = $send_user_orders[0]->score;

                        // n人以上承認
                        if ($route_mode == TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN
                            && $route_wait == TemplateRouteUtils::TEMPLATE_MODE_ALL_MUST_NOT_WAIT
                            && $user_completed >= $route_score) {
                            // 該当ノードに未承認者は該当ノード承認済み状態を変更
                            DB::table('circular_user')
                                ->where('circular_id', $circular_id)
                                ->where('parent_send_order', $sender_user->parent_send_order)
                                ->where('child_send_order', $sender_user->child_send_order)
                                ->whereNotIn('circular_status', [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS])
                                ->update([
                                    'circular_status' => CircularUserUtils::NODE_COMPLETED_STATUS,
                                    'update_at' => Carbon::now(),
                                    'update_user' => $login_user->email,
                                ]);
                                $template_circular_completed = true;
                            }
                        }
                }else{
                    if($user_uncompleted === 0){
                        $next_order_circular_users = $next_orders;
                        $arrIds = [];
                        // 該当回覧者以降回覧状態が「未通知」に変更
                        foreach ($next_order_circular_users as $next_order_circular_user){
                            $arrIds[] = $next_order_circular_user->id;
                        }
                        DB::table('circular_user')
                            ->whereIn('id', $arrIds)
                            ->update([
                                'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                'received_date' => Carbon::now(),
                                'update_at'=> Carbon::now(),
                                'update_user'=> $login_user->email,
                                'node_flg' => CircularUserUtils::NODE_OTHER
                            ]);
                    }else{
                        $route_mode = $send_user_orders[0]->mode;
                        $route_wait = $send_user_orders[0]->wait; // 1:待つ 0:待たない
                        $route_score = $send_user_orders[0]->score;

                        // n人以上承認
                        if (($route_mode == TemplateRouteUtils::TEMPLATE_MODE_MORE_THAN
                            && $route_wait == TemplateRouteUtils::TEMPLATE_MODE_ALL_MUST_NOT_WAIT
                            && $user_completed >= $route_score) || true ==  $boolSikpCurrentHandler && !empty($arrWaitSkipHandlerUser)){
                            // 該当ノードに未承認者は該当ノード承認済み状態を変更
                            DB::table('circular_user')
                                ->where('circular_id', $circular_id)
                                ->where('parent_send_order', $sender_user->parent_send_order)
                                ->where('child_send_order', $sender_user->child_send_order)
                                ->whereNotIn('circular_status', [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS])
                                ->update([
                                    'circular_status' => CircularUserUtils::NODE_COMPLETED_STATUS,
                                    'update_at' => Carbon::now(),
                                    'update_user' => $login_user->email,
                                ]);

                                $next_order_circular_users = $next_orders;
                                $arrIds = [];
                                // 該当回覧者以降回覧状態が「未通知」に変更
                                foreach ($next_order_circular_users as $next_order_circular_user){
                                    $arrIds[] = $next_order_circular_user->id;
                                }
                                DB::table('circular_user')
                                    ->whereIn('id', $arrIds)
                                    ->update([
                                        'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                        'received_date' => Carbon::now(),
                                        'update_at'=> Carbon::now(),
                                        'update_user'=> $login_user->email,
                                        'node_flg' => CircularUserUtils::NODE_OTHER
                                    ]);
                            }
                        }
                    }

                //
                if(true ==  $boolSikpCurrentHandler && !empty($arrWaitSkipHandlerUser)){

                    DB::table('circular_user')
                        ->whereIn('id', $arrWaitSkipHandlerUser)
                        ->whereNotIn('circular_status', [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS])
                        ->update([
                            'circular_status' => CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at'=> Carbon::now(),
                            'update_user'=> $login_user->email,
                            'is_skip' => CircularUserUtils::IS_SKIP_ACTION_TRUE,
                        ]);
                    if(!count($next_orders)){
                        $template_circular_completed = true;
                    }else{
                        $next_order_circular_users = $next_orders;
                        $arrIds = [];
                        // 該当回覧者以降回覧状態が「未通知」に変更
                        foreach ($next_order_circular_users as $next_order_circular_user){
                            $arrIds[] = $next_order_circular_user->id;
                        }
                        DB::table('circular_user')
                            ->whereIn('id', $arrIds)
                            ->update([
                                'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                'received_date' => Carbon::now(),
                                'update_at'=> Carbon::now(),
                                'update_user'=> $login_user->email,
                            ]);
                    }
                }

            }else{
                // 次の回覧者
                //   remark   Single process
                if(true == $boolSikpCurrentHandler){
                    $circular_user = DB::table('circular_user')
                        ->where('circular_id',$circular_id)
                        ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                        ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) > "
                            .str_pad($sender_user->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($sender_user->child_send_order,3,'0', STR_PAD_LEFT))
                        ->orderBy('parent_send_order', 'asc')
                        ->orderBy('child_send_order', 'asc')
                        ->first();
                    if (!empty($circular_user)&&$circular_user->plan_id!=0){
                        $next_orders = DB::table('circular_user')
                            ->where('circular_id', $circular_id)
                            ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                            ->where('plan_id','=',$circular_user->plan_id)
                            ->get();
                        $next_isPlan=true;
                        $next_order_circular_users=$next_orders;
                    }
                }else if ($is_plan) {
                    $plan = DB::table('circular_user_plan')
                        ->where('id', $sender_user->plan_id)
                        ->first();
                    $next_orders = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                        ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                        ->orderBy('parent_send_order', 'asc')
                        ->orderBy('child_send_order', 'asc')
                        ->first();

                    $send_user_orders = DB::table('circular_user')
                        ->leftjoin('circular_user_plan', function ($query) {
                            $query->on('circular_user.circular_id', '=', 'circular_user_plan.circular_id');
                            $query->on('circular_user.plan_id', '=', 'circular_user_plan.id');
                        })
                        ->where('circular_user.circular_id', $circular_id)
                        //->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])
                        ->where('circular_user.parent_send_order', $sender_user->parent_send_order)
                        ->where('circular_user.plan_id' , '=' , $sender_user->plan_id)
                        ->where('circular_user.child_send_order', $sender_user->child_send_order)
                        ->select('circular_user.circular_status', 'circular_user_plan.mode', 'circular_user_plan.score', 'circular_user.id')
                        ->get();
                    // 未承認人数
                    $user_uncompleted = 0;
                    // 承認済み人数
                    $user_completed = 0;
                    foreach ($send_user_orders as $send_user_order) {
                        // 1,2,8,10
                        if ($send_user_order->id != $request['sender_id'] && in_array($send_user_order->circular_status, [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS, CircularUserUtils::REVIEWING_STATUS])) {
                            $user_uncompleted++;
                        } else {
                            $user_completed++;
                        }
                    }
                    if ($user_completed >= $plan->score) {
                        //update other circular_user
                        DB::table('circular_user')
                            ->where('circular_id', $circular_id)
                            ->where('parent_send_order', $sender_user->parent_send_order)
                            ->where('child_send_order', $sender_user->child_send_order)
                            ->where('plan_id', $sender_user->plan_id)
                            ->whereNotIn('circular_status', [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS])
                            ->update([
                                'circular_status' => CircularUserUtils::NODE_COMPLETED_STATUS,
                                'update_at' => Carbon::now(),
                                'update_user' => $login_user->email,
                                'node_flg' => CircularUserUtils::NODE_COMPLETED
                            ]);
                        $now_plan_circular_completed = true;
                        if (!$next_orders) {
                            $plan_circular_completed = true;
                        } else {
                            if ($next_orders->plan_id>0){
                                $next_order_circular_users = DB::table('circular_user')
                                    ->where('circular_id', $circular_id)
                                    ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                                    ->where('plan_id','=',$next_orders->plan_id)
                                    ->get();;
                                $next_isPlan = true;
                            }else{
                                $circular_user=$next_orders;
                            }
//                            if (count($next_orders) == 1) {
//                                $circular_user = DB::table('circular_user')
//                                    ->where('circular_id', $circular_id)
//                                    ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
//                                    ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
//                                    ->orderBy('parent_send_order', 'asc')
//                                    ->orderBy('child_send_order', 'asc')
//                                    ->first();
//                            } else {
//                                $next_order_circular_users = $next_orders;
//                                $next_isPlan = true;
//                            }

                        }
                    } else {
//                        $circular_user = DB::table('circular_user')
//                            ->where('circular_id', $circular_id)
//                            ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
//                            ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
//                            ->orderBy('parent_send_order', 'asc')
//                            ->orderBy('child_send_order', 'asc')
//                            ->first();
                    }
                } else {
                    // 次の回覧者
            $circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                ->orderBy('parent_send_order', 'asc')
                ->orderBy('child_send_order', 'asc')
                ->first();
                    if (!empty($circular_user)&&$circular_user->plan_id!=0){
                        $next_orders = DB::table('circular_user')
                            ->where('circular_id', $circular_id)
                            ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                            ->where('plan_id','=',$circular_user->plan_id)
                            ->get();
                        $next_isPlan=true;
                        $next_order_circular_users=$next_orders;
            }
                }
            }

            // 最終回覧者
            if (isset($input["isTemplateCircular"]) && $input["isTemplateCircular"]) {
                // 合議の場合、最終回覧者
            $last_circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                ->orderBy('parent_send_order', 'desc')
                ->orderBy('child_send_order', 'desc')
                    ->orderBy('update_at', 'desc')
                ->first();
            }else{
                $last_circular_user = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                    ->orderBy('parent_send_order', 'desc')
                    ->orderBy('child_send_order', 'desc')
                    ->orderBy('update_at', 'desc')
                    ->first();
            }

            // !$template_circular_completed && !$next_order_circular_users 現在のノード 未完
            if(isset($input["isTemplateCircular"]) && $input["isTemplateCircular"] && !$template_circular_completed && !$next_order_circular_users){
                // PAC_5-445 社外アクセスコードリフレッシュの場合
                if(isset($input['outsideAccessCode']) && $input['outsideAccessCode']){
                    DB::table('circular')->where('id', $circular_id)->update([
                        'outside_access_code_flg' => CircularUtils::OUTSIDE_ACCESS_CODE_VALID,
                        'outside_access_code' => $input['outsideAccessCode'],
                        'final_updated_date' => Carbon::now(),
                    ]);
                }
            }else{
                // PAC_5-445 社外アクセスコードリフレッシュの場合
                if(isset($input['outsideAccessCode']) && $input['outsideAccessCode']){
                    DB::table('circular')->where('id', $circular_id)->update([
                        'outside_access_code_flg' => CircularUtils::OUTSIDE_ACCESS_CODE_VALID,
                        'outside_access_code' => $input['outsideAccessCode'],
                        'circular_status' =>  CircularUtils::CIRCULATING_STATUS,
                        'final_updated_date' => Carbon::now(),
                        ]);
                }else{
                    DB::table('circular')->where('id', $circular_id)->update([
                        'circular_status' =>  CircularUtils::CIRCULATING_STATUS,
                        'final_updated_date' => Carbon::now(),
                        ]);
                }
            }

            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('return_send_back', DB::raw(1))
                ->update([
                    'return_send_back' => 0,
                ]);

            $circular_updated = DB::table('circular')
                ->where('id', $circular_id)
                ->first();

            $title = $sender_user->title;
            if($circular_user && $circular_user->id) {
                $title = $circular_user->title;
            }
            // PAC_5-1973 ログインパスワードの変更画面から利用者としてログインできるように修正 Start
            if (is_string($title))$title = preg_replace('/[\t]/', '', $title);
            // PAC_5-1973
            Session::flash('mail_title', $title === " " ? "(件名設定なし)" : $title);

            $circularDocuments = DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->orderby('id')
                ->get()->toArray();
            $hasConfidenceFiles = false;
            $ConfidenceFilesInfo = array();
            $firstDocument = null;
            foreach($circularDocuments as $circular_doc ){
                if ($firstDocument === null || $firstDocument->circular_id < $circular_doc->circular_id){
                    $firstDocument = $circular_doc;
                }

                if($circular_doc->confidential_flg){
                    $hasConfidenceFiles = true;
                    $ConfidenceFileInfo = [
                        'origin_edition_flg' => $circular_doc->origin_edition_flg,
                        'origin_env_flg'     => $circular_doc->origin_env_flg,
                        'origin_server_flg'  => $circular_doc->origin_server_flg,
                        'create_company_id'  => $circular_doc->create_company_id,
                    ];
                    $ConfidenceFilesInfo[] = $ConfidenceFileInfo;

                }
            }
			// 件名・メッセージ入力した場合
			if(isset($input['text'])&&$input['text']){
				$circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
					'circular_id'=> $circular_id,
					'circular_document_id' => $input['circular_document_id'],
					'operation_email' => $login_user->email,
					'operation_name' => $user_name,
					'acceptor_email'=> '',
					'acceptor_name'=> '',
					'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS,
					'create_at' => Carbon::now(),
				]);
				//
				$current_circular_user = DB::table('circular_user')->where('circular_id', $circular_id)
					->where('email', true == $boolSikpCurrentHandler ? $objSkipUser->email : $login_user->email)
					->where('edition_flg', $edition_flg)
					->where('env_flg', $env_flg)
                    ->where('server_flg', $server_flg)
					->orderByDesc('parent_send_order')
					->orderByDesc('child_send_order')
					->first();
				DB::table('document_comment_info')->insert([
					'circular_document_id' => $input['circular_document_id'],
					'circular_operation_id' => $circular_operation_history_id,
					'parent_send_order' => $current_circular_user ? $current_circular_user->parent_send_order:0,
					'name'=> $user_name,  // @todo
					'email'=> true == $boolSikpCurrentHandler ? $objSkipUser->email : $login_user->email,
					'text'=> $input['text'],
					'private_flg'=> CircularOperationHistoryUtils::DOCUMENT_COMMENT_PRIVATE,
					'create_at' => Carbon::now(),
				]);
			}

			// PAC_5-539 承認履歴情報登録
            // 合議の場合
            if((isset($input["isTemplateCircular"]) && $input["isTemplateCircular"])|| $next_isPlan){
                // 次のノードに複数人
                if ($next_order_circular_users) {
                    $circular_operation_history_id = [];
                    foreach ($next_order_circular_users as $circular_user) {
                        $arrTemp = [
                            'circular_id' => $circular_id,
                            'operation_email' => $login_user->email,
                            'operation_name' => $user_name,
                            'acceptor_email' => $circular_user ? $circular_user->email : '',
                            'acceptor_name' => $circular_user ? $circular_user->name : '',
                            'circular_status' => (
                                $author_user->email == (!empty($boolSikpCurrentHandler) ? $sender_user->email : $login_user->email)
                                && $sender_user->circular_status != CircularUserUtils::REVIEWING_STATUS)
                                ? CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS : CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS,
                            'create_at' => Carbon::now(),
                            'is_skip' => 0,
                        ];
                        if(
                            (true == $boolSikpCurrentHandler)
                        ){
                            $arrTemp['is_skip'] = 1;
                        }
                        $circular_operation_history_id[] = DB::table('circular_operation_history')->insertGetId($arrTemp);
                    }
                } else {
                    $arrTemp = [
                        'circular_id' => $circular_id,
                        'operation_email' => $login_user->email,
                        'operation_name' => $user_name,
                        'acceptor_email' => '',
                        'acceptor_name' => '',
                        'circular_status' => ($author_user->email == (!empty($boolSikpCurrentHandler) ? $sender_user->email : $login_user->email) && $sender_user->circular_status != CircularUserUtils::REVIEWING_STATUS) ? CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS : CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS,
                        'create_at' => Carbon::now(),
                        'is_skip' => 0,
                    ];

                    if(
                        (true == $boolSikpCurrentHandler)
                    ){
                        $arrTemp['is_skip'] = 1;
                    }
                    // 次のノードにデータがありません。 acceptor_email = ''  acceptor_name = ''
                    $circular_operation_history_id = DB::table('circular_operation_history')->insertGetId($arrTemp);
                }
            } else {
                $arrTemp = [
                    'circular_id' => $circular_id,
                    'operation_email' => $login_user->email,
                    'operation_name' => $user_name,
                    'acceptor_email' => $circular_user ? $circular_user->email : '',
                    'acceptor_name' => $circular_user ? $circular_user->name : '',
                    'circular_status' => ($author_user->email == (!empty($boolSikpCurrentHandler) ? $sender_user->email : $login_user->email) && $sender_user->circular_status != CircularUserUtils::REVIEWING_STATUS) ? CircularOperationHistoryUtils::CIRCULAR_APPLY_STATUS : CircularOperationHistoryUtils::CIRCULAR_APPROVE_STATUS,
                    'create_at' => Carbon::now(),
                    'is_skip' =>  0,
                ];

                if(
                    (true == $boolSikpCurrentHandler)
                ){
                    $arrTemp['is_skip'] = 1;
                }
                $circular_operation_history_id = DB::table('circular_operation_history')->insertGetId($arrTemp);
            }

            //insert table mail_text
            DB::table('mail_text')
                ->insert([
                    'text'=> $input['text'] ? $input['text'] : '',
                    'circular_user_id' => $request['sender_id'],
                    'create_at' => Carbon::now()
                ]);
            // PAC_5-2258 承認ボタン押下時、ロード中にウィンドウを閉じると捺印していても「捺印状況」が「承認(捺印なし)」となる
            $objCurrentSender = DB::table('circular_user')->where('id', $request['sender_id'])->where("circular_id",$circular->id)->first();
            $intCurrentSenderUpdateAt = strtotime($objCurrentSender->update_at);
            $intCurrentHandlerUserHistoryCount = DB::table("circular_operation_history")->where("circular_id", $circular->id)
                ->whereRaw("UNIX_TIMESTAMP(create_at) > $intCurrentSenderUpdateAt ")
                ->where("operation_email", $objCurrentSender->email)
                ->where("circular_status", CircularOperationHistoryUtils::CIRCULAR_IMPRINT_STATUS)
                ->count();
            $boolIsHasStamp = !empty($intCurrentHandlerUserHistoryCount) ?  true : false;
            $arrUpdateCircularUserData = [
                    'circular_status' => $sender_user->circular_status != CircularUserUtils::REVIEWING_STATUS ? ($request['add_stamp'] ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : ($boolIsHasStamp ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS)) : CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                    'update_at'=> Carbon::now(),
                    'stamp_flg' => $sender_user->circular_status != CircularUserUtils::REVIEWING_STATUS ? ($request['add_stamp'] || $boolIsHasStamp ? 1 : 0) : 0,
                    'update_user'=> $login_user->email,
                'is_skip' => !empty($boolSikpCurrentHandler) ? CircularUserUtils::IS_SKIP_ACTION_TRUE : CircularUserUtils::IS_SKIP_ACTION_FALSE,
                'node_flg' => CircularUserUtils::NODE_APPROVED,
            ];
            DB::table('circular_user')
                ->where('id', $request['sender_id'])
                ->update($arrUpdateCircularUserData);
            if($objCurrentSender->plan_id > 0 && $boolSikpCurrentHandler == true){
                DB::table('circular_user')
                    ->where('circular_id', $objCurrentSender->circular_id)
                    ->where('plan_id', $objCurrentSender->plan_id)
                    ->where('parent_send_order', $objCurrentSender->parent_send_order)
                    ->where('child_send_order', $objCurrentSender->child_send_order)
                    ->update($arrUpdateCircularUserData);
            }

            // 合議の場合
            if(isset($input["isTemplateCircular"]) && $input["isTemplateCircular"]){
                $is_circular_completed = $template_circular_completed;
            }elseif ($is_plan) {
                $is_circular_completed = $plan_circular_completed;
            }else{
            if($last_circular_user && $last_circular_user->id == $request['sender_id'] && ($windowCircularUser->id == $request['sender_id'] || !$windowCircularUser->return_flg || $last_circular_user->parent_send_order === 0)) {
                // The last sender is the window user or there is not any window user on the last company or this is the internal circular
                $is_circular_completed = true;
            }

            if ($windowCircularUser->id == $request['sender_id'] && $windowCircularUser->circular_status == CircularUserUtils::REVIEWING_STATUS
                && $windowCircularUser->parent_send_order == $last_circular_user->parent_send_order){
                // The sender is the window user, and the current status is reviewing, and it is the last company
                $is_circular_completed = true;
            }
            }

            // PAC_5-2011 / PAC_5-1438
            $arrAccessIDs = [];
            //SEND CIRCULAR ENDED NOTIFY
            // 現在の回覧者が最終回覧者の場合

            if($is_circular_completed) {
                // get all old public document id
                $oldPublicDocumentIds = DB::table('circular_document')->where('circular_id', $circular_id)
                    ->where('confidential_flg', 0)
                    ->where('parent_send_order', '!=', $last_circular_user->parent_send_order)
                    ->pluck('id');

                // delete old public document data, stamp, timestamp and text info
                DB::table('text_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                DB::table('stamp_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
				// PAC_5-368 document_comment_info削除
				DB::table('document_comment_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
				DB::table('sticky_notes')->whereIn('document_id', $oldPublicDocumentIds)->delete();
                DB::table('time_stamp_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                DB::table('document_data')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                DB::table('circular_document')->whereIn('id', $oldPublicDocumentIds)->delete();
				DB::table('circular_operation_history')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();

                // update origin_document_id of new public document data
                DB::table('circular_document')->where('circular_id',$circular_id)
                    ->where('confidential_flg', 0)
                    ->where('parent_send_order', $last_circular_user->parent_send_order)
                    ->update(['origin_document_id' => 0]);

                DB::table('circular')
                    ->where('id', $circular_id)
                    ->update([
                        'circular_status' => CircularUtils::CIRCULAR_COMPLETED_STATUS,
                        'completed_date' => Carbon::now(),
                        'update_at' => Carbon::now(),
                        'update_user' => $login_user->email,
                        'final_updated_date' => Carbon::now(),
                    ]);

                $check=DB::table('eps_t_app')
                    ->where('circular_id', $circular_id)
                    ->first();

                if(!($check==null)){
                    //経費精算　完了ステータスに更新
                    $expensecheck=ExpenseUtils::checkExpense($circular_id,CircularUtils::CIRCULAR_COMPLETED_STATUS);
                    if(!$expensecheck){
                        DB::rollBack();
                        return $this->sendError('経費精算の処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }

                $circularUsers = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->get();

                $viewingUsers = DB::table('viewing_user')
                    ->where('circular_id', $circular_id)
                    ->get();

                if ($last_circular_user && $last_circular_user->id) {
                    $title = $last_circular_user->title;
                }

                $emails = [];
                foreach ($circularUsers as $circularUser) {
                    if($circularUser->stamp_flg > 0){
                        $arrAccessIDs[] = $circularUser->id;
                    }
                    $emails[] = $circularUser->email;
                }

                $userIds = [];
                $mapCompanyIds = [];
                foreach ($viewingUsers as $viewingUser) {
                    $userIds[] = $viewingUser->mst_user_id;
                    $mapCompanyIds[$viewingUser->mst_company_id] = null;
                }

                $mstUsers = DB::table('mst_user')
                    ->select( 'email', DB::raw('id as mst_user_id'),'mst_company_id',DB::raw('CONCAT(family_name,\' \',given_name) as user_name'))
                    ->whereIn('id', $userIds)
                    ->whereNotIn('email',$emails)
                    ->get();
                $viewingUserInfoIds = [];
                foreach ($mstUsers as $mstUser){
                    $viewingUserInfoIds[$mstUser->mst_user_id] = $mstUser;
                    }

                $circularDocumentsUpdated = DB::table('circular_document')
                    ->where('circular_id', $circular_id)
                    ->orderby('id')
                    ->get()->toArray();

                $mapCompanyIds = $this->companyRepository->getSameEnvCompanies($mapCompanyIds);


                foreach ($circularUsers as $circularUser) {
                    //回覧完了メール（承認者時）
                    $mailType = 'completion';
                    if($circularUser->parent_send_order == 0 && $circularUser->child_send_order == 0){
                        //回覧完了メール（申請者時）
                        $mailType = 'completion_sender';
                    }
                    if (CircularUserUtils::checkAllowReceivedEmail($circularUser->email, $mailType,$circularUser->mst_company_id,$circularUser->env_flg,$circularUser->edition_flg,$circularUser->server_flg)) {
                        $data = [];
                        $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ENDED_NOTIFY']['CODE'];
                        $filterDocuments = array_filter($circularDocumentsUpdated, function ($item) use ($circularUser) {
                            if ($item->confidential_flg
                                && $item->origin_edition_flg == config('app.edition_flg')
                                && $item->origin_env_flg == config('app.server_env')
                                && $item->origin_server_flg == config('app.server_flg')
                                && $item->create_company_id == $circularUser->mst_company_id) {
                                // 社外秘：origin_document_idが-1固定
                                // 同社メンバー参照可
                                return true;
                            } else if (!$item->confidential_flg) {
                                // 回覧終了時：origin_document_id＝0のレコードのみ、別条件不要
                                return true;
                            }
                            return false;
                        });

                        $filenames = array_column($filterDocuments, 'file_name');
                        $data['filenames'] = $filenames;
                        if(count($filenames)){
                            $data['filenamestext'] = '';
                            foreach($filenames as $filename){
                                if ($data['filenamestext'] == '') {
                                    $data['filenamestext'] = $filename;
                                    continue;
                                }
                                $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                            }
                        }else{
                            $data['filenamestext'] = '';
                        }

                        if (!trim($title)) {
                            $mailTitle =  $filenames[0];
                        } else {
                            $mailTitle = $title;
                        }

                        $data['body'] = 'mail.circular_has_ended_template.body';
                        $data['title'] = trans('mail.circular_has_ended_template.subject', ['title' => $mailTitle]);
                        $data['receiver_name'] = $circularUser->name;
                        $data['creator_name'] = $author_user->name;
                        $data['mail_name'] = $mailTitle;
                        $data['author_email'] = $circular->create_user;
                        $data['last_updated_email'] = $last_circular_user->update_user;
                        $data['last_updated_text'] = $input['text'];
                        $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                        $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circularUser->email, $circularUser->edition_flg, $circularUser->env_flg, $circularUser->server_flg, $circular_id);
                        // hide_circular_approval_url false:表示 true:非表示
                        // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                        // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                        $data['hide_circular_approval_url'] = false;
                        if($hasConfidenceFiles){
                            // 社外秘文書ある、
                            foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                                if($ConfidenceFileInfo['origin_edition_flg'] == $circularUser->edition_flg
                                    && $ConfidenceFileInfo['origin_env_flg'] == $circularUser->env_flg
                                    && $ConfidenceFileInfo['origin_server_flg'] == $circularUser->server_flg
                                    && $ConfidenceFileInfo['create_company_id'] == $circularUser->mst_company_id){
                                    $data['hide_circular_approval_url'] = true;
                                }
                            }
                        }

                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                        }else{
                            $data['circular_approval_url_text'] = '';
                        }
                        $data['send_to'] = $circularUser->email;
                        $data['send_to_company'] = $circularUser->mst_company_id;
                        $data['parent_send_order'] = $circularUser->parent_send_order;
                        // check to use SAMl Login URL or not
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($circularUser);

                        $mailDatas[] = $data;

                        // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            // 次の回覧者が社内回覧の場合
                            if($circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID
                                && $author_user->mst_company_id == $circularUser->mst_company_id
                                && $author_user->edition_flg == $circularUser->edition_flg
                                && $author_user->env_flg == $circularUser->env_flg
                                && $author_user->server_flg == $circularUser->server_flg){
                                $notice_mail_date['title'] = $mailTitle;
                                $notice_mail_date['access_code'] = $circular->access_code;
                                $notice_mail_date['send_to'] = $circularUser->email;
                                $notice_mail_date['send_to_company'] = $circularUser->mst_company_id;
                                $noticeMailDatas[] = $notice_mail_date;
                            }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                && ($author_user->mst_company_id != $circularUser->mst_company_id
                                    || $author_user->edition_flg != $circularUser->edition_flg
                                    || $author_user->env_flg != $circularUser->env_flg
                                    || $author_user->server_flg != $circularUser->server_flg)) {
                                // 窓口が社外の場合
                                $notice_mail_date['title'] = $mailTitle;
                                $notice_mail_date['access_code'] = $circular->outside_access_code;
                                $notice_mail_date['send_to'] = $circularUser->email;
                                $notice_mail_date['send_to_company'] = $circularUser->mst_company_id;
                                $noticeMailDatas[] = $notice_mail_date;
                            }

                        }
                    }
                }

                foreach ($viewingUsers as $viewingUser) {
                    if (CircularUserUtils::checkAllowReceivedEmail($viewingUserInfoIds[$viewingUser->mst_user_id]->email, 'completion',$viewingUserInfoIds[$viewingUser->mst_user_id]->mst_company_id,config('app.server_env'),config('app.edition_flg'),config('app.server_flg'))) {
                        $mstUser = $viewingUserInfoIds[$viewingUser->mst_user_id];
                        $data = [];
                        $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ENDED_NOTIFY']['CODE'];
                        $filterDocuments = array_filter($circularDocumentsUpdated, function ($item) use ($mstUser) {
                            if ($item->confidential_flg
                                && $item->origin_edition_flg == config('app.edition_flg')
                                && $item->origin_env_flg == config('app.server_env')
                                && $item->origin_server_flg == config('app.server_flg')
                                && $item->create_company_id == $mstUser->mst_company_id) {
                                // 社外秘：origin_document_idが-1固定
                                // 同社メンバー参照可
                                return true;
                            } else if (!$item->confidential_flg) {
                                // 回覧終了時：origin_document_id＝0のレコードのみ、別条件不要
                                return true;
                            }
                            return false;
                        });
                        $filenames = array_column($filterDocuments, 'file_name');
                        $data['filenames'] = $filenames;
                        if(count($filenames)){
                            $data['filenamestext'] = '';
                            foreach($filenames as $filename){
                                if ($data['filenamestext'] == '') {
                                    $data['filenamestext'] = $filename;
                                    continue;
                                }
                                $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                            }
                        }else{
                            $data['filenamestext'] = '';
                        }

                        if (!trim($title)) {
                            $mailTitle = $filenames[0];
                        } else {
                            $mailTitle = $title;
                        }

                        $data['body'] = 'mail.circular_has_ended_template.body';
                        $data['title'] = trans('mail.circular_has_ended_template.subject', ['title' => $mailTitle]);
                        $data['receiver_name'] = $mstUser->user_name;
                        $data['creator_name'] = $author_user->name;
                        $data['mail_name'] = $mailTitle;
                        $data['author_email'] = $circular->create_user;
                        $data['last_updated_email'] = $last_circular_user->update_user;
						$data['last_updated_text'] = $input['text'];
                        $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                        $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($mstUser->email, config('app.edition_flg'),config('app.server_env'),config('app.server_flg'), $circular_id);
                        // hide_circular_approval_url false:表示 true:非表示
                        // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                        // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                        $data['hide_circular_approval_url'] = false;
                        if($hasConfidenceFiles){
                            // 社外秘文書ある、
                            foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                                if($ConfidenceFileInfo['origin_edition_flg'] == config('app.edition_flg')
                                    && $ConfidenceFileInfo['origin_env_flg'] == config('app.server_env')
                                    && $ConfidenceFileInfo['origin_server_flg'] == config('app.server_flg')
                                    && $ConfidenceFileInfo['create_company_id'] == $mstUser->mst_company_id){
                                    $data['hide_circular_approval_url'] = true;
                                }
                            }
                        }

                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                        }else{
                            $data['circular_approval_url_text'] = '';
                        }
                        $data['send_to'] = $mstUser->email;
                        $data['send_to_company'] = $mstUser->mst_company_id;
                        $data['parent_send_order'] = $viewingUser->parent_send_order;
                        if (isset($mapCompanyIds[$mstUser->mst_company_id])){
                            $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'),config('app.server_flg'), CircularUserUtils::NEW_EDITION, $mapCompanyIds[$mstUser->mst_company_id]);
                        }else{
                            $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'),config('app.server_flg'), CircularUserUtils::NEW_EDITION, null);
                        }
                        $mailDatas[] = $data;
                        //    Mail::to()->queue(new SendCircularUserMail($data));

                        // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            // 次の回覧者が社内回覧の場合
                            if($circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID
                                && $author_user->mst_company_id == $mstUser->mst_company_id
                                && $author_user->edition_flg == config('app.edition_flg')
                                && $author_user->env_flg == config('app.server_env')
                                && $author_user->server_flg == config('app.server_flg')){
                                $notice_mail_date['title'] = $mailTitle;
                                $notice_mail_date['access_code'] = $circular->access_code;
                                $notice_mail_date['send_to'] = $mstUser->email;
                                $notice_mail_date['send_to_company'] = $mstUser->mst_company_id;
                                $noticeMailDatas[] = $notice_mail_date;
                            }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                && ($author_user->mst_company_id != $mstUser->mst_company_id
                                    || $author_user->edition_flg != config('app.edition_flg')
                                    || $author_user->env_flg != config('app.server_env')
                                    || $author_user->server_flg != config('app.server_flg'))) {
                                // 窓口が社外の場合
                                $notice_mail_date['title'] = $mailTitle;
                                $notice_mail_date['access_code'] = $circular->outside_access_code;
                                $notice_mail_date['send_to'] = $mstUser->email;
                                $notice_mail_date['send_to_company'] = $mstUser->mst_company_id;
                                $noticeMailDatas[] = $notice_mail_date;
                            }

                        }
                    }
                }
                // PAC_5-2011 / PAC_5-1438
                if(!empty($arrAccessIDs)){
                    DB::table("circular_user")->whereIn('id',$arrAccessIDs)->update([
                        "update_user" => $sender_user->email,
                        'update_at' => Carbon::now(),
                        "circular_status" => CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                    ]);
                }
            }
            $sender_user->circular_status = $request['add_stamp'] ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS;
            if ($is_circular_completed){
                Log::debug('Finish circular, check ths circular has been transferred to other application');
                $hasUpdateTransferredDocumentStatus = false;

                $otherEnvUsers = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', '!=',0)
                    ->where(function ($query){
                        $query->where('edition_flg', '!=', config('app.edition_flg'))
                            ->orWhere('env_flg', '!=', config('app.server_env'))
                            ->orWhere('server_flg', '!=', config('app.server_flg'));
                    })
                    ->orderBy('child_send_order','desc')
                    ->get();

                $array_env_server = [];

                foreach ($otherEnvUsers as $circularUser){
                    if ($circularUser->edition_flg != config('app.edition_flg')){
                        $this->sendUpdateDocumentStatus2OtherEdition($circular, $circularUser, $circularUser->env_flg, true);
                    }else{
                        $hasUpdateTransferredDocumentStatus = true;

                        $detail_env_server = [
                            'env_flg'=>$circularUser->env_flg,
                            'server_flg'=>$circularUser->server_flg,
                            'parent_send_order' => $circularUser->parent_send_order,
                            'child_send_order' => $circularUser->child_send_order,
                        ];
                        array_push($array_env_server, $detail_env_server);
                    }
                }
                if ($hasUpdateTransferredDocumentStatus){
                    foreach ($array_env_server as $item){
                        $this->sendUpdateTransferredDocumentStatus($circular, $item['parent_send_order'], $item['child_send_order'], $title, $input['text'], $item['env_flg'], $item['server_flg']);
                    }
                }
            }else{
                //SEND NOTICE UPDATED MAIL
                if($author_user && $author_user->id && $sender_user->id != $author_user->id) {
                    if(CircularUserUtils::checkAllowReceivedEmail($author_user->email, 'updated',$author_user->mst_company_id,$author_user->env_flg,$author_user->edition_flg,$author_user->server_flg)) {
                        // 通知メール送信
                        $data = [];
                        $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_UPDATED_NOTIFY']['CODE'];
                        // hide_thumbnail_flg 0:表示 1:非表示
                        if (!$circular->hide_thumbnail_flg) {
                            // thumbnail表示
                            if ($firstDocument && $firstDocument->confidential_flg
                                && $firstDocument->origin_edition_flg == $author_user->edition_flg
                                && $firstDocument->origin_env_flg == $author_user->env_flg
                                && $firstDocument->origin_server_flg == $author_user->server_flg
                                && $firstDocument->create_company_id == $author_user->mst_company_id){
                                // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                $previewPath = AppUtils::getPreviewPagePath($author_user->edition_flg, $author_user->env_flg, $author_user->server_flg, $author_user->mst_company_id, $author_user->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else if ($firstDocument && !$firstDocument->confidential_flg){
                                // 一ページ目が社外秘ではない
                                $previewPath = AppUtils::getPreviewPagePath($author_user->edition_flg, $author_user->env_flg, $author_user->server_flg, $author_user->mst_company_id, $author_user->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else{
                                // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                $data['image_path'] = public_path()."/images/no-preview.png";
                            }
                        }

                        $filenames = DB::table('circular_document')
                            ->where('circular_id', $circular_id)
                            ->where(function($query) use ($author_user){

                                $query->where(function ($query0) use ($author_user){
                                    // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                    $query0->where('confidential_flg', 0);
                                    $query0->where(function ($query01) use ($author_user){
                                        // 回覧終了時：origin_document_id＝0のレコード
                                        $query01->where('origin_document_id', 0);
                                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                        $query01->orWhere('parent_send_order', $author_user->parent_send_order);
                                    });
                                });

                                $query->orWhere(function($query1) use ($author_user){
                                    // 社外秘：origin_document_idが-1固定
                                    // 同社メンバー参照可
                                    $query1->where('confidential_flg', 1);
                                    $query1->where('origin_edition_flg', $author_user->edition_flg);
                                    $query1->where('origin_env_flg', $author_user->env_flg);
                                    $query1->where('origin_server_flg', $author_user->server_flg);
                                    $query1->where('create_company_id', $author_user->mst_company_id);
                                });
                            })
                            ->pluck('file_name');

                        if (!trim($title)) {
                            $title = $filenames->toArray()[0];
                        }

                        $data['username'] = $author_user->name;
                        $data['filenames'] = $filenames;
                        if(count($filenames)){
                            $data['filenamestext'] = '';
                            foreach($filenames as $filename){
                                if ($data['filenamestext'] == '') {
                                    $data['filenamestext'] = $filename;
                                    continue;
                                }
                                $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                            }
                        }else{
                            $data['filenamestext'] = '';
                        }
                        $data['title'] = trans('mail.circular_updated_notify_template.subject', ['title' => $title]);
                        $data['body'] = 'mail.circular_updated_notify_template.body';
                        $data['receiver_name'] = $author_user->name;
                        $data['creator_name'] = $author_user->name;
                        $data['mail_name'] = $title;
                        $data['author_email'] = $circular->create_user;
                        $data['circular_id'] = $circular_id;// PAC_5-2490
                        $data['last_updated_email'] = $last_circular_user->update_user;
						$data['last_updated_text'] = $input['text'];
                        $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                        $data['send_to'] = $author_user->email;
                        $data['send_to_company'] = $author_user->mst_company_id;
                        $data['parent_send_order'] = $author_user->parent_send_order;
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($author_user);

                        $mailDatas[] = $data;
                    }
                }

                //SEND TO CONTINUE WINDOW USER NOTIFY
                if ($windowCircularUser->return_flg && ($windowCircularUser->id != $sender_user->id) && (!$circular_user || ($circular_user && $circular_user->parent_send_order != $sender_user->parent_send_order )) && (!isset($input["isTemplateCircular"]) || !$input["isTemplateCircular"]) && (!$is_plan||$now_plan_circular_completed)){
                    // The sender is in the last company and there is the window user in the last company
                    // OR The sender send to the next company and there is the window user in the current company
                    DB::table('circular_user')
                        ->where('id', $windowCircularUser->id)
                        ->update([
                            'circular_status' => CircularUserUtils::REVIEWING_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at'=> Carbon::now(),
                            'update_user'=> $login_user->email,
                        ]);
                    /*PAC_5-1698 S*/
                    if ($windowCircularUser->plan_id > 0) {
                        DB::table('circular_user')
                            ->where('plan_id', $windowCircularUser->plan_id)
                            ->where('circular_id', $circular_id)
                            ->update([
                                'circular_status' => CircularUserUtils::REVIEWING_STATUS,
                                'received_date' => Carbon::now(),
                                'update_at' => Carbon::now(),
                                'update_user' => $login_user->email,
                            ]);
                    }
                    $windowCircularUsers = DB::table('circular_user')
                        ->where('plan_id', $windowCircularUser->plan_id)
                        ->where('circular_id', $circular_id)
                        ->get();
                    /*PAC_5-1698 E*/
					DB::table('circular_operation_history')
                        ->where(function($query) use ($next_isPlan, $circular_operation_history_id) {
                            if(is_array($circular_operation_history_id)&&$next_isPlan){
                               $query ->whereIn('id',$circular_operation_history_id);
                            }else{
                                $query->where('id', $circular_operation_history_id);
                            }
                        })
						->update([
							'acceptor_name' => $windowCircularUser->name,
							'acceptor_email' => $windowCircularUser->email,
						]);
                    if ($sender_user->edition_flg == $system_edition_flg && ($sender_user->env_flg != $system_env_flg || $sender_user->server_flg != $system_server_flg)){
                        $this->sendUpdateTransferredDocumentStatus($circular, $sender_user->parent_send_order, $sender_user->child_send_order,$title, $input['text'], $sender_user->env_flg,$sender_user->server_flg);
                    }

                    if ($windowCircularUser->plan_id == 0) {
                    if(CircularUserUtils::checkAllowReceivedEmail($windowCircularUser->email, 'approval',$windowCircularUser->mst_company_id,$windowCircularUser->env_flg,$windowCircularUser->edition_flg,$windowCircularUser->server_flg)) {
                        $data = [];
                        $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'];
                        // hide_thumbnail_flg 0:表示 1:非表示
                        if (!$circular->hide_thumbnail_flg) {
                            // thumbnail表示
                            if ($firstDocument && $firstDocument->confidential_flg
                                && $firstDocument->origin_edition_flg == $windowCircularUser->edition_flg
                                && $firstDocument->origin_env_flg == $windowCircularUser->env_flg
                                && $firstDocument->origin_server_flg == $windowCircularUser->server_flg
                                && $firstDocument->create_company_id == $windowCircularUser->mst_company_id){
                                // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                $previewPath = AppUtils::getPreviewPagePath($windowCircularUser->edition_flg, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $windowCircularUser->mst_company_id, $windowCircularUser->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else if ($firstDocument && !$firstDocument->confidential_flg){
                                // 一ページ目が社外秘ではない
                                $previewPath = AppUtils::getPreviewPagePath($windowCircularUser->edition_flg, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $windowCircularUser->mst_company_id, $windowCircularUser->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else{
                                // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                $data['image_path'] = public_path()."/images/no-preview.png";
                            }
                        }
                        $filenames = DB::table('circular_document')
                            ->where('circular_id',$circular_id)
                            ->where(function($query) use ($windowCircularUser){

                                $query->where(function ($query0) use ($windowCircularUser){
                                    // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                    $query0->where('confidential_flg', 0);
                                    $query0->where(function ($query01) use ($windowCircularUser){
                                        // 回覧終了時：origin_document_id＝0のレコード
                                        $query01->where('origin_document_id', 0);
                                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                        $query01->orWhere('parent_send_order', $windowCircularUser->parent_send_order);
                                    });
                                });

                                $query->orWhere(function($query1) use ($windowCircularUser){
                                    // 社外秘：origin_document_idが-1固定
                                    // 同社メンバー参照可
                                    $query1->where('confidential_flg', 1);
                                    $query1->where('origin_edition_flg', $windowCircularUser->edition_flg);
                                    $query1->where('origin_env_flg', $windowCircularUser->env_flg);
                                    $query1->where('origin_server_flg', $windowCircularUser->server_flg);
                                    $query1->where('create_company_id', $windowCircularUser->mst_company_id);
                                });
                            })
                            ->pluck('file_name');

                        if (!trim($title)) {
                            $title = $filenames->toArray()[0];
                        }

                        $data['user_name'] = $user_name;
                        $data['circular_id'] = $circular_id;
                        $data['filenames'] = $filenames;
                        if(count($filenames)){
                            $data['filenamestext'] = '';
                            foreach($filenames as $filename){
                                if ($data['filenamestext'] == '') {
                                    $data['filenamestext'] = $filename;
                                    continue;
                                }
                                $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                            }
                        }else{
                            $data['filenamestext'] = '';
                        }
                        $data['title'] = trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $author_user->name]);
                        $data['body'] = 'mail.circular_user_template.body';
                        $data['receiver_name'] = $windowCircularUser->name;
                        $data['creator_name'] = $author_user->name;
                        $data['mail_name'] = $title;
						$data['text'] = $input['text'];
                        $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                        $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($windowCircularUser->email, $windowCircularUser->edition_flg, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $circular_id);
                        // hide_circular_approval_url false:表示 true:非表示
                        // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                        // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                        $data['hide_circular_approval_url'] = false;
                        if($hasConfidenceFiles){
                            // 社外秘文書ある、
                            foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                                if($ConfidenceFileInfo['origin_edition_flg'] == $windowCircularUser->edition_flg
                                    && $ConfidenceFileInfo['origin_env_flg'] == $windowCircularUser->env_flg
                                    && $ConfidenceFileInfo['origin_server_flg'] == $windowCircularUser->server_flg
                                    && $ConfidenceFileInfo['create_company_id'] == $windowCircularUser->mst_company_id){
                                    $data['hide_circular_approval_url'] = true;
                                }
                            }
                        }

                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                        }else{
                            $data['circular_approval_url_text'] = '';
                        }

                        // check to use SAMl Login URL or not
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($windowCircularUser);
                        $data['send_to'] = $windowCircularUser->email;
                        $data['send_to_company'] = $windowCircularUser->mst_company_id;
                        $data['parent_send_order'] = $windowCircularUser->parent_send_order;
                        //data push notify
                        if (config('app.enable_push_notification')
                            && $windowCircularUser->edition_flg == $system_edition_flg){
                            $badgenumber = $this->getNotifyBadgeNumber($windowCircularUser->email, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $windowCircularUser->edition_flg);
                            $dataNotify = new PushNotify("Shachihata Cloud", "回覧文書が届いています", $windowCircularUser->email, $windowCircularUser->env_flg, $windowCircularUser->server_flg, 1, $badgenumber);
                            Log::debug('sendNotifyContinue');
                            $this->dispatch(new SendNotification($dataNotify));
                        }

                        $mailDatas[] = $data;
                        //   Mail::to($circular_user->email)->queue(new SendCircularUserMail($data));

                        // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            // 窓口が社内回覧の場合
                            if($author_user->mst_company_id == $windowCircularUser->mst_company_id
                                && $author_user->edition_flg == $windowCircularUser->edition_flg
                                && $author_user->env_flg == $windowCircularUser->env_flg
                                && $author_user->server_flg == $windowCircularUser->server_flg
                                && $circular_updated->access_code_flg === CircularUtils::ACCESS_CODE_VALID){
                                $notice_mail_date['title'] = $title;
                                $notice_mail_date['access_code'] = $circular_updated->access_code;
                                $notice_mail_date['send_to'] = $windowCircularUser->email;
                                $notice_mail_date['send_to_company'] = $windowCircularUser->mst_company_id;
                                $notice_mail_date['parent_send_order'] = $windowCircularUser->parent_send_order;
                                $noticeMailDatas[] = $notice_mail_date;
                            }elseif ($circular_updated->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                && ($author_user->mst_company_id != $windowCircularUser->mst_company_id
                                    || $author_user->edition_flg != $windowCircularUser->edition_flg
                                    || $author_user->env_flg != $windowCircularUser->env_flg
                                    || $author_user->server_flg != $windowCircularUser->server_flg)) {
                                // 窓口が社外の場合
                                $notice_mail_date['title'] = $title;
                                $notice_mail_date['access_code'] = $circular_updated->outside_access_code;
                                $notice_mail_date['send_to'] = $windowCircularUser->email;
                                $notice_mail_date['send_to_company'] = $windowCircularUser->mst_company_id;
                                $notice_mail_date['parent_send_order'] = $windowCircularUser->parent_send_order;
                                $noticeMailDatas[] = $notice_mail_date;
                            }
                        }
                    }
                    } else {
                        foreach ($windowCircularUsers as $windowCircularUser){
                            if (CircularUserUtils::checkAllowReceivedEmail($windowCircularUser->email, 'approval', $windowCircularUser->mst_company_id, $windowCircularUser->env_flg, $windowCircularUser->edition_flg, $windowCircularUser->server_flg)) {
                                $data = [];
                                $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'];
                                // hide_thumbnail_flg 0:表示 1:非表示
                                if (!$circular->hide_thumbnail_flg) {
                                    // thumbnail表示
                                    if ($firstDocument && $firstDocument->confidential_flg
                                        && $firstDocument->origin_edition_flg == $windowCircularUser->edition_flg
                                        && $firstDocument->origin_env_flg == $windowCircularUser->env_flg
                                        && $firstDocument->origin_server_flg == $windowCircularUser->server_flg
                                        && $firstDocument->create_company_id == $windowCircularUser->mst_company_id) {
                                        // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                        $previewPath = AppUtils::getPreviewPagePath($windowCircularUser->edition_flg, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $windowCircularUser->mst_company_id, $windowCircularUser->id);
                                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                        $data['image_path'] = $previewPath;
                                    } else if ($firstDocument && !$firstDocument->confidential_flg) {
                                        // 一ページ目が社外秘ではない
                                        $previewPath = AppUtils::getPreviewPagePath($windowCircularUser->edition_flg, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $windowCircularUser->mst_company_id, $windowCircularUser->id);
                                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                        $data['image_path'] = $previewPath;
                                    } else {
                                        // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                        $data['image_path'] = public_path() . "/images/no-preview.png";
                                    }
                                }
                                $filenames = DB::table('circular_document')
                                    ->where('circular_id', $circular_id)
                                    ->where(function ($query) use ($windowCircularUser) {

                                        $query->where(function ($query0) use ($windowCircularUser) {
                                            // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                            $query0->where('confidential_flg', 0);
                                            $query0->where(function ($query01) use ($windowCircularUser) {
                                                // 回覧終了時：origin_document_id＝0のレコード
                                                $query01->where('origin_document_id', 0);
                                                // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                                $query01->orWhere('parent_send_order', $windowCircularUser->parent_send_order);
                                            });
                                        });

                                        $query->orWhere(function ($query1) use ($windowCircularUser) {
                                            // 社外秘：origin_document_idが-1固定
                                            // 同社メンバー参照可
                                            $query1->where('confidential_flg', 1);
                                            $query1->where('origin_edition_flg', $windowCircularUser->edition_flg);
                                            $query1->where('origin_env_flg', $windowCircularUser->env_flg);
                                            $query1->where('origin_server_flg', $windowCircularUser->server_flg);
                                            $query1->where('create_company_id', $windowCircularUser->mst_company_id);
                                        });
                                    })
                                    ->pluck('file_name');

                                if (!trim($title)) {
                                    $title = $filenames->toArray()[0];
                                }

                                $data['user_name'] = $user_name;
                                $data['circular_id'] = $circular_id;
                                $data['filenames'] = $filenames;
                                if (count($filenames)) {
                                    $data['filenamestext'] = '';
                                    foreach ($filenames as $filename) {
                                        if ($data['filenamestext'] == '') {
                                            $data['filenamestext'] = $filename;
                                            continue;
                                        }
                                        $data['filenamestext'] .= '\r\n' . '　　　　　　' . $filename;
                                    }
                                } else {
                                    $data['filenamestext'] = '';
                                }
                                $data['title'] = trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $author_user->name]);
                                $data['body'] = 'mail.circular_user_template.body';
                                $data['receiver_name'] = $windowCircularUser->name;
                                $data['creator_name'] = $author_user->name;
                                $data['mail_name'] = $title;
                                $data['text'] = $input['text'];
                                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($windowCircularUser->email, $windowCircularUser->edition_flg, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $circular_id);
                                // hide_circular_approval_url false:表示 true:非表示
                                // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                                // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                                $data['hide_circular_approval_url'] = false;
                                if ($hasConfidenceFiles) {
                                    // 社外秘文書ある、
                                    foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo) {
                                        if ($ConfidenceFileInfo['origin_edition_flg'] == $windowCircularUser->edition_flg
                                            && $ConfidenceFileInfo['origin_env_flg'] == $windowCircularUser->env_flg
                                            && $ConfidenceFileInfo['origin_server_flg'] == $windowCircularUser->server_flg
                                            && $ConfidenceFileInfo['create_company_id'] == $windowCircularUser->mst_company_id) {
                                            $data['hide_circular_approval_url'] = true;
                                        }
                                    }
                                }

                                if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                                    $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                                } else {
                                    $data['circular_approval_url_text'] = '';
                                }

                                // check to use SAMl Login URL or not
                                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($windowCircularUser);
                                $data['send_to'] = $windowCircularUser->email;
                                $data['send_to_company'] = $windowCircularUser->mst_company_id;
                                $data['parent_send_order'] = $windowCircularUser->parent_send_order;
                                //data push notify
                                if (config('app.enable_push_notification')
                                    && $windowCircularUser->edition_flg == $system_edition_flg) {
                                    $badgenumber = $this->getNotifyBadgeNumber($windowCircularUser->email, $windowCircularUser->env_flg, $windowCircularUser->server_flg, $windowCircularUser->edition_flg);
                                    $dataNotify = new PushNotify("Shachihata Cloud", "回覧文書が届いています", $windowCircularUser->email, $windowCircularUser->env_flg, $windowCircularUser->server_flg, 1, $badgenumber);
                                    Log::debug('sendNotifyContinue');
                                    $this->dispatch(new SendNotification($dataNotify));
                                }

                                $mailDatas[] = $data;
                                //   Mail::to($circular_user->email)->queue(new SendCircularUserMail($data));

                                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                                if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                                    // 窓口が社内回覧の場合
                                    if ($author_user->mst_company_id == $windowCircularUser->mst_company_id
                                        && $author_user->edition_flg == $windowCircularUser->edition_flg
                                        && $author_user->env_flg == $windowCircularUser->env_flg
                                        && $author_user->server_flg == $windowCircularUser->server_flg
                                        && $circular_updated->access_code_flg === CircularUtils::ACCESS_CODE_VALID) {
                                        $notice_mail_date['title'] = $title;
                                        $notice_mail_date['access_code'] = $circular_updated->access_code;
                                        $notice_mail_date['send_to'] = $windowCircularUser->email;
                                        $notice_mail_date['send_to_company'] = $windowCircularUser->mst_company_id;
                                        $notice_mail_date['parent_send_order'] = $windowCircularUser->parent_send_order;
                                        $noticeMailDatas[] = $notice_mail_date;
                                    } elseif ($circular_updated->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                        && ($author_user->mst_company_id != $windowCircularUser->mst_company_id
                                            || $author_user->edition_flg != $windowCircularUser->edition_flg
                                            || $author_user->env_flg != $windowCircularUser->env_flg
                                            || $author_user->server_flg != $windowCircularUser->server_flg)) {
                                        // 窓口が社外の場合
                                        $notice_mail_date['title'] = $title;
                                        $notice_mail_date['access_code'] = $circular_updated->outside_access_code;
                                        $notice_mail_date['send_to'] = $windowCircularUser->email;
                                        $notice_mail_date['send_to_company'] = $windowCircularUser->mst_company_id;
                                        $notice_mail_date['parent_send_order'] = $windowCircularUser->parent_send_order;
                                        $noticeMailDatas[] = $notice_mail_date;
                                    }
                                }
                            }
                        }
                    }
                }else if($circular_user && $circular_user->id && (!isset($input["isTemplateCircular"]) || !$input["isTemplateCircular"])) {
                    //SEND TO CONTINUE USER NOTIFY
                    // check to copy document
                    if ($next_order_circular_users && $next_isPlan){
                        $arrIds = [];
                        foreach ($next_order_circular_users as $next_order_circular_user) {
                            $arrIds[] = $next_order_circular_user->id;
                        }
                    DB::table('circular_user')
                            ->whereIn('id', $arrIds)
                            ->update([
                                'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                'received_date' => Carbon::now(),
                                'update_at' => Carbon::now(),
                                'update_user' => $login_user->email,
                                'node_flg' => CircularUserUtils::NODE_OTHER
                            ]);
                    }else{
                    DB::table('circular_user')
                        ->where('id', $circular_user->id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at'=> Carbon::now(),
                            'update_user'=> $login_user->email,
                            'node_flg' => CircularUserUtils::NODE_OTHER
                        ]);
                    }

                    if ($next_isPlan && $next_order_circular_users){
                        foreach ($next_order_circular_users as $next_order_circular_user){
                            if ($next_order_circular_user->parent_send_order != $sender_user->parent_send_order) {
                                $hasUpdateDocumentStatus = $this->sendDocument2OtherCompany($circular, $sender_user->parent_send_order, $sender_user->child_send_order, $next_order_circular_user, $sender_user, $isSendbackCircular);

                                if ($hasUpdateDocumentStatus['hasUpdateTransferredDocumentStatus']) {
                                    $this->sendUpdateTransferredDocumentStatus($circular, $sender_user->parent_send_order, $sender_user->child_send_order, $title, $input['text'], $hasUpdateDocumentStatus['hasUpdateTransferredDocumentEnv'], $hasUpdateDocumentStatus['hasUpdateTransferredDocumentServer']);
                                }
                                $next_order_circular_user->circular_status = CircularUserUtils::NOTIFIED_UNREAD_STATUS;
                                if ($hasUpdateDocumentStatus['hasUpdateCurrentAWSDocumentStatus']) {
                                    $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_AWS, false, $isSendbackCircular, $next_order_circular_user);
                                }
                                if ($hasUpdateDocumentStatus['hasUpdateCurrentK5DocumentStatus']) {
                                    $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_K5, false, $isSendbackCircular, $next_order_circular_user);
                                }
                            } else {
                                Log::debug('Same parent_send_order, send update transfer status');
                                if ($sender_user->edition_flg != config('app.edition_flg')) {
                                    if ($sender_user->env_flg == EnvApiUtils::ENV_FLG_AWS) {
                                        Log::debug('Send update transfer status to AWS current edition');
                                        $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_AWS, false, $isSendbackCircular, $next_order_circular_user);
                                    } else {
                                        Log::debug('Send update transfer status to K5 current edition');;
                                        $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_K5, false, $isSendbackCircular, $next_order_circular_user);
                                    }

                                    Log::debug('Send to next circular user on current edition');
                                    $this->sendDocument2OtherEdition($circular, $sender_user->parent_send_order, $sender_user->child_send_order, $next_order_circular_user);
                                } else if ($sender_user->env_flg != config('app.server_env') || $sender_user->server_flg != config('app.server_flg')) {
                                    Log::debug('Send update transfer status to other application in new edition');
                                    $this->sendUpdateTransferredDocumentStatus($circular, $sender_user->parent_send_order, $sender_user->child_send_order, $title, $input['text'], $sender_user->env_flg, $sender_user->server_flg, false);
                                }
                            }
                            break;
                        }
                    }else{
                    if ($circular_user->parent_send_order != $sender_user->parent_send_order){
                        $hasUpdateDocumentStatus = $this->sendDocument2OtherCompany($circular, $sender_user->parent_send_order, $sender_user->child_send_order, $circular_user, $sender_user, $isSendbackCircular);

                        if ($hasUpdateDocumentStatus['hasUpdateTransferredDocumentStatus']){
                            $this->sendUpdateTransferredDocumentStatus($circular, $sender_user->parent_send_order, $sender_user->child_send_order,$title, $input['text'], $hasUpdateDocumentStatus['hasUpdateTransferredDocumentEnv'], $hasUpdateDocumentStatus['hasUpdateTransferredDocumentServer']);
                        }
                        $circular_user->circular_status = CircularUserUtils::NOTIFIED_UNREAD_STATUS;
                        if ($hasUpdateDocumentStatus['hasUpdateCurrentAWSDocumentStatus']){
                            $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_AWS, false, $isSendbackCircular, $circular_user);
                        }
                        if ($hasUpdateDocumentStatus['hasUpdateCurrentK5DocumentStatus']){
                            $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_K5, false, $isSendbackCircular, $circular_user);
                        }
                    }else{
                        Log::debug('Same parent_send_order, send update transfer status');
                        if ($sender_user->edition_flg != config('app.edition_flg')){
                            if ($sender_user->env_flg == EnvApiUtils::ENV_FLG_AWS){
                                Log::debug('Send update transfer status to AWS current edition');
                                $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_AWS, false, $isSendbackCircular, $circular_user);
                            }else{
                                Log::debug('Send update transfer status to K5 current edition');;
                                $this->sendUpdateDocumentStatus2OtherEdition($circular, $sender_user, EnvApiUtils::ENV_FLG_K5, false, $isSendbackCircular, $circular_user);
                            }

                            Log::debug('Send to next circular user on current edition');
                            $this->sendDocument2OtherEdition($circular, $sender_user->parent_send_order, $sender_user->child_send_order, $circular_user);
                        }else if ($sender_user->env_flg != config('app.server_env') || $sender_user->server_flg != config('app.server_flg')){
                            Log::debug('Send update transfer status to other application in new edition');
                            $this->sendUpdateTransferredDocumentStatus($circular, $sender_user->parent_send_order, $sender_user->child_send_order,$title, $input['text'], $sender_user->env_flg, $sender_user->server_flg, false);
                        }
                    }
                    }
                    if ($next_order_circular_users && $next_isPlan){
                        foreach ($next_order_circular_users as $next_order_circular_user) {
                            if (CircularUserUtils::checkAllowReceivedEmail($next_order_circular_user->email, 'approval', $next_order_circular_user->mst_company_id, $next_order_circular_user->env_flg, $next_order_circular_user->edition_flg, $next_order_circular_user->server_flg)) {
                                $data = [];
                                // hide_thumbnail_flg 0:表示 1:非表示
                                if (!$circular->hide_thumbnail_flg) {
                                    // thumbnail表示
                                    if ($firstDocument && $firstDocument->confidential_flg
                                        && $firstDocument->origin_edition_flg == $next_order_circular_user->edition_flg
                                        && $firstDocument->origin_env_flg == $next_order_circular_user->env_flg
                                        && $firstDocument->origin_server_flg == $next_order_circular_user->server_flg
                                        && $firstDocument->create_company_id == $next_order_circular_user->mst_company_id) {
                                        // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                        $previewPath = AppUtils::getPreviewPagePath($next_order_circular_user->edition_flg, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $next_order_circular_user->mst_company_id, $next_order_circular_user->id);
                                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                        $data['image_path'] = $previewPath;
                                    } else if ($firstDocument && !$firstDocument->confidential_flg) {
                                        // 一ページ目が社外秘ではない
                                        $previewPath = AppUtils::getPreviewPagePath($next_order_circular_user->edition_flg, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $next_order_circular_user->mst_company_id, $next_order_circular_user->id);
                                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                        $data['image_path'] = $previewPath;
                                    } else {
                                        // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                        $data['image_path'] = public_path() . "/images/no-preview.png";
                                    }
                                }
                                $filenames = DB::table('circular_document')
                                    ->where('circular_id', $circular_id)
                                    ->where(function ($query) use ($next_order_circular_user) {

                                        $query->where(function ($query0) use ($next_order_circular_user) {
                                            // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                            $query0->where('confidential_flg', 0);
                                            $query0->where(function ($query01) use ($next_order_circular_user) {
                                                // 回覧終了時：origin_document_id＝0のレコード
                                                $query01->where('origin_document_id', 0);
                                                // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                                $query01->orWhere('parent_send_order', $next_order_circular_user->parent_send_order);
                                            });
                                        });

                                        $query->orWhere(function ($query1) use ($next_order_circular_user) {
                                            // 社外秘：origin_document_idが-1固定
                                            // 同社メンバー参照可
                                            $query1->where('confidential_flg', 1);
                                            $query1->where('origin_edition_flg', $next_order_circular_user->edition_flg);
                                            $query1->where('origin_env_flg', $next_order_circular_user->env_flg);
                                            $query1->where('origin_server_flg', $next_order_circular_user->server_flg);
                                            $query1->where('create_company_id', $next_order_circular_user->mst_company_id);
                                        });
                                    })
                                    ->pluck('file_name');

                                if (!trim($title)) {
                                    $title = $filenames->toArray()[0];
                                }

                                $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'];
                                $data['user_name'] = $user_name;
                                $data['circular_id'] = $circular_id;
                                $data['filenames'] = $filenames;
                                if (count($filenames)) {
                                    $data['filenamestext'] = '';
                                    foreach ($filenames as $filename) {
                                        if ($data['filenamestext'] == '') {
                                            $data['filenamestext'] = $filename;
                                            continue;
                                        }
                                        $data['filenamestext'] .= '\r\n' . '　　　　　　' . $filename;
                                    }
                                } else {
                                    $data['filenamestext'] = '';
                                }
                                //$data['title'] = $title . ' - ' . $author_user->name . ' さんの回覧文書が届いています';
                                $data['receiver_name'] = $next_order_circular_user->name;
                                $data['creator_name'] = $author_user->name;
                                $data['mail_name'] = $title;
                                $data['text'] = $input['text'];
                                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($next_order_circular_user->email, $next_order_circular_user->edition_flg, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $circular_id);
                                // hide_circular_approval_url false:表示 true:非表示
                                // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                                // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                                $data['hide_circular_approval_url'] = false;
                                if ($hasConfidenceFiles) {
                                    // 社外秘文書ある、
                                    foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo) {
                                        if ($ConfidenceFileInfo['origin_edition_flg'] == $next_order_circular_user->edition_flg
                                            && $ConfidenceFileInfo['origin_env_flg'] == $next_order_circular_user->env_flg
                                            && $ConfidenceFileInfo['origin_server_flg'] == $next_order_circular_user->server_flg
                                            && $ConfidenceFileInfo['create_company_id'] == $next_order_circular_user->mst_company_id) {
                                            $data['hide_circular_approval_url'] = true;
                                        }
                                    }
                                }

                                if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                                    $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                                } else {
                                    $data['circular_approval_url_text'] = '';
                                }

                                // check to use SAMl Login URL or not
                                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($next_order_circular_user);
                                $data['send_to'] = $next_order_circular_user->email;
                                $data['send_to_company'] = $next_order_circular_user->mst_company_id;
                                $data['parent_send_order'] = $next_order_circular_user->parent_send_order;
                                $data['title'] = trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $author_user->name]);
                                $data['body'] = 'mail.circular_user_template.body';

                                //data push notify
                                if (config('app.enable_push_notification') && $next_order_circular_user->edition_flg == $system_edition_flg) {
                                    $badgenumber = $this->getNotifyBadgeNumber($next_order_circular_user->email, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $next_order_circular_user->edition_flg);
                                    $dataNotify = new PushNotify("Shachihata Cloud", "回覧文書が届いています", $next_order_circular_user->email, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, 1, $badgenumber);
                                    Log::debug('sendNotifyContinue：circular_user.idあり');
                                    $this->dispatch(new SendNotification($dataNotify));
                                }

                                $mailDatas[] = $data;
                                //   Mail::to($circular_user->email)->queue(new SendCircularUserMail($data));

                                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                                if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                                    // 次の回覧者が社内回覧の場合
                                    if ($author_user->mst_company_id == $next_order_circular_user->mst_company_id
                                        && $author_user->edition_flg == $next_order_circular_user->edition_flg
                                        && $author_user->env_flg == $next_order_circular_user->env_flg
                                        && $author_user->server_flg == $next_order_circular_user->server_flg
                                        && $circular_updated->access_code_flg === CircularUtils::ACCESS_CODE_VALID) {
                                        $notice_mail_date['title'] = $title;
                                        $notice_mail_date['access_code'] = $circular_updated->access_code;
                                        $notice_mail_date['send_to'] = $next_order_circular_user->email;
                                        $notice_mail_date['send_to_company'] = $next_order_circular_user->mst_company_id;
                                        $noticeMailDatas[] = $notice_mail_date;
                                    } elseif ($circular_updated->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                        && ($author_user->mst_company_id != $next_order_circular_user->mst_company_id
                                            || $author_user->edition_flg != $next_order_circular_user->edition_flg
                                            || $author_user->env_flg != $next_order_circular_user->env_flg
                                            || $author_user->server_flg != $next_order_circular_user->server_flg)) {
                                        // 窓口が社外の場合
                                        $notice_mail_date['title'] = $title;
                                        $notice_mail_date['access_code'] = $circular_updated->outside_access_code;
                                        $notice_mail_date['send_to'] = $next_order_circular_user->email;
                                        $notice_mail_date['send_to_company'] = $next_order_circular_user->mst_company_id;
                                        $noticeMailDatas[] = $notice_mail_date;
                                    }
                                }
                            }
                        }
                    }else{
                    if(CircularUserUtils::checkAllowReceivedEmail($circular_user->email, 'approval',$circular_user->mst_company_id,$circular_user->env_flg,$circular_user->edition_flg,$circular_user->server_flg)) {
                        $data = [];
                        // hide_thumbnail_flg 0:表示 1:非表示
                        if (!$circular->hide_thumbnail_flg) {
                            // thumbnail表示
                            if ($firstDocument && $firstDocument->confidential_flg
                                && $firstDocument->origin_edition_flg == $circular_user->edition_flg
                                && $firstDocument->origin_env_flg == $circular_user->env_flg
                                && $firstDocument->origin_server_flg == $circular_user->server_flg
                                && $firstDocument->create_company_id == $circular_user->mst_company_id){
                                // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else if ($firstDocument && !$firstDocument->confidential_flg){
                                // 一ページ目が社外秘ではない
                                $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                                file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                $data['image_path'] = $previewPath;
                            }else{
                                // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                $data['image_path'] = public_path()."/images/no-preview.png";
                            }
                        }
                        $filenames = DB::table('circular_document')
                            ->where('circular_id',$circular_id)
                            ->where(function($query) use ($circular_user){

                                $query->where(function ($query0) use ($circular_user){
                                    // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                    $query0->where('confidential_flg', 0);
                                    $query0->where(function ($query01) use ($circular_user){
                                        // 回覧終了時：origin_document_id＝0のレコード
                                        $query01->where('origin_document_id', 0);
                                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                        $query01->orWhere('parent_send_order', $circular_user->parent_send_order);
                                    });
                                });

                                $query->orWhere(function($query1) use ($circular_user){
                                    // 社外秘：origin_document_idが-1固定
                                    // 同社メンバー参照可
                                    $query1->where('confidential_flg', 1);
                                    $query1->where('origin_edition_flg', $circular_user->edition_flg);
                                    $query1->where('origin_env_flg', $circular_user->env_flg);
                                    $query1->where('origin_server_flg', $circular_user->server_flg);
                                    $query1->where('create_company_id', $circular_user->mst_company_id);
                                });
                            })
                            ->pluck('file_name');

                        if (!trim($title)) {
                            $title = $filenames->toArray()[0];
                        }

                        $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'];
                        $data['user_name'] = $user_name;
                        $data['circular_id'] =$circular_id;
                        $data['filenames'] = $filenames;
                        if(count($filenames)){
                            $data['filenamestext'] = '';
                            foreach($filenames as $filename){
                                if ($data['filenamestext'] == '') {
                                    $data['filenamestext'] = $filename;
                                    continue;
                                }
                                $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                            }
                        }else{
                            $data['filenamestext'] = '';
                        }

                        $data['receiver_name'] = $circular_user->name;
                        $data['creator_name'] = $author_user->name;
                        $data['mail_name'] = $title;
						$data['text'] = $input['text'];
                        $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                        $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_id);
                        // hide_circular_approval_url false:表示 true:非表示
                        // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                        // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                        $data['hide_circular_approval_url'] = false;
                        if($hasConfidenceFiles){
                            // 社外秘文書ある、
                            foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                                if($ConfidenceFileInfo['origin_edition_flg'] == $circular_user->edition_flg
                                    && $ConfidenceFileInfo['origin_env_flg'] == $circular_user->env_flg
                                    && $ConfidenceFileInfo['origin_server_flg'] == $circular_user->server_flg
                                    && $ConfidenceFileInfo['create_company_id'] == $circular_user->mst_company_id){
                                    $data['hide_circular_approval_url'] = true;
                                }
                            }
                        }

                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                        }else{
                            $data['circular_approval_url_text'] = '';
                        }

                        // check to use SAMl Login URL or not
                        $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($circular_user);
                        $data['send_to'] = $circular_user->email;
                        $data['send_to_company'] = $circular_user->mst_company_id;
                        $data['parent_send_order'] = $circular_user->parent_send_order;
                        $data['title'] = trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $author_user->name]);
                        $data['body'] = 'mail.circular_user_template.body';

                        //data push notify
                        if (config('app.enable_push_notification') && $circular_user->edition_flg == $system_edition_flg){
                            $badgenumber = $this->getNotifyBadgeNumber($circular_user->email, $circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg);
                            $dataNotify = new PushNotify("Shachihata Cloud", "回覧文書が届いています", $circular_user->email, $circular_user->env_flg, $circular_user->server_flg, 1, $badgenumber);
                            Log::debug('sendNotifyContinue：circular_user.idあり');
                            $this->dispatch(new SendNotification($dataNotify));
                        }

                        $mailDatas[] = $data;
                        //   Mail::to($circular_user->email)->queue(new SendCircularUserMail($data));

                        // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                        if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                            // 次の回覧者が社内回覧の場合
                            if($author_user->mst_company_id == $circular_user->mst_company_id
                                && $author_user->edition_flg == $circular_user->edition_flg
                                && $author_user->env_flg == $circular_user->env_flg
                                && $author_user->server_flg == $circular_user->server_flg
                                && $circular_updated->access_code_flg === CircularUtils::ACCESS_CODE_VALID){
                                $notice_mail_date['title'] = $title;
                                $notice_mail_date['access_code'] = $circular_updated->access_code;
                                $notice_mail_date['send_to'] = $circular_user->email;
                                $notice_mail_date['send_to_company'] = $circular_user->mst_company_id;
                                $noticeMailDatas[] = $notice_mail_date;
                            }elseif ($circular_updated->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                && ($author_user->mst_company_id != $circular_user->mst_company_id
                                    || $author_user->edition_flg != $circular_user->edition_flg
                                    || $author_user->env_flg != $circular_user->env_flg
                                    || $author_user->server_flg != $circular_user->server_flg )) {
                                // 窓口が社外の場合
                                $notice_mail_date['title'] = $title;
                                $notice_mail_date['access_code'] = $circular_updated->outside_access_code;
                                $notice_mail_date['send_to'] = $circular_user->email;
                                $notice_mail_date['send_to_company'] = $circular_user->mst_company_id;
                                $noticeMailDatas[] = $notice_mail_date;
                            }
                        }
                    }
                    }
                }else if($next_order_circular_users && ((isset($input["isTemplateCircular"]) && $input["isTemplateCircular"]))){
                    $arrIds = [];
                    foreach ($next_order_circular_users as $next_order_circular_user){
                        $arrIds[] = $next_order_circular_user->id;
                }
                    DB::table('circular_user')
                        ->whereIn('id', $arrIds)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at'=> Carbon::now(),
                            'update_user'=> $login_user->email,
                            'node_flg' => CircularUserUtils::NODE_OTHER
                        ]);
                    foreach($next_order_circular_users as $next_order_circular_user){
                        if(CircularUserUtils::checkAllowReceivedEmail($next_order_circular_user->email, 'approval',$next_order_circular_user->mst_company_id,$next_order_circular_user->env_flg,$next_order_circular_user->edition_flg,$next_order_circular_user->server_flg)) {
                            $data = [];
                            // hide_thumbnail_flg 0:表示 1:非表示
                            if (!$circular->hide_thumbnail_flg) {
                                // thumbnail表示
                                if ($firstDocument && $firstDocument->confidential_flg
                                    && $firstDocument->origin_edition_flg == $next_order_circular_user->edition_flg
                                    && $firstDocument->origin_env_flg == $next_order_circular_user->env_flg
                                    && $firstDocument->origin_server_flg == $next_order_circular_user->server_flg
                                    && $firstDocument->create_company_id == $next_order_circular_user->mst_company_id){
                                    // 一ページ目が社外秘　＋　upload会社＝宛先会社
                                    $previewPath = AppUtils::getPreviewPagePath($next_order_circular_user->edition_flg, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $next_order_circular_user->mst_company_id, $next_order_circular_user->id);
                                    file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                    $data['image_path'] = $previewPath;
                                }else if ($firstDocument && !$firstDocument->confidential_flg){
                                    // 一ページ目が社外秘ではない
                                    $previewPath = AppUtils::getPreviewPagePath($next_order_circular_user->edition_flg, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $next_order_circular_user->mst_company_id, $next_order_circular_user->id);
                                    file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                                    $data['image_path'] = $previewPath;
                                }else{
                                    // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                                    $data['image_path'] = public_path()."/images/no-preview.png";
            }
            }
                            $filenames = DB::table('circular_document')
                                ->where('circular_id',$circular_id)
                                ->where(function($query) use ($circular_user){

                                    $query->where(function ($query0) use ($circular_user){
                                        // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                                        $query0->where('confidential_flg', 0);
                                        $query0->where(function ($query01) use ($circular_user){
                                            // 回覧終了時：origin_document_id＝0のレコード
                                            $query01->where('origin_document_id', 0);
                                            // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                            $query01->orWhere('parent_send_order', $circular_user->parent_send_order);
                                        });
                                    });

                                    $query->orWhere(function($query1) use ($circular_user){
                                        // 社外秘：origin_document_idが-1固定
                                        // 同社メンバー参照可
                                        $query1->where('confidential_flg', 1);
                                        $query1->where('origin_edition_flg', $circular_user->edition_flg);
                                        $query1->where('origin_env_flg', $circular_user->env_flg);
                                        $query1->where('origin_server_flg', $circular_user->server_flg);
                                        $query1->where('create_company_id', $circular_user->mst_company_id);
                                    });
                                })
                                ->pluck('file_name');

                            if (!trim($title)) {
                                $title = $filenames->toArray()[0];
                            }

                            $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ARRIVED_NOTIFY']['CODE'];
                            $data['user_name'] = $user_name;
                            $data['circular_id'] =$circular_id;
                            $data['filenames'] = $filenames;
                            if(count($filenames)){
                                $data['filenamestext'] = '';
                                foreach($filenames as $filename){
                                    if ($data['filenamestext'] == '') {
                                        $data['filenamestext'] = $filename;
                                        continue;
                                    }
                                    $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                                }
                            }else{
                                $data['filenamestext'] = '';
                            }
                            //$data['title'] = $title . ' - ' . $author_user->name . ' さんの回覧文書が届いています';
                            $data['receiver_name'] = $next_order_circular_user->name;
                            $data['creator_name'] = $author_user->name;
                            $data['mail_name'] = $title;
                            $data['text'] = $input['text'];
                            $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                            $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($next_order_circular_user->email, $next_order_circular_user->edition_flg, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, $circular_id);
                            // hide_circular_approval_url false:表示 true:非表示
                            // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                            // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                            $data['hide_circular_approval_url'] = false;
                            if($hasConfidenceFiles){
                                // 社外秘文書ある、
                                foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                                    if($ConfidenceFileInfo['origin_edition_flg'] == $next_order_circular_user->edition_flg
                                        && $ConfidenceFileInfo['origin_env_flg'] == $next_order_circular_user->env_flg
                                        && $ConfidenceFileInfo['origin_server_flg'] == $next_order_circular_user->server_flg
                                        && $ConfidenceFileInfo['create_company_id'] == $next_order_circular_user->mst_company_id){
                                        $data['hide_circular_approval_url'] = true;
                                    }
                                }
                            }

                            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                                $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                            }else{
                                $data['circular_approval_url_text'] = '';
                            }

                            // check to use SAMl Login URL or not
                            $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($next_order_circular_user);
                            $data['send_to'] = $next_order_circular_user->email;
                            $data['send_to_company'] = $next_order_circular_user->mst_company_id;
                            $data['parent_send_order'] = $next_order_circular_user->parent_send_order;
                            $data['title'] = trans('mail.circular_user_template.subject', ['title' => $title, 'author_user' => $author_user->name]);
                            $data['body'] = 'mail.circular_user_template.body';

                            //data push notify
                            if (config('app.enable_push_notification') && $next_order_circular_user->edition_flg == $system_edition_flg){
                                $badgenumber = $this->getNotifyBadgeNumber($circular_user->email, $circular_user->env_flg, $circular_user->server_flg, $circular_user->edition_flg);
                                $dataNotify = new PushNotify("Shachihata Cloud", "回覧文書が届いています", $next_order_circular_user->email, $next_order_circular_user->env_flg, $next_order_circular_user->server_flg, 1, $badgenumber);
                                Log::debug('sendNotifyContinue：circular_user.idあり');
                                $this->dispatch(new SendNotification($dataNotify));
                            }

                            $mailDatas[] = $data;
                            //   Mail::to($circular_user->email)->queue(new SendCircularUserMail($data));

                            // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                                // 次の回覧者が社内回覧の場合
                                if($author_user->mst_company_id == $next_order_circular_user->mst_company_id
                                    && $author_user->edition_flg == $next_order_circular_user->edition_flg
                                    && $author_user->env_flg == $next_order_circular_user->env_flg
                                    && $author_user->server_flg == $next_order_circular_user->server_flg
                                    && $circular_updated->access_code_flg === CircularUtils::ACCESS_CODE_VALID){
                                    $notice_mail_date['title'] = $title;
                                    $notice_mail_date['access_code'] = $circular_updated->access_code;
                                    $notice_mail_date['send_to'] = $next_order_circular_user->email;
                                    $notice_mail_date['send_to_company'] = $next_order_circular_user->mst_company_id;
                                    $noticeMailDatas[] = $notice_mail_date;
                                }elseif ($circular_updated->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                    && ($author_user->mst_company_id != $next_order_circular_user->mst_company_id
                                        || $author_user->edition_flg != $next_order_circular_user->edition_flg
                                        || $author_user->env_flg != $next_order_circular_user->env_flg
                                        || $author_user->server_flg != $next_order_circular_user->server_flg )) {
                                    // 窓口が社外の場合
                                    $notice_mail_date['title'] = $title;
                                    $notice_mail_date['access_code'] = $circular_updated->outside_access_code;
                                    $notice_mail_date['send_to'] = $next_order_circular_user->email;
                                    $notice_mail_date['send_to_company'] = $next_order_circular_user->mst_company_id;
                                    $noticeMailDatas[] = $notice_mail_date;
                                }
                            }
                        }
                    }
                }
            }
            if(true == $boolSikpCurrentHandler){
                $this->handlerSkipMail($arrSendSkipMailUser,$user_name,$login_user);
            }
            if(isset($input['operationNotice']) && $input['operationNotice']){
                DB::table('mst_user_info')
                ->where('mst_user_id',$login_user->id)
                ->update(['operation_notice_flg' => CircularUserUtils::DEFAULT_OPERATION_NOTICE_FLG]);
            }

            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        // summaryCircularForCompleted if the circular finish
        if ($is_circular_completed){
            $this->summaryCircularForCompleted($circular_id);
        }else{
            CircularUserUtils::summaryInProgressCircular($circular_id);
        }

        //特設サイト 提出側回覧状態変更
        if ($circular->special_site_flg){
            $circular_users = DB::table('circular_user')->where('circular_id',$circular_id)->orderBy('id')->get();
            $circular = DB::table('circular')
                ->select('id','circular_status','completed_date','final_updated_date','edition_flg','env_flg','server_flg')
                ->where('id',$circular_id)->first();
            $client = EnvApiUtils::getAuthorizeClient($author_user->env_flg, $author_user->server_flg);
            if (!$client){
                throw new \Exception('Cannot connect to Env Api');
            }
            $response = $client->post("updateSpecialSiteUserStatus",[
                RequestOptions::JSON => [
                    'circular' => $circular,
                    'circular_users' => $circular_users,
                ]
            ]);
            if ( $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                Log::error($response->getBody());
                throw new \Exception('提出環境の回覧状態更新に失敗しました');
            }
        }

        if (count($mailDatas)){
            try{
                $hantei=1;
                $emails=array();
                $times=0;
                foreach ($mailDatas as $data){
                    $email = $data['send_to'];
                    unset($data['send_to']);
                    $send_to_company = $data['send_to_company'];
                    unset($data['send_to_company']);

                    if(count($data['filenames'])){
                        $data['filenamestext'] = '';
                        foreach($data['filenames'] as $filename){
                            if ($data['filenamestext'] == '') {
                                $data['filenamestext'] = $filename;
                                continue;
                            }
                            $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                        }
                    }else{
                        $data['filenamestext'] = '';
                    }

                    $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                    unset($data['filenames']);

                    $hantei=1;

                    if($times>0){
                        if(in_array("$email",$emails)){
                                $hantei=0;
                        }
                    }


                    if($hantei==1){

                        $emails[$times]=$email;
                        $times++;
                        MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                            $email,
                            // メールテンプレート
                            $data['code'],
                            // パラメータ
                            $param,
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.user') . $data['title'],
                            // メールボディ
                            trans($data['body'], $data)
                        );

                    }
                }

				// PAC_5-445 アクセスコードが設定されている場合、アクセスコード通知メール（MAPP0012）を次の宛先に送信する。
                $hantei=1;
                $emails_code=array();
                $times=0;
				if(count($noticeMailDatas)){
					foreach ($noticeMailDatas as $data){
						$email = $data['send_to'];
						unset($data['send_to']);
                        $send_to_company = $data['send_to_company'];
                        unset($data['send_to_company']);

                        $hantei=1;

                        if($times>0){
                            if(in_array("$email",$emails_code)){
                                    $hantei=0;
                            }
                        }

                        if($hantei==1){

                            $emails_code[$times]=$email;
                            $times++;
                            //利用者:アクセスコードのお知らせ
                            MailUtils::InsertMailSendResume(
                                // 送信先メールアドレス
                                $email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($data,JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $title]),
                                // メールボディ
                                trans('mail.SendAccessCodeNoticeMail.body', $data)
                            );
                        }
					}
				}
            }catch(\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
            }
        }
        /*PAC_5-2418 S*/
        if ($is_circular_completed) {
            $circularUsers = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->get();
            $circular = DB::table('circular')
                ->where('id', $circular_id)
                ->first();
            // 回覧状態を更新
            foreach ($circularUsers as $circularUser) {
                // クロス環境ファイルかどうかを判別します
                if ($circularUser->edition_flg == config('app.edition_flg') && ($circularUser->env_flg != config('app.server_env') || $circularUser->server_flg != config('app.server_flg'))) {
                    // クロス環境
                    Log::info('別環境の文書状態を更新。circular_id:' . $circular->id . 'circular_user id:' . $circularUser->id);
                    $this->sendUpdateTransferredDocumentStatus($circular, $last_circular_user->parent_send_order, $last_circular_user->child_send_order, $title, $input['text'], $circularUser->env_flg, $circularUser->server_flg);
                }
            }
        }
        /*PAC_5-2418 E*/
        return $this->sendResponse(["is_circular_completed" => $is_circular_completed], '通知メールを送信しました。');
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
        /** @var CircularUser $circularUser */
        $circularUser = $this->circularUserRepository->find($id);
        if (empty($circularUser)) {
            return $this->sendError('Circular User not found');
        }

        return $this->sendResponse($circularUser->toArray(), 'Circular User retrieved successfully');
    }

    /**
     * Update the specified CircularUser in storage.
     * PUT/PATCH /circularUsers/{id}
     *
     * @param int $id
     * @param UpdateCircularUserAPIRequest $request
     *
     * @return Response
     */
    public function update($circular_id,$id, UpdateCircularUserAPIRequest $request)
    {
        try{
        $input = $request->all();
        $input['circular_id'] = $circular_id;
        if(!$input['title']) {
            $input['title'] = '';
        }
        if(!$input['text']) {
            $input['text'] = '';
        }
        if(array_key_exists('usingHash', $input)) {
            unset($input['usingHash']);
        }
            DB::beginTransaction();
        /** @var CircularUser $circularUser */
        $circularUser = $this->circularUserRepository->find($id);

        if (empty($circularUser)) {
                DB::rollBack();
            return $this->sendError('ユーザーが見つかりません。');
        }
            if ($circularUser->plan_id > 0) {
                unset($input['email']);
                DB::table('circular_user')
                    ->where('plan_id', '=', $circularUser->plan_id)
                    ->update($input);
                DB::table('circular_user_plan')
                    ->where('id','=', $circularUser->plan_id)
                    ->update([
                        'child_send_order'=>$input['child_send_order']
                    ]);
            } else {
        $circularUser = $this->circularUserRepository->update($input, $id);
            }
        return $this->sendResponse($circularUser->toArray(), '回覧ユーザーの更新処理に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('ユーザーが見つかりません。');
    }
    }


    /**
     * Update the multiple CircularUser of circular in storage.
     * PUT /circularUsers/updates
     *
     * @param UpdateMultipleCircularUserAPIRequest $request
     *
     * @return Response
     */
    public function updates(UpdateMultipleCircularUserAPIRequest $request, $circular_id)
    {
        try {
            $input = $request->all();
            $login_user = $request->user();

            if(!$login_user || !$login_user->id) {
                $login_user = $request['user'];
            }

            $circularUsers = $input['circular_users'];

            DB::beginTransaction();
            $otherEnv = config('app.server_env');
            $otherServer = config('app.server_flg');

            $validCircularUserIds = [];
            foreach ($circularUsers as $circularUser) {
                if (isset($circularUser['id'])) {
                    $validCircularUserIds[] = $circularUser['id'];
                }
            }

            if (count($validCircularUserIds)){
                $validCircularUserIds = DB::table('circular_user')->where('circular_id', $circular_id)->whereIn('id', $validCircularUserIds)->pluck('id')->toArray();
            }

            $updateCircularUsers = [];
            foreach ($circularUsers as $circularUser) {
                if (isset($circularUser['id']) && $circularUser['id'] && in_array($circularUser['id'], $validCircularUserIds)){
                    $id = $circularUser['id'];
                    unset($circularUser['id']);
                    if(!$circularUser['title']){
                        $circularUser['title'] = '';
                    }
                    // PAC_5-2011  重複したユーザのステータスを正確に取りたい 承認者B（1回目）は「承認（捺印あり）」承認者B（2回目）は「承認（捺印なし）」としたい
                    // $circularUser['stamp_flg'] = 0     承認（捺印なし   $circularUser['stamp_flg'] = Number (>1)   捺印あり  ;
                    $circularUser['update_at'] = Carbon::now();
                    $circularUser['update_user'] = $login_user->email;
                    unset($circularUser['text']);
                    if ($circularUser['edition_flg'] == config('app.edition_flg')
                        && ($circularUser['env_flg'] != config('app.server_env') || $circularUser['server_flg'] != config('app.server_flg'))
                        && $circularUser['circular_status'] != CircularUserUtils::NOT_NOTIFY_STATUS){
                        $otherEnv = $circularUser['env_flg'];
                        $otherServer = $circularUser['server_flg'];
                    }
                    $updatedCircularUser = $this->circularUserRepository->update($circularUser, $id);
                    $updateCircularUsers[] = [
                        "email" => $updatedCircularUser->email,
                        "parent_send_order" => $updatedCircularUser->parent_send_order,
                        "child_send_order" => $updatedCircularUser->child_send_order,
                        "env_flg" => $updatedCircularUser->env_flg,
                        "edition_flg" => $updatedCircularUser->edition_flg,
                        "server_flg" => $updatedCircularUser->server_flg,
                        "mst_company_id" => $updatedCircularUser->mst_company_id,
                        "mst_company_name" => $updatedCircularUser->mst_company_name,
                        "mst_user_id" => $updatedCircularUser->mst_user_id,
                        "name" => $updatedCircularUser->name,
                        "return_flg" => $updatedCircularUser->return_flg,
                        "circular_status" => $updatedCircularUser->circular_status,
                        "received_date" => $updatedCircularUser->received_date,
                        "sent_date" => $updatedCircularUser->sent_date,
                        "title" => $updatedCircularUser->title,
                        "origin_circular_url" => CircularUtils::generateApprovalUrl($updatedCircularUser->email, $updatedCircularUser->edition_flg, $updatedCircularUser->env_flg, $updatedCircularUser->server_flg, $updatedCircularUser->circular_id) . CircularUtils::encryptOutsideAccessCode($id),
                    ];
                }

            }
            if (($otherEnv != config('app.server_env') || $otherServer != config('app.server_flg')) && count($updateCircularUsers)){
                Log::debug('sync new circular to other application');

                $circular = DB::table('circular')->where('id', $circular_id)->select('env_flg', 'edition_flg', 'server_flg')->first();

                $transferredCircularUser = ['origin_circular_id' => $circular_id,
                    'env_flg' => $circular->env_flg,
                    'edition_flg' => $circular->edition_flg,
                    'server_flg' => $circular->server_flg,
                    'update_user' => $login_user->email,
                    'update_circular_users' => $updateCircularUsers
                ];

                $envClient = EnvApiUtils::getAuthorizeClient($otherEnv,$otherServer);
                if (!$envClient){
                    //TODO message
                    throw new \Exception('Cannot connect to Env Api');
                }

                $response = $envClient->put("circularUsers/updatesTransferred",[
                    RequestOptions::JSON => $transferredCircularUser
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::error('Cannot store circular user');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot store circular user');
                }
            }

            DB::commit();
            return $this->sendSuccess('回覧ユーザーの更新処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the multiple transferred CircularUser of circular in storage.
     * PUT /circularUsers/updatesTransferred
     *
     * @param UpdateTransferredCircularUserAPIRequest $request
     *
     * @return Response
     */
    public function updatesTransferred(UpdateTransferredCircularUserAPIRequest $request)
    {
        try {
            $update_user = $request['update_user'];

            DB::beginTransaction();

            $existCircular = DB::table('circular')->where('origin_circular_id', $request['origin_circular_id'])
                ->where('env_flg', $request['env_flg'])
                ->where('edition_flg', $request['edition_flg'])
                ->where('server_flg', $request['server_flg'])
                ->first();
            if ($existCircular){
                $circular_id = $existCircular->id;
                if (isset($request['new_circular_users']) && $request['new_circular_users']){
                    $newCircularUsers = [];
                    foreach($request['new_circular_users'] as $new_circular_user){
                        $newCircularUser = $new_circular_user;
                        $newCircularUser['circular_id'] = $circular_id;
                        $newCircularUser['title'] = isset($new_circular_user['title']) && $new_circular_user['title']?$new_circular_user['title']:'';
                        $newCircularUser['del_flg'] = 0;
                        $newCircularUser['create_at'] = Carbon::now();
                        $newCircularUser['create_user'] = $update_user;
                        $newCircularUsers[] = $newCircularUser;
                    }

                    if (count($newCircularUsers)){
                        DB::table('circular_user')->insert($newCircularUsers);
                    }
                }
                if (isset($request['remove_circular_users']) && $request['remove_circular_users']){
                    foreach($request['remove_circular_users'] as $remove_circular_user){
                        // TODO improve performance
                        DB::table('circular_user')->where('circular_status', CircularUserUtils::NOT_NOTIFY_STATUS)
                            ->where('circular_id', $circular_id)
                            ->where('parent_send_order', $remove_circular_user['parent_send_order'])
                            ->where('child_send_order', $remove_circular_user['child_send_order'])
                            ->delete();

                        $this->reBuildCircularUserAfterDelete($circular_id, $remove_circular_user['parent_send_order'], $remove_circular_user['child_send_order']);
                    }
                }
                if (isset($request['update_circular_users']) && $request['update_circular_users']){
                    foreach($request['update_circular_users'] as $update_circular_user){
                        // TODO improve performance
                        $updateCircularUser = $update_circular_user;
                        $updateCircularUser['title'] = isset($update_circular_user['title']) && $update_circular_user['title']?$update_circular_user['title']:'';
                        $updateCircularUser['update_at'] = Carbon::now();
                        $updateCircularUser['update_user'] = $update_user;
                        if (isset($updateCircularUser['received_date']) && $updateCircularUser['received_date']){
                            $updateCircularUser['received_date'] = Carbon::parse($updateCircularUser['received_date'])->format('Y-m-d H:i:s');
                        }
                        if (isset($updateCircularUser['sent_date']) && $updateCircularUser['sent_date']){
                            $updateCircularUser['sent_date'] = Carbon::parse($updateCircularUser['sent_date'])->format('Y-m-d H:i:s');
                        }
                        DB::table('circular_user')->where('circular_id', $circular_id)->where('parent_send_order', $update_circular_user['parent_send_order'])
                            ->where('child_send_order', $update_circular_user['child_send_order'])->update($updateCircularUser);
                    }
                }
            }else{
                $this->sendError('Circular invalid', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            DB::commit();
            return $this->sendSuccess('回覧ユーザーの更新処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function reBuildCircularUserAfterDelete($circularId, $deletedParentSendOrder, $deletedChildSendOrder){
        DB::table('circular_user')
            ->where('circular_id', $circularId)
            ->where('parent_send_order', $deletedParentSendOrder)
            ->where('child_send_order','>', $deletedChildSendOrder)
            ->update([
                'child_send_order' => DB::raw( 'child_send_order - 1')
            ]);

        if($deletedParentSendOrder > 0 && $deletedChildSendOrder == 1) {
            $count = DB::table('circular_user')
                ->where('circular_id', $circularId)
                ->where('parent_send_order', '=', $deletedParentSendOrder)
                ->count();
            if ($count === 0){
                $previousCircularUser = DB::table('circular_user')
                    ->where('circular_id', $circularId)
                    ->where('parent_send_order', '=', $deletedParentSendOrder - 1)
                    ->orderByDesc('child_send_order')
                    ->first();
                $nextCircularUser = DB::table('circular_user')
                    ->where('circular_id', $circularId)
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
                    DB::table('circular_user')
                        ->where('circular_id', $circularId)
                        ->where('parent_send_order', '=', $deletedParentSendOrder + 1)
                        ->update([
                            'child_send_order' => DB::raw('child_send_order + '.$previousCircularUser->child_send_order)
                        ]);
                    DB::table('circular_user')
                        ->where('circular_id', $circularId)
                        ->where('parent_send_order', '>', $deletedParentSendOrder)
                        ->update([
                            'parent_send_order' => DB::raw('parent_send_order - 2')
                        ]);
                }else{
                    DB::table('circular_user')
                        ->where('circular_id', $circularId)
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
            /** @var CircularUser $circularUser */
            $circularUser = $this->circularUserRepository->find($id);

            if (empty($circularUser)) {
                DB::rollBack();
                return $this->sendError('ユーザーが見つかりません。');
            }
            if ($circularUser->plan_id > 0) {
                DB::table('circular_user')
                    ->where('plan_id', '=', $circularUser->plan_id)
                    ->delete();
                DB::table('circular_user_plan')
                    ->where('circular_id', '=', $circular_id)
                    ->where('id', '=', $circularUser->plan_id)
                    ->delete();
            } else {
            $circularUser->delete();
            }

            $this->reBuildCircularUserAfterDelete($circularUser->circular_id, $circularUser->parent_send_order, $circularUser->child_send_order);

            if ($circularUser->edition_flg == config('app.edition_flg')
                && ($circularUser->env_flg != config('app.server_env') || $circularUser->server_flg != config('app.server_flg'))){
                Log::debug('sync new circular to other application: check current_circular_user');
                if (isset($request['current_circular_user'])){
                    $currentCircularUser = $request['current_circular_user'];
                    if ($currentCircularUser->edition_flg == config('app.edition_flg')
                        && ($currentCircularUser->env_flg != config('app.server_env') || $currentCircularUser->server_flg != config('app.server_flg'))){
                        Log::debug('sync new circular to other application');

                        $circular = DB::table('circular')->select('edition_flg', 'env_flg', 'server_flg')->where('id',  $request['current_circular'])->first();
                        $transferredCircularUser = ['origin_circular_id' => $request['current_circular'],
                            'env_flg' => $circular->env_flg,
                            'edition_flg' => $circular->edition_flg,
                            'server_flg' => $circular->server_flg,
                            'update_user' => $request['current_email'],
                            'remove_circular_users' => [[
                                "parent_send_order" => $circularUser->parent_send_order,
                                "child_send_order" => $circularUser->child_send_order,
                            ]]
                        ];

                        $envClient = EnvApiUtils::getAuthorizeClient($circularUser->env_flg,$circularUser->server_flg);
                        if (!$envClient){
                            //TODO message
                            throw new \Exception('Cannot connect to Env Api');
                        }

                        $response = $envClient->put("circularUsers/updatesTransferred",[
                            RequestOptions::JSON => $transferredCircularUser
                        ]);
                        if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                            Log::error('Cannot store circular user');
                            Log::error($response->getBody());
                            throw new \Exception('Cannot store circular user');
                        }
                    }
                }
            }
            DB::commit();
            $circular_users = DB::table('circular_user')
                ->where('circular_id', $circularUser->circular_id)
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
    public function clear($circular_id,ClearCircularUserRequest $request)
    {
        try {

            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('child_send_order', '!=', 0)
                ->delete();

            // 合議の場合
            DB::table('circular_user_routes')
                ->where('circular_id', $circular_id)
                ->delete();

            DB::table('circular_user_plan')
                ->where('circular_id', $circular_id)
                ->delete();
            return $this->sendSuccess('削除処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 送信一覧リスト画面初期化
     *
     * @param SearchCircularUserAPIRequest $request
     * @return mixed
     */
    public function indexSent(SearchCircularUserAPIRequest $request){
        $user       = $request->user();

        $filename   = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $userName   = $request->get('userName');
        $userEmail  = $request->get('userEmail');
        $fromdate   = $request->get('fromdate');
        $todate     = $request->get('todate');
        $destEnv    = $request->get('destEnv');
        $status     = $request->get('status', false);
        $page       = $request->get('page', 1);
        $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy    = $request->get('orderBy', "update_at");
        $orderDir   =  AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword   = CircularDocumentUtils::charactersReplace($request->get('keyword'));

        $arrOrder   = ['title' => 'title','emails' => 'emails', 'C.access_code' => 'C.access_code',
            'update_at' => 'update_at', 'C.circular_status' => 'C.circular_status'];
        $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';

        $where = ["C.mst_user_id = $user->id " ];
        $where_arg = [];

        if($filename){
            $where[]        = '(T.title like ? OR ((T.title IS NULL OR trim(T.title)=\'\') and T.receiver_title like ?))';
            $where_arg[]    = "%$filename%";
            $where_arg[]    = "%$filename%";
        }
        if($userName){
            $where[]        = 'U.name like ?';
            $where_arg[]    = "%$userName%";
        }
        if($userEmail){
            $where[]        = 'U.email like ?';
            $where_arg[]    = "%$userEmail%";
        }
        if($fromdate){
            $where[]        = 'T.sent_date >= ?';
            $where_arg[]    = date($fromdate).' 00:00:00';
        }
        if($todate){
            $where[]        = 'T.sent_date <= ?';
            $where_arg[]    = date($todate).' 23:59:59';
        }
        if ($destEnv) {
            $destenv_flgs = str_split($destEnv);
            $where[]        = 'U.edition_flg = ?';
            $where_arg[]    = $destenv_flgs[0];
            $where[]        = 'U.env_flg = ?';
            $where_arg[]    = $destenv_flgs[1];
            $where[]        = 'U.server_flg = ?';
            $where_arg[]    = $destenv_flgs[2];
        }
        if(intval($status)){
            $where[]        = 'C.circular_status = '. \intval($status);
        }

        if ($keyword) {
            $where[]        = '(T.title like ? OR ((T.title IS NULL OR trim(T.title)=\'\') and T.receiver_title like ?) OR U.name like ? OR U.email like ?)';
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
        }
        $where[]        = 'C.circular_status != '.CircularUtils::SAVING_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::RETRACTION_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::DELETE_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::CIRCULAR_COMPLETED_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS;

        try{
            $query_sub_title = DB::table('circular as C')
                ->join('circular_user as U', 'C.id', 'U.circular_id')
                ->select(['C.id','U.title', 'U.sent_date', 'U.receiver_title'])
                ->where('C.mst_user_id',$user->id)
                ->where('U.parent_send_order',0)
                ->where('U.child_send_order',0)
                ->where('U.del_flg',0);

            $data = DB::table('circular as C')
                ->joinSub($query_sub_title, 'T', function ($join) {
                    $join->on('C.id', '=', 'T.id');
                })
                ->join('circular_user as U', 'C.id', 'U.circular_id')
                ->select(DB::raw('C.id,C.special_site_flg, C.access_code, C.outside_access_code, T.sent_date as update_at, C.update_at as upd_at, C.circular_status, C.re_notification_day,T.title as subject,T.receiver_title as file_names, T.title, GROUP_CONCAT(CONCAT(U.name, \' &lt;\',U.email, \'&gt;\')  ORDER BY U.parent_send_order, U.child_send_order ASC SEPARATOR \'<br />\') as emails, GROUP_CONCAT(CONCAT(CASE U.edition_flg WHEN 1 THEN \'プロフェッショナル\' WHEN 0 THEN \'スタンダード\' END, CASE U.env_flg WHEN 1 THEN \'K5\' WHEN 0 THEN \'AWS\' END) ORDER BY U.parent_send_order, U.child_send_order ASC SEPARATOR \'<br />\') as dests' ))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->whereRaw('(U.child_send_order != 0)')
                ->where(function ($query) {
                    $query->where(function ($query1) {
                        $query1->where('C.edition_flg', config('app.edition_flg'))
                ->where('C.env_flg', config('app.server_env'))
                            ->where('C.server_flg', config('app.server_flg'));
                    })->orWhere(function ($query2) {
                        $query2->where('C.special_site_flg', 1);
                    }
                    );
                })
                ->orderBy($orderBy,$orderDir)
                ->groupBy(DB::raw('C.id, C.access_code, C.outside_access_code, T.sent_date, C.circular_status, C.re_notification_day,  T.title, T.receiver_title'))
                ->paginate($limit)->appends(request()->input());

            if(!$data->isEmpty()){
                $listCircular_id = $data->pluck('id')->all();
                $listUserSend = DB::table('circular_user')
                    ->whereIn('circular_id', $listCircular_id)
                    ->get();

                foreach($data as $item){
                    $circularUsers = $listUserSend->filter(function ($value) use ($item){
                        return $value->circular_id == $item->id;
                    });

                    $item->showBtnBack = true;
                    $item->showBtnRequestSendBack = true;
                    $item->hasRequestSendBack = false;
                    // 件名設定
                    $fileNames = explode(', ', $item->file_names);
                    if (!$item->title || trim($item->title,' ') == '') {
                        $item->title = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames),0,100) : reset($fileNames);
                    }

                    if($circularUsers->some(function($value){ return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->hasRequestSendBack = true;
                    }

                    // check if there is any external user or current edition user in circular
                    if($circularUsers->some(function($value){ return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null || $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;

                        // PAC_5-2131課題キーと件名をクリップボードにコピーします。Shiftキーを押しながらクリックすると課題キーのみがコピーされます。
                        //ゲストユーザーに回覧されている時は引き戻しができるようにしたい
                        // return orderby asc data
                        $collectionCircularUsers = $circularUsers->sortBy('id')->all();
                        // count All company_id's category
                        $arrCountCompanyID = [];
                        // ↑ count
                        foreach($collectionCircularUsers as $iV){
                            if(empty($iV->mst_company_id)){
                                $arrCountCompanyID['guesser'] = isset($arrCountCompanyID['guesser']) ? $arrCountCompanyID['guesser'] + 1 : 1;
                            }else{
                                // Solve the problem of the same company ID caused by inconsistent environment
                                $arrCountCompanyID[sprintf("%s%s%s_%s",$iV->env_flg,$iV->edition_flg,$iV->server_flg,$iV->mst_company_id)] = true;
                            }
                        }
                        if(isset($arrCountCompanyID['guesser']) && (count($arrCountCompanyID) > 2 || $arrCountCompanyID['guesser'] > 1)){
                            continue;
                        }
                        $item->showBtnBack = true;
                        //PAC_5-2357 Start
                        if ($circularUsers->some(function($value){return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})){
                            $item->showBtnBack = false;
                        }
                        //PAC_5-2357 End
                        continue;
                    }

                    // check if there is any working circular user
                    $currentCircularUser = $circularUsers->first(function ($value) use ($item) {
                        return $value->circular_id == $item->id && ($value->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS || $value->circular_status == CircularUserUtils::READ_STATUS || $value->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS || $value->circular_status == CircularUserUtils::REVIEWING_STATUS);
                    });

                    if(!$currentCircularUser) {
                        // there is not any working circular user
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    if($currentCircularUser->parent_send_order == 0 && $currentCircularUser->child_send_order == 0) {
                        // the working circular user is sender
                        $item->showBtnBack = false;
                    }

                    if($currentCircularUser->parent_send_order == 0) {
                        $item->showBtnRequestSendBack = false;
                    }

                    // check if the company of working circular user is as same as the sender's company
                    $item->showBtnBack = $currentCircularUser->parent_send_order == 0 && $currentCircularUser->child_send_order != 0;
                    $item->showBtnRequestSendBack = $currentCircularUser->parent_send_order > 0;
                }
            }

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($data, __('message.success.data_get', ['attribute'=>'送信文書']));
    }

    public function getCircularSentById(Request $request, $circularId){
        $user       = $request->user();

        $status     = $request->get('status', false);
        $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);

        $where = ["C.mst_user_id = $user->id " ];
        $where_arg = [];

        $where[]        = 'C.circular_status = '. CircularUtils::CIRCULATING_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::SAVING_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::RETRACTION_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::DELETE_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::CIRCULAR_COMPLETED_STATUS;
        $where[]        = 'C.circular_status != '.CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS;

        try{
            $query_sub_title = DB::table('circular as C')
                ->join('circular_user as U', 'C.id', 'U.circular_id')
                ->select(['C.id','U.title', 'U.sent_date', 'U.receiver_title'])
                ->where([
                    'C.mst_user_id' => $user->id,
                    'C.id' => $circularId,
                    'U.parent_send_order' => 0,
                    'U.child_send_order' => 0,
                    'U.del_flg' => 0,
                ]);

            $data = DB::table('circular as C')
                ->joinSub($query_sub_title, 'T', function ($join) {
                    $join->on('C.id', '=', 'T.id');
                })
                ->join('circular_user as U', 'C.id', 'U.circular_id')
                ->select(DB::raw('C.id,C.special_site_flg, C.access_code, C.outside_access_code, T.sent_date as update_at, C.update_at as upd_at, C.circular_status, C.re_notification_day,T.title as subject,T.receiver_title as file_names, T.title, GROUP_CONCAT(CONCAT(U.name, \' &lt;\',U.email, \'&gt;\')  ORDER BY U.parent_send_order, U.child_send_order ASC SEPARATOR \'<br />\') as emails, GROUP_CONCAT(CONCAT(CASE U.edition_flg WHEN 1 THEN \'プロフェッショナル\' WHEN 0 THEN \'スタンダード\' END, CASE U.env_flg WHEN 1 THEN \'K5\' WHEN 0 THEN \'AWS\' END) ORDER BY U.parent_send_order, U.child_send_order ASC SEPARATOR \'<br />\') as dests' ))
                ->whereRaw(implode(" AND ", $where), $where_arg)
                ->whereRaw('(U.child_send_order != 0)')
                ->where(function ($query) {
                    $query->where(function ($query1) {
                        $query1->where('C.edition_flg', config('app.edition_flg'))
                            ->where('C.env_flg', config('app.server_env'))
                            ->where('C.server_flg', config('app.server_flg'));
                    })->orWhere(function ($query2) {
                        $query2->where('C.special_site_flg', 1);
                    }
                    );
                })
                ->groupBy(DB::raw('C.id, C.access_code, C.outside_access_code, T.sent_date, C.circular_status, C.re_notification_day,  T.title, T.receiver_title'))
                ->paginate($limit)->appends(request()->input());

            if(!$data->isEmpty()){
                $listCircular_id = $data->pluck('id')->all();
                $listUserSend = DB::table('circular_user')
                    ->whereIn('circular_id', $listCircular_id)
                    ->get();

                foreach($data as $item){
                    $circularUsers = $listUserSend->filter(function ($value) use ($item){
                        return $value->circular_id == $item->id;
                    });

                    $item->showBtnBack = true;
                    $item->showBtnRequestSendBack = true;
                    $item->hasRequestSendBack = false;
                    // 件名設定
                    $fileNames = explode(', ', $item->file_names);
                    if (!$item->title || trim($item->title,' ') == '') {
                        $item->title = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames),0,100) : reset($fileNames);
                    }

                    if($circularUsers->some(function($value){ return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->hasRequestSendBack = true;
                    }

                    // check if there is any external user or current edition user in circular
                    if($circularUsers->some(function($value){ return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null || $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;

                        // PAC_5-2131課題キーと件名をクリップボードにコピーします。Shiftキーを押しながらクリックすると課題キーのみがコピーされます。
                        //ゲストユーザーに回覧されている時は引き戻しができるようにしたい
                        // return orderby asc data
                        $collectionCircularUsers = $circularUsers->sortBy('id')->all();
                        // count All company_id's category
                        $arrCountCompanyID = [];
                        // ↑ count
                        foreach($collectionCircularUsers as $iV){
                            if(empty($iV->mst_company_id)){
                                $arrCountCompanyID['guesser'] = isset($arrCountCompanyID['guesser']) ? $arrCountCompanyID['guesser'] + 1 : 1;
                            }else{
                                // Solve the problem of the same company ID caused by inconsistent environment
                                $arrCountCompanyID[sprintf("%s%s%s_%s",$iV->env_flg,$iV->edition_flg,$iV->server_flg,$iV->mst_company_id)] = true;
                            }
                        }
                        if(isset($arrCountCompanyID['guesser']) && (count($arrCountCompanyID) > 2 || $arrCountCompanyID['guesser'] > 1)){
                            continue;
                        }
                        $item->showBtnBack = true;
                        //PAC_5-2357 Start
                        if ($circularUsers->some(function($value){return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK || $value->circular_status == CircularUserUtils::SEND_BACK_STATUS;})){
                            $item->showBtnBack = false;
                        }
                        //PAC_5-2357 End
                        continue;
                    }

                    // check if there is any working circular user
                    $currentCircularUser = $circularUsers->first(function ($value) use ($item) {
                        return $value->circular_id == $item->id && ($value->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS || $value->circular_status == CircularUserUtils::READ_STATUS || $value->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS || $value->circular_status == CircularUserUtils::REVIEWING_STATUS);
                    });

                    if(!$currentCircularUser) {
                        // there is not any working circular user
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    if($currentCircularUser->parent_send_order == 0 && $currentCircularUser->child_send_order == 0) {
                        // the working circular user is sender
                        $item->showBtnBack = false;
                    }

                    if($currentCircularUser->parent_send_order == 0) {
                        $item->showBtnRequestSendBack = false;
                    }

                    // check if the company of working circular user is as same as the sender's company
                    $item->showBtnBack = $currentCircularUser->parent_send_order == 0 && $currentCircularUser->child_send_order != 0;
                    $item->showBtnRequestSendBack = $currentCircularUser->parent_send_order > 0;
                }
            }

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($data, __('message.success.data_get', ['attribute'=>'送信文書']));
    }

    /**
     * 回覧詳細内容表示エリア
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function detailUser($id,Request $request){
        try {
            $user = $request->user();
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            $longTermFlg=$request->get('longTermFlg',0);
            $lid=$request->get('lid');
            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            if(!$longTermFlg){
                $circular = DB::table("circular$finishedDate")->where('id', $id)->first();
                if (!$circular || !$circular->id) {
                    return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
                }
                $getOriginFlag = false;
                if ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg') || $circular->edition_flg != config('app.edition_flg')) {
                    $env_client = EnvApiUtils::getAuthorizeClient($circular->env_flg, $circular->server_flg);
                    if (!$env_client) {
                        Log::debug('Cannot connect to other server Api');
                    }
                    $response = $env_client->get('getFirstPageData', [
                        RequestOptions::JSON => ['circular_id' => $circular->origin_circular_id]
                    ]);
                    if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                        $result = json_decode((string)$response->getBody(), true);
                        if (!empty($result['data'])) {
                            $getOriginFlag = true;
                            $circular->first_page_data = $result['data']['first_page_data'];
                        }
                    }
                }
                $firstDocument = DB::table("circular_document$finishedDate")
                    ->where('circular_id', $id)
                    ->orderBy('id')->first();

                if ($firstDocument && $firstDocument->confidential_flg
                    && $firstDocument->origin_edition_flg == config('app.edition_flg')
                    && $firstDocument->origin_env_flg == config('app.server_env')
                    && $firstDocument->origin_server_flg == config('app.server_flg')
                    && $firstDocument->create_company_id == $user->mst_company_id){
                    $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
                }else if ($firstDocument && !$firstDocument->confidential_flg){
                    $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
                }else{
                    if($circular->first_page_data && $getOriginFlag){
                        $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
                    }else{
                        $noPreviewPath =  public_path()."/images/no-preview.png";
                        $data = file_get_contents($noPreviewPath);
                        $base64 = 'data:image/png;base64,' . base64_encode($data);
                        $circular->first_page_data = $base64;
                    }
                }

                $userReceives = DB::table("circular_user$finishedDate as U")
                    ->leftJoin('circular_user_routes as R', function($query){
                        $query->on('U.circular_id','=','R.circular_id');
                        $query->on('U.child_send_order','=','R.child_send_order');
                    })
                    ->where('U.circular_id', $id)
                    ->orderBy('U.parent_send_order', 'ASC')
                    ->orderBy('U.child_send_order', 'ASC')
                    ->select('U.*','R.mode','R.wait','R.score')
                    ->get();

                $hasRequestSendBack = $userReceives->some(function($value) {
                    return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;
                });

                $author = null;
                if(count($userReceives)){
                    $arrNew = [[]];
                    foreach($userReceives as $userReceive){
                        if($userReceive->parent_send_order == 0 OR $user->mst_company_id == $userReceive->mst_company_id)
                            $userReceive->isOutCopany = 0;
                        else $userReceive->isOutCopany = 1;

                        if($userReceive->parent_send_order == 0 AND $userReceive->child_send_order == 0){
                            $author = $userReceive;
                            continue;
                        }
                        $arrNew[$userReceive->parent_send_order][] = $userReceive;

                        if ($circular->edition_flg != config('app.edition_flg')
                            || $circular->env_flg != config('app.server_env')
                            || $circular->server_flg != config('app.server_flg')){
                            Log::debug("Loop User: $userReceive->edition_flg - $userReceive->env_flg - $userReceive->server_flg - $userReceive->mst_company_id - $userReceive->email");
                            Log::debug("Auth User: ".config('app.edition_flg')." - ".config('app.server_env')." - ".config('app.server_flg')." - $user->mst_company_id - $user->email");
                            // Set view_url for circular
                            if ($userReceive->edition_flg === (int)config('app.edition_flg')
                                && $userReceive->env_flg === (int)config('app.server_env')
                                && $userReceive->server_flg === (int)config('app.server_flg')
                                && strtolower(trim($user->email)) == strtolower(trim($userReceive->email))){
                                Log::debug("Set origin_circular_url");
                                $circular->origin_circular_url = $userReceive->origin_circular_url;
                            }
                        }
                    }

                    if (($circular->edition_flg != config('app.edition_flg') || $circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))
                        && (!isset($circular->origin_circular_url) || !$circular->origin_circular_url)){
                        Log::debug("External circular, check origin_circular_url from viewing user");
                        $viewingUser = DB::table('viewing_user')->where('circular_id', $id)->where('mst_user_id', $user->id)->first();
                        if ($viewingUser){
                            Log::debug("Set origin_circular_url from viewing user");
                            $circular->origin_circular_url = $viewingUser->origin_circular_url;
                        }
                    }

                    $userReceives = [];

                    if($author && $user->mst_company_id != $author->mst_company_id){
                        $userReceives = [$author];
                    }

                    foreach($arrNew as $parent_send_order => $items){
                        if (count($items) > 0){
                            //送信の場合、すべてユーザー表示します。
                            if($author && $user->mst_company_id == $author->mst_company_id && $user->email == $author->email){
                                foreach($items as $userReceive){
                                    if($userReceive->child_send_order == 0){
                                        continue;
                                    }
                                    $userReceives[] = $userReceive;
                                }
                            }else{
                                //受信の場合、自社と社外窓口ユーザー表示します。
                                $item   = $items[0];
                                $num_done = $num_wait = 0; // default
                                foreach($items as $userReceive){
                                    //自社の場合、宛先にユーザー追加
                                    if($user->mst_company_id == $userReceive->mst_company_id ){
                                        $userReceives[] = $userReceive;
                                    }else{
                                        //他社の場合、会社回覧状態確認
                                        if($userReceive->circular_status == CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS
                                            OR $userReceive->circular_status == CircularUserUtils::APPROVED_WITH_STAMP_STATUS){
                                            $num_done ++;
                                        }else if($userReceive->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS OR $userReceive->circular_status == CircularUserUtils::READ_STATUS) {
                                            $num_wait ++;
                                        }
                                    }
                                }
                                //他社の場合、宛先に窓口追加だけ
                                if($parent_send_order != 0 AND $user->mst_company_id != $userReceive->mst_company_id){
                                    if($num_done == count($items)) $item->status = 2;
                                    else if($num_wait != 0 OR $num_done != 0) $item->status = 1;
                                    else $item->status = 0;
                                    $userReceives[] = $item;
                                    /*PAC_5-1698 S*/
                                    if ($item->plan_id > 0) {
                                        foreach ($items as $it) {
                                            if ($it->plan_id == $item->plan_id && $it->id != $item->id) {
                                                if($num_done == count($items)) $it->status = 2;
                                                else if($num_wait != 0 OR $num_done != 0) $it->status = 1;
                                                else $it->status = 0;
                                                $userReceives[] = $it;
                                            }
                                        }
                                    }
                                    /*PAC_5-1698 E*/
                                }
                            }
                        }
                    }
                }

                if ($author){
                    $userSend = new \stdClass();
                    $userSend->family_name = $author->name;
                    $userSend->given_name = '';
                }else{
                    $userSend = new \stdClass();
                    $userSend->family_name = '';
                    $userSend->given_name = '';
                }

                if(!$userReceives){
                    // PAC_5-1383 log追加
                    Log::debug(json_encode($request->server()));
                    Log::debug('[detailUser]circular_id:'.$id.';userReceives:'.json_encode($userReceives).';user:'.json_encode($user).';author:'.json_encode($author).';');
                    Log::error("CircularAPIController@getDetailSend not found userReceives");
                }
            }else{
                $long_term_documents=DB::table('long_term_document')->where('id',$lid)->first();
                $circular=DB::table('upload_data as ud')->leftJoin('long_term_document as ltd','ud.id','=','ltd.upload_id')
                    ->where('ud.id',$long_term_documents->upload_id)
                    ->select(['ud.id','ud.first_img_review as first_page_data','ltd.upload_id'])->first();
                if ($circular && $circular->first_page_data){
                    $circular->first_page_data = AppUtils::decrypt($circular->first_page_data);
                }else{
                    $noPreviewPath =  public_path()."/images/no-preview.png";
                    $data = file_get_contents($noPreviewPath);
                    $base64 = 'data:image/png;base64,' . base64_encode($data);
                    $circular->first_page_data = $base64;
                }
            }

            // 閲覧ユーザを取得
            $viewingUser = DB::table('mst_user as u')
                ->select('u.email', DB::raw('CONCAT(u.family_name,\' \',u.given_name) as name'))
                ->join('viewing_user as v', 'v.mst_user_id', '=', 'u.id')
                ->where('v.circular_id', $id)
                ->where('v.mst_company_id', $user->mst_company_id)
                ->get();

            return $this->sendResponse(['circular'=>$circular, 'userSend' => $userSend??[], 'userReceives' => $userReceives??[], 'viewingUser'=>$viewingUser, 'mid' => $user->mst_company_id,'hasRequestSendBack'=> $hasRequestSendBack??[]],'回覧取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 完了一覧回覧詳細Urlを取得
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function getOriginCircularUrl($id,Request $request){
        try {
            $user = $request->user();
            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');

            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            $circular = DB::table("circular$finishedDate")->where('id', $id)->first();

            if(!$circular || !$circular->id) {
                return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            $userReceives = DB::table("circular_user$finishedDate")
                ->where('circular_id', $id)
                ->orderBy('parent_send_order', 'ASC')
                ->orderBy('child_send_order', 'ASC')
                ->get();

            $hasRequestSendBack = $userReceives->some(function($value) {
                return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;
            });

            $circular->origin_circular_url = null;
            if(count($userReceives)){
                foreach($userReceives as $userReceive){
                    if ($circular->edition_flg != config('app.edition_flg')
                        || $circular->env_flg != config('app.server_env')
                        || $circular->server_flg != config('app.server_flg')){
                        // Set view_url for circular
                        if ($userReceive->edition_flg === (int)config('app.edition_flg')
                            && $userReceive->env_flg === (int)config('app.server_env')
                            && $userReceive->server_flg === (int)config('app.server_flg')
                            && strtolower(trim($user->email)) == strtolower(trim($userReceive->email))){
                            Log::debug("Set origin_circular_url");
                            $circular->origin_circular_url = $userReceive->origin_circular_url;
                        }elseif ($circular->special_site_flg == 1 && strtolower(trim($user->email)) == strtolower(trim($userReceive->email))){
                            $circular->origin_circular_url = $userReceive->origin_circular_url;
                        }
                    }
                }
            }
            return $this->sendResponse(['originCircularUrl'=>$circular->origin_circular_url,'hasRequestSendBack'=> $hasRequestSendBack],'回覧取得処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * pullback for one sent
     */
    public function pullback($circular_id, Request $request){
        $user = $request->user();
        // PAC_5-1516 回覧操作履歴情報登録
        if($request['usingHash']) {
            $user_name = $request['current_name'];
        }else{
            $user_name = $user->getFullName();
        }
        $circular = DB::table('circular')->where('id', $circular_id)->first();

        $pullback_parent_send_order = $request['parent_send_order'];
        $pullback_child_send_order = $request['child_send_order'];
        $strRemark = $request['pullback_remark'] ?? "" ;
        $pullback_user = DB::table('circular_user')
            ->where('circular_id', $circular_id)
            ->where('email', $user->email)
            ->where('parent_send_order', $pullback_parent_send_order)
            ->where('child_send_order', $pullback_child_send_order)
            ->first();

        if(!$circular || !$circular->id || !$pullback_user) {
            return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
        }
        if( isset($request['update_at']) && strtotime($request['update_at']) != strtotime($circular->update_at)){
            return $this->sendError('回覧が見つかりません', StatusCodeUtils::HTTP_NOT_ACCEPTABLE);
        }

        $circular_users = DB::table('circular_user')
            ->where('circular_id', $circular_id)
            ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                CircularUserUtils::READ_STATUS,
                CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                CircularUserUtils::PULL_BACK_TO_USER_STATUS])
            ->orderBy("parent_send_order",'asc')
            ->orderBy("child_send_order",'asc')
            ->get();

        try{
            DB::beginTransaction();
            //If pullback is sender
            if ($pullback_user->parent_send_order == 0 && $pullback_user->child_send_order == 0){
                DB::table('circular')->where('id', $circular_id)->update([
                    'circular_status' => CircularUtils::RETRACTION_STATUS,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $user->email,
                    'final_updated_date' => Carbon::now(),
                ]);
                DB::table('circular_user')->where('circular_id', $circular_id)->update([
                    'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $user->email,
                    'node_flg' => CircularUserUtils::NODE_OTHER,
                    'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                ]);
            }
            if($pullback_user->child_send_order > 0) {
                DB::table('circular_user')->where('id', $pullback_user->id)->update([
                    'circular_status' => CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                    'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                ]);
                /*PAC_5-2250 S*/
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('id', '!=',$pullback_user->id)
                    ->where('parent_send_order', $pullback_user->parent_send_order)
                    ->where('child_send_order', $pullback_user->child_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
                /*PAC_5-2250 E*/
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $pullback_user->parent_send_order)
                    ->where('child_send_order','>', $pullback_user->child_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order','>', $pullback_user->parent_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
                DB::table('circular')->where('id', $circular_id)->update([
                    'update_at'=> Carbon::now(),
                    'update_user'=> $user->email,
                    'final_updated_date' => Carbon::now(),
                ]);
            }
            // PAC_5-1798 申請者が引戻しを行った際はその回覧の閲覧ユーザを削除して回覧先・閲覧者どちらか一方に登録できるようにする
            DB::table('viewing_user')
                ->where('circular_id', $circular_id)
                /*PAC_5-2261 S*/
                ->where([
                    'parent_send_order' => $pullback_user->parent_send_order,
                    'create_user' => $user->email
                ])
                /*PAC_5-2261 E*/
                ->delete();
            // PAC_5-1516 回覧操作履歴情報登録
            DB::table('circular_operation_history')->insert([
                'circular_id'=> $circular_id,
                'operation_email' => $user->email,
                'operation_name' => $user_name,
                'acceptor_email'=> $user->email,
                'acceptor_name'=> $user_name,
                'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_PULL_BACK_TO_USER_STATUS,
                'create_at' => Carbon::now()
            ]);
            if ($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))){
                Log::debug('Pull back in other application on new edition, update transferred document');
                $this->sendUpdateTransferredDocumentStatus($circular, $pullback_user->parent_send_order, $pullback_user->child_send_order, $pullback_user->title, '', $circular->env_flg,$circular->server_flg, false);
            }
            if ($pullback_user->edition_flg == config('app.edition_flg')
                && ($pullback_user->env_flg != config('app.server_env') || $pullback_user->server_flg != config('app.server_flg'))){
                Log::debug('Pull back in other application on new edition, update transferred document');
                $this->sendUpdateTransferredDocumentStatus($circular, $pullback_user->parent_send_order, $pullback_user->child_send_order, $pullback_user->title, '', $pullback_user->env_flg,$pullback_user->server_flg, false);
            }
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $circular_doc = DB::table('circular_document')
            ->select('file_name', 'confidential_flg', 'origin_edition_flg', 'origin_env_flg', 'origin_server_flg', 'create_company_id', 'origin_document_id', 'parent_send_order')
            ->where('circular_id', $circular_id)
            ->get()->toArray();
        try{
            $mapSameEnvCompanies = [];
            $mapOtherEnvCompanies = [];
            foreach($circular_users as $circular_user){
                if ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->mst_company_id){
                    if ($circular_user->env_flg == config('app.server_env') && $circular_user->server_flg == config('app.server_flg')){
                        $mapSameEnvCompanies[$circular_user->mst_company_id] = null;
                    }else{
                        if (isset($mapOtherEnvCompanies[$circular_user->env_flg])){
                            if (isset($mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg])){
                                $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id] = null;
                            }else{
                                $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg] = [$circular_user->mst_company_id => null];
                            }
                        }else{
                            $mapOtherEnvCompanies[$circular_user->env_flg] = [$circular_user->server_flg =>[[$circular_user->mst_company_id => null]]];
                        }
                    }
                }
            }
            $mapSameEnvCompanies = $this->companyRepository->getSameEnvCompanies($mapSameEnvCompanies);
            $mapOtherEnvCompanies = EnvApiDelegate::getOtherEnvCompanies($mapOtherEnvCompanies);
            $objLastUser = $circular_users->last();
            // PAC_5-2183  引戻しを行ったユーザーより前のユーザーには回覧撤回メールを送らない
            $circular_users =  $circular_users->filter(function ($value, $key) use ($pullback_user,$objLastUser){
                $strCurrentUser = str_pad((string) $value->parent_send_order,3,'0',STR_PAD_RIGHT) . str_pad((string) $value->child_send_order,3,'0',STR_PAD_LEFT);
                $strLastUser = str_pad((string) $objLastUser->parent_send_order,3,'0',STR_PAD_RIGHT) . str_pad((string) $objLastUser->child_send_order,3,'0',STR_PAD_LEFT);
                $strPullBackUser = str_pad((string) $pullback_user->parent_send_order,3,'0',STR_PAD_RIGHT) . str_pad((string) $pullback_user->child_send_order,3,'0',STR_PAD_LEFT);
                return $strCurrentUser == $strLastUser || $strCurrentUser == $strPullBackUser;
            });

            foreach($circular_users as $circular_user){
              if ($circular_user && CircularUserUtils::checkAllowReceivedEmail($circular_user->email, 'pullback',$circular_user->mst_company_id,$circular_user->env_flg,$circular_user->edition_flg,$circular_user->server_flg)) {
                $data = [];

                $data['title']      = $circular_user->title;
                $filterDocuments    = array_filter($circular_doc, function($item) use($circular_user){
                    if($item->confidential_flg
                        && $item->origin_edition_flg == $circular_user->edition_flg
                        && $item->origin_env_flg == $circular_user->env_flg
                        && $item->origin_server_flg == $circular_user->server_flg
                        && $item->create_company_id == $circular_user->mst_company_id){
                        // 社外秘：origin_document_idが-1固定
                        // 同社メンバー参照可
                        return true;
                    }else if (!$item->confidential_flg
                        && (!$item->origin_document_id || $item->parent_send_order == $circular_user->parent_send_order)){
                        // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                        // 回覧終了時：origin_document_id＝0のレコード
                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                        return true;
                    }
                    return false;
                });
                $data['docs']       = array_column($filterDocuments, 'file_name');
                $data['mail_name']      = $circular_user->title;
                $data['pullback_remark'] = $strRemark;

                if(!trim($circular_user->title)){
                    $data['mail_name']      = $data['docs'][0];
                }
                if(count($data['docs'])){
                    $data['docstext'] = '';
                    foreach($data['docs'] as $filename){
                        if ($data['docstext'] == '') {
                            $data['docstext'] = $filename;
                            continue;
                        }
                        $data['docstext'] .= '\r\n'.'　　　　　　'.$filename;
                    }
                }else{
                    $data['docstext'] = '';
                }

                $data['receiver_name'] = $circular_user->name;
                $data['user_name']  = $user->getFullName();
                // check to use SAML Login URL or not
                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompanies($circular_user, $mapSameEnvCompanies, $mapOtherEnvCompanies);

                $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                unset($data['docs']);

                //利用者:回覧文書の引戻し通知
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $circular_user->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CIRCULAR_PULLBACK_NOTIFY']['CODE'],
                    // パラメータ
                    $param,
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_pullback_template.subject', ['title' => $data['title']]),
                    // メールボディ
                    trans('mail.circular_pullback_template.body', $data)
                );
              }
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $message = '文書を引戻し、受信一覧に移動しました。';
        if($pullback_parent_send_order == 0 && $pullback_child_send_order == 0) {
            //PAC_5-2517引戻文件削除
            $circularDataId = DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->where('origin_document_id', '<>', -1)
                ->pluck('id')
                ->toArray();
            if(isset($circularDataId)){
                DB::table('document_data')->whereIn('circular_document_id', $circularDataId)->delete();
                DB::table('circular_document')->whereIn('id', $circularDataId)->delete();
                DB::table('circular_operation_history')->whereIn('circular_document_id', $circularDataId)->delete();
                DB::table('document_comment_info')->whereIn('circular_document_id',$circularDataId)->delete();
                DB::table('stamp_info')->whereIn('circular_document_id', $circularDataId)->delete();
                DB::table('text_info')->whereIn('circular_document_id', $circularDataId)->delete();
                DB::table('time_stamp_info')->whereIn('circular_document_id', $circularDataId)->delete();
            }
            //PAC_5-2517完了
            $message = '文書を引戻し、下書き一覧に移動しました。';
        }

        return $this->sendResponse(true,$message);
    }

    public function requestSendBack($circular_id, Request $request) {
        try {

            DB::beginTransaction();
            $user = $request->user();
            // PAC_5-1516 回覧操作履歴情報登録
            if($request['usingHash']) {
                $user_name = $request['current_name'];
            }else{
                $user_name = $user->getFullName();
            }
            $circular = DB::table('circular')->where('id', $circular_id)->first();

            $request_parent_send_order = $request['parent_send_order'];
            $request_child_send_order = $request['child_send_order'];
            $request_user = DB::table('circular_user')
                ->leftJoin('mail_text', function($join){
                    $join->on('circular_user.id', '=', 'mail_text.circular_user_id');
                    $join->on('mail_text.id', '=', DB::raw("(select max(id) from mail_text WHERE mail_text.circular_user_id = circular_user.id)"));
                })
                ->select('circular_user.*', 'mail_text.text as text')
                ->where('circular_id', $circular_id)
                ->where('email', $user->email)
                ->where('parent_send_order', $request_parent_send_order)
                ->where('child_send_order', $request_child_send_order)
                ->first();
            if(!$circular || !$circular->id || !$request_user) {
                DB::rollBack();
                return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }
            if( isset($request['update_at']) && strtotime($request['update_at']) != strtotime($circular->update_at)){
                DB::rollBack();
                return $this->sendError('回覧が見つかりません', StatusCodeUtils::HTTP_NOT_ACCEPTABLE);
            }

            //一つ前の回覧更新者も情報を取得
            $before_email=DB::table('circular')->where('id', $circular_id)->first();

            DB::table('circular_user')
                ->where('id', $request_user->id)
                ->update([
                    'circular_status' => CircularUserUtils::SUBMIT_REQUEST_SEND_BACK,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $user->email,
                    'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                ]);

            DB::table('circular')->where('id', $circular_id)->update([
                'update_at'=> Carbon::now(),
                'update_user'=> $user->email,
                'final_updated_date' => Carbon::now(),
            ]);

            $current_circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                ->first();

            $circular_users = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('parent_send_order','>', $request_parent_send_order)
                ->where('parent_send_order','<=', $current_circular_user->parent_send_order)
                ->where('child_send_order', 1)
                ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                    CircularUserUtils::READ_STATUS,
                    CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                    CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                    CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                ->orderByDesc('parent_send_order')
                ->get();
            if ($circular->edition_flg == config('app.edition_flg') && ($circular->env_flg != config('app.server_env') || $circular->server_flg != config('app.server_flg'))){
                Log::debug('Request sendback circular of other application on new edition, update transferred document');
                $this->sendUpdateTransferredDocumentStatus($circular, $request_user->parent_send_order, $request_user->child_send_order, $request_user->title, '', $circular->env_flg,$circular->server_flg, false);
            }else if ($request_user->edition_flg == config('app.edition_flg') && ($request_user->env_flg != config('app.server_env') || $request_user->server_flg != config('app.server_flg'))){
                Log::debug('Request sendback in other application on new edition, update transferred document');
                $this->sendUpdateTransferredDocumentStatus($circular, $request_user->parent_send_order, $request_user->child_send_order, $request_user->title, '', $request_user->env_flg,$request_user->server_flg, false);
            }else{
                Log::debug('check if there is any other application user on new edition');


                $envs = [config('app.server_env').config('app.server_flg')];
                foreach ($circular_users as $circular_user) {
                    if(!in_array($circular_user->env_flg.$circular_user->server_flg,$envs)){
                        $envs[] = $circular_user->env_flg.$circular_user->server_flg;

                        Log::debug('There is any other application user on new edition, request sendback in other application on new edition, update transferred document');
                        $this->sendUpdateTransferredDocumentStatus($circular, $request_user->parent_send_order, $request_user->child_send_order, $request_user->title, '', $circular_user->env_flg,$circular_user->server_flg, false);
                    }
                }
            }
            /*PAC_5-2261 S*/
            DB::table('viewing_user')
                ->where('circular_id', $circular_id)
                ->where([
                    'parent_send_order' => $request_user->parent_send_order,
                    'create_user' => $user->email
                ])
                ->delete();
            /*PAC_5-2261 E*/
            // PAC_5-1516 回覧操作履歴情報登録
            DB::table('circular_operation_history')->insert([
                'circular_id'=> $circular_id,
                'operation_email' => $user->email,
                'operation_name' => $user_name,
                'acceptor_email'=> $current_circular_user->email,
                'acceptor_name'=> $current_circular_user->name,
                'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_SUBMIT_REQUEST_SEND_BACK_STATUS,
                'create_at' => Carbon::now()
            ]);

            DB::commit();

            // all file
            $allDocument = DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->orderBy('id')
                ->get()
                ->keyBy('id')
                ->toArray();

            // first file
            $firstDocument = DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->orderBy('id')->first();

            $mapSameEnvCompanies = [];
            $mapOtherEnvCompanies = [];
            foreach ($circular_users as $circular_user) {
                if ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->mst_company_id){
                    if ($circular_user->env_flg == config('app.server_env') && $circular_user->server_flg == config('app.server_flg')){
                        $mapSameEnvCompanies[$circular_user->mst_company_id] = null;
                    }else{
                        if (isset($mapOtherEnvCompanies[$circular_user->env_flg])){
                            if (isset($mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg])){
                                $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id] = null;
                            }else{
                                $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg] = [$circular_user->mst_company_id => null];
                            }
                        }else{
                            $mapOtherEnvCompanies[$circular_user->env_flg] = [$circular_user->server_flg =>[[$circular_user->mst_company_id => null]]];
                        }
                    }
                }
            }
            $mapSameEnvCompanies = $this->companyRepository->getSameEnvCompanies($mapSameEnvCompanies);
            $mapOtherEnvCompanies = EnvApiDelegate::getOtherEnvCompanies($mapOtherEnvCompanies);

            foreach ($circular_users as $kc=>$circular_user) {
                $data = [];
                // hide_thumbnail_flg 0:表示 1:非表示
                if (!$circular->hide_thumbnail_flg) {
                    // thumbnail表示
                    $canSeePreview = false;
                    if ($firstDocument && $firstDocument->confidential_flg
                        && $firstDocument->origin_edition_flg == $circular_user->edition_flg
                        && $firstDocument->origin_env_flg == $circular_user->env_flg
                        && $firstDocument->origin_server_flg == $circular_user->server_flg
                        && $firstDocument->create_company_id == $circular_user->mst_company_id) {
                        // 一ページ目が社外秘　＋　upload会社＝宛先会社
                        $canSeePreview = true;
                    } else if ($firstDocument && !$firstDocument->confidential_flg) {
                        // 一ページ目が社外秘ではない
                        $canSeePreview = true;
                    }

                    if ($canSeePreview && $circular->first_page_data) {
                        $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                        $data['image_path'] = $previewPath;
                    } else {
                        $data['image_path'] = public_path() . "/images/no-preview.png";
                    }
                } else {
                    $data['image_path'] = '';
                }

                // 回覧文書見る
                $data['hide_circular_approval_url'] = false;
                foreach ($allDocument as $document) {
                    if ($document->confidential_flg) {
                        // hide_circular_approval_url false:表示 true:非表示
                        // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                        // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                        if ($document->origin_edition_flg == $circular_user->edition_flg
                            && $document->origin_env_flg == $circular_user->env_flg
                            && $document->origin_server_flg == $circular_user->server_flg
                            && $document->create_company_id == $circular_user->mst_company_id) {
                            // 当社社外秘ファイル存在時、「回覧文書を見る」非表示
                            $data['hide_circular_approval_url'] = true;
                        }
                    }
                }
                // file name
                $filterDocuments = array_filter($allDocument, function ($item) use ($circular_user, $circular) {
                    if ($item->confidential_flg
                        && $item->origin_edition_flg == $circular_user->edition_flg
                        && $item->origin_env_flg == $circular_user->env_flg
                        && $item->origin_server_flg == $circular_user->server_flg
                        && $item->create_company_id == $circular_user->mst_company_id) {
                        // 社外秘：origin_document_idが-1固定
                        // 同社メンバー参照可
                        return true;
                    } else if (!$item->confidential_flg
                        && (!$item->origin_document_id || $item->parent_send_order == $circular_user->parent_send_order) || ($circular->special_site_flg && $item->origin_document_id = -1)) {
                        // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                        // 回覧終了時：origin_document_id＝0のレコード
                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                        return true;
                    }
                    return false;
                });
                $filenames = array_column($filterDocuments, 'file_name');

                $title = $circular_user->title;
                if (!trim($title)) {
                    $title = $filenames[0];
                }

                $data['receiver_name'] = $circular_user->name;
                $data['return_requester'] = $user->getFullName();
                $data['mail_name'] = $title;
                $data['user_name'] = $user->getFullName();
                $data['circular_id'] = $circular_id;
                $data['filenames'] = $filenames;
                if (count($filenames)) {
                    $data['filenamestext'] = '';
                    foreach ($filenames as $filename) {
                        if ($data['filenamestext'] == '') {
                            $data['filenamestext'] = $filename;
                            continue;
                        }
                        $data['filenamestext'] .= '\r\n' . '　　　　　　' . $filename;
                    }
                } else {
                    $data['filenamestext'] = '';
                }
                $data['text'] = $request_user->text;
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_id);
                if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                    $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                } else {
                    $data['circular_approval_url_text'] = '';
                }
                // check to use SAML Login URL or not
                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompanies($circular_user, $mapSameEnvCompanies, $mapOtherEnvCompanies);


                $param = json_encode($data, JSON_UNESCAPED_UNICODE);
                unset($data['filenames']);

                $isSendEmailFlag = $circular_users[0]->plan_id > 0 ? ($circular_user->plan_id == $circular_users[0]->plan_id) : ($kc==0);
                if($isSendEmailFlag) {
                    // 利用者:回覧文書の差戻し依頼通知
                    MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                        $circular_user->email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['CIRCULAR_SEND_BACK_REQUEST_NOTIFY']['CODE'],
                        // パラメータ
                        $param,
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_request_sendback_template.subject', ['title' => $title, 'user' => $user->getFullName()]),
                        // メールボディ
                        trans('mail.circular_user_request_sendback_template.body', $data)
                    );

                    // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                    if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                        // 申請者
                        $author_user = DB::table('circular_user')
                            ->where('circular_id', $circular_id)
                            ->where('parent_send_order', 0)
                            ->where('child_send_order', 0)
                            ->first();

                        if ($circular->access_code_flg == CircularUtils::ACCESS_CODE_VALID
                            && $author_user->mst_company_id == $circular_user->mst_company_id
                            && $author_user->edition_flg == $circular_user->edition_flg
                            && $author_user->env_flg == $circular_user->env_flg
                            && $author_user->server_flg == $circular_user->server_flg) {
                            $access_data['title'] = $data['mail_name'];
                            $access_data['access_code'] = $circular->access_code;
                            MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                                $circular_user->email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($access_data, JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                // メールボディ
                                trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                            );
                        } elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                            && ($author_user->mst_company_id != $circular_user->mst_company_id
                                || $author_user->edition_flg != $circular_user->edition_flg
                                || $author_user->env_flg != $circular_user->env_flg
                                || $author_user->server_flg != $circular_user->server_flg)) {
                            // 次の宛先が社外の場合
                            $access_data['title'] = $data['mail_name'];
                            $access_data['access_code'] = $circular->outside_access_code;

                            //利用者:アクセスコードのお知らせ
                            MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                                $circular_user->email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($access_data, JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                // メールボディ
                                trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                            );
                        }
                    }
                }
            }

            return $this->sendResponse(true,'差戻しの依頼処理に成功しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function approvalReqSendBack($circular_id, Request $request)
    {
        try {

            DB::beginTransaction();
            $user = $request->user();
            if(!$user || !$user->id) {
                $user = $request['user'];
                $approval_circular_user = $request['current_circular_user'];
                $user_name = $request['current_name'];
            }else{
                $approval_circular_user = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('email', $user->email)
                    ->where('child_send_order', 1)
                    ->where('env_flg', config('app.server_env'))
                    ->where('edition_flg', config('app.edition_flg'))
                    ->where('server_flg', config('app.server_flg'))
                    ->first();
                $user_name = $user->getFullName();
            }
            $circular = DB::table('circular')->where('id', $circular_id)->first();

            if(!$circular || !$circular->id || !$approval_circular_user) {
                DB::rollBack();
                return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
            }

            if(!($approval_circular_user->parent_send_order > 0 && $approval_circular_user->child_send_order == 1)) {
                DB::rollBack();
                return $this->sendError('Approval request sendback denied', \Illuminate\Http\Response::HTTP_FORBIDDEN);
            }
            $endSendBackExist = DB::table('circular_user')->where('circular_id', $circular_id)->where('circular_status', CircularUserUtils::END_OF_REQUEST_SEND_BACK)->exists();

            if(!$endSendBackExist){
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $approval_circular_user->parent_send_order)
                    ->whereNotIn('circular_status', [CircularUserUtils::READ_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);

                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $approval_circular_user->parent_send_order)
                    ->whereIn('circular_status', [CircularUserUtils::READ_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                    ->update([
                        'circular_status' => CircularUserUtils::END_OF_REQUEST_SEND_BACK,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);

                if ($approval_circular_user->edition_flg == config('app.edition_flg')
                    && ($approval_circular_user->env_flg != config('app.server_env') || $approval_circular_user->server_flg != config('app.server_flg'))){
                    Log::debug('Approve request sendback in other application on new edition, update transferred document');
                    $this->sendUpdateTransferredDocumentStatus($circular, $approval_circular_user->parent_send_order, $approval_circular_user->child_send_order, $approval_circular_user->title, '', $approval_circular_user->env_flg,$approval_circular_user->server_flg, false, true);
                }
            }else{
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $approval_circular_user->parent_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);

                if ($approval_circular_user->edition_flg == config('app.edition_flg')
                    && ($approval_circular_user->env_flg != config('app.server_env') || $approval_circular_user->server_flg != config('app.server_flg'))){
                    Log::debug('Approve request sendback in other application on new edition, update transferred document');
                    $this->sendUpdateTransferredDocumentStatus($circular, $approval_circular_user->parent_send_order, $approval_circular_user->child_send_order, $approval_circular_user->title, '', $approval_circular_user->env_flg,$approval_circular_user->server_flg, false, false);
                }
            }

            $request_circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK)
                ->first();

            $countNotAprroval = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('parent_send_order','>', $request_circular_user->parent_send_order)
                ->where('child_send_order', 1)
                ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                ->count();
            $isLastApproval = true;
            $lastId = null;
            if($countNotAprroval > 0) {
                $isLastApproval = false;
                //send mail to next approval user
                $this->approvalReqSendBackNextUserEmail($circular,$request_circular_user,$approval_circular_user);
            }else {
                $last_user = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('circular_status', CircularUserUtils::END_OF_REQUEST_SEND_BACK)
                    ->first();
                if($last_user) $lastId = $last_user->id;
            }

            // PAC_5-1516 回覧操作履歴情報登録
            DB::table('circular_operation_history')->insert([
                'circular_id'=> $circular_id,
                'operation_email' => $user->email,
                'operation_name' => $user_name,
                'acceptor_email'=> $request_circular_user->email,
                'acceptor_name'=> $request_circular_user->name,
                'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_RECOGNITION_REQUEST_SEND_BACK_STATUS,
                'create_at' => Carbon::now()
            ]);

            DB::commit();
            return $this->sendResponse(['isLastApproval' => $isLastApproval, 'lastId' => $lastId],'差戻しの依頼を承認しました。');

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 受信一覧リスト画面初期化
     *
     * @param SearchCircularUserAPIRequest $request
     * @return mixed
     */
    public function indexReceived(SearchCircularUserAPIRequest $request){
        $user       = $request->user();
        $filename   = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $userName   = $request->get('userName');
        $userEmail  = $request->get('userEmail');
        $fromdate   = $request->get('fromdate');
        $todate     = $request->get('todate');
        $sender     = $request->get('sender');
        $status     = $request->get('status', false);
        $page       = $request->get('page', 1);
        $limit      = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy    = $request->get('orderBy', "update_at");
        $orderDir   = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword   = CircularDocumentUtils::charactersReplace($request->get('keyword'));

        $arrOrder   = ['title' => 'title','A.email' => 'A.email', 'update_at' => 'update_at',
            'U.circular_status' => 'U.circular_status'];
        $orderBy = [isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at'];

        $where = [];
        $where_arg = [];

        if($filename){
            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and D.file_names like ?))';
            $where_arg[]    = "%$filename%";
            $where_arg[]    = "%$filename%";
        }
        if($userName){
            $where[]        = '(A.name like ? )';
            $where_arg[]    = "%$userName%";
        }
        if($userEmail){
            $where[]        = 'A.email like ?';
            $where_arg[]    = "%$userEmail%";
        }
        if($fromdate){
            $where[]        = 'U.received_date >= ?';
            $where_arg[]    = date($fromdate).' 00:00:00';
        }
        if($todate){
            $where[]        = 'U.received_date <= ?';
            $where_arg[]    = date($todate).' 23:59:59';
        }
        if ($sender) {
            $sender_flg = str_split($sender);
            $where[]        = 'C.edition_flg = ?';
            $where_arg[]    = $sender_flg[0];
            $where[]        = 'C.env_flg = ?';
            $where_arg[]    = $sender_flg[1];
            $where[]        = 'C.server_flg = ?';
            $where_arg[]    = $sender_flg[2];
        }
        if ($keyword) {
            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and D.file_names like ?) OR A.email like ? OR A.name like ?)';
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
        }

        try{
            // PAC_5-2114 Start
            // 統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to ID App');
            }

            $id_app_user_id = 0;
            $response = $client->post("users/checkEmail", [
                RequestOptions::JSON => ['email' => $user->email]
            ]);
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $resData = json_decode((string) $response->getBody());
                if(!empty($resData) && !empty($resData->data)){
                    $id_app_users = $resData->data;
                    // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                    foreach ($id_app_users as $id_app_user) {
                        if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                            $id_app_user_id = $id_app_user->id;
                            break;
                        }
                    }
                }
            }
            //PAC_5-2114 End
            $query_sub = DB::table('circular as C')
                ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
                ->select(DB::raw('C.id, U.parent_send_order, U.receiver_title as file_names, MAX(U.child_send_order) as child_send_order,U.circular_id'))
                // PAC_5-2114 Start
                ->where(function($query) use ($user, $id_app_user_id){
                    $query->where('U.email', $user->email)
                        ->where(function ($query) use ($user, $id_app_user_id) {
                            if ($id_app_user_id !== 0) {
                                $query->where('U.mst_user_id', $user->id)
                                    ->orWhere('U.mst_user_id', $id_app_user_id);
                            } else {
                                $query->where('U.mst_user_id', $user->id);
                            }
                        })
                        ->where('U.edition_flg', config('app.edition_flg'))
                        ->where('U.env_flg', config('app.server_env'))
                        ->where('U.server_flg', config('app.server_flg'));
                })
                // PAC_5-2114 End
                ->groupBy(['C.id', 'U.parent_send_order','U.circular_id','U.receiver_title']);

            $data_query = DB::table('circular as C')
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->join('circular_user as U', function($join){
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('D.parent_send_order','=','U.parent_send_order');
                })
                ->leftjoin('circular_user as A', function($join){
                    $join->on('A.circular_id', '=', 'C.id');
                    $join->on('A.parent_send_order','=',DB::raw("0"));
                    $join->on('A.child_send_order','=',DB::raw("0"));
                })
                ->select(DB::raw('C.id, C.special_site_flg, U.plan_id, U.received_date as update_at, C.update_at as upd_at, C.re_notification_day, C.circular_status status, U.circular_status,U.parent_send_order,U.child_send_order,U.title as subject, D.file_names, IF(U.title IS NULL or trim(U.title) = \'\', D.file_names, U.title) as title, CONCAT(A.name, \' &lt;\',A.email, \'&gt;\') as email, CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender, A.name,U.is_skip'));
            if (count($where)){
                $data_query->whereRaw(implode(" AND ", $where), $where_arg);
            }
            $data_query->where(function($query)use ($user, $status){
                $query->where(function($query1) use ($user, $status){
                    $query1->where('U.email', $user->email);
                        if(!$status){
                            $query1->where(function($query2) {
                                $query2->where(function($query3) {
                                    $query3->where('U.parent_send_order',0);
                                    $query3->where('U.child_send_order',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS]);
                                    });
                                $query2->orWhere(function($query3) {
                                    $query3->where('U.child_send_order','>',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS,
                                            CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                                            CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::NODE_COMPLETED_STATUS]);
                                    });
                                })
                                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 1){
                            $query1->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 2){
                            $query1->where(function($query2) {
                                $query2->where(function($query3) {
                                    $query3->where('U.parent_send_order',0);
                                    $query3->where('U.child_send_order',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
                                });
                                $query2->orWhere(function($query3) {
                                    $query3->where('U.child_send_order','>',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
                                });
                            })
                            ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 3){
                            $query1->where('U.child_send_order','>',0);
                            // PAC_5-2375 START 承認を選択した時は、承認（捺印あり）、承認（捺印なし）を表示
                            $query1->whereIn('U.circular_status', [CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS]);
                            // PAC_5-2375 END
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 4){
                            $query1->where('U.child_send_order','>',0);
                            $query1->where('U.circular_status', CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS);
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }else if($status == 6){
                            $query1->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                        }else if($status == 5){
                            // PAC_5-2375 START 差戻しを選択した時は、差戻し（既読）、差戻し（未読）を表示
                            $query1->whereIn('U.circular_status', [CircularUserUtils::READ_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS])
                                ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                            // PAC_5-2375 END
                        }else if($status == 7){
							// 差戻し依頼 PAC_5-508 回覧状況に差戻し依頼を追加 引戻しは、下書き一覧に入るため、回覧状況から削除
                            $query1->where('U.circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK);
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                            // PAC_5-2375 START 差戻し依頼を選択した時は、差戻し依頼、差戻し依頼（既読）、差戻し依頼（未読）を表示
                            $query1->orWhere(function ($query2) {
                                $query2->where(function($query3) {
                                    $query3->where('U.parent_send_order',0);
                                    $query3->where('U.child_send_order',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
                                });
                                $query2->orWhere(function($query3) {
                                    $query3->where('U.child_send_order','>',0);
                                    $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
                                });
                            })->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('circular_user as U1')
                                    ->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
                                    ->whereRaw('U.circular_id = U1.circular_id');
                            });
                            // 差戻し依頼(未読)
                            $query1->orWhere(function ($query2) {
                                $query2->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                    ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS)
                                    ->whereExists(function ($query) {
                                        $query->select(DB::raw(1))
                                            ->from('circular_user as U1')
                                            ->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
                                            ->whereRaw('U.circular_id = U1.circular_id');
                                    });
                            });
                            // PAC_5-2375 END
                        }else if($status == 11){
							// 差戻し依頼(既読)
							$query1->where(function($query2) {
								$query2->where(function($query3) {
									$query3->where('U.parent_send_order',0);
									$query3->where('U.child_send_order',0);
									$query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
								});
								$query2->orWhere(function($query3) {
									$query3->where('U.child_send_order','>',0);
									$query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS]);
								});
							})
								->whereExists(function ($query) {
									$query->select(DB::raw(1))
										->from('circular_user as U1')
										->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
										->whereRaw('U.circular_id = U1.circular_id');
								})
								->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS]);
						}else if($status == 12){
							// 差戻し依頼(未読)
							$query1->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
								->where('C.circular_status', CircularUtils::CIRCULATING_STATUS)
								->whereExists(function ($query) {
									$query->select(DB::raw(1))
										->from('circular_user as U1')
										->whereRaw('U1.circular_status = ?',[CircularUserUtils::SUBMIT_REQUEST_SEND_BACK])
										->whereRaw('U.circular_id = U1.circular_id');
								});
						}
                        /*PAC_5-2250 S*/
                        else if($status == 14) {
                            $query1->where('U.child_send_order', '>', 0);
                            $query1->where('U.circular_status', CircularUserUtils::NODE_COMPLETED_STATUS);
                            $query1->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                        }
                        /*PAC_5-2250 E*/
                        else if(strlen($status) > 1){
                            // support search multiple search
                            $arrStatus = str_split($status);
                        $query1->where(function($query2) use ($arrStatus){
                            foreach ($arrStatus as $s){
                                if($s == 1){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                            ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
                                    });
                                }else if($s == 2){
                                    $query2->orWhere(function($query3){
                                        $query3->whereIn('U.circular_status',[CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                                            ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
                                    });
                                }else if($s == 3){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.child_send_order','>',0);
                                        $query3->where('U.circular_status', CircularUserUtils::APPROVED_WITH_STAMP_STATUS);
                                        $query3->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                                    });

                                }else if($s == 4){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.child_send_order','>',0);
                                        $query3->where('U.circular_status', CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS);
                                        $query3->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                                    });
                                }else if($s == 6){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                                            ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                                    });
                                }else if($s == 5){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::READ_STATUS)
                                            ->where('C.circular_status', CircularUtils::SEND_BACK_STATUS);
                                    });
                                }else if($s == 7){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::SUBMIT_REQUEST_SEND_BACK);
                                        $query3->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
                                    });
                                }else if($s == 8){
                                    $query2->orWhere(function($query3){
                                        $query3->where('U.circular_status', CircularUserUtils::PULL_BACK_TO_USER_STATUS)
                                            ->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
                                    });
                                }
                            }
                        });
                    }
                });
                    });
            foreach ($orderBy as $order) {
                $data_query = $data_query->orderBy($order, $orderDir)->orderBy('U.circular_status');
            }
            $data = $data_query->paginate($limit)->appends(request()->input());

            $num_unread = DB::table('circular as C')
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->join('circular_user as U', function($join){
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('D.parent_send_order','=','U.parent_send_order');
                })
                ->where("U.email", $user->email)
                ->where('U.circular_status', CircularUserUtils::NOTIFIED_UNREAD_STATUS)
                ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                ->count();
            if(!$data->isEmpty()){
                $listCircular_id = $data->pluck('id')->all();
                $listUserSend = DB::table('circular_user')
                    ->whereIn('circular_id', $listCircular_id)
                    ->get();

                foreach($data as $item){
                    $circularUsers = $listUserSend->filter(function ($value) use ($item){
                        return $value->circular_id == $item->id;
                    });
                    // PAC_5-634 自身のメールアドレスを宛先に追加して申請後、受信一覧で同じ文書名が連続して表示される, process file name
                    $fileNames = explode(CircularUserUtils::SEPERATOR, $item->file_names);
                    if (!trim($item->subject)){
                        $item->title = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames),0,100) : reset($fileNames);
                    }

                    $item->showBtnBack = true;
                    $item->showBtnRequestSendBack = true;
                    $item->hasRequestSendBack = false;
					$item->hasOperationNotice = false; // 閲覧ユーザー確認フラグ
                    if($circularUsers->some(function($value){ return $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->hasRequestSendBack = true;
                    }

                    // PAC_5-263 閲覧ユーザーの場合、差戻し依頼ボタン表示しない
					if(!$circularUsers->some(function($value) use ($user){ return $value->email == $user->email;})) {
						$item->hasOperationNotice = true;
					}

                    // check if there is any external user or current edition user in circular
                    if($circularUsers->some(function($value){ return $value->edition_flg != config('app.edition_flg') || $value->mst_company_id === null || $value->circular_status == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK;})) {
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    // check if there is any the working user
                    $currentCircularUser = $circularUsers->first(function ($value) use ($item) {
                        return $value->circular_id == $item->id && ($value->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS || $value->circular_status == CircularUserUtils::READ_STATUS || $value->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS|| $value->circular_status == CircularUserUtils::REVIEWING_STATUS);
                    });
                    if(!$currentCircularUser) {
                        // there is not any the working user
                        $item->showBtnBack = false;
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    // check if the current user is working user
                    if($item->circular_status == CircularUserUtils::NOTIFIED_UNREAD_STATUS OR $item->circular_status == CircularUserUtils::READ_STATUS OR $item->circular_status == CircularUserUtils::PULL_BACK_TO_USER_STATUS OR $item->circular_status == CircularUserUtils::REVIEWING_STATUS){
                        $item->showBtnBack = false;
                    }else if($item->circular_status == CircularUserUtils::APPROVED_WITH_STAMP_STATUS || $item->circular_status == CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS ){
                        // check if the company of current user is as same as the working's company
                        $item->showBtnBack = $item->parent_send_order == $currentCircularUser->parent_send_order;
                    }
                    /*PAC_5-2250 S*/
                    if($item->circular_status == CircularUserUtils::NODE_COMPLETED_STATUS && in_array($item->status,[CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])) {
                        $item->showBtnBack = false;
                    }
                    /*PAC_5-2250 E*/
                    /*PAC_5-1698 S*/
                    if(in_array($item->circular_status , [CircularUserUtils::APPROVED_WITH_STAMP_STATUS,CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS]) && $item->plan_id > 0 && $circularUsers->some(function($circular_user) use ($item) {
                           return $circular_user->plan_id == $item->plan_id && $circular_user->circular_status == CircularUserUtils::REVIEWING_STATUS;
                    })) {
                        $item->showBtnBack = false;
                    }
                    /*PAC_5-1698 E*/
                    if(($item->parent_send_order == 0 && $item->child_send_order > 0) || ($item->parent_send_order > 0 && $item->child_send_order > 1)) {
                        $item->showBtnRequestSendBack = false;
                        continue;
                    }

                    if($item->parent_send_order >= $currentCircularUser->parent_send_order) {
                        $item->showBtnRequestSendBack = false;
                    }
                }
            }

        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse(['num_unread' => $num_unread, 'data' => $data], __('message.success.data_get', ['attribute'=>'受信文書']));
    }

    public function countCircularStatus(Request $request){
        $user       = $request->user();

        try{

            $data = $this->getCountCircularStatus($user);
            $dataSentCirculating = DB::table('circular')
                ->join('circular_user', function ($join){
                    $join->on('circular_user.circular_id', '=', 'circular.id');
                    $join->on('circular_user.parent_send_order','=',DB::raw('0'));
                    $join->on('circular_user.child_send_order','=',DB::raw('0'));
                    $join->on('circular_user.del_flg','=',DB::raw('0'));
                })
                ->where('circular.mst_user_id', $user->id)
                ->where('circular.circular_status', CircularUtils::CIRCULATING_STATUS)
                ->where('circular.edition_flg', config('app.edition_flg'))
                ->where('circular.env_flg', config('app.server_env'))
                ->where('circular.server_flg', config('app.server_flg'))
                ->count();

            $dataSentSendback = DB::table('circular')
                ->join('circular_user', function ($join){
                    $join->on('circular_user.circular_id', '=', 'circular.id');
                    $join->on('circular_user.parent_send_order','=',DB::raw('0'));
                    $join->on('circular_user.child_send_order','=',DB::raw('0'));
                    $join->on('circular_user.del_flg','=',DB::raw('0'));
                })
                ->where('circular.mst_user_id', $user->id)
                ->where('circular.circular_status', CircularUtils::SEND_BACK_STATUS)
                ->where('circular.edition_flg', config('app.edition_flg'))
                ->where('circular.env_flg', config('app.server_env'))
                ->where('circular.server_flg', config('app.server_flg'))
                ->count();

            $dataSaved = DB::table('circular as C')
                ->where('C.mst_user_id', $user->id)
                ->whereIn('C.circular_status', [CircularUtils::SAVING_STATUS, CircularUtils::RETRACTION_STATUS])
                ->where('C.edition_flg', config('app.edition_flg'))
                ->where('C.env_flg', config('app.server_env'))
                ->where('C.server_flg', config('app.server_flg'))
                ->count('C.id');

            return $this->sendResponse(['num_untreated' => $data->num_untreated, 'num_unread' => $data->num_unread, 'num_return' => $data->num_return, 'num_unread_return' => $data->num_unread_return
                , 'num_read' => $data->num_read, 'num_approval' => $data->num_approval, 'num_pullback' => $data->num_pullback
                , 'num_sent' => ($dataSentCirculating + $dataSentSendback), 'num_saved' => $dataSaved
                , 'num_sent_circulating' => $dataSentCirculating, 'num_sent_sendback' => $dataSentSendback ],'');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //　回覧文書関連情報取得
    private function getCountCircularStatus($user)
    {
        // PAC_5-2114 Start
        if (is_null($user->id)) {
            $mst_user = DB::table('mst_user')
                ->select('id', 'mst_company_id', 'email')
                ->where('email', $user->email)
                ->where('state_flg', AppUtils::STATE_VALID)
                ->first();
            if ($mst_user && $mst_user->id) $user->id = $mst_user->id;
        }
        // PAC_5-2114 Start
        // 統合ID側からユーザー情報取得
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            throw new \Exception('Cannot connect to ID App');
        }

        $id_app_user_id = 0;
        $response = $client->post("users/checkEmail", [
            RequestOptions::JSON => ['email' => $user->email]
        ]);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
            $resData = json_decode((string) $response->getBody());
            if(!empty($resData) && !empty($resData->data)){
                $id_app_users = $resData->data;
                // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                foreach ($id_app_users as $id_app_user) {
                    if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                        $id_app_user_id = $id_app_user->id;
                        break;
                    }
                }
            }
        }
        // PAC_5-2114 End
        $query_sub = DB::table('circular as C')
            ->join('circular_user as U', 'C.id', '=', 'U.circular_id')
            ->join('circular_document as D', function ($join) use ($user) {
                $join->on('C.id', '=', 'D.circular_id');
                $join->on(function ($condition) use ($user) {
                    $condition->on('confidential_flg', DB::raw('0'));
                    $condition->orOn(function ($condition1) use ($user) {
                        $condition1->on('confidential_flg', DB::raw('1'));
                        $condition1->on('origin_edition_flg', DB::raw(config('app.edition_flg')));
                        $condition1->on('origin_env_flg', DB::raw(config('app.server_env')));
                        $condition1->on('origin_server_flg', DB::raw(config('app.server_flg')));
                        if (isset($user->mst_company_id)) {
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        }
                    });
                });
                $join->on(function ($condition) use ($user) {
                    $condition->on('origin_document_id', DB::raw('0'));
                    $condition->orOn(function ($condition1) use ($user) {
                        $condition1->on('D.parent_send_order', 'U.parent_send_order');
                    });
                });
            })
            ->select(DB::raw('C.id, U.parent_send_order'))
            ->where(function ($query) use ($user, $id_app_user_id) {
                $query->where('U.email', $user->email)
                    // PAC_5-2114 Start
                    ->where(function ($query) use ($user, $id_app_user_id) {
                        if ($id_app_user_id !== 0) {
                            $query->where('U.mst_user_id', $user->id)
                                ->orWhere('U.mst_user_id', $id_app_user_id);
                        } else {
                            $query->where('U.mst_user_id', $user->id);
                        }
                    })
                    // PAC_5-2114 End
                    ->where('U.edition_flg', config('app.edition_flg'))
                    ->where('U.env_flg', config('app.server_env'))
                    ->where('U.server_flg', config('app.server_flg'));
            })
            ->groupBy(['C.id', 'U.parent_send_order']);

        $data_query = DB::table('circular as C')
            ->leftJoinSub($query_sub, 'D', function ($join) {
                $join->on('C.id', '=', 'D.id');
            })
            ->join('circular_user as U', function ($join) {
                $join->on('U.circular_id', '=', 'C.id');
                $join->on('D.parent_send_order', '=', 'U.parent_send_order');
            })
            ->select(DB::raw('SUM(C.circular_status = ' . CircularUtils::SEND_BACK_STATUS . ' AND U.circular_status = ' . CircularUserUtils::READ_STATUS . ' ) as num_return,
                                SUM(C.circular_status = ' . CircularUtils::SEND_BACK_STATUS . ' AND U.circular_status = ' . CircularUserUtils::NOTIFIED_UNREAD_STATUS . ') as num_unread_return,
                                SUM(C.circular_status = ' . CircularUtils::CIRCULATING_STATUS . ' AND U.circular_status = ' . CircularUserUtils::NOTIFIED_UNREAD_STATUS . ') as num_unread,
                                SUM(C.circular_status = ' . CircularUtils::CIRCULATING_STATUS . ' AND (U.circular_status = ' . CircularUserUtils::READ_STATUS . ' OR
                                                                                                   U.circular_status = ' . CircularUserUtils::SUBMIT_REQUEST_SEND_BACK . '  OR
                                                                                                   U.circular_status = ' . CircularUserUtils::REVIEWING_STATUS . '  OR
                                                                                                   U.circular_status = ' . CircularUserUtils::PULL_BACK_TO_USER_STATUS . ')) as num_read,
                                SUM(U.circular_status = ' . CircularUserUtils::APPROVED_WITH_STAMP_STATUS . ' OR U.circular_status = ' . CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS . ' ) as num_approval,
                                SUM(C.circular_status = ' . CircularUtils::CIRCULATING_STATUS . ' AND U.circular_status = ' . CircularUserUtils::PULL_BACK_TO_USER_STATUS . ') as num_pullback,
        	                COUNT(C.id) as num_untreated'))
            ->where(function ($query) use ($user) {
                $query->where(function ($query1) use ($user) {
                    $query1->where(function ($query2) {
                        $query2->where(function ($query3) {
                            $query3->where('U.parent_send_order', 0);
                            $query3->where('U.child_send_order', 0);
                            $query3->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS]);
                        });
                        $query2->orWhere(function ($query3) {
                            $query3->where('U.child_send_order', '>', 0);
                            $query3->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS,
                                CircularUserUtils::APPROVED_WITH_STAMP_STATUS, CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS,
                                CircularUserUtils::SUBMIT_REQUEST_SEND_BACK, CircularUserUtils::REVIEWING_STATUS]);
                        });
                    })
                        ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                        ->where('U.email', $user->email);
                });
            });

        return $data_query->first();
    }

    /**
     * プッシュ通知用のバッジ設定数を取得
     * @param email       ：通知先のメールアドレス
     * @param env_flg     ：circularテーブルの設定値
     * @param server_flg  ：circularテーブルの設定値
     * @param edition_flg ：circularテーブルの設定値
     */
    private function getNotifyBadgeNumber($email, $env_flg, $server_flg, $edition_flg) {
        // クロス環境判定
        if ((($env_flg != config('app.server_env')) || ($server_flg != config('app.server_flg'))) && $edition_flg != 0) {
            // 他環境の場合、他環境のapiを呼び出す。
            $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
            if (!$envClient){
                //TODO message
                throw new \Exception('Cannot connect to other server Api');
            }

            $response = $envClient->get("count-circular-number",[
                RequestOptions::JSON => ['email' => $email]
            ]);
            if (!$response) {
                return $this->sendError('回覧情報取得に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                Log::debug('getStatusCode：'. $response->getStatusCode());
                Log::error($response->getBody());
                throw new \Exception('Cannot get count-status');
            }

            $resData = json_decode((string)$response->getBody());
            Log::debug('他環境検索結果：'. json_encode($resData));
            $data = $resData->data;

        } else {
        $mst_user = DB::table('mst_user')
        ->select('id', 'mst_company_id', 'email')
        ->where('email', $email)
        ->first();

            // mst_userテーブルに無いユーザの場合はゲストユーザ
        if ($mst_user == null) {
                $mst_user = (object)array('id' => null, 'email' => $email, 'mst_company_id' => null);
        }
            Log::debug('company_id: ' . $mst_user->mst_company_id);

        $data = $this->getCountCircularStatus($mst_user);
        }

        Log::debug('●num_unread: ' . $data->num_unread);
        return $data->num_unread;
    }

    /**
     * 他環境の通知数取得API
     * @param Request $request email：通知先のメールアドレス
     */
    public function getCountCircularNumber(Request $request){
        try {
            $email = $request['email'];
            $user = (object)array('id' => null, 'email' => $email, 'mst_company_id' => null);

            $data = $this->getCountCircularStatus($user);

            return ['status' =>\Illuminate\Http\Response::HTTP_OK, 'message' =>'通知数の取得処理に成功しました。', 'data' => $data];

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return ['status' =>\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR, 'message' =>$ex->getMessage(), 'data' => []];
        }
    }

    /**
     * @param $circular_id
     * @param Request $request
     * @return mixed
     */
    public function sendViewedMail($circular_id, Request $request) {
        try {
            $login_user = $request->user();
            if(!$login_user || !$login_user->id) {
                $login_user = $request['user'];
            }

            DB::beginTransaction();
            $circular_user_id = $request['circular_user_id'];
            $is_template_circular = $request['is_template_circular'];

            $circular = DB::table('circular')
                ->where('id', $circular_id)
                ->first();

            $circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('id', $circular_user_id)
                ->first();

            if (!$circular || !$circular_user){
                return $this->sendError(['status'=>\Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message'=> 'Param Circular is invalid'], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $sender_circular_user = DB::table('circular_user')
                ->where('id', '!=', $circular_user_id)
                ->where('circular_id', $circular_id)
                ->whereIn('circular_status', [CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::APPROVED_WITH_STAMP_STATUS])
                ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) < "
                    .str_pad($circular_user->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($circular_user->child_send_order,3,'0', STR_PAD_LEFT))
                ->orderBy(DB::raw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0'))"), 'desc')
                ->first();

            $arr_sender_emails = []; // sender emails
            // 合議の場合
            if($is_template_circular){
                $sender_circular_users = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    //->whereIn('circular_status', [CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS, CircularUserUtils::APPROVED_WITH_STAMP_STATUS])
                    ->where('child_send_order', $circular_user->child_send_order - 1)
                    ->get();
            }else{
                if($sender_circular_user){
                    $sender_circular_users = [$sender_circular_user];
                }else{
                    $sender_circular_users = [];
                }
            }

            foreach($sender_circular_users as $item) {
                $arr_sender_emails[] = $item->email;
                if ($item && CircularUserUtils::checkAllowReceivedEmail($item->email, 'viewed',$sender_circular_user->mst_company_id,$sender_circular_user->env_flg,$sender_circular_user->edition_flg,$sender_circular_user->server_flg)) {
                $data = [];
                // hide_thumbnail_flg 0:表示 1:非表示
                if (!$circular->hide_thumbnail_flg) {
                    // thumbnail表示
                    $canSeePreview = false;
                    $firstDocument = DB::table('circular_document')
                        ->where('circular_id', $circular_id)
                        ->orderBy('id')->first();
                    if ($firstDocument && $firstDocument->confidential_flg
                            && $firstDocument->origin_edition_flg == $item->edition_flg
                            && $firstDocument->origin_env_flg == $item->env_flg
                            && $firstDocument->origin_server_flg == $item->server_flg
                            && $firstDocument->create_company_id == $item->mst_company_id){
                        // 一ページ目が社外秘　＋　upload会社＝宛先会社
                        $canSeePreview = true;
                    }else if ($firstDocument && !$firstDocument->confidential_flg){
                        // 一ページ目が社外秘ではない
                        $canSeePreview = true;
                    }

                    if($canSeePreview && $circular->first_page_data){
                        // 一ページ目表示
                            $previewPath = AppUtils::getPreviewPagePath($item->edition_flg, $item->env_flg, $item->server_flg, $item->mst_company_id, $item->id);
                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                        $data['image_path'] = $previewPath;
                    }else{
                        // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                        $data['image_path'] = public_path()."/images/no-preview.png";
                    }
                }else{
                    $data['image_path'] = '';
                }

                $filenames = DB::table('circular_document')
                    ->where('circular_id', $circular_id)
                        ->where(function($query) use ($item){

                            $query->where(function ($query0) use ($item){
                            // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                            $query0->where('confidential_flg', 0);
                                $query0->where(function ($query01) use ($item){
                                // 回覧終了時：origin_document_id＝0のレコード
                                $query01->where('origin_document_id', 0);
                                // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                    $query01->orWhere('parent_send_order', $item->parent_send_order);
                            });
                        });

                            $query->orWhere(function($query1) use ($item){
                            // 社外秘：origin_document_idが-1固定
                            // 同社メンバー参照可
                            $query1->where('confidential_flg', 1);
                                $query1->where('origin_edition_flg', $item->edition_flg);
                                $query1->where('origin_env_flg', $item->env_flg);
                                $query1->where('origin_server_flg', $item->server_flg);
                                $query1->where('create_company_id', $item->mst_company_id);
                        });
                    })
                    ->pluck('file_name');

                $title = $circular_user->title;
                if (!trim($title)) {
                    $title = $filenames->toArray()[0];
                }

                $data['email_title'] = $circular_user->title;
                $data['filenames'] = $filenames;
                if(count($filenames)){
                    $data['filenamestext'] = '';
                    foreach($filenames as $filename){
                        if ($data['filenamestext'] == '') {
                            $data['filenamestext'] = $filename;
                            continue;
                        }
                        $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                    }
                }else{
                    $data['filenamestext'] = '';
                }
                $data['mail_name'] = $title;
                    $data['receiver_name'] = $item->name;
                $data['browsing_user'] = $circular_user->name;
                $data['email'] = $circular_user->email;
                $data['author_email'] = $circular->create_user;
                    $data['circular_id'] = $circular_id;// PAC_5-2490
                $data['last_updated_email'] = $circular_user->update_user;
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                // 回覧文書をみる　なし
                    $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($item);

                //利用者:回覧文書の閲覧通知
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $item->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CIRCULAR_USER_VIEWED_NOTIFY']['CODE'],
                    // パラメータ
                    json_encode($data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_viewed_template.subject', ['title' => $title, 'user_email' => $circular_user->email]),
                    // メールボディ
                    trans('mail.circular_user_viewed_template.body', $data)
                );
            }
            }

            $author_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('parent_send_order', 0)
                ->where('child_send_order', 0)
                ->first();

            if(CircularUserUtils::checkAllowReceivedEmail($author_user->email, 'viewed',$author_user->mst_company_id,$author_user->env_flg,$author_user->edition_flg,$author_user->server_flg) && (trim($author_user->id) != trim($circular_user_id))
                && (!$arr_sender_emails || !in_array($author_user->email, $arr_sender_emails))) {
                $data = [];
                // hide_thumbnail_flg 0:表示 1:非表示
                if (!$circular->hide_thumbnail_flg) {
                    // thumbnail表示
                    $canSeePreview = false;
                    $firstDocument = DB::table('circular_document')
                        ->where('circular_id', $circular_id)
                        ->orderBy('id')->first();
                    if ($firstDocument && $firstDocument->confidential_flg
                        && $firstDocument->origin_edition_flg == $author_user->edition_flg
                        && $firstDocument->origin_env_flg == $author_user->env_flg
                        && $firstDocument->origin_server_flg == $author_user->server_flg
                        && $firstDocument->create_company_id == $author_user->mst_company_id){
                        // 一ページ目が社外秘　＋　upload会社＝宛先会社
                        $canSeePreview = true;
                    }else if ($firstDocument && !$firstDocument->confidential_flg){
                        // 一ページ目が社外秘ではない
                        $canSeePreview = true;
                    }

                    if($canSeePreview && $circular->first_page_data){
                        $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                        file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                        $data['image_path'] = $previewPath;
                    }else{
                        // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                        $data['image_path'] = public_path()."/images/no-preview.png";
                    }
                }else{
                    $data['image_path'] = '';
                }

                $filenames = DB::table('circular_document')
                    ->where('circular_id', $circular_id)
                    ->where(function($query) use ($author_user){

                        $query->where(function ($query0) use ($author_user){
                            // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                            $query0->where('confidential_flg', 0);
                            $query0->where(function ($query01) use ($author_user){
                                // 回覧終了時：origin_document_id＝0のレコード
                                $query01->where('origin_document_id', 0);
                                // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                                $query01->orWhere('parent_send_order', $author_user->parent_send_order);
                            });
                        });

                        $query->orWhere(function($query1) use ($author_user){
                            // 社外秘：origin_document_idが-1固定
                            // 同社メンバー参照可
                            $query1->where('confidential_flg', 1);
                            $query1->where('origin_edition_flg', $author_user->edition_flg);
                            $query1->where('origin_env_flg', $author_user->env_flg);
                            $query1->where('origin_server_flg', $author_user->server_flg);
                            $query1->where('create_company_id', $author_user->mst_company_id);
                        });
                    })
                    ->pluck('file_name');

                $title = $circular_user->title;
                if (!trim($title)) {
                    $title = $filenames->toArray()[0];
                }

                $data['email_title'] = $circular_user->title;
                $data['filenames'] = $filenames;
                if(count($filenames)){
                    $data['filenamestext'] = '';
                    foreach($filenames as $filename){
                        if ($data['filenamestext'] == '') {
                            $data['filenamestext'] = $filename;
                            continue;
                        }
                        $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                    }
                }else{
                    $data['filenamestext'] = '';
                }

                $data['mail_name'] = $title;
                $data['receiver_name'] = $author_user->name;
                $data['browsing_user'] = $circular_user->name;
                $data['circular_id'] = $circular_id;// PAC_5-2490
                $data['email'] = $circular_user->email;
                $data['author_email'] = $circular->create_user;
                $data['last_updated_email'] = $circular_user->update_user;
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                // 回覧文書をみる　なし
                // check to use SAMl Login URL or not
                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($author_user);

                //利用者:回覧文書の閲覧通知
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $author_user->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CIRCULAR_USER_VIEWED_NOTIFY']['CODE'],
                    // パラメータ
                    json_encode($data,JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_viewed_template.subject', ['title' => $title, 'user_email' => $circular_user->email]),
                    // メールボディ
                    trans('mail.circular_user_viewed_template.body', $data)
                );
            }

            $new_status = CircularUserUtils::READ_STATUS;

            if($login_user->email == $circular_user->email){
                DB::table('circular_user')
                    ->where('circular_user.id', $circular_user_id)
                    ->update([
                        'circular_status' => $new_status,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $login_user->email,
                    ]);
            }

            if ($circular_user->edition_flg == config('app.edition_flg') && ($circular_user->env_flg != config('app.server_env') || $circular_user->server_flg != config('app.server_flg'))){
                $this->sendUpdateTransferredDocumentStatus($circular, $circular_user->parent_send_order, $circular_user->child_send_order, $circular_user->title, '', $circular_user->env_flg,$circular_user->server_flg, false, false);
            }

            DB::commit();

            return $this->sendSuccess('Send viewed mail successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @param $circular_id
     * @param Request $request
     * @return mixed
     */
    public function sendBack($circular_id, SendBackRequest $request) {
        $mailData = [];
        try {
            $login_user = $request->user();
			$edition_flg = config('app.edition_flg');
			$env_flg = config('app.server_env');
            $server_flg = config('app.server_flg');
            $is_plan = false;
			if($request['usingHash']) {
				$login_user = $request['user'];
				$user_name = $request['current_name'];
				$edition_flg = $request['current_circular_user']->edition_flg;
				$env_flg = $request['current_circular_user']->env_flg;
                $server_flg = $request['current_circular_user']->server_flg;
			}else{
				$user_name = $login_user->getFullName();
			}

            DB::beginTransaction();
            // TODO review get send_from_id from login
            $is_template_circular_flg = $request['is_template_circular_flg'];
            if($is_template_circular_flg){
                $circular_user_from = DB::table('circular_user')
                    ->select('circular_user.*')
                    ->where('circular_user.circular_id', $circular_id)
                    ->where('circular_user.child_send_order', $request['send_from_child_order'])
                    ->where('circular_user.email', $login_user->email)
                    ->first();

                $send_from_id = $circular_user_from->id;
            }else{
            $send_from_id = $request['send_from_id'];

                // send back の人
                $circular_user_from = DB::table('circular_user')
                    ->where('id', $send_from_id)
                    ->first();
                if ($circular_user_from->plan_id > 0){
                    $is_plan=true;
                }
            }

            $send_to_id = $request['send_to_id'];

            $circular = DB::table('circular')
                ->select('circular.*', 'mst_user.email')
                ->leftJoin('mst_user', 'circular.mst_user_id','=','mst_user.id')
                ->where('circular.id', $circular_id)
                ->first();

            $circular_user_to = DB::table('circular_user')
                ->where('id', $send_to_id)
                ->first();

            // 回覧申請者
			$author_user = DB::table('circular_user')
				->where('circular_id', $circular_id)
				->where('parent_send_order', 0)
				->where('child_send_order', 0)
				->first();

			// 回覧文書
            $allDocument = DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->orderBy('id')->get();
            $hasConfidenceFiles = false;
            $ConfidenceFilesInfo = array();
            foreach($allDocument as $document){
                if($document->confidential_flg){
                    $hasConfidenceFiles = true;
                    $ConfidenceFileInfo = [
                        'origin_edition_flg' => $document->origin_edition_flg,
                        'origin_env_flg'     => $document->origin_env_flg,
                        'origin_server_flg'  => $document->origin_server_flg,
                        'create_company_id'  => $document->create_company_id,
                    ];
                    $ConfidenceFilesInfo[] = $ConfidenceFileInfo;
                }
            }


            if (!$request['isRequestSendBack']) {
                // TODO translate message
                if ($circular_user_from->parent_send_order > 0 && $circular_user_from->child_send_order == 1) {
                    if ($circular_user_from->circular_status != CircularUserUtils::REVIEWING_STATUS) {
                        $back_circular_user = DB::table('circular_user')
                            ->where('circular_id', $circular_id)
                            ->where('parent_send_order', '<', $circular_user_from->parent_send_order)
                            ->orderBy('parent_send_order', 'desc')
                            ->orderBy('child_send_order', 'asc')
                            ->first();

                        if ($circular_user_to->id != $back_circular_user->id) {
                            // TODO translate message
                            DB::rollBack();
                            return $this->sendError('Cannot send back over two companies', \Illuminate\Http\Response::HTTP_FORBIDDEN);
                        }
                    } else {
                        if ($circular_user_from->parent_send_order != $circular_user_to->parent_send_order) {
                            // TODO translate message
                            DB::rollBack();
                            return $this->sendError('The final confirmer cannot be sent back to the previous companies', \Illuminate\Http\Response::HTTP_FORBIDDEN);
                        }
                    }
                } else {
                    if ($circular_user_from->parent_send_order != $circular_user_to->parent_send_order) {
                        // TODO translate message
                        DB::rollBack();
                        return $this->sendError('Only the first user can send back to previous companies', \Illuminate\Http\Response::HTTP_FORBIDDEN);
                    }
                    if ($circular_user_from->child_send_order <= $circular_user_to->child_send_order && $circular_user_from->circular_status != CircularUserUtils::REVIEWING_STATUS) {
                        // TODO translate message
                        DB::rollBack();
                        return $this->sendError('Cannot send back to next user', \Illuminate\Http\Response::HTTP_FORBIDDEN);
                    }
                }
            }
            // hide_thumbnail_flg 0:表示 1:非表示
            if (!$circular->hide_thumbnail_flg) {
                // thumbnail表示
                $canSeePreview = false;
                $firstDocument = DB::table('circular_document')
                    ->where('circular_id', $circular_id)
                    ->orderBy('id')->first();
                if ($firstDocument && $firstDocument->confidential_flg
                    && $firstDocument->origin_edition_flg == $circular_user_to->edition_flg
                    && $firstDocument->origin_env_flg == $circular_user_to->env_flg
                    && $firstDocument->origin_server_flg == $circular_user_to->server_flg
                    && $firstDocument->create_company_id == $circular_user_to->mst_company_id){
                    // 一ページ目が社外秘　＋　upload会社＝宛先会社
                    $canSeePreview = true;
                }else if ($firstDocument && !$firstDocument->confidential_flg){
                    // 一ページ目が社外秘ではない
                    $canSeePreview = true;
                }

                if($canSeePreview && $circular->first_page_data){
                    // 一ページ目表示
                    $previewPath = AppUtils::getPreviewPagePath($circular_user_to->edition_flg, $circular_user_to->env_flg, $circular_user_to->server_flg, $circular_user_to->mst_company_id, $circular_user_to->id);
                    file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                    $mailData['image_path'] = $previewPath;
                }else{
                    // no-preview（一ページ目が社外秘　＋　upload会社 <> 宛先会社）
                    $mailData['image_path'] = public_path()."/images/no-preview.png";
                }
            }else{
                // thumbnail非表示
                $mailData['image_path'] = '';
            }

            $filenames = DB::table('circular_document')
                ->where('circular_id', $circular_id)
                ->where(function($query) use ($circular_user_to){

                    $query->where(function ($query0) use ($circular_user_to){
                        // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                        $query0->where('confidential_flg', 0);
                        $query0->where(function ($query01) use ($circular_user_to){
                            // 回覧終了時：origin_document_id＝0のレコード
                            $query01->where('origin_document_id', 0);
                            // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                            $query01->orWhere('parent_send_order', $circular_user_to->parent_send_order);
                        });
                    });

                    $query->orWhere(function($query1) use ($circular_user_to){
                        // 社外秘：origin_document_idが-1固定
                        // 同社メンバー参照可
                        $query1->where('confidential_flg', 1);
                        $query1->where('origin_edition_flg', $circular_user_to->edition_flg);
                        $query1->where('origin_env_flg', $circular_user_to->env_flg);
                        $query1->where('origin_server_flg', $circular_user_to->server_flg);
                        $query1->where('create_company_id', $circular_user_to->mst_company_id);
                    });
                })
                ->pluck('file_name');

            $title = $circular_user_from->title;
            if(!trim($title)) {
                $title = $filenames->toArray()[0];
            }

            $mailData['mail_name'] = $title;
            //$mailData['receiver_name'] = $circular_user_to->name;
            $mailData['return_user'] = $circular_user_from->name;

            $mailData['email_title'] = $circular_user_from->title;
            $mailData['filenames'] = $filenames;
			$mailData['text'] = $request['text'];
            $mailData['email'] = $circular_user_from->email;
            $mailData['author_email'] = $circular->email;
            $mailData['circular_id'] = $circular_id;// PAC_5-2490
            $mailData['last_updated_email'] = $circular_user_from->email;
            $mailData['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
            //$mailData['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user_to->email, $circular_user_to->edition_flg, $circular_user_to->env_flg, $circular_user_to->server_flg,$circular_id);
            // hide_circular_approval_url false:表示 true:非表示
            // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
            // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
            $mailData['hide_circular_approval_url'] = false;
            if($hasConfidenceFiles){
                // 社外秘文書ある、
                foreach ($ConfidenceFilesInfo as $ConfidenceFileInfo){
                    if($ConfidenceFileInfo['origin_edition_flg'] == $circular_user_to->edition_flg
                        && $ConfidenceFileInfo['origin_env_flg'] == $circular_user_to->env_flg
                        && $ConfidenceFileInfo['origin_server_flg'] == $circular_user_to->server_flg
                        && $ConfidenceFileInfo['create_company_id'] == $circular_user_to->mst_company_id){
                        $mailData['hide_circular_approval_url'] = true;
                    }
                }
            }

//            if(isset($mailData['hide_circular_approval_url']) && !$mailData['hide_circular_approval_url']){
//                $mailData['circular_approval_url_text'] = '回覧文書をみる:' . $mailData['circular_approval_url'] . '\r\n\r\n';
//            }else{
//                $mailData['circular_approval_url_text'] = '';
//            }
            $mailData['send_to'] = $circular_user_to->email;
            $mailData['send_to_company'] = $circular_user_to->mst_company_id;

            // check to use SAMl Login URL or not
            $mailData['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompany($circular_user_to);

            DB::table('circular')
                ->where('id', $circular_id)
                ->update([
                    'circular_status' => CircularUtils::SEND_BACK_STATUS,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                    'final_updated_date' => Carbon::now(),
                ]);

            if($is_template_circular_flg) {
                // 合議の場合、同じノード circular_status変更
            DB::table('circular_user')
                    ->where('circular_id', $circular_user_to->circular_id)
                    ->where('parent_send_order', $circular_user_to->parent_send_order)
                    ->where('child_send_order', $circular_user_to->child_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                        'received_date' => Carbon::now(),
                        'update_at'=> Carbon::now(),
                        'update_user'=> $login_user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
            }else{
                if ($circular_user_from->plan_id>0){
                DB::table('circular_user')
                        ->where('circular_id', $circular_user_to->circular_id)
                        ->where('plan_id', $circular_user_from->plan_id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at' => Carbon::now(),
                            'update_user' => $login_user->email,
                        ]);
                }
                $touser=DB::table('circular_user')
                ->where('id', $send_to_id)
                        ->first();
                if ($touser->plan_id>0){
                    DB::table('circular_user')
                        ->where('circular_id', $circular_user_to->circular_id)
                        ->where('plan_id', $touser->plan_id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at' => Carbon::now(),
                            'update_user' => $login_user->email,
                            'node_flg' => CircularUserUtils::NODE_OTHER
                        ]);
                }
                DB::table('circular_user')
                ->where('id', $send_to_id)
                ->update([
                    'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                    'received_date' => Carbon::now(),
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                    'node_flg' => CircularUserUtils::NODE_OTHER,
                    'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                ]);
            }

			// 件名・メッセージ入力した場合
			if($request['text']){
				$circular_operation_history_id = DB::table('circular_operation_history')->insertGetId([
					'circular_id'=> $circular_id,
					'circular_document_id' => $request['circular_document_id'],
					'operation_email' => $login_user->email,
					'operation_name' => $user_name,
					'acceptor_email'=> '',
					'acceptor_name'=> '',
					'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_COMMENT_STATUS,
					'create_at' => Carbon::now(),
				]);
				$current_circular_user = DB::table('circular_user')->where('circular_id', $circular_id)
					->where('email', $login_user->email)
					->where('edition_flg', $edition_flg)
					->where('env_flg', $env_flg)
                    ->where('server_flg', $server_flg)
					->orderByDesc('parent_send_order')
					->orderByDesc('child_send_order')
					->first();
				DB::table('document_comment_info')->insert([
					'circular_document_id' => $request['circular_document_id'],
					'circular_operation_id' => $circular_operation_history_id,
					'parent_send_order' => $current_circular_user ? $current_circular_user->parent_send_order:0,
					'name'=> $user_name,
					'email'=> $login_user->email,
					'text'=> $request['text'],
					'private_flg'=> CircularOperationHistoryUtils::DOCUMENT_COMMENT_PRIVATE,
					'create_at' => Carbon::now(),
				]);
			}

            // PAC_5-539 承認履歴情報登録
            if($is_template_circular_flg){
                // 合議の場合、同じノード 履歴情報登録
                $circular_user_to_items = DB::table('circular_user')
                    ->where('circular_id', $circular_user_to->circular_id)
                    ->where('parent_send_order', $circular_user_to->parent_send_order)
                    ->where('child_send_order', $circular_user_to->child_send_order)
                    ->get();
                foreach($circular_user_to_items as $circular_user_to_item){
			DB::table('circular_operation_history')->insert([
				'circular_id'=> $circular_id,
				'operation_email' => $login_user->email,
				'operation_name' => $user_name,
                        'acceptor_email'=> $circular_user_to_item ? $circular_user_to_item->email : '',
                        'acceptor_name'=> $circular_user_to_item ? $circular_user_to_item->name : '',
                        'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS,
                        'create_at' => Carbon::now(),
                    ]);
                }
            } elseif ($is_plan && $circular_user_to->plan_id > 0) {
                $circular_user_to_items = DB::table('circular_user')
                    ->where('circular_id', $circular_user_to->circular_id)
                    ->where('plan_id', $circular_user_to->plan_id)
                    ->get();
                foreach ($circular_user_to_items as $circular_user_to_item) {
                    DB::table('circular_operation_history')->insert([
                        'circular_id' => $circular_id,
                        'operation_email' => $login_user->email,
                        'operation_name' => $user_name,
                        'acceptor_email' => $circular_user_to_item ? $circular_user_to_item->email : '',
                        'acceptor_name' => $circular_user_to_item ? $circular_user_to_item->name : '',
                        'circular_status' => CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS,
                        'create_at' => Carbon::now(),
                    ]);
                }
            }else{
                DB::table('circular_operation_history')->insert([
                    'circular_id'=> $circular_id,
                    'operation_email' => $login_user->email,
                    'operation_name' => $user_name,
				'acceptor_email'=> $circular_user_to ? $circular_user_to->email : '',
				'acceptor_name'=> $circular_user_to ? $circular_user_to->name : '',
				'circular_status'=> CircularOperationHistoryUtils::CIRCULAR_SEND_BACK_STATUS,
				'create_at' => Carbon::now(),
			]);
            }

            //insert table mail_text
            DB::table('mail_text')
                ->insert([
                    'text'=> $request['text'] ? $request['text'] : '',
                    'circular_user_id' => $send_from_id,
                    'create_at' => Carbon::now()
                ]);
            $max_child_send_order = $circular_user_from->child_send_order;
            if ($circular_user_from->circular_status == CircularUserUtils::REVIEWING_STATUS ) {
                $max_child_send_order = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user_from->parent_send_order)
                    ->max('child_send_order');
                $max_child_send_order++;
            }

            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) < "
                    .str_pad($circular_user_from->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($max_child_send_order,3,'0', STR_PAD_LEFT))

                ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) > "
                    .str_pad($circular_user_to->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($circular_user_to->child_send_order,3,'0', STR_PAD_LEFT))
                ->where('child_send_order', '>', $circular_user_to->child_send_order)
                ->update([
                    'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                    'received_date' => null,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                    'node_flg' => CircularUserUtils::NODE_OTHER,
                    'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                ]);

            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('return_send_back', DB::raw(1))
                ->update([
                    'return_send_back' => 0,
                    'circular_status' => CircularUserUtils::SEND_BACK_STATUS,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                    'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                ]);
            if ($circular_user_from->circular_status != CircularUserUtils::REVIEWING_STATUS) {
                DB::table('circular_user')
                    ->where('id', $send_from_id)
                    ->update([
                        'circular_status' => CircularUserUtils::SEND_BACK_STATUS,
                        'update_at' => Carbon::now(),
                        'update_user' => $login_user->email,
                    ]);
            } else if ($circular_user_from->circular_status == CircularUserUtils::REVIEWING_STATUS && $circular_user_to->circular_status != CircularUserUtils::REVIEWING_STATUS) {
                if ($is_plan) {
                    $circular_user_from_items = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->where('plan_id', $circular_user_from->plan_id)
                        ->get();
                    foreach ($circular_user_from_items as $item) {
                        if ($item->node_flg == CircularUserUtils::NODE_COMPLETED){
                            $item_status = CircularUserUtils::NODE_COMPLETED_STATUS;
                        } else {
                            $item_status = $item->stamp_flg ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS;
                        }
                        DB::table('circular_user')
                            ->where('id', $item->id)
                            ->update([
                                'circular_status' => $item_status,
                                'return_send_back' => 1,
                                'update_at' => Carbon::now(),
                                'update_user' => $login_user->email,
                            ]);
                    }
                } else {
                    DB::table('circular_user')
                        ->where('id', $send_from_id)
                        ->update([
                            'circular_status' => $circular_user_from->stamp_flg ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                            'return_send_back' => 1,
                            'update_at' => Carbon::now(),
                            'update_user' => $login_user->email,
                        ]);
                }
            } else if ($circular_user_from->circular_status == CircularUserUtils::REVIEWING_STATUS && $circular_user_to->circular_status == CircularUserUtils::REVIEWING_STATUS) {
                if ($is_plan) {
                    DB::table('circular_user')
                        ->where('plan_id', $circular_user_from->plan_id)
                        ->where('circular_id', $circular_id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'return_send_back' => 1,
                            'update_at' => Carbon::now(),
                            'update_user' => $login_user->email,
                            'node_flg' => CircularUserUtils::NODE_OTHER
                        ]);
                } else {
                    DB::table('circular_user')
                        ->where('id', $send_from_id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'return_send_back' => 1,
                            'update_at' => Carbon::now(),
                            'update_user' => $login_user->email,
                            'node_flg' => CircularUserUtils::NODE_OTHER
                        ]);
                }
            }

            // 合議の場合、同じノード 状態変更
            if($is_template_circular_flg){
                DB::table('circular_user')
                    ->where('circular_user.circular_id', $circular_id)
                    ->where('circular_user.child_send_order', $circular_user_from->child_send_order)
                    ->where('circular_user.parent_send_order', $circular_user_from->parent_send_order)
                    ->where('id', '!=', $send_from_id)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $login_user->email,
                    ]);
            }
            $is_plan_to = false;
            if ($circular_user_to->plan_id > 0) {
                $is_plan_to = true;
                $circular_user_to_items_arr = DB::table('circular_user')
                    ->where('circular_id', $circular_user_to->circular_id)
                    ->where('plan_id', $circular_user_to->plan_id)
                    ->get();
            }
            if ($circular_user_from->parent_send_order != $circular_user_to->parent_send_order){
                Log::debug('Send back to other company');
                // get old document
                $oldPublicDocuments = DB::table('circular_document')->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user_to->parent_send_order)
                    ->where('confidential_flg', 0)
                    ->get();

                $mapOldPublicDocuments = [];
                foreach ($oldPublicDocuments as $oldPublicDocument){
                    $mapOldPublicDocuments[$oldPublicDocument->id] = $oldPublicDocument;
                }

                // delete old public document data, stamp, timestamp and text info
                $deletedIds = array_keys($mapOldPublicDocuments);
                DB::table('text_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
				// PAC_5-368 document_comment_info削除
				DB::table('document_comment_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('sticky_notes')->whereIn('document_id', $deletedIds)->delete();
                DB::table('time_stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('document_data')->whereIn('circular_document_id', $deletedIds)->delete();
				DB::table('circular_operation_history')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('circular_document')->whereIn('id', $deletedIds)->delete();

                // overwrite public document data
                DB::table('circular_document')->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user_from->parent_send_order)
                    ->where('confidential_flg', 0)
                    ->update([
                        'parent_send_order' => $circular_user_to->parent_send_order
                    ]);

                if($request['isRequestSendBack']) {
                    $queryDocuments = DB::table('circular_document')->where('circular_id', $circular_id)
                        ->where('parent_send_order','<', $circular_user_from->parent_send_order)
                        ->where('parent_send_order','>', $circular_user_to->parent_send_order);
                    $deletedIds = $queryDocuments->pluck('id');
                    DB::table('text_info')->whereIn('circular_document_id', $deletedIds)->delete();
                    DB::table('stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
					// PAC_5-368 document_comment_info削除
					DB::table('document_comment_info')->whereIn('circular_document_id', $deletedIds)->delete();
                    DB::table('sticky_notes')->whereIn('document_id', $deletedIds)->delete();
                    DB::table('time_stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
                    DB::table('document_data')->whereIn('circular_document_id', $deletedIds)->delete();
					DB::table('circular_operation_history')->whereIn('circular_document_id', $deletedIds)->delete();
                    $queryDocuments->delete();

                }

                // delete new document data, stamp, timestammp and text info
                $deletedIds = DB::table('circular_document')->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user_from->parent_send_order)
                    ->pluck('id');
                DB::table('text_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
				DB::table('document_comment_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('sticky_notes')->whereIn('document_id', $deletedIds)->delete();
                DB::table('time_stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('document_data')->whereIn('circular_document_id', $deletedIds)->delete();
				DB::table('circular_operation_history')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('circular_document')->whereIn('id', $deletedIds)->delete();

                // FROM:新Edition+他環境時  連携
                if ($circular_user_from->edition_flg == config('app.edition_flg')
                    && ($circular_user_from->env_flg != config('app.server_env') || $circular_user_from->server_flg != config('app.server_flg'))){
                    Log::debug('Send back to other application on new edition, update transferred document');
                    $this->sendUpdateTransferredDocumentStatusForSendback($circular, $circular_user_from->parent_send_order, $circular_user_from->child_send_order, $circular_user_to->parent_send_order, $circular_user_to->child_send_order, $title, $request['text'],$circular_user_from->env_flg,$circular_user_from->server_flg);
                }

                // TO:新Edition+他環境+FROMと別環境時  連携
                if ($circular_user_to->edition_flg == config('app.edition_flg')
                    && ($circular_user_to->env_flg != config('app.server_env') || $circular_user_to->server_flg != config('app.server_flg'))
                    && ($circular_user_to->edition_flg != $circular_user_from->edition_flg || $circular_user_to->env_flg != $circular_user_from->env_flg || $circular_user_to->server_flg != $circular_user_from->server_flg)){
                    Log::debug('Send back to other application on new edition, update transferred document');
                    $this->sendUpdateTransferredDocumentStatusForSendback($circular, $circular_user_from->parent_send_order, $circular_user_from->child_send_order, $circular_user_to->parent_send_order, $circular_user_to->child_send_order, $title, $request['text'],$circular_user_to->env_flg,$circular_user_to->server_flg);
                }

                // FROM TO:いずれが現行の場合  連携
                Log::debug('Send back to different edition, overwrite document');
                $circular_user_from->circular_status = CircularUserUtils::SEND_BACK_STATUS;
                $circular_user_to->circular_status = CircularUserUtils::NOTIFIED_UNREAD_STATUS;
                if ($circular_user_from->edition_flg != config('app.edition_flg')
                    || $circular_user_to->edition_flg != config('app.edition_flg')){
                    $this->sendBackWithOtherEdition($circular, $circular_user_from, $circular_user_to, $request['text']);
                }


            }else if ($circular_user_from->edition_flg == config('app.edition_flg')
                && ($circular_user_from->env_flg != config('app.server_env') || $circular_user_from->server_flg != config('app.server_flg'))){
                Log::debug('Send back in other application on new edition, update transferred document');
                $this->sendUpdateTransferredDocumentStatusForSendback($circular, $circular_user_from->parent_send_order, $circular_user_from->child_send_order, $circular_user_to->parent_send_order, $circular_user_to->child_send_order, $title, $request['text'],$circular_user_from->env_flg,$circular_user_from->server_flg);
            }

            DB::commit();

            Session::flash('mail_title', $title);
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        CircularUserUtils::summaryInProgressCircular($circular_id);

        if (count($mailData)){
            try{
                $email = $mailData['send_to'];
                unset($mailData['send_to']);
                $send_to_company = $mailData['send_to_company'];
                unset($mailData['send_to_company']);

                if(count($mailData['filenames'])){
                    $mailData['filenamestext'] = '';
                    foreach($mailData['filenames'] as $filename){
                        if ($mailData['filenamestext'] == '') {
                            $mailData['filenamestext'] = $filename;
                            continue;
                        }
                        $mailData['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                    }
                }else{
                    $mailData['filenamestext'] = '';
                }

                //利用者:回覧文書の差戻し通知
                if($is_template_circular_flg && isset($circular_user_to_items)){
                    // 合議の場合、同じノード メールで手紙を送る
                    foreach($circular_user_to_items as $circular_user_to_item){
                        if ($circular_user_to_item && CircularUserUtils::checkAllowReceivedEmail($circular_user_to_item->email, 'sendback',$circular_user_to_item->mst_company_id,$circular_user_to_item->env_flg,$circular_user_to_item->edition_flg,$circular_user_to_item->server_flg)) {
                            $email = $circular_user_to_item->email;
                            $mailData['receiver_name'] = $circular_user_to_item->name;
                            $mailData['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user_to_item->email, $circular_user_to_item->edition_flg, $circular_user_to_item->env_flg, $circular_user_to_item->server_flg, $circular_id);
                            if (isset($mailData['hide_circular_approval_url']) && !$mailData['hide_circular_approval_url']) {
                                $mailData['circular_approval_url_text'] = '回覧文書をみる:' . $mailData['circular_approval_url'] . '\r\n\r\n';
                            } else {
                                $mailData['circular_approval_url_text'] = '';
                            }

                            MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                                $email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['CIRCULAR_SEND_BACK_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($mailData, JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_sendback_template.subject', ['title' => $title]),
                                // メールボディ
                                trans('mail.circular_user_sendback_template.body', $mailData)
                            );
                        }
                    }
                } else if ($is_plan_to && isset($circular_user_to_items_arr)) {
                    // 合議の場合、同じノード メールで手紙を送る
                    foreach($circular_user_to_items_arr as $circular_user_to_item){
                        if ($circular_user_to_item && CircularUserUtils::checkAllowReceivedEmail($circular_user_to_item->email, 'sendback',$circular_user_to_item->mst_company_id,$circular_user_to_item->env_flg,$circular_user_to_item->edition_flg,$circular_user_to_item->server_flg)) {
                            $email = $circular_user_to_item->email;
                            $mailData['receiver_name'] = $circular_user_to_item->name;
                            $mailData['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user_to_item->email, $circular_user_to_item->edition_flg, $circular_user_to_item->env_flg, $circular_user_to_item->server_flg, $circular_id);
                            if (isset($mailData['hide_circular_approval_url']) && !$mailData['hide_circular_approval_url']) {
                                $mailData['circular_approval_url_text'] = '回覧文書をみる:' . $mailData['circular_approval_url'] . '\r\n\r\n';
                            } else {
                                $mailData['circular_approval_url_text'] = '';
                            }

                            MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                                $email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['CIRCULAR_SEND_BACK_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($mailData, JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_sendback_template.subject', ['title' => $title]),
                                // メールボディ
                                trans('mail.circular_user_sendback_template.body', $mailData)
                            );
                        }
                    }
                }else{
                    if ($circular_user_to && CircularUserUtils::checkAllowReceivedEmail($circular_user_to->email, 'sendback',$circular_user_to->mst_company_id,$circular_user_to->env_flg,$circular_user_to->edition_flg,$circular_user_to->server_flg)) {
                        $mailData['receiver_name'] = $circular_user_to->name;
                        $mailData['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user_to->email, $circular_user_to->edition_flg, $circular_user_to->env_flg, $circular_user_to->server_flg, $circular_id);
                        if (isset($mailData['hide_circular_approval_url']) && !$mailData['hide_circular_approval_url']) {
                            $mailData['circular_approval_url_text'] = '回覧文書をみる:' . $mailData['circular_approval_url'] . '\r\n\r\n';
                        } else {
                            $mailData['circular_approval_url_text'] = '';
                        }
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['CIRCULAR_SEND_BACK_NOTIFY']['CODE'],
                            // パラメータ
                            json_encode($mailData, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_sendback_template.subject', ['title' => $title]),
                            // メールボディ
                            trans('mail.circular_user_sendback_template.body', $mailData)
                        );
                    }
                }

                // PAC_5-445 アクセスコードが設定されている場合、アクセスコード通知メール（MAPP0012）を次の宛先に送信する。
                // 次の宛先が社内の場合
                if(isset($mailData['hide_circular_approval_url']) && !$mailData['hide_circular_approval_url']){
                    if ($circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID
                        && $circular_user_to->mst_company_id == $author_user->mst_company_id
                        && $circular_user_to->edition_flg == $author_user->edition_flg
                        && $circular_user_to->env_flg == $author_user->env_flg
                        && $circular_user_to->server_flg == $author_user->server_flg) {
                        $access_data['title'] = $title;
                        $access_data['access_code'] = $circular->access_code;

                        if($is_template_circular_flg && isset($circular_user_to_items)){
                            // 合議の場合、同じノード メールで手紙を送る
                            foreach($circular_user_to_items as $circular_user_to_item){
                                if ($circular_user_to_item && CircularUserUtils::checkAllowReceivedEmail($circular_user_to_item->email, 'sendback',$circular_user_to_item->mst_company_id,$circular_user_to_item->env_flg,$circular_user_to_item->edition_flg,$circular_user_to_item->server_flg)) {
                                    MailUtils::InsertMailSendResume(
                                    // 送信先メールアドレス
                                        $circular_user_to_item->email,
                                        // メールテンプレート
                                        MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                        // パラメータ
                                        json_encode($access_data, JSON_UNESCAPED_UNICODE),
                                        // タイプ
                                        AppUtils::MAIL_TYPE_USER,
                                        // 件名
                                        trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                        // メールボディ
                                        trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                    );
                                }
                            }
                        } else if ($is_plan_to && isset($circular_user_to_items_arr)) {
                            // 合議の場合、同じノード メールで手紙を送る
                            foreach($circular_user_to_items_arr as $circular_user_to_item){
                                if ($circular_user_to_item && CircularUserUtils::checkAllowReceivedEmail($circular_user_to_item->email, 'sendback',$circular_user_to_item->mst_company_id,$circular_user_to_item->env_flg,$circular_user_to_item->edition_flg,$circular_user_to_item->server_flg)) {
                                    MailUtils::InsertMailSendResume(
                                    // 送信先メールアドレス
                                        $circular_user_to_item->email,
                                        // メールテンプレート
                                        MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                        // パラメータ
                                        json_encode($access_data, JSON_UNESCAPED_UNICODE),
                                        // タイプ
                                        AppUtils::MAIL_TYPE_USER,
                                        // 件名
                                        trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                        // メールボディ
                                        trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                    );
                                }
                            }
                        }else {
                            //利用者:アクセスコードのお知らせ
                            if ($circular_user_to && CircularUserUtils::checkAllowReceivedEmail($circular_user_to->email, 'sendback', $circular_user_to->mst_company_id, $circular_user_to->env_flg, $circular_user_to->edition_flg, $circular_user_to->server_flg)) {
                                MailUtils::InsertMailSendResume(
                                // 送信先メールアドレス
                                    $circular_user_to->email,
                                    // メールテンプレート
                                    MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                    // パラメータ
                                    json_encode($access_data, JSON_UNESCAPED_UNICODE),
                                    // タイプ
                                    AppUtils::MAIL_TYPE_USER,
                                    // 件名
                                    trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                    // メールボディ
                                    trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                                );
                            }
                        }
                    }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                        && ($circular_user_to->mst_company_id != $author_user->mst_company_id
                            || $circular_user_to->edition_flg != $author_user->edition_flg
                            || $circular_user_to->env_flg != $author_user->env_flg
                            || $circular_user_to->server_flg != $author_user->server_flg)) {
                        // 次の宛先が社外の場合
                        $access_data['title'] = $title;
                        $access_data['access_code'] = $circular->outside_access_code;
                        if ($circular_user_to && CircularUserUtils::checkAllowReceivedEmail($circular_user_to->email, 'sendback', $circular_user_to->mst_company_id, $circular_user_to->env_flg, $circular_user_to->edition_flg, $circular_user_to->server_flg)) {
                            //利用者:アクセスコードのお知らせ
                            MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                                $circular_user_to->email,
                                // メールテンプレート
                                MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                                // パラメータ
                                json_encode($access_data, JSON_UNESCAPED_UNICODE),
                                // タイプ
                                AppUtils::MAIL_TYPE_USER,
                                // 件名
                                trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                                // メールボディ
                                trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                            );
                        }
                    }
                }
            }catch (\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
            }
        }
        // TODO translate message
        return $this->sendSuccess('差戻しの処理に成功しました。');
    }

    /**
     * 完了一覧リスト画面初期化
     *
     * @param SearchCircularUserAPIRequest $request
     * @return
     * @throws \Exception
     */
    public function indexCompleted(SearchCircularUserAPIRequest $request){
        $user       = $request->user();

        $kind           = $request->get('kind');
        $filename       = CircularDocumentUtils::charactersReplace($request->get('filename'));
        $senderName     = $request->get('senderName');
        $senderEmail    = $request->get('senderEmail');
        $destEnv        = $request->get('destEnv');
        $fromdate       = $request->get('fromdate');
        $todate         = $request->get('todate');
        $receiverName   = $request->get('receiverName');
        $receiverEmail  = $request->get('receiverEmail');
        $status         = $request->get('status', false);
        $page           = $request->get('page', 1);
        $limit          = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy        = $request->get('orderBy', "update_at");
        $orderDir       = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
        $keyword        = CircularDocumentUtils::charactersReplace($request->get('keyword'));
        $system_env_flg     = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $templateFrom   = $request->get('templateFrom');
        $templateTo     = $request->get('templateTo');
        $templateNum    = $request->get('templateNum');
        $templateText   = $request->get('templateText');
        $useTemplate    = false;
        $system_server_flg = config('app.server_flg');

        // 回覧完了日時
        $finishedDateKey = $request->get('finishedDate');
        // 当月
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }

        $arrOrder   = ['circular_kind' => 'circular_kind','file_names' => 'file_names', 'sender' => 'sender',
            'emails' => 'emails', 'update_at' => 'update_at'];
        $orderBy = isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at';

        $where = [];
        $where_arg = [];
        $where_temp = [];
        $where_arg_temp = [];

        if (isset($kind) && $kind === '0') {
            // 受信 PAC_5-1303 送信者自身が受信者に含まれるケースの回避 U.child_send_order != 0 → C.mst_user_id != U.mst_user_id
            $where[] = "(U.email = ? and C.mst_user_id != U.mst_user_id AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?)";
            $where_arg[] = $user->email;
            $where_arg[] = CircularUserUtils::NOT_DELETE;
            $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
            $where_arg[] = $system_edition_flg;
            $where_arg[] = $system_env_flg;
            $where_arg[] = $system_server_flg;
        } else if (isset($kind) && $kind === '1') {
            // 送信 PAC_5-1303 受信側の条件に合わせる
            $where[] = "(U.email = ? and C.mst_user_id = U.mst_user_id AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?)";
            $where_arg[] = $user->email;
            $where_arg[] = CircularUserUtils::NOT_DELETE;
            $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
            $where_arg[] = $system_edition_flg;
            $where_arg[] = $system_env_flg;
            $where_arg[] = $system_server_flg;
        } else {
            $where[] = "(U.email = ? AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?)";
            $where_arg[] = $user->email;
            $where_arg[] = CircularUserUtils::NOT_DELETE;
            $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
            $where_arg[] = $system_edition_flg;
            $where_arg[] = $system_env_flg;
            $where_arg[] = $system_server_flg;
        }

        if($filename){
            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?))';
            $where_arg[]    = "%$filename%";
            $where_arg[]    = "%$filename%";
        }
        if($senderName){
            $where[]        = 'U.sender_name like ?';
            $where_arg[]    = "%$senderName%";
        }
        if($senderEmail){
            $where[]        = 'U.sender_email like ?';
            $where_arg[]    = "%$senderEmail%";
        }
        if($receiverName){
            $where[]        = 'U.receiver_name like ?';
            $where_arg[]    = "%$receiverName%";
        }
        if($receiverEmail){
            $where[]        = 'U.receiver_email like ?';
            $where_arg[]    = "%$receiverEmail%";
        }
        if($fromdate){
            $where[]        = 'C.completed_date >= ?';
            $where_arg[]    = $fromdate;
        }
        if($todate){
            $where[]        = 'C.completed_date < ?';
            $where_arg[]    = (new \DateTime($todate))->modify('+1 day')->format('Y-m-d');
        }
        if ($destEnv) {
            $destenv_flgs = str_split($destEnv);
            $where[]        = 'U.edition_flg = ?';
            $where_arg[]    = $destenv_flgs[0];
            $where[]        = 'U.env_flg = ?';
            $where_arg[]    = $destenv_flgs[1];
            $where[]        = 'U.server_flg = ?';
            $where_arg[]    = $destenv_flgs[2];
        }
        if ($keyword) {
            $where[]        = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?) OR U.sender_name like ? OR U.receiver_name like ? OR U.sender_email like ? OR U.receiver_email like ?)';
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
            $where_arg[]    = "%$keyword%";
        }
        if ((isset($templateFrom) && trim($templateFrom) )|| (isset($templateTo) && trim($templateTo)) || (isset($templateNum) && trim($templateNum) )|| (isset($templateText)&& trim($templateText)) ){
            $useTemplate = true;
        }
        if (isset($templateFrom) && trim($templateFrom)) {
            $where_temp[]     = 'date_data >= ?';
            $where_arg_temp[] = $templateFrom;
        }
        if (isset($templateTo) && trim($templateTo)) {
            $where_temp[]     = 'date_data < ?';
            $where_arg_temp[] = $templateTo;
        }
        if (isset($templateNum) && trim($templateNum)) {
            $where_temp[]     = 'num_data = ?';
            $where_arg_temp[] = $templateNum;
        }
        if (isset($templateText) && trim($templateText)) {
            $where_temp[]     = "text_data like ?";
            $where_arg_temp[] = "%".$templateText."%";
        }
        try{
            // PAC_5-2114 Start
            // 統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                throw new \Exception('Cannot connect to ID App');
            }

            $id_app_user_id = 0;
            $response = $client->post("users/checkEmail", [
                RequestOptions::JSON => ['email' => $user->email]
            ]);
            if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                $resData = json_decode((string) $response->getBody());
                if(!empty($resData) && !empty($resData->data)){
                    $id_app_users = $resData->data;
                    // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                    foreach ($id_app_users as $id_app_user) {
                        if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                            $id_app_user_id = $id_app_user->id;
                            break;
                        }
                    }
                }
            }
            $query = DB::table("circular$finishedDate as C")
                ->join("circular_user$finishedDate as U", function ($join) use ($user) {
                    $join->on('C.id', 'U.circular_id')
                        ->on('U.mst_company_id', DB::raw($user->mst_company_id));
                })
                ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                    $query->on('C.id', 'auto_his.circular_id')
                        ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
                })
                ->selectRaw('C.id, C.special_site_flg, C.access_code, C.outside_access_code, CASE WHEN C.mst_user_id = ' . $user->id  .
                ' and C.edition_flg = '.config('app.edition_flg') . ' and C.env_flg = ' . config('app.server_env') . ' and C.server_flg = ' . config('app.server_flg'). ' THEN 1 ELSE 0 END AS circular_kind,
                CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender_env,C.completed_date as update_at, C.circular_status, U.title as file_names,
                U.receiver_title as d_file_names,CONCAT(U.sender_name, \' &lt;\',U.sender_email, \'&gt;\') as sender, U.receiver_name_email AS emails,
                U.sender_name as sender_name, auto_his.result')
                ->whereRaw(implode(" AND ", $where), $where_arg);
            //PAC_5-2114 Start
            $data = $query->where(function ($query) use ($kind, $user, $id_app_user_id) {
                if (isset($kind) && $kind === '1') {
                    $query->where('U.mst_user_id', $user->id);
                } else {
                    $query->whereIn('U.mst_user_id', array_filter([$user->id, $id_app_user_id]));
                }
            })
            // PAC_5-2114 End
                ->whereNotNull("C.completed_date")
                // PAC_5-1664:回覧破棄をすると削除ステータスになり、利用者からは見えないくなる    期待した結果  完了一覧にはいる
                ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);

            // 当月又は上月コピーなし条件追加
            if (0 == $finishedDateKey || null == $finishedDateKey) {
                $data->whereRaw("DATE_FORMAT( C.completed_date, '%Y%m' ) = ".date('Ym'));
            }

            if($useTemplate) {
                $idByTemplates = DB::table('template_input_data')
                    ->select('circular_id')
                    ->whereRaw(implode(" AND ", $where_temp), $where_arg_temp)
                    ->distinct()
                    ->get();

                $ids = array();
                foreach ($idByTemplates as $value) {
                    $ids[] = $value->circular_id;
                }
                Log::debug($idByTemplates);
                Log::debug($ids);
                $data->whereIn('C.id', $ids);
            }

            $data = $data->groupByRaw('C.id, U.sender_name, U.sender_email, U.title, U.receiver_title, U.receiver_name_email, result')
                ->orderBy($orderBy, $orderDir)
                ->paginate($limit)
                ->appends(request()->input());

            // 件名設定
            foreach ($data as $item) {
                if (!$item->file_names || trim($item->file_names, ' ') == '') {
                    $fileNames = explode(', ', $item->d_file_names);
                    $item->file_names = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames), 0, 100) : reset($fileNames);
                }
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($data, __('message.success.data_get', ['attribute'=>'完了文書']));
    }

    /**
     * 閲覧一覧リスト画面初期化
     *
     * @param SearchCircularUserAPIRequest $request
     * @return mixed
     */
    public function indexViewing(SearchCircularUserAPIRequest $request){
        try {
            $user = $request->user();

            // 画面検索項目
            $filename = CircularDocumentUtils::charactersReplace($request->get('filename'));
            $senderName = $request->get('senderName');
            $senderEmail = $request->get('senderEmail');
            $state = $request->get('state');
            $limit = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $orderBy = $request->get('orderBy', "update_at");
            $orderDir = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

            // 回覧完了日時
            $finishedDateKey = $request->get('finishedDate');
            // 当月又は回覧中
            if (!$finishedDateKey || !$state) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            $arrOrder = ['file_names' => 'file_names', 'sender' => 'sender',
                'emails' => 'emails', 'update_at' => 'update_at'];
            $orderBy = $arrOrder[$orderBy] ?? 'update_at';

            $where = [];
            $where_arg = [];

            if ($filename) {
                $where[] = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?))';
                $where_arg[] = "%$filename%";
                $where_arg[] = "%$filename%";
            }
            if ($senderName) {
                $where[] = 'D.sender_name like ?';
                $where_arg[] = "%$senderName%";
            }
            if ($senderEmail) {
                $where[] = 'D.sender_email like ?';
                $where_arg[] = "%$senderEmail%";
            }
            if ($state) {
                $stateArr = [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS];
            } else {
                $stateArr = [CircularUtils::CIRCULATING_STATUS];
            }

            // 差出人
            $query = DB::table("circular_user$finishedDate as U")
                ->join('viewing_user as V', 'U.circular_id', 'V.circular_id')
                ->selectRaw('U.circular_id, IF(U.sender_name IS NULL,U.name,U.sender_name) as sender_name, IF(U.sender_email IS NULL,U.email,U.sender_email) as sender_email')
                ->where('U.parent_send_order', 0)
                ->where('U.child_send_order', 0);

            $data = DB::table("circular$finishedDate as C")
                ->join("circular_user$finishedDate as U", function ($join) use ($user) {
                    $join->on('C.id', 'U.circular_id')
                        ->where('U.parent_send_order', 0)
                        ->where('U.child_send_order', 0);
                })
                ->join("viewing_user as V", function ($join) use ($user) {
                    $join->on('C.id', 'V.circular_id')
                        ->on('V.mst_user_id', DB::raw($user->id));
                })
                ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                    $query->on('C.id', 'auto_his.circular_id')
                        ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
                })
                ->joinSub($query, 'D', function ($join) {
                    $join->on('C.id', 'D.circular_id');
                })
                ->selectRaw('C.id, C.access_code, C.outside_access_code, CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender_env, C.completed_date as update_at,
                C.circular_status, U.title as file_names, U.receiver_title as d_file_names,CONCAT(D.sender_name, \' &lt;\',D.sender_email, \'&gt;\') as sender,
                D.sender_name as sender_name, auto_his.result,
                CASE C.circular_status WHEN 1 THEN \'回覧中\' WHEN 2 THEN \'回覧完了\' WHEN 3 THEN \'回覧完了\' ELSE \'\' END state')
                ->whereIn('C.circular_status', $stateArr);

            if (!empty($where)) {
                $data = $data->whereRaw(implode(" AND ", $where), $where_arg);
            }

            // 当月又は上月コピーなし条件追加
            if (!$finishedDateKey && $state) {
                $data->whereRaw("DATE_FORMAT( C.completed_date, '%Y%m' ) = " . date('Ym'));
            }

            $data = $data->groupByRaw('C.id, D.sender_name, D.sender_email, U.title, U.receiver_title, auto_his.result')
                ->orderBy($orderBy, $orderDir)
                ->orderBy('id', 'desc')
                ->paginate($limit)
                ->appends(request()->input());

            // 件名設定
            foreach ($data as $item) {
                if (!$item->file_names || trim($item->file_names, ' ') == '') {
                    $fileNames = explode(', ', $item->d_file_names);
                    $item->file_names = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames), 0, 100) : reset($fileNames);
                }
            }
            return $this->sendResponse($data, __('message.success.data_get', ['attribute'=>'閲覧文書']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * update status for a transferred Circular in storage.
     * POST /updateStatus
     *
     * @param UpdateTransferredStatusAPIRequest $request
     *
     * @return Response
     */
    public function receiveUpdateTransferredStatus(UpdateTransferredStatusAPIRequest $request)
    {
        Log::debug("UpdateTransferredStatusAPIRequest param: " . print_r($request->all(), true));

        $mailDatas = [];
        $noticeMailDatas = [];
        $is_circular_completed = false;// 回覧完成フラグ
        $circular_id = $request['circular_id'] ;
        try {
            DB::beginTransaction();
            $circular_env_flg = isset($request['circular_env_flg'])?$request['circular_env_flg']:(isset($request['env_flg'])?$request['env_flg']:null);
            $circular_edition_flg = isset($request['circular_edition_flg'])?$request['circular_edition_flg']: (isset($request['edition_flg'])?$request['edition_flg']:null);
            $circular_server_flg = isset($request['circular_server_flg'])?$request['circular_server_flg']: (isset($request['server_flg'])?$request['server_flg']:null);
            if ($circular_edition_flg !== null && $circular_env_flg !== null && $circular_server_flg !== null
                && ($circular_edition_flg != config('app.edition_flg') || $circular_env_flg != config('app.server_env') || $circular_server_flg != config('app.server_flg'))){
                $circular = DB::table('circular')
                    ->where('origin_circular_id', $circular_id)
                    ->where('env_flg', $circular_env_flg)
                    ->where('edition_flg', $circular_edition_flg)
                    ->where('server_flg', $circular_server_flg)
                    ->first();
                Log::debug("updateTransferredStatus find circular for $circular_id on env $circular_env_flg and server $circular_server_flg and edition $circular_edition_flg ");
                if ($circular){
                    $circular_id = $circular->id;
                    Log::debug("updateTransferredStatus found circular $circular_id on env $circular_env_flg and server $circular_server_flg and edition $circular_edition_flg ");
                }else{
                    DB::rollBack();
                    return $this->sendError(['status'=>\Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message'=> 'Param Circular is invalid'], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                }
            }

            Log::debug("updateTransferredStatus for circular $circular_id on env $circular_env_flg and server $circular_server_flg and edition $circular_edition_flg ");
            $updateParams = [];
            if (isset($request['current_k5_circular_id']) && $request['current_k5_circular_id']){
                $updateParams['current_k5_circular_id'] = $request['current_k5_circular_id'];
            }
            if (isset($request['current_aws_circular_id']) && $request['current_aws_circular_id']){
                $updateParams['current_aws_circular_id'] = $request['current_aws_circular_id'];
            }
            if (count($updateParams)){
                $updateParams['final_updated_date'] = Carbon::now();
                DB::table('circular')
                    ->where('id', $circular_id)
                    ->update($updateParams);
            }

            $circular_user = DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('parent_send_order', $request['parent_send_order'])
                ->where('child_send_order', $request['child_send_order'])->first();

            if (!$circular_user){
                DB::rollBack();
                return $this->sendError(['status'=>\Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message'=> 'Param Circular User is invalid'], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            if ($request['circular_status'] != CircularUserUtils::READ_STATUS){
                DB::table('mail_text')
                    ->insert([
                        'text' => $request['text']?:'',
                        'circular_user_id' =>$circular_user->id,
                        'create_at' => Carbon::now()
                    ]);

                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('return_send_back', DB::raw(1))
                    ->update([
                        'return_send_back' => 0,
                    ]);
            }

            DB::table('circular_user')->where('id', $circular_user->id)->update([
                'circular_status' => $request['circular_status']==CircularUserUtils::END_OF_REQUEST_SEND_BACK?CircularUserUtils::NOT_NOTIFY_STATUS:$request['circular_status'],
                'title' => $request['title']?:'',
                'update_at' => Carbon::now(),
                'update_user' => $circular_user->email,
                'is_skip' => !empty($request['is_skip']) ? $request['is_skip'] : CircularUserUtils::IS_SKIP_ACTION_FALSE,// PAC_5-2352
            ]);
            if (isset($request['circular_documents']) || isset($request['document_datas']) || isset($request['stamp_infos']) || isset($request['text_infos']) || isset($request['time_stamp_infos'])){
                if (isset($request['circular_documents'])){

                    DB::table('circular_document')
                        ->where('circular_id', $circular_id)
                        ->where('parent_send_order', '>=', $request['parent_send_order'])->delete();

                    $circular_documents = [];
                    foreach ($request['circular_documents'] as $circularDocument){
                        $circular_document = $circularDocument;

                        $circular_document['circular_id'] = $circular_id;
                        $circular_document['origin_env_flg'] = $circular_document['env_flg'];
                        $circular_document['origin_edition_flg'] = $circular_document['edition_flg'];
                        $circular_document['origin_server_flg'] = $circular_document['server_flg'];
                        $circular_document['create_at'] = Carbon::now();
                        $circular_document['update_at'] = Carbon::now();
                        $circular_document['create_user'] = $circular_user->email;
                        $circular_document['update_user'] = $circular_user->email;
                        unset($circular_document['env_flg']);
                        unset($circular_document['edition_flg']);
                        unset($circular_document['server_flg']);
                        $circular_documents[] = $circular_document;
                    }
                    DB::table('circular_document')->insert($circular_documents);
                }

                $insertedCircularDocs = DB::table('circular_document')->select('id', 'origin_document_id')->where('circular_id', $circular_id)->get();

                $mapNewDocumentId = [];
                foreach($insertedCircularDocs as $insertedCircularDoc){
                    $mapNewDocumentId[$insertedCircularDoc->origin_document_id] = $insertedCircularDoc->id;
                }

                if (isset($request['document_datas'])){
                    $circular_datas = [];
                    foreach ($request['document_datas'] as $circularData){
                        $circular_data = $circularData;

                        $circular_data['circular_document_id'] = $mapNewDocumentId[$circularData['circular_document_id']];
                        $circular_data['create_at'] = Carbon::now();
                        $circular_data['update_at'] = Carbon::now();
                        $circular_data['create_user'] = $circular_user->email;
                        $circular_data['update_user'] = $circular_user->email;

                        $circular_datas[] = $circular_data;
                    }
                    if (count($request['document_datas'])){
                        DB::table('document_data')->insert($circular_datas);
                    }
                }

                if (isset($request['stamp_infos'])){
                    $circular_stamps = [];
                    foreach ($request['stamp_infos'] as $circularStamp){
                        $circular_stamp = $circularStamp;

                        $circular_stamp['circular_document_id'] = $mapNewDocumentId[$circularStamp['circular_document_id']];

                        $circular_stamps[] = $circular_stamp;
                    }
                    if (count($request['stamp_infos'])){
                        DB::table('stamp_info')->insert($circular_stamps);
                    }
                }

				// PAC_5-368 document_comment_info登録
				if (isset($request['comment_infos'])){
					$circular_comments = [];
					foreach ($request['comment_infos'] as $circularComment){
						$circular_comment = $circularComment;

						$circular_comment['circular_document_id'] = $mapNewDocumentId[$circularComment['circular_document_id']];

						$circular_comments[] = $circular_comment;
					}
					if (count($request['comment_infos'])){
						DB::table('document_comment_info')->insert($circular_comments);
					}
				}

                if (isset($request['time_stamp_infos'])){
                    $circular_timestamps = [];
                    foreach ($request['time_stamp_infos'] as $circularTimestamp){
                        $circular_timestamp = $circularTimestamp;

                        $circular_timestamp['circular_document_id'] = $mapNewDocumentId[$circularTimestamp['circular_document_id']];

                        $circular_timestamps[] = $circular_timestamp;
                    }
                    if (count($request['time_stamp_infos'])){
                        DB::table('time_stamp_info')->insert($circular_timestamps);
                    }
                }

                if (isset($request['text_infos'])){
                    $circular_texts = [];
                    foreach ($request['text_infos'] as $circularText){
                        $circular_text = $circularText;

                        $circular_text['circular_document_id'] = $mapNewDocumentId[$circularText['circular_document_id']];

                        $circular_texts[] = $circular_text;
                    }
                    if (count($request['text_infos'])){
                        DB::table('text_info')->insert($circular_texts);
                    }
                }
            }
            if ($request['circular_status'] == CircularUserUtils::SEND_BACK_STATUS){
                Log::debug("updateTransferredStatus to send back, overwrite document");
                if (isset($request['sendback_child_send_order'])&&isset($request['sendback_parent_send_order'])){
                    $back_circular_user = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->where('parent_send_order', '=', $request['sendback_parent_send_order'])
                        ->where('child_send_order', '=', $request['sendback_child_send_order'])
                        ->first();
                }else{
                    $back_circular_user = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->where('parent_send_order', '<', $request['parent_send_order'])
                        ->orderBy('parent_send_order', 'desc')
                        ->orderBy('child_send_order', 'asc')
                        ->first();
                }
                if (!$back_circular_user){
                    DB::rollBack();
                    return $this->sendError(['status'=>\Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message'=> 'Param Back Circular User is invalid'], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                }

                DB::table('circular')
                    ->where('id', $circular_id)
                    ->update([
                        'circular_status' => CircularUtils::SEND_BACK_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                        'final_updated_date' => Carbon::now(),
                    ]);
                $max_child_send_order = $circular_user->child_send_order;
                $circularSendBackUserStatus = CircularUserUtils::SEND_BACK_STATUS;
                $return_send_back = 0;
                if (isset($request['sendback_child_send_order'])&&isset($request['sendback_parent_send_order'])) {

                    if ($circular_user->circular_status == CircularUserUtils::REVIEWING_STATUS && $back_circular_user->parent_send_order == $circular_user->parent_send_order) {
                        $max_child_send_order = DB::table('circular_user')
                            ->where('circular_id', $circular_id)
                            ->where('parent_send_order', $circular_user->parent_send_order)
                            ->max('child_send_order');
                        $max_child_send_order++;

                        if ($back_circular_user->child_send_order != $circular_user->child_send_order) {
                            if ($circular_user->plan_id > 0) {
                                $circular_user_from_items = DB::table('circular_user')
                                    ->where('circular_id', $circular_id)
                                    ->where('plan_id', $circular_user->plan_id)
                                    ->get();
                                foreach ($circular_user_from_items as $item) {
                                    if ($item->node_flg == CircularUserUtils::NODE_COMPLETED){
                                        $item_status = CircularUserUtils::NODE_COMPLETED_STATUS;
                                    } else {
                                        $item_status = $item->stamp_flg ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS;
                                    }
                                    if ($item->id === $circular_user->id) {
                                        $circularSendBackUserStatus = $item_status;
                                    }
                                    DB::table('circular_user')
                                        ->where('id', $item->id)
                                        ->update([
                                            'circular_status' => $item_status,
                                            'return_send_back' => 1,
                                            'update_at' => Carbon::now(),
                                            'update_user' => $circular_user->email,
                                        ]);
                                }
                            } else {
                                $circularSendBackUserStatus = $circular_user->stamp_flg ? CircularUserUtils::APPROVED_WITH_STAMP_STATUS : CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS;
                            }
                            $return_send_back = 1;
                        } else {
                            $circularSendBackUserStatus = CircularUserUtils::NOTIFIED_UNREAD_STATUS;
                            $return_send_back = 1;
                        }
                    }
                }

                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) < "
                        .str_pad($circular_user->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($max_child_send_order,3,'0', STR_PAD_LEFT))
                    ->whereRaw("CONCAT(LPAD(parent_send_order, 3, '0'),LPAD(child_send_order, 3, '0')) > "
                        .str_pad($back_circular_user->parent_send_order,3,'0', STR_PAD_LEFT).str_pad($back_circular_user->child_send_order,3,'0', STR_PAD_LEFT))
                    ->where('child_send_order', '>', $back_circular_user->child_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'received_date' => null,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);

                DB::table('circular_user')
                    ->where('id', $circular_user->id)
                    ->update([
                        'circular_status' => $circularSendBackUserStatus,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                        'return_send_back' => $return_send_back,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);

                if (isset($request['view_url']) && $request['view_url']){
                    DB::table('circular_user')
                        ->where('id', $back_circular_user->id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at'=> Carbon::now(),
                            'update_user'=> $circular_user->email,
                            'origin_circular_url' => (isset($request['view_url'])?$request['view_url']:''),
                            'node_flg' => CircularUserUtils::NODE_OTHER,
                            'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                        ]);
                }else{
                    DB::table('circular_user')
                        ->where('id', $back_circular_user->id)
                        ->update([
                            'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                            'received_date' => Carbon::now(),
                            'update_at'=> Carbon::now(),
                            'update_user'=> $circular_user->email,
                            'node_flg' => CircularUserUtils::NODE_OTHER,
                            'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                        ]);
                }

                // get old document
                $oldPublicDocuments = DB::table('circular_document')->where('circular_id', $circular_id)
                    ->where('parent_send_order', $back_circular_user->parent_send_order)
                    ->where('confidential_flg', 0)
                    ->get();

                $deletedIds = [];
                foreach ($oldPublicDocuments as $oldPublicDocument){
                    $deletedIds[] = $oldPublicDocument->id;
                }

                // delete old public document data, stamp, timestamp and text info
                DB::table('text_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
				// PAC_5-368 document_comment_info削除
				DB::table('document_comment_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('sticky_notes')->whereIn('document_id', $deletedIds)->delete();
                DB::table('time_stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('document_data')->whereIn('circular_document_id', $deletedIds)->delete();
				DB::table('circular_operation_history')->whereIn('circular_document_id', $deletedIds)->delete();
                DB::table('circular_document')->whereIn('id', $deletedIds)->delete();

                DB::table('circular_document')->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user->parent_send_order)
                    ->where('confidential_flg', 0)
                    ->update(['parent_send_order' => $back_circular_user->parent_send_order]);
            }else if ($request['circular_status'] == CircularUserUtils::PULL_BACK_TO_USER_STATUS){
                Log::debug("updateTransferredStatus to pull back, overwrite document");

                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user->parent_send_order)
                    ->where('child_send_order','>', $circular_user->child_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order','>', $circular_user->parent_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
            }else if ($request['circular_status'] == CircularUserUtils::SUBMIT_REQUEST_SEND_BACK){
                Log::debug("updateTransferredStatus to request sendback, do nothing");
            }else if ($request['circular_status'] == CircularUserUtils::END_OF_REQUEST_SEND_BACK){
                Log::debug("updateTransferredStatus to approve request sendback");

                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $circular_user->parent_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOT_NOTIFY_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                        'node_flg' => CircularUserUtils::NODE_OTHER,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);
            }else if ($request['circular_status'] != CircularUserUtils::READ_STATUS){
                // get window user
                $windowUser = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('parent_send_order', $request['parent_send_order'])
                    ->where('child_send_order', $request['parent_send_order']?1:0)
                    ->first();

                // get next user
                $next_circular_user = DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->whereIn('circular_status', [CircularUserUtils::NOT_NOTIFY_STATUS, CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::SEND_BACK_STATUS])
                    ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                    ->orderBy('parent_send_order', 'asc')
                    ->orderBy('child_send_order', 'asc')
                    ->first();

                if ($windowUser->return_flg && (!$next_circular_user || ($next_circular_user && $next_circular_user->parent_send_order != $circular_user->parent_send_order ))
                    && $windowUser->circular_status != CircularUserUtils::REVIEWING_STATUS && ($windowUser->child_send_order != $circular_user->child_send_order)) {
                    // The sender is in the last company and there is the window user in the last company
                    // OR The sender send to the next company and there is the window user in the current company
                    $updateData = [
                        'circular_status' => CircularUserUtils::REVIEWING_STATUS,
                        'received_date' => Carbon::now(),
                        'update_at'=> Carbon::now(),
                        'update_user'=> $circular_user->email,
                    ];
                    if (isset($request['view_url']) && $request['view_url']){
                        $updateData['origin_circular_url'] = isset($request['view_url'])?$request['view_url']:'';
                    }
                    DB::table('circular_user')->where('id', $windowUser->id)->update($updateData);
                }else if ($next_circular_user){
                    Log::debug('continue circular, copy document!');
                    if ($next_circular_user->circular_status == CircularUserUtils::SEND_BACK_STATUS){
                        DB::table('circular')
                            ->where('id', $circular_id)
                            ->update([
                                'circular_status' => CircularUtils::CIRCULATING_STATUS,
                                'update_at'=> Carbon::now(),
                                'update_user'=> $circular_user->email,
                            ]);
                    }
                    if ($next_circular_user->circular_status == CircularUserUtils::SEND_BACK_STATUS && $next_circular_user->parent_send_order != $circular_user->parent_send_order){
                        // get old document
                        $oldPublicDocuments = DB::table('circular_document')->where('circular_id', $circular_id)
                            ->where('parent_send_order', $next_circular_user->parent_send_order)
                            ->where('confidential_flg', 0)
                            ->get();

                        $deletedIds = [];
                        foreach ($oldPublicDocuments as $oldPublicDocument){
                            $deletedIds[] = $oldPublicDocument->id;
                        }

                        // delete old public document data, stamp, timestamp and text info
                        DB::table('text_info')->whereIn('circular_document_id', $deletedIds)->delete();
                        DB::table('stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
						// PAC_5-368 document_comment_info削除
						DB::table('document_comment_info')->whereIn('circular_document_id', $deletedIds)->delete();
                        DB::table('sticky_notes')->whereIn('document_id', $deletedIds)->delete();
                        DB::table('time_stamp_info')->whereIn('circular_document_id', $deletedIds)->delete();
                        DB::table('document_data')->whereIn('circular_document_id', $deletedIds)->delete();
						DB::table('circular_operation_history')->whereIn('circular_document_id', $deletedIds)->delete();
                        DB::table('circular_document')->whereIn('id', $deletedIds)->delete();
                    }
                    if (isset($request['view_url']) && $request['view_url']){
                        DB::table('circular_user')
                            ->where('id', $next_circular_user->id)
                            ->update([
                                'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                'update_at'=> Carbon::now(),
                                'received_date' => Carbon::now(),
                                'update_user'=> $circular_user->email,
                                'origin_circular_url' => (isset($request['view_url'])?$request['view_url']:''),
                                'node_flg' => CircularUserUtils::NODE_OTHER
                            ]);
                    }else{
                        DB::table('circular_user')
                            ->where('id', $next_circular_user->id)
                            ->update([
                                'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                                'received_date' => Carbon::now(),
                                'update_at'=> Carbon::now(),
                                'update_user'=> $circular_user->email,
                                'node_flg' => CircularUserUtils::NODE_OTHER,
                                'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                            ]);
                    }

                    if ($next_circular_user->parent_send_order != $circular_user->parent_send_order){
                        $this->copyDocument($circular_id, $request['parent_send_order'], $next_circular_user->parent_send_order);
                    }
                }else{
                    Log::debug('finish circular');
                    $is_circular_completed = true;
                    DB::table('circular_user')->where('id', $circular_id)->where('circular_status', CircularUserUtils::REVIEWING_STATUS)->update([
                        'circular_status' => CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                        'update_at' => Carbon::now(),
                        'update_user' => $circular_user->email,
                    ]);
                    DB::table('circular')->where('id', $circular_id)->update([
                        'circular_status' => CircularUtils::CIRCULAR_COMPLETED_STATUS,
                        'completed_date' => Carbon::now(),
                        'update_at' => Carbon::now(),
                        'update_user' => $circular_user->email,
                        'final_updated_date' => Carbon::now(),
                    ]);

                    // get all old public document id
                    $oldPublicDocumentIds = DB::table('circular_document')->where('circular_id',$circular_id)
                        ->where('confidential_flg', 0)
                        ->where('parent_send_order', '!=', $request['parent_send_order'])
                        ->pluck('id');

                    // delete old public document data, stamp, timestamp and text info
                    DB::table('text_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                    DB::table('stamp_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
					// PAC_5-368 document_comment_info削除
					DB::table('document_comment_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                    DB::table('sticky_notes')->whereIn('document_id', $oldPublicDocumentIds)->delete();
                    DB::table('time_stamp_info')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                    DB::table('document_data')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
					DB::table('circular_operation_history')->whereIn('circular_document_id', $oldPublicDocumentIds)->delete();
                    DB::table('circular_document')->whereIn('id', $oldPublicDocumentIds)->delete();

                    // update origin_document_id of new public document data
                    DB::table('circular_document')->where('circular_id', $circular_id)
                        ->where('confidential_flg', 0)
                        ->where('parent_send_order', $request['parent_send_order'])
                        ->update(['origin_document_id' => 0]);

                    Log::debug('send email viewer');
                    $viewingUsers = DB::table('viewing_user')
                        ->where('circular_id', $circular_id)
                        ->get();

                    $userIds = [];
                    foreach ($viewingUsers as $viewingUser) {
                        $userIds[] = $viewingUser->mst_user_id;
                    }

                    $mapUserInfoIds = [];
                    $mapCompanyIds = [];

                    if (count($userIds)){
                    $mstUsers = DB::table('mst_user')
                            ->select( 'email', DB::raw('id as mst_user_id'),'mst_company_id',DB::raw('CONCAT(family_name,\' \',given_name) as name'))
                            ->orWhereIn('id', $userIds)
                        ->get();
                    foreach ($mstUsers as $mstUser){
                        $mapUserInfoIds[$mstUser->mst_user_id] = $mstUser;
                            $mapCompanyIds[$mstUser->mst_company_id] = null;
                    }
                    }

                    $mapCompanyIds = $this->companyRepository->getSameEnvCompanies($mapCompanyIds);

                    $circularDocuments = DB::table('circular_document')
                        ->where('circular_id', $circular_id)
                        ->orderby('id')
                        ->get()->toArray();
                    $firstDocument = null;
                    foreach($circularDocuments as $circular_doc ){
                        if ($firstDocument === null || $firstDocument->circular_id < $circular_doc->circular_id){
                            $firstDocument = $circular_doc;
                        }
                    }

                    $circular = DB::table('circular')
                        ->where('id', $circular_id)
                        ->where('env_flg', $circular_env_flg)
                        ->where('edition_flg', $circular_edition_flg)
                        ->where('server_flg', $circular_server_flg)
                        ->first();

                    $last_circular_user = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->whereRaw('NOT (parent_send_order =0 AND child_send_order = 0)')
                        ->orderBy('parent_send_order', 'desc')
                        ->orderBy('child_send_order', 'desc')
                        ->first();

                    // プレビュー作成
                    $previewPath = null;
                    $noPreviewPath =  public_path()."/images/no-preview.png";
                    if (!$circular->hide_thumbnail_flg) {
                        if($circular->first_page_data){
                            $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                            file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                        }else{
                            $previewPath = $noPreviewPath;
                        }
                    }
                    foreach ($viewingUsers as $viewingUser) {
                        if (CircularUserUtils::checkAllowReceivedEmail($mapUserInfoIds[$viewingUser->mst_user_id]->email, 'completion',$mapUserInfoIds[$viewingUser->mst_user_id]->mst_company_id,config('app.server_env'),config('app.edition_flg'),config('app.server_flg'))) {
                            $mstUser = $mapUserInfoIds[$viewingUser->mst_user_id];
                            $data = [];
                            $data['code'] = MailUtils::MAIL_DICTIONARY['CIRCULAR_ENDED_NOTIFY']['CODE'];
                            $filterDocuments = array_filter($circularDocuments, function ($item) use ($mstUser) {
                                if ($item->confidential_flg
                                    && $item->origin_edition_flg == config('app.edition_flg')
                                    && $item->origin_env_flg == config('app.server_env')
                                    && $item->origin_server_flg == config('app.server_flg')
                                    && $item->create_company_id == $mstUser->mst_company_id) {
                                    // 社外秘：origin_document_idが-1固定
                                    // 同社メンバー参照可
                                    return true;
                                } else if (!$item->confidential_flg) {
                                    // 回覧終了時：origin_document_id＝0のレコードのみ、別条件不要
                                    return true;
                                }
                                return false;
                            });
                            $filenames = array_column($filterDocuments, 'file_name');
                            $data['filenames'] = $filenames;
                            if(count($filenames)){
                                $data['filenamestext'] = '';
                                foreach($filenames as $filename){
                                    if ($data['filenamestext'] == '') {
                                        $data['filenamestext'] = $filename;
                                        continue;
                                    }
                                    $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                                }
                            }else{
                                $data['filenamestext'] = '';
                            }

                            if (!trim($request['title'])) {
                                $mailTitle = $filenames[0];
                            } else {
                                $mailTitle = $request['title'];
                            }

                            // 申請人名前を取得
                            $creator = DB::table('circular_user')
                                ->where('circular_id', $circular_id)
                                ->where('parent_send_order', '0')
                                ->where('child_send_order', '0')
                                ->first();

                            // 社外秘ファイル
                            $confidentialFiles = array_filter($circularDocuments, function ($item) use ($mstUser) {
                                if ($item->confidential_flg
                                    && $item->origin_edition_flg == config('app.edition_flg')
                                    && $item->origin_env_flg == config('app.server_env')
                                    && $item->origin_server_flg == config('app.server_flg')
                                    && $item->create_company_id == $mstUser->mst_company_id) {
                                    return true;
                                }
                                return false;
                            });
                            // hide_circular_approval_url false:表示 true:非表示
                            // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                            // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                            $data['hide_circular_approval_url'] = false;
                            if(count(array_column($confidentialFiles, 'file_name'))){
                                // 当社社外秘ファイル存在時、「回覧文書を見る」非表示
                                $data['hide_circular_approval_url'] = true;
                            }

                            $data['receiver_name'] = $mstUser->name;
                            $data['creator_name'] = $creator->name;
                            $data['mail_name'] = $mailTitle;
                            $data['author_email'] = $circular->create_user;
                            $data['last_updated_email'] = $last_circular_user->update_user;
                            $data['last_updated_text'] = $request['text']?:'';
                            $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                            $data['circular_approval_url'] = $viewingUser->origin_circular_url;
                            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                                $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                            }else{
                                $data['circular_approval_url_text'] = '';
                            }
                            $data['send_to'] = $mstUser->email;
                            $data['send_to_company'] = $mstUser->mst_company_id;
                            if (isset($mapCompanyIds[$mstUser->mst_company_id])){
                                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'),config('app.server_flg'), CircularUserUtils::NEW_EDITION, $mapCompanyIds[$mstUser->mst_company_id]);
                            }else{
                                $data['env_app_url'] = CircularUserUtils::getEnvAppUrlByEnv(config('app.server_env'),config('app.server_flg'), CircularUserUtils::NEW_EDITION, null);
                            }
                            $mailDatas[] = $data;
                            //    Mail::to()->queue(new SendCircularUserMail($data));

                            // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                                // 次の回覧者が社内回覧の場合
                                if($circular->access_code_flg === CircularUtils::ACCESS_CODE_VALID
                                    && $creator->mst_company_id == $mstUser->mst_company_id
                                    && $creator->edition_flg == config('app.edition_flg')
                                    && $creator->env_flg == config('app.server_env')
                                    && $creator->server_flg == config('app.server_flg')){
                                    $notice_mail_date['title'] = $mailTitle;
                                    $notice_mail_date['access_code'] = $circular->access_code;
                                    $notice_mail_date['send_to'] = $mstUser->email;
                                    $notice_mail_date['send_to_company'] = $mstUser->mst_company_id;
                                    $noticeMailDatas[] = $notice_mail_date;
                                }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                                    && ($creator->mst_company_id != $mstUser->mst_company_id
                                        || $creator->edition_flg != config('app.edition_flg')
                                        || $creator->env_flg != config('app.server_env')
                                        || $creator->server_flg != config('app.server_flg'))) {
                                    // 窓口が社外の場合
                                    $notice_mail_date['title'] = $mailTitle;
                                    $notice_mail_date['access_code'] = $circular->outside_access_code;
                                    $notice_mail_date['send_to'] = $mstUser->email;
                                    $notice_mail_date['send_to_company'] = $mstUser->mst_company_id;
                                    $noticeMailDatas[] = $notice_mail_date;
                                }

                            }
                        }
                    }
                }
            }
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if ($is_circular_completed){
            $this->summaryCircularForCompleted($circular_id);
        }else{
            CircularUserUtils::summaryInProgressCircular($circular_id);
        }
        try{

            if (count($mailDatas)){
                Log::debug("sending email");
                foreach ($mailDatas as $data){
                    $email = $data['send_to'];
                    unset($data['send_to']);
                    $send_to_company = $data['send_to_company'];
                    unset($data['send_to_company']);
                    $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                    unset($data['filenames']);

                    MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                        $email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['CIRCULAR_ENDED_NOTIFY']['CODE'],
                        // パラメータ
                        $param,
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_has_ended_template.subject', ['title' => $mailTitle]),
                        // メールボディ
                        trans('mail.circular_has_ended_template.body', $data)
                    );
                }
            }

            if(count($noticeMailDatas)){
                foreach ($noticeMailDatas as $data){
                    $email = $data['send_to'];
                    unset($data['send_to']);
                    $send_to_company = $data['send_to_company'];
                    unset($data['send_to_company']);

                    //利用者:アクセスコードのお知らせ
                    MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                        $email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                        // パラメータ
                        json_encode($data,JSON_UNESCAPED_UNICODE),
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $data['title']]),
                        // メールボディ
                        trans('mail.SendAccessCodeNoticeMail.body', $data)
                    );
                }
            }
        }catch(\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
        }

        return $this->sendApiResponse('回覧登録処理に成功しました。', \Illuminate\Http\Response::HTTP_CREATED);
    }

    public function checkEmailView($email, Request $request){
        if (isset($request['usingHash']) && $request['usingHash']){
            try {
                $user = $request['current_circular_user'];

                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client){
                    return response()->json(['status' => false,
                        'message' => ['Cannot connect to ID App']
                    ]);
                }
                $result =  $client->post("users/checkEmail",[
                    RequestOptions::JSON => ['email' => $email]
                ]);
                $user_view = null;
                $resData = json_decode((string)$result->getBody());
                $id_app_users = $resData->data;

                foreach ($id_app_users as $id_app_user) {
                    if($user->mst_company_id == $id_app_user->company_id && $user->edition_flg == $id_app_user->edition_flg && $user->env_flg == $id_app_user->env_flg && $user->server_flg == $id_app_user->server_flg) {
                        $user_view = $id_app_user;
                    }
                }
                return $this->sendResponse($user_view,'get email true');
            }catch(\Exception $ex) {
                Log::error($ex->getMessage().$ex->getTraceAsString());
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else{
            $login_user = $request->user();

            $user = DB::table('mst_user')
                ->select('id', 'email', 'mst_company_id', 'family_name', 'given_name', 'option_flg')
                ->where('mst_company_id', $login_user->mst_company_id)
                ->where('email', $email)
                ->where('state_flg', AppUtils::STATE_VALID)
                ->first();
            return $this->sendResponse($user,'get email true');
        }
    }

    public function updateReturnflg($circular_id, $id, Request $request) {
        try {
            $return_flg = $request['returnFlg'];
            $login_user = $request->user();

            if(!$login_user || !$login_user->id) {
                $login_user = $request['user'];
            }
            DB::beginTransaction();

            DB::table('circular_user')
                ->where('id', $id)
                ->update([
                    'return_flg' => $return_flg?:0,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                ]);
            $circularUser = DB::table('circular_user')
                ->where('id', $id)->first();
            DB::table('circular_user')
                ->where('circular_id', $circularUser->circular_id)
                ->where('plan_id', $circularUser->plan_id)
                ->where('plan_id', '!=','0')
                ->update([
                    'return_flg' => $return_flg?:0,
                    'update_at'=> Carbon::now(),
                    'update_user'=> $login_user->email,
                ]);
            if ($circularUser->edition_flg == config('app.edition_flg') && ($circularUser->env_flg != config('app.server_env') || $circularUser->server_flg != config('app.server_flg')) && $circularUser->circular_status != CircularUserUtils::NOT_NOTIFY_STATUS){
                $updateCircularUsers[] = [
                    "email" => $circularUser->email,
                    "parent_send_order" => $circularUser->parent_send_order,
                    "child_send_order" => $circularUser->child_send_order,
                    "env_flg" => $circularUser->env_flg,
                    "edition_flg" => $circularUser->edition_flg,
                    "server_flg" => $circularUser->server_flg,
                    "mst_company_id" => $circularUser->mst_company_id,
                    "mst_company_name" => $circularUser->mst_company_name,
                    "mst_user_id" => $circularUser->mst_user_id,
                    "name" => $circularUser->name,
                    "return_flg" => $circularUser->return_flg,
                    "circular_status" => $circularUser->circular_status,
                    "title" => $circularUser->title,
                    "origin_circular_url" => CircularUtils::generateApprovalUrl($circularUser->email, $circularUser->edition_flg, $circularUser->env_flg, $circularUser->server_flg, $circularUser->circular_id) . CircularUtils::encryptOutsideAccessCode($circularUser->id),
                ];

                $transferredCircularUser = ['origin_circular_id' => $circularUser->circular_id,
                    'env_flg' => config('app.server_env'),
                    'edition_flg' => config('app.edition_flg'),
                    'server_flg' => config('app.server_flg'),
                    'update_user' => $login_user->email,
                    'update_circular_users' => $updateCircularUsers
                ];

                $envClient = EnvApiUtils::getAuthorizeClient($circularUser->env_flg, $circularUser->server_flg);
                if (!$envClient){
                    //TODO message
                    throw new \Exception('Cannot connect to Env Api');
                }

                $response = $envClient->put("circularUsers/updatesTransferred",[
                    RequestOptions::JSON => $transferredCircularUser
                ]);
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_CREATED && $response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::error('Cannot store circular user');
                    Log::error($response->getBody());
                    throw new \Exception('Cannot store circular user');
                }
            }

            DB::commit();
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendSuccess('');
    }

    private function summaryCircularForCompleted($completedCircularId){

        $system_edition_flg = config('app.edition_flg');
        $system_env_flg = config('app.server_env');
        $system_server_flg = config('app.server_flg');

        DB::beginTransaction();
        try{
            Log::debug("Start summaryCircularForCompleted for circular $completedCircularId!");
            $circularUsers = DB::table('circular_user')->where('circular_id', $completedCircularId)->select('email', 'title', 'parent_send_order', 'child_send_order', 'mst_company_id', 'mst_user_id')->get();
            $senderUser = DB::table('circular_user')->where('circular_id', $completedCircularId)
                ->where('parent_send_order', 0)->where('child_send_order',0)
                ->select('name', 'email')->first();

            $strSqls = '';
            $countSql = 0;
            foreach ($circularUsers as $circularUser){
                $circularUser->email = str_replace("'","''",$circularUser->email);
                $circularUser->email = str_replace("\\","\\\\",$circularUser->email);
                Log::debug("Query receiver for email $circularUser->email in circular $completedCircularId!");
                $receivers = DB::table('circular_user as E')
                    ->select(DB::raw('E.circular_id as id, GROUP_CONCAT(CONCAT(E.name, \' &lt;\',E.email, \'&gt;\') ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \'<br />\') as receiver_name_emails
                                , GROUP_CONCAT(E.name ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \',\') as receiver_names
                                , GROUP_CONCAT(E.email ORDER BY E.parent_send_order, E.child_send_order ASC SEPARATOR \',\') as receiver_emails'))
                    // 宛先に自分自身を設定していた場合の対策としてNOT EXISTS
                    ->where('E.child_send_order', '!=', 0)
                    ->where('E.circular_id', $completedCircularId)
                    ->whereRaw("EXISTS (SELECT M.circular_id from circular_user as M where E.circular_id = M.circular_id
                        AND M.email = '$circularUser->email'
                        AND M.edition_flg = '$system_edition_flg'
                        AND M.env_flg = '$system_env_flg'
                        AND M.server_flg = '$system_server_flg'
                        AND ((E.parent_send_order != 0 AND E.child_send_order = 1) OR (E.parent_send_order = M.parent_send_order)))")
                    //->whereRaw('((E.parent_send_order != 0 AND E.child_send_order = 1) OR (E.parent_send_order = M.parent_send_order))')
                    ->groupBy(['E.circular_id'])->get();
                Log::debug("Finished query receiver for email $circularUser->email in circular $completedCircularId!");

                if ($senderUser){
                    $strSqls.="UPDATE circular_user SET sender_name = '$senderUser->name', sender_email = '$senderUser->email' where email = '$circularUser->email' and circular_id = $completedCircularId;\n";
                    $countSql++;
                }
                foreach ($receivers as $receiver){
                    $receiver->receiver_emails = str_replace("'","''",$receiver->receiver_emails);
                    $receiver->receiver_emails = str_replace("\\","\\\\",$receiver->receiver_emails);
                    $receiver->receiver_name_emails = str_replace("'","''",$receiver->receiver_name_emails);
                    $receiver->receiver_name_emails = str_replace("\\","\\\\",$receiver->receiver_name_emails);
                    $strSqls.="UPDATE circular_user SET receiver_name = '$receiver->receiver_names', receiver_email = '$receiver->receiver_emails', receiver_name_email = '$receiver->receiver_name_emails' where email = '$circularUser->email' and circular_id = $completedCircularId;\n";
                    $countSql++;
                }
                if (trim($circularUser->title)){
                    Log::debug("No query for title $circularUser->email in circular $completedCircularId because this email has title already!");
                }else{
                    Log::debug("Query for title $circularUser->email in circular $completedCircularId!");
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
                        ->where('C.id', $completedCircularId)
                        ->where('U.parent_send_order', $circularUser->parent_send_order)
                        ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS])
                        ->groupBy(['C.id', 'U.parent_send_order'])->get();
                    Log::debug("Finished query title for email $circularUser->email in circular $completedCircularId!");
                    foreach ($titles as $title){
                        $strSqls.="UPDATE circular_user SET receiver_title = '$title->file_names' where email = '$circularUser->email' and parent_send_order = $circularUser->parent_send_order and circular_id = $completedCircularId;\n";
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
            Log::debug('Error in summaryCircularForCompleted for receiver/sender/title!');
            Log::error($ex->getMessage().$ex->getTraceAsString());
        }
    }

    /**
     * 名刺ID取得
     * @param Request $request
     * @return mixed
     */
    public function getBizcardId(Request $request) {
        try {
            Log::debug('getBizcardId Request Parameter: ' . json_encode($request->all()));

            // クロス環境判定
            $env_flg = $request->filled('env_flg') ? $request->input('env_flg') : config('app.server_env');
            $server_flg = $request->filled('server_flg') ? $request->input('server_flg') : config('app.server_flg');
            $edition_flg = $request->filled('edition_flg') ? $request->input('edition_flg') : config('app.edition_flg');

            $bizcard_id = null;
            if ((($env_flg != config('app.server_env')) || ($server_flg != config('app.server_flg'))) && $edition_flg != 0) {
                Log::debug('他環境の名刺ID取得');
                // 他環境の場合、他環境のapiを呼び出す。
                $envClient = EnvApiUtils::getAuthorizeClient($env_flg, $server_flg);
                if (!$envClient){
                    throw new \Exception('Cannot connect to other server Api');
                }

                // パラメータからURLエンコードされたメールアドレスを取得
                $encodedEmail = $request->input('email');
                $response = $envClient->get('user/getExternalBizcardId/' . $encodedEmail);
                if (!$response) {
                    return $this->sendError('名刺IDの取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                    Log::debug('getStatusCode：'. $response->getStatusCode());
                    Log::error($response->getBody());
                    throw new \Exception('Cannot get bizcard ID');
                }
                $resData = json_decode((string)$response->getBody());
                $bizcard_id = $resData->data->bizcard_id;
            } else {
                Log::debug('同一環境の名刺ID取得');
                // 捺印するユーザの情報を取得する
                $user = $request->user();
                $circular_user = $request['current_circular_user'];
                if ($user != null && isset($user->id)) {
                    // ログイン済みユーザ
                    Log::debug('ログインユーザの情報取得');
                    $bizcard_id = DB::table('mst_user_info')->where('mst_user_id', $user->id)->value('bizcard_id');
                } else if ($circular_user != null && isset($circular_user->mst_user_id)) {
                    // 未ログインかつゲストユーザでない(回覧メールから文書表示)
                    Log::debug('未ログインかつゲストユーザでないユーザの情報取得');
                    $externalBizcardIdResponse = $this->getExternalBizcardId($circular_user->email);
                    $bizcard_id = $externalBizcardIdResponse->original['data']['bizcard_id'];
                }
            }

            return $this->sendResponse([
                'bizcard_id' => $bizcard_id,
            ], '名刺IDの取得処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * メールアドレスから名刺ID取得
     * @param $email
     * @return mixed
     */
    public function getExternalBizcardId($encodedEmail) {
        Log::debug('getExternalBizcardId encodedEmail: ' . $encodedEmail);
        $email = rawurldecode($encodedEmail);
        $user_id = DB::table('mst_user')->where('email', $email)->value('id');
        $bizcard_id = DB::table('mst_user_info')->where('mst_user_id', $user_id)->value('bizcard_id');

        return $this->sendResponse([
            'bizcard_id' => $bizcard_id,
        ], '名刺IDの取得処理に成功しました。');
    }

    public function getUserView(Request $request) {

        Log::debug("値チェック".$request->get("circular_id"));
        $strEmail = $request->get("email") ? trim($request->get("email")) : '';
        $email=str_replace([' ','  '], '+', $request->get("email"));
        Log::debug("値チェック".$email);
        /*PAC_5-2331 S*/
        $mail = [];
        $name = [];
        $i = 0;
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $response = $client->post("users/checkEmail",[
            RequestOptions::JSON => ['email' => $email ]
        ]);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
            $result = json_decode((string) $response->getBody());
            if (empty($result) || empty($result->data)){
                return [];
            }

            $id_app_users = $result->data;
            $getVUserFlg = false;
            // 現行、新エディション側同時存在（新エディション側複数存在不可）
            // 現行ユーザーに閲覧ユーザー取得不要
            foreach ($id_app_users as $user) {
                if($user->edition_flg == config('app.edition_flg')){
                    $currUser = $user;
                    $getVUserFlg = true;
                }
            }
            if(!$getVUserFlg){
                return [];
            }

            $system_env_flg     = config('app.server_env');
            $system_edition_flg = config('app.edition_flg');
            $system_server_flg = config('app.server_flg');
            if ($currUser->env_flg != $system_env_flg || $currUser->server_flg != $system_server_flg || $currUser->edition_flg != $system_edition_flg){
                $env_client = EnvApiUtils::getAuthorizeClient($currUser->env_flg,$currUser->server_flg);
                if (!$env_client){
                    throw new \Exception('Cannot connect to other server Api');
                }
                $response = $env_client->get('getViewingUsers' ,[
                    RequestOptions::JSON =>[ 'circular_id' => $request->get("circular_id"), 'email' => $email]
                ]);
                if ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                    $result = json_decode((string) $response->getBody(),true);
                    $view_users = $result['data'];
                    foreach ($view_users as $view_user){
                        array_push($mail,$view_user['email']);
                        array_push($name,$view_user['family_name'].' '.$view_user['given_name']);
                        $i++;
                    }

                }elseif ($response->getStatusCode() == \Illuminate\Http\Response::HTTP_NOT_FOUND){

                }else{
                    Log::error($response->getBody());
                    return   [];
                }
            }else{
                DB::table('mst_user as u')
                    ->select('u.email', DB::raw('CONCAT(u.family_name,\' \',u.given_name) as name'))
                    ->join('viewing_user as v', 'v.mst_user_id', '=', 'u.id')
                    ->where('v.circular_id',  $request->get("circular_id"))
                    ->where('v.mst_company_id', $currUser->company_id)
                    ->get()
                    ->each(function ($user) use (&$mail,&$name,&$i){
                        array_push($mail,$user->email);
                        array_push($name,$user->name);
                        $i++;
                    });
            }
        }
        if ($i > 0){
            return   ['email' => $mail, 'name' => $name, 'i' => $i-1 ];
        }
        /*PAC_5-2331 E*/
    }

    public function deleteUserView(Request $request) {

        $email=str_replace([' ','  '], '+', $request->get("email"));
        $id=DB::table('mst_user')->where('email',$email)->value("id");
        DB::table('viewing_user')->where('circular_id', $request->get("circular_id"))->where('mst_user_id',$id)->delete();

    }

    /**
     *  現在の操作のEMAIL情報を処理します。
     * @param $objCircularUser
     * @param $strFullName
     * @return array
     */
    private function handlerSkipMail($arrCircularUsers, $strFullName, $objLoginUser)
    {
        $objCircularUser = $arrCircularUsers[0];

        $data = [];
        $circular_doc = DB::table('circular_document')
            ->select('file_name', 'confidential_flg', 'origin_edition_flg', 'origin_env_flg', 'origin_server_flg', 'create_company_id', 'origin_document_id', 'parent_send_order')
            ->where('circular_id', $objCircularUser->circular_id)
            ->get()->toArray();

        $data['title'] = $objCircularUser->title;
        $filterDocuments = array_filter($circular_doc, function ($item) use ($objCircularUser) {
            if ($item->confidential_flg
                && $item->origin_edition_flg == $objCircularUser->edition_flg
                && $item->origin_env_flg == $objCircularUser->env_flg
                && $item->origin_server_flg == $objCircularUser->server_flg
                && $item->create_company_id == $objCircularUser->mst_company_id) {
                // 社外秘：origin_document_idが-1固定
                // 同社メンバー参照可
                return true;
            } else if (!$item->confidential_flg
                && (!$item->origin_document_id || $item->parent_send_order == $objCircularUser->parent_send_order)) {
                // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                // 回覧終了時：origin_document_id＝0のレコード
                // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                return true;
            }
            return false;
        });
        $data['docs'] = array_column($filterDocuments, 'file_name');

        if (!trim($objCircularUser->title)) {
            $data['mail_name'] = $data['docs'][0];
            if (empty($data['title']) || !trim($data['title'])) {
                $data['title'] = $data['mail_name'];
            }
        }
        if (count($data['docs'])) {
            $data['docstext'] = '';
            foreach ($data['docs'] as $filename) {
                if ($data['docstext'] == '') {
                    $data['docstext'] = $filename;
                    continue;
                }
                $data['docstext'] .= '\r\n' . '　　　　　　' . $filename;
            }
        } else {
            $data['docstext'] = '';
        }
        $mapSameEnvCompanies = [];
        $mapOtherEnvCompanies = [];
        foreach($arrCircularUsers as $circular_user){
            if ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->mst_company_id){
                if ($circular_user->env_flg == config('app.server_env') && $circular_user->server_flg == config('app.server_flg')){
                    $mapSameEnvCompanies[$circular_user->mst_company_id] = null;
                }else{
                    if (isset($mapOtherEnvCompanies[$circular_user->env_flg])){
                        if (isset($mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg])){
                            $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id] = null;
                        }else{
                            $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg] = [$circular_user->mst_company_id => null];
                        }
                    }else{
                        $mapOtherEnvCompanies[$circular_user->env_flg] = [$circular_user->server_flg =>[[$circular_user->mst_company_id => null]]];
                    }
                }
            }
        }
        $mapSameEnvCompanies = $this->companyRepository->getSameEnvCompanies($mapSameEnvCompanies);
        $mapOtherEnvCompanies = EnvApiDelegate::getOtherEnvCompanies($mapOtherEnvCompanies);
        foreach ($arrCircularUsers as $objCircularUser) {
            $data['mail_name'] = $objCircularUser->title;
            $data['receiver_name'] = $objCircularUser->name;
            $data['user_name'] = $strFullName;
            $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompanies($objCircularUser, $mapSameEnvCompanies, $mapOtherEnvCompanies);
            $arrData = $data;
            if(isset($data['docs'])){unset($data['docs']);}
            //利用者:回覧文書が届いています
            MailUtils::InsertMailSendResume(
            // 送信先メールアドレス
                $objCircularUser->email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['USER_SKIP_HANDLER_COMPLETED']['CODE'],
                // パラメータ
                json_encode($arrData, JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_USER,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_skip_template.subject', ['title' => $data['title']]),
                // メールボディ
                trans('mail.circular_skip_template.body', $data)
            );
        }
    }

    /**
     * @param Request $request
     * @param $circular_id
     * @return mixed
     */
    public function updatePlan(Request $request, $circular_id)
    {
        try {
            $mode = $request->get('plan_mode');
            $score = $mode == 1 ? count($request->get('plan_users')) : $request->get('score');
            $plan = DB::table("circular_user_plan")
                ->where('id', '=', intval($request->get('plan_id')))
                ->where('circular_id', '=', $circular_id)
                ->first();
            if ($plan) {
                DB::table("circular_user_plan")
                    ->where('id', '=', intval($request->get('plan_id')))
                    ->where('circular_id', '=', $circular_id)
                    ->update([
                        'mode' => $mode,
                        'score' => $score,
                        'child_send_order' => $request->get('child_send_order'),
                        'update_user' => $request->user()->email,
                        'update_at' => Carbon::now()
                    ]);
                DB::table('circular_user')
                    ->where('circular_id', '=', $circular_id)
                    ->where('plan_id', '=', intval($request->get('plan_id')))
                    ->update([
                        'child_send_order' => $request->get('child_send_order'),
                        'update_user' => $request->user()->email,
                        'update_at' => Carbon::now()
                    ]);
                return $this->sendResponse([
                    'plan_id' => $plan->id,
                ], '');
}
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $circular_id
     * @return mixed
     */
    public function planList($circular_id)
    {
        $list = DB::table('circular_user_plan')
            ->select(['id', 'mode', 'score', 'circular_id', 'child_send_order'])
            ->where('circular_id', '=', $circular_id)
            ->get()
            ->keyBy('id');
        return $this->sendResponse($list->toArray(), '');
    }


    /**
     * 文書申請の回覧設定保存
     * @param $circular_id 回覧ID,$request
     * @return $updateat 更新时间
     */
    public function saveCircularSetting($circular_id, Request $request) {
        $input = $request->all();
        if (isset($input['title']) && is_string($input['title'])) $input['title'] = preg_replace('/[\t]/', '', $input['title']);
        $validator = Validator::make($input, [
            'hide_thumbnail_flg' => 'nullable|boolean',
            're_notification_day' => 'nullable|date',
            'address_change_flg' => 'nullable|boolean',
            'access_code_flg' => 'nullable|boolean',
            'access_code' => "nullable|string|max:10",
            'outside_access_code_flg' => 'nullable|boolean',
            'outside_access_code' => "nullable|string|max:10",
            'title' => "nullable|string|max:256",
            'text_append_flg' => 'nullable|boolean',
            'require_print' => 'nullable|boolean',
            'text' => 'nullable|string|max:500'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        DB::beginTransaction();
        try {
            //保護設定、再通知設定保存
            DB::table('circular')->where('id', $circular_id)->update([
                'address_change_flg' => $input['address_change_flg'],
                'text_append_flg' => $input['text_append_flg'],
                'hide_thumbnail_flg' => $input['hide_thumbnail_flg'],
                'require_print' => $input['require_print'],
                'access_code_flg' => $input['access_code_flg'],
                'access_code' => $input['access_code'],
                'outside_access_code_flg' => $input['outside_access_code_flg'],
                'outside_access_code' => $input['outside_access_code'],
                're_notification_day' => $input['re_notification_day'],
                'final_updated_date' => Carbon::now(),
                'update_at' => Carbon::now(),
            ]);
            //件名・メッセージ保存
            DB::table('circular_user')
                ->where('circular_id', $circular_id)
                ->where('circular_user.parent_send_order', 0)
                ->where('circular_user.child_send_order', 0)
                ->update([
                    'title'=> $input['title']?:' ',
                    'text'=> $input['text']?:'',
                ]);
            DB::commit();
            $updateat = DB::table('circular')->where('id', $circular_id)->first()->update_at;
            return $this->sendResponse($updateat,'回覧情報の保存処理に成功しました。');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 文書申請の回覧設定取得
     * @param $circular_id 回覧ID
     * @return $savedSetting 回覧設定
     */
    public function getCircularSetting($circular_id) {
        try {
            //保護設定、再通知設定、件名・メッセージ取得
            $savedSetting = DB::table('circular')
                ->join('circular_user','circular.id','circular_user.circular_id')
                ->where('circular.id', $circular_id)
                ->where('circular_user.parent_send_order', 0)
                ->where('circular_user.child_send_order', 0)
                ->select('circular.address_change_flg','circular.text_append_flg','circular.hide_thumbnail_flg','circular.require_print','circular.access_code_flg',
                    'circular.access_code','circular.outside_access_code_flg','circular.outside_access_code','circular.re_notification_day','circular_user.title','circular_user.text')
                ->get();
            return $this->sendResponse($savedSetting,'回覧情報の取得処理に成功しました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧情報の取得処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /** PAC_5-2353 all user send mail
     * @param $circular_id
     * @param Request $request
     * @return mixed
     */
    public function handlerCircularUserSendNotifyFirst($circular_id, Request $request)
    {
        $input = $request->all();
        // PAC_5-1973 ログインパスワードの変更画面から利用者としてログインできるように修正 Start
        if (isset($input['title']) && is_string($input['title'])) $input['title'] = preg_replace('/[\t]/', '', $input['title']);
        // PAC_5-1973
        $validator = Validator::make($input, [
            'hide_thumbnail_flg' => 'nullable|boolean',
            're_notification_day' => 'nullable|date',
            'address_change_flg' => 'nullable|boolean',
            'access_code_flg' => 'nullable|boolean',
            'access_code' => "nullable|string|max:10",
            'title' => "nullable|string|max:256",
            'text_append_flg' => 'nullable|boolean',
            'require_print' => 'nullable|boolean',
            'text' => 'nullable|string|max:500'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Get Current Circular
        $objCircularData = DB::table("circular")->where("id", $circular_id)->first();
        // Get Current Circular User
        $objCircularUsers = DB::table("circular_user")->where("circular_id", $circular_id)->get();
        if (empty($objCircularData) || empty($objCircularUsers)) {
            return $this->sendError(__('message.false.sendAllUserNotExists'), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->dispatch(new SendAllUserCircular($circular_id, json_encode($request->input())));

        return $this->sendSuccess('通知メールを送信しました。');
    }

    /**
     *  PAC_5-2353
     * @param $circular_id
     * @param $arrParams
     * @param Request $request
     */
    public function handlerCircularUserInsert($circular_id, $arrParams, Request $request)
    {
        $request->merge($arrParams);

        $this->sendNotifyFirst($arrParams['circular_id'], $request);
    }

	/**
     * 特設サイト 提出側回覧状態変更
     * @param Request $request
     * @return mixed
     */
    public function updateSpecialSiteUserStatus(Request $request){
        try {
            DB::beginTransaction();

            $circular      = $request->get('circular');
            $circular_users = $request->get('circular_users');
            $local_circular_id = DB::table('circular')->select('id')
                ->where('origin_circular_id', $circular['id'])
                ->where('edition_flg', $circular['edition_flg'])
                ->where('env_flg', $circular['env_flg'])
                ->where('server_flg', $circular['server_flg'])
                ->value('id');
            $circular_update = [
                'circular_status' => $circular['circular_status'],
                'final_updated_date' => $circular['final_updated_date'],
            ];

            if($circular['circular_status'] == CircularUtils::CIRCULAR_COMPLETED_STATUS || $circular['circular_status'] == CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS){
                $circular_update['completed_date'] = $circular['completed_date'];
            }


            DB::table('circular')
                ->where('id',$local_circular_id)
                ->update($circular_update);

            $local_circular_users = DB::table('circular_user')->where('circular_id', $local_circular_id)->orderBy('id')->pluck('id')->toArray();
            foreach ($circular_users as $key => $circular_user) {
                if ($key < count($local_circular_users) && (($circular_user['parent_send_order'] == 0 && $circular_user['child_send_order'] == 0)
                        || $circular_user['edition_flg']  != config('app.edition_flg') || $circular_user['env_flg'] != config('app.server_env') || $circular_user['server_flg'] != config('app.server_flg'))) {

                    $circular_user_update = [
                        'circular_status' => $circular_user['circular_status'],
                        'parent_send_order' => $circular_user['parent_send_order'],
                        'child_send_order' => $circular_user['child_send_order'],
                        'title' => $circular_user['title'] ?: '',
                        'return_flg' => $circular_user['return_flg'],
                        'received_date' => $circular_user['received_date'],
                        'sent_date' => $circular_user['sent_date'],
                        'sender_name' => $circular_user['sender_name'],
                        'sender_email' => $circular_user['sender_email'],
                        'receiver_name' => $circular_user['receiver_name'],
                        'receiver_email' => $circular_user['receiver_email'],
                        'receiver_name_email' => $circular_user['receiver_name_email'],
                        'receiver_title' => $circular_user['receiver_title'],
                        'email' => $circular_user['email'],
                        'name' => $circular_user['name'],
                        'mst_user_id' => $circular_user['mst_user_id'],
                        'stamp_flg' => $circular_user['stamp_flg']
                    ];

                    if(CommonUtils::isNullOrEmpty($circular_user['receiver_title'])){
                        unset($circular_user_update['receiver_title']);
                    }
                    DB::table('circular_user')
                        ->where('id', $local_circular_users[$key])
                        ->update($circular_user_update);
                }
            }

            $this->summaryCircularForCompleted($local_circular_id);

            DB::commit();
            return $this->sendApiResponse('回覧登録処理に成功しました。', \Illuminate\Http\Response::HTTP_OK);
        }catch (\Exception $ex){
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendApiError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkFavoriteUserStatus(Request $request)
    {
        try {
            $users = $request->get('favorite');
            foreach ($users as $user) {
                if (!isset($user['email_env_flg']) || !isset($user['email_edition_flg']) || !isset($user['email_server_flg']) || !isset($user['email_company_id'])) {
                    continue;
                }
                if ($user['email_env_flg'] == config('app.server_env') && $user['email_edition_flg'] == config('app.edition_flg')
                    && $user['email_server_flg'] == config('app.server_flg')) {
                    $dbUser = DB::table('mst_user')->where('email', $user['email'])
                        ->where('state_flg', AppUtils::STATE_VALID)->select('email', 'id')->first();
                    if (!$dbUser) {
                        return response()->json(['status' => false,'message' => ['削除または無効の利用者が存在しています。']]);
                    }
                } else {
                    $client = IdAppApiUtils::getAuthorizeClient();
                    if (!$client){
                        return $this->sendError('Cannot connect to ID App', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $response = $client->post("users/checkEmail",[
                        RequestOptions::JSON => ['email' => $user['email'] ,'contract_app' => $user['email_edition_flg'] ,'app_env' => $user['email_env_flg']]
                    ]);
                    if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                        $result = json_decode((string) $response->getBody());
                        if($result->data == []){
                            return response()->json(['status' => false,'message' => ['削除または無効の利用者が存在しています。']]);
                        }
                    } else {
                        return $this->sendError('お気に入り登録されているユーザー有効チェック失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
            return $this->sendResponse('', 'お気に入り登録されているユーザー有効利用者です。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('お気に入り登録されているユーザー有効チェック失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $circular　文書
     * @param $request_parent_send_order 申請者
     * @param $approval_parent_send_order 承認者
     * @return void
     */
    public function approvalReqSendBackNextUserEmail($circular,$request,$approval_user){

        $user = DB::table('mst_user')->where('id', $circular->mst_user_id)->where('email', $circular->create_user)->first();
        $circular_id = $circular->id;
        $request_parent_send_order = $request->parent_send_order;
        $approval_parent_send_order = $approval_user->parent_send_order;
        $request_user = DB::table('circular_user')
            ->leftJoin('mail_text', function($join){
                $join->on('circular_user.id', '=', 'mail_text.circular_user_id');
                $join->on('mail_text.id', '=', DB::raw("(select max(id) from mail_text WHERE mail_text.circular_user_id = circular_user.id)"));
            })
            ->select('circular_user.*', 'mail_text.text as text')
            ->where('circular_id', $circular_id)
            ->where('email', $request->email)
            ->where('parent_send_order', $request->parent_send_order)
            ->where('child_send_order', $request->child_send_order)
            ->first();

        if(!$circular || !$circular->id || !$request_user) {
            DB::rollBack();
            return $this->sendError('回覧が見つかりません', \Illuminate\Http\Response::HTTP_NOT_FOUND);
        }
        if(!isset($request->update_at)){
            DB::rollBack();
            return $this->sendError('回覧が見つかりません', StatusCodeUtils::HTTP_NOT_ACCEPTABLE);
        }
        $circular_users = DB::table('circular_user')
            ->where('circular_id', $circular_id)
            ->where('parent_send_order','>', $request_parent_send_order)
            ->where('parent_send_order','<', $approval_parent_send_order)
            ->where('child_send_order', 1)
            ->whereIn('circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                CircularUserUtils::READ_STATUS,
                CircularUserUtils::APPROVED_WITH_STAMP_STATUS,
                CircularUserUtils::APPROVED_WITHOUT_STAMP_STATUS,
                CircularUserUtils::PULL_BACK_TO_USER_STATUS])
            ->orderByDesc('parent_send_order')
            ->get();

        // all file
        $allDocument = DB::table('circular_document')
            ->where('circular_id', $circular_id)
            ->orderBy('id')
            ->get()
            ->keyBy('id')
            ->toArray();

        // first file
        $firstDocument = DB::table('circular_document')
            ->where('circular_id', $circular_id)
            ->orderBy('id')->first();

        $mapSameEnvCompanies = [];
        $mapOtherEnvCompanies = [];
        foreach ($circular_users as $circular_user) {
            if ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->mst_company_id){
                if ($circular_user->env_flg == config('app.server_env') && $circular_user->server_flg == config('app.server_flg')){
                    $mapSameEnvCompanies[$circular_user->mst_company_id] = null;
                }else{
                    if (isset($mapOtherEnvCompanies[$circular_user->env_flg])){
                        if (isset($mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg])){
                            $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg][$circular_user->mst_company_id] = null;
                        }else{
                            $mapOtherEnvCompanies[$circular_user->env_flg][$circular_user->server_flg] = [$circular_user->mst_company_id => null];
                        }
                    }else{
                        $mapOtherEnvCompanies[$circular_user->env_flg] = [$circular_user->server_flg =>[[$circular_user->mst_company_id => null]]];
                    }
                }
            }
        }
        $mapSameEnvCompanies = $this->companyRepository->getSameEnvCompanies($mapSameEnvCompanies);
        $mapOtherEnvCompanies = EnvApiDelegate::getOtherEnvCompanies($mapOtherEnvCompanies);

        foreach ($circular_users as $kc=>$circular_user) {
            $data = [];
            // hide_thumbnail_flg 0:表示 1:非表示
            if (!$circular->hide_thumbnail_flg) {
                // thumbnail表示
                $canSeePreview = false;
                if ($firstDocument && $firstDocument->confidential_flg
                    && $firstDocument->origin_edition_flg == $circular_user->edition_flg
                    && $firstDocument->origin_env_flg == $circular_user->env_flg
                    && $firstDocument->origin_server_flg == $circular_user->server_flg
                    && $firstDocument->create_company_id == $circular_user->mst_company_id){
                    // 一ページ目が社外秘　＋　upload会社＝宛先会社
                    $canSeePreview = true;
                }else if ($firstDocument && !$firstDocument->confidential_flg){
                    // 一ページ目が社外秘ではない
                    $canSeePreview = true;
                }

                if($canSeePreview && $circular->first_page_data){
                    $previewPath = AppUtils::getPreviewPagePath($circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_user->mst_company_id, $circular_user->id);
                    file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                    $data['image_path'] = $previewPath;
                }else{
                    $data['image_path'] = public_path()."/images/no-preview.png";
                }
            }else{
                $data['image_path'] = '';
            }

            // 回覧文書見る
            $data['hide_circular_approval_url'] = false;
            foreach($allDocument as $document){
                if($document->confidential_flg){
                    // hide_circular_approval_url false:表示 true:非表示
                    // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                    // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                    if($document->origin_edition_flg == $circular_user->edition_flg
                        && $document->origin_env_flg == $circular_user->env_flg
                        && $document->origin_server_flg == $circular_user->server_flg
                        && $document->create_company_id == $circular_user->mst_company_id){
                        // 当社社外秘ファイル存在時、「回覧文書を見る」非表示
                        $data['hide_circular_approval_url'] = true;
                    }
                }
            }
            // file name
            $filterDocuments    = array_filter($allDocument, function($item) use($circular_user, $circular){
                if ($item->confidential_flg
                    && $item->origin_edition_flg == $circular_user->edition_flg
                    && $item->origin_env_flg == $circular_user->env_flg
                    && $item->origin_server_flg == $circular_user->server_flg
                    && $item->create_company_id == $circular_user->mst_company_id){
                    // 社外秘：origin_document_idが-1固定
                    // 同社メンバー参照可
                    return true;
                }else if (!$item->confidential_flg
                    && (!$item->origin_document_id || $item->parent_send_order == $circular_user->parent_send_order) || ($circular->special_site_flg && $item->origin_document_id = -1)){
                    // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                    // 回覧終了時：origin_document_id＝0のレコード
                    // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                    return true;
                }
                return false;
            });

            $filenames = array_column($filterDocuments, 'file_name');

            $title = $circular_user->title;
            if (!trim($title)) {
                $title = $filenames[0];
            }
            $fullName = implode(' ', [$user->family_name, $user->given_name]);
            $data['receiver_name'] = $circular_user->name;
            $data['return_requester'] = $fullName;
            $data['mail_name'] = $title;
            $data['user_name'] = $fullName;
            $data['circular_id'] = $circular_id;
            $data['filenames'] = $filenames;
            if(count($filenames)){
                $data['filenamestext'] = '';
                foreach($filenames as $filename){
                    if ($data['filenamestext'] == '') {
                        $data['filenamestext'] = $filename;
                        continue;
                    }
                    $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                }
            }else{
                $data['filenamestext'] = '';
            }
            $data['text'] = $request_user->text;
            $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
            $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($circular_user->email, $circular_user->edition_flg, $circular_user->env_flg, $circular_user->server_flg, $circular_id);
            if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] .'\r\n\r\n';
            }else{
                $data['circular_approval_url_text'] = '';
            }
            // check to use SAML Login URL or not
            $data['env_app_url'] = CircularUserUtils::getEnvAppUrlWithoutCompanies($circular_user, $mapSameEnvCompanies, $mapOtherEnvCompanies);


            $param = json_encode($data,JSON_UNESCAPED_UNICODE);
            unset($data['filenames']);

            $isSendEmailFlag = $circular_users[0]->plan_id > 0 ? ($circular_user->plan_id == $circular_users[0]->plan_id) : ($kc==0);
            if($isSendEmailFlag) {
                DB::table('circular_user')
                    ->where('circular_id', $circular_id)
                    ->where('id',$circular_user->id)
                    ->where('email',$circular_user->email)
                    ->where('parent_send_order', $circular_user->parent_send_order)
                    ->where('child_send_order', $circular_user->child_send_order)
                    ->update([
                        'circular_status' => CircularUserUtils::NOTIFIED_UNREAD_STATUS,
                        'update_at'=> Carbon::now(),
                        'update_user'=> $approval_user->email,
                        'is_skip' => CircularUserUtils::IS_SKIP_ACTION_FALSE,
                    ]);

                if ($circular_user->edition_flg == config('app.edition_flg')
                    && ($circular_user->env_flg != config('app.server_env') || $circular_user->server_flg != config('app.server_flg'))){
                    Log::debug('Approve request sendback in other application on new edition, update the next Approve user transferred document');
                    $this->sendUpdateTransferredDocumentStatus($circular, $circular_user->parent_send_order, $circular_user->child_send_order, $circular_user->title, '', $circular_user->env_flg,$circular_user->server_flg, false, false);
                }
                // 利用者:回覧文書の差戻し依頼通知
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $circular_user->email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CIRCULAR_SEND_BACK_REQUEST_NOTIFY']['CODE'],
                    // パラメータ
                    $param,
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_request_sendback_template.subject', ['title' => $title, 'user' => $fullName]),
                    // メールボディ
                    trans('mail.circular_user_request_sendback_template.body', $data)
                );
                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                if (isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']) {
                    // 申請者
                    $author_user = DB::table('circular_user')
                        ->where('circular_id', $circular_id)
                        ->where('parent_send_order', 0)
                        ->where('child_send_order', 0)
                        ->first();

                    if ($circular->access_code_flg == CircularUtils::ACCESS_CODE_VALID
                        && $author_user->mst_company_id == $circular_user->mst_company_id
                        && $author_user->edition_flg == $circular_user->edition_flg
                        && $author_user->env_flg == $circular_user->env_flg
                        && $author_user->server_flg == $circular_user->server_flg) {
                        $access_data['title'] = $data['mail_name'];
                        $access_data['access_code'] = $circular->access_code;
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $circular_user->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                            // パラメータ
                            json_encode($access_data, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                            // メールボディ
                            trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                        );
                    } elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                        && ($author_user->mst_company_id != $circular_user->mst_company_id
                            || $author_user->edition_flg != $circular_user->edition_flg
                            || $author_user->env_flg != $circular_user->env_flg
                            || $author_user->server_flg != $circular_user->server_flg)) {
                        // 次の宛先が社外の場合
                        $access_data['title'] = $data['mail_name'];
                        $access_data['access_code'] = $circular->outside_access_code;

                        //利用者:アクセスコードのお知らせ
                        MailUtils::InsertMailSendResume(
                        // 送信先メールアドレス
                            $circular_user->email,
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                            // パラメータ
                            json_encode($access_data, JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                            // メールボディ
                            trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                        );
                    }
                }
            }
        }
    }
}
