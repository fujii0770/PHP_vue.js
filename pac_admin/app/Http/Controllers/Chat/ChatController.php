<?php

namespace App\Http\Controllers\Chat;

use App\Chat\ChatService;
use App\Http\Controllers\AdminController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\ChatUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ChatController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {

        $user = \Auth::user();
        $action = $request->get('action', '');
        $limit = AppUtils::normalizeLimit($request->get('limit'), config('app.page_limit'));
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir') : 'desc';
        $validator = Validator::make($request->all(), [
            'status' => [
                'nullable',
                Rule::in([
                    AppUtils::CHAT_SERVER_USER_STATUS_VALID,
                    AppUtils::CHAT_SERVER_USER_STATUS_STOPPED,
                    AppUtils::CHAT_SERVER_USER_STATUS_INVALID,
                    AppUtils::CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR,
                    AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR,
                    AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR,
                    AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR,
                ])
            ]
        ]);
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        // get params search
        $status = $request->get('status', null);
        $chatUserName = $request->get('chat_user_name', null);
        $emailOption = $request->get('email_option', null);
        $email = $request->get('email', null);
        $username = $request->get('username', null);

        $chatServerUser = DB::table('chat_server_users')
            ->where(function($query) {
                $query->where('chat_server_users.status', null)
                    ->orWhere('chat_server_users.status', '!=', AppUtils::CHAT_SERVER_USER_STATUS_DELETED);
            });

        $resultData = DB::table('mst_user')
            ->leftJoinSub($chatServerUser, 'chat_server_users',
                'chat_server_users.mst_user_id', 'mst_user.id')
            ->where([
                'mst_user.mst_company_id' => $user->mst_company_id,
                'mst_user.state_flg' => AppUtils::STATE_VALID
            ])
            ->select(DB::raw('mst_user.id, chat_server_users.status, mst_user.email, chat_server_users.chat_user_name,
        CASE WHEN mst_user.option_flg = 0 THEN mst_user.email
        WHEN mst_user.option_flg = 1 THEN mst_user.notification_email
        END as email_option
        , CONCAT(mst_user.family_name, mst_user.given_name) AS username'))
            ->orderBy($orderBy, $orderDir);

        $where = ['1=1'];
        $where_arg = [];
        if (isset($status)) {
            if($status == AppUtils::CHAT_SERVER_USER_STATUS_INVALID) {
                $conditionStatus = [
                    AppUtils::CHAT_SERVER_USER_STATUS_INVALID,
                ];
                $resultData->where(function ($query) use ($conditionStatus) {
                    $query->whereIn('chat_server_users.status', $conditionStatus)
                        ->orWhere('chat_server_users.status', null);
                });
            } else {
                $conditionStatus = $status;
                $resultData->where('chat_server_users.status', $conditionStatus);
            }
        }
        if (isset($chatUserName)) {
            $where[] = 'INSTR(chat_server_users.chat_user_name, ?)';
            $where_arg[] = $chatUserName;
        }
        if (isset($emailOption)) {
            $where[] = 'INSTR(CASE
             WHEN mst_user.option_flg = 0 THEN mst_user.email
             WHEN mst_user.option_flg = 1 THEN mst_user.notification_email
             END, ?)';
            $where_arg[] = $emailOption;
        }
        if ($email) {
            $where[] = 'INSTR(mst_user.email, ?)';
            $where_arg[] = $email;
        }
        if($username) {
            $where[] = 'INSTR(CONCAT(mst_user.family_name,mst_user.given_name), ?)';
            $where_arg[] = $username;
        }

        $resultData = $resultData->whereRaw(implode(" AND ", $where), $where_arg);

        $resultData = $resultData->paginate($limit)->appends(request()->input());

        $orderDir = strtolower($orderDir) == "asc" ? "desc" : "asc";

        $this->assign('resultData', $resultData);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);

        $this->setMetaTitle("ササッとTalk利用者設定");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->addStyleSheet('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css');
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);

        return $this->render('Chat.index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $chatServerUser = DB::table('chat_server_users')
            ->where(function($query) {
                $query->where('chat_server_users.status', null)
                    ->orWhere('chat_server_users.status', '!=', AppUtils::CHAT_SERVER_USER_STATUS_DELETED);
            });
        $item = DB::table('mst_user')
            ->leftJoinSub($chatServerUser, 'chat_server_users', 'mst_user.id', 'chat_server_users.mst_user_id')
            ->select(DB::raw('mst_user.id, mst_user.email, CONCAT(mst_user.family_name, " ", mst_user.given_name) as username,
            CASE WHEN mst_user.option_flg = 0 THEN mst_user.email
            WHEN mst_user.option_flg = 1 THEN mst_user.notification_email
            END as email_option, chat_server_users.chat_user_name,
            mst_user.mst_company_id, chat_server_users.chat_role_flg, chat_server_users.status'))
            ->where('mst_user.id', $id)
            ->first();

        return response()->json(['status' => true, 'item' => $item ]);

    }


    public function bulkUsage(Request $request)
    {

        $user = \Auth::user();
        $companyId = $user->mst_company_id;
        $createUser = $user->email;
        $ids = $request->get('cids', []);
        $actionType = $request->get('actionType', '');
        $action = $request->get('action', '');
        $isSingleData = $request->get('isSingleData', false);

        $status = true;
        $message = '';
        if ($actionType == ChatUtils::ACTION_GROUP_REGISTER) {
            $type = ChatUtils::ACTION_TYPE_REGISTER;
            if ($isSingleData) {
                $item = $request->get('item', []);
                $validator = Validator::make($item, [
                    'id' => 'required|numeric|min:1',
                    'email' => 'required|email|max:256',
                    'chat_user_name' => 'nullable|string|max:64',
                    'email_option' => 'required|email|max:256',
                    'username' => 'required|max:128',
                    'chat_role_flg' => [
                        'required',
                        Rule::in([
                            AppUtils::CHAT_SERVER_USER_CHAT_ROLE_USER,
                            AppUtils::CHAT_SERVER_USER_CHAT_ROLE_ADMIN,
                        ])
                    ],
                    'status' => [
                        'required',
                        Rule::in([
                            AppUtils::CHAT_SERVER_USER_STATUS_VALID,
                            AppUtils::CHAT_SERVER_USER_STATUS_INVALID,
                        ]),
                    ]

                ]);
                if ($validator->fails())
                {
                    $message = $validator->messages();
                    $message_all = $message->all();
                    return response()->json(['status' => false,'message' => $message_all]);
                }
                if (isset($item) && isset($item['chat_user_name'])) {
                    $checkChatUserNameValid = $this->validateChatUserName($item['chat_user_name']);
                    if (!$checkChatUserNameValid) {
                        return response()->json([
                            'status' => false,
                            'message' => [__( 'message.false.register_user_talk_chat_user_name_incorrect_format')]
                        ]);
                    }
                }
            }

        } elseif ($actionType == ChatUtils::ACTION_GROUP_DELETE) {
            $type = ChatUtils::ACTION_TYPE_CANCEL;
        } elseif ($actionType == ChatUtils::ACTION_GROUP_STOP) {
            $type = ChatUtils::ACTION_TYPE_STOP;
        } elseif ($actionType == ChatUtils::ACTION_GROUP_UNSTOP) {
            $type = ChatUtils::ACTION_TYPE_UNSTOP;
        }


        $companyUser = DB::table('mst_company')
            ->join('mst_chat', 'mst_chat.mst_company_id', 'mst_company.id')
            ->where('mst_company.id', $user->mst_company_id)
            ->where('mst_company.chat_flg', AppUtils::MST_COMPANY_CHAT_FLG_USING)
            ->select('mst_company.id', 'mst_chat.user_max_limit', 'mst_chat.id')
            ->first();
        if(empty($companyUser)) {
            $status = false;
        }
        if (!$status) {
            return response()->json(['status' => false, 'message' => [__('message.false.company_talk_contract_invalid')]]);
        }

        $resultCallToRocketChat = null;
        if (count($ids)) {
            try {
                $mstChatCompany = DB::table('mst_chat')
                    ->where([
                        'mst_company_id' => $companyId,
                        'status' => AppUtils::MST_CHAT_STATUS_VALID
                    ])->select('id')
                    ->first();
                $mstChatId = $mstChatCompany->id;

                DB::beginTransaction();

                if ($type == ChatUtils::ACTION_TYPE_REGISTER) {
                    $dataInsertToDB = [];
                    $dataForNextProcess = [];
                    // 2: check mst_chat.user_max_limit by mst_company
                    $numberUserCompanyInServerChat = DB::table('chat_server_users')
                        ->whereIn('chat_server_users.status',[
                                // status data in rocketChat = 1
                                AppUtils::CHAT_SERVER_USER_STATUS_VALID,
                                AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_DELETION,
                                AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_STOP,
                                AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR,
                                AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR,

                                // status data in rocketChat = 2
                                AppUtils::CHAT_SERVER_USER_STATUS_STOPPED,
                                AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_UNSTOP,
                                AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR,
                        ])
                        ->where('chat_server_users.mst_company_id', $user->mst_company_id)
                        ->count();
                    if ($numberUserCompanyInServerChat >= $companyUser->user_max_limit) {
                        $status = false;
                        $message = [__('message.false.register_user_talk_over_limit_user')];
                    }
                    if (!$status) {
                        return response()->json(['status' => $status, 'message' => $message]);
                    }
                    $numberUserInsert = 0;
                    $chatUserRegister = 0;

                    foreach ($ids as $id) {
                        if ($isSingleData) {
                            $resultPrepare = $this->registerSingleUser($id, $companyId, $mstChatId,  $createUser, $action, true, $item);
                        } else {
                            $resultPrepare = $this->registerSingleUser($id, $companyId, $mstChatId, $createUser, $action);
                        }

                        if ($resultPrepare['result']) {

                            // Single Data
                            if ($isSingleData) {
                                if ($resultPrepare['isExisted']) {
                                    // status = 0, 1, 2, 90, 91, 92, 93
                                    $message = 'message.false.register_user_talk_user_existed';
                                } else {
                                    // insert with status = 0,
                                    if ($resultPrepare['insertToDB'] && !$resultPrepare['nextStep']) {
                                        $message = 'message.success.register_single_user_talk_without_use_service';
                                    }
                                    else {
                                        $message = 'message.success.register_single_user_talk_without_use_service';
                                    }
                                }

                            }
                            if ($resultPrepare['userActive']) {
                                $chatUserRegister++;
                            }

                            if ($resultPrepare['insertToDB']) {
                                $numberUserInsert++;
                                $chatServerUserInfoId = DB::table('chat_server_users')
                                    ->insertGetId($resultPrepare['dataInsert']);
                            }
                            if ($resultPrepare['updateDB']) {
                                $chatServerUserInfoId = $resultPrepare['chat_server_users_id'];
                                DB::table('chat_server_users')
                                    ->where('id', $chatServerUserInfoId)->update($resultPrepare['dataUpdate']);
                            }
                            if ($resultPrepare['nextStep']) {
                                array_push($dataForNextProcess, $chatServerUserInfoId);
                            }


                        } else {
                            $message = $resultPrepare['message'];
                            if (isset($resultPrepare['option'])) {
                                $option = $resultPrepare['option'];
                                $key = $option['key'];
                                $value = $option['value'];

                                return response()->json(['status' => false, 'message' => [__( $message, [
                                    $key => $value ])
                                ]]);
                            } else {
                                return response()->json(['status' => false, 'message' => [__( $message)]]);
                            }

                        }
                    }
                    if ($numberUserCompanyInServerChat + $numberUserInsert > $companyUser->user_max_limit) {
                        return response()->json(['status' => false, 'message' => [__('message.false.register_user_talk_over_limit_user')]]);
                    }
                    DB::commit();

                    if (count($dataForNextProcess)) {
                        $multipleRegister = false;
                        if ($action == ChatUtils::ACTION_MULTIPLE_REGISTER) {
                            $multipleRegister = true;
                        }
                        $resultCallToRocketChat = $this->registerToRocketChat($mstChatId, $multipleRegister);
                    } else {
                        // not call api to RocketChat
                        if (empty($message) && $chatUserRegister) {
                            $message = 'message.false.register_user_talk_user_existed';
                        }
                    }

                    if (isset($resultCallToRocketChat)) {
                        $status = $resultCallToRocketChat['status'];
                        $message = $resultCallToRocketChat['message'];
                        if (isset($resultCallToRocketChat['option']) &&  $resultCallToRocketChat['option']) {
                            $option = $resultCallToRocketChat['option'];
                        }
                    }

                } else if ($type == ChatUtils::ACTION_TYPE_CANCEL){
                    $dataForNextProcess = [];
                    $isMultipleDelete = false;
                    if ($action == ChatUtils::ACTION_MULTIPLE_DELETE) {
                        $isMultipleDelete = true;
                    }

                    foreach ($ids as $id) {
                        $resultPrepare = $this->deleteSingleTalk($id, $isSingleData, $isMultipleDelete);
                        if ($resultPrepare['result']) {
                            if ($resultPrepare['registerWithoutUserService']) {
                                $message = 'message.success.delete_single_user_talk_registered_without_use_service';
                            }
                            if ($resultPrepare['isUpdate']) {
                                DB::table('chat_server_users')
                                    ->where('id', $resultPrepare['chat_server_user_id'])
                                    ->update($resultPrepare['dataUpdate']);
                            }
                            if ($resultPrepare['isNextStep']) {
                                array_push($dataForNextProcess, $resultPrepare['chat_server_user_id']);
                            }

                        } else {
                            $message = $resultPrepare['message'];
                            return response()->json(['status' => false, 'message' => [__( $message)]]);
                        }
                    }
                    DB::commit();
                    if (count($dataForNextProcess)) {
                        $resultCallToRocketChat = $this->deleteToRocketChat($mstChatId);
                    } else {

                        // multiple delete without call to RocketChat
                        if (empty($message)) {
                            $message = 'message.success.bulk_delete_user_talk_success';
                        }
                    }
                    if (isset($resultCallToRocketChat)) {
                        $target = $resultCallToRocketChat['target'];
                        $success = $resultCallToRocketChat['success'];
                        $failure = $resultCallToRocketChat['failure'];
                        $numberDataSuccess = $target - $failure;
                        if (!$success) {
                            $status = false;
                            // error
                            if ($failure == ChatUtils::SINGLE_DATA && !$isMultipleDelete) {
                                if (count($ids) == 1) {
                                    $userId = $ids[0];
                                    $chatServerUser = DB::table('chat_server_users')
                                        ->where([
                                            'mst_company_id' => $companyId,
                                            'mst_user_id' => $userId,
                                            'status' => AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR
                                        ])->select('chat_user_name')
                                        ->first();
                                    if ($chatServerUser) {
                                        $option = [
                                            'chat_user_name' => $chatServerUser->chat_user_name
                                        ];
                                    }
                                }
                                $message = 'message.false.delete_single_user_talk';

                            } else {
                                // error multiple delete
                                $message = 'message.false.delete_multiple_user_talk';
                            }
                        } else {
                            // success
                            if ($failure == ChatUtils::NONE_DATA) {
                                if (!$isMultipleDelete) {
                                    // success single delete
                                    $message = 'message.success.delete_single_user_talk_success';
                                } else {
                                    $message = 'message.success.bulk_delete_user_talk_success';
                                }
                            } else {
                                $message = 'message.success.delete_multiple_user_talk_success';
                                $option = [
                                    'success' => $numberDataSuccess,
                                    'failure' => $failure,
                                ];
                            }
                        }
                    }
                } else if ($type == ChatUtils::ACTION_TYPE_STOP) {
                    $isMultipleStop = false;
                    $dataForNextProcess = [];
                    if ($action == ChatUtils::ACTION_MULTIPLE_STOP) {
                        $isMultipleStop = true;
                    }
                    $numberDataUpdateForStopAction = 0;
                    $totalDataRequest = count($ids);
                    $numberDataNotRegister = 0;
                    $chatUserStopped = 0;
                    $chatUserStopProcess = 0;
                    foreach ($ids as $id) {
                        $resultPrepare = $this->stopSingleTalk($id, $isSingleData);
                        if ($resultPrepare['result']) {
                            if ($resultPrepare['stopWithoutUseService']) {
                                $message = 'message.success.stop_single_user_talk_without_call_to_rocket_chat';
                            }
                            if ($resultPrepare['isStopped']) {
                                $chatUserStopped++;
                            } else {
                                if ($resultPrepare['isUpdate'] || $resultPrepare['isNextStep']) {
                                    $chatUserStopProcess++;
                                }
                            }
                            if ($resultPrepare['isUpdate']) {
                                $numberDataUpdateForStopAction++;
                                DB::table('chat_server_users')
                                    ->where('id', $resultPrepare['chat_server_user_id'])
                                    ->update($resultPrepare['dataUpdate']);
                            }
                            if ($resultPrepare['isNextStep']) {
                                array_push($dataForNextProcess, $resultPrepare['chat_server_user_id']);
                            }
                            if ($resultPrepare['isNotRegister']) {
                                $numberDataNotRegister++;
                                $message = 'message.false.stop_user_not_registered';
                            }
                        } else {
                            $message = $resultPrepare['message'];
                            return response()->json(['status' => false, 'message' => [__( $message)]]);
                        }
                    }
                    DB::commit();
                    if (count($dataForNextProcess)) {
                        $resultCallToRocketChat = $this->stopToRocketChat($mstChatId, $numberDataUpdateForStopAction, $isMultipleStop);
                    } else {
                        if ($chatUserStopped) {
                            $message = 'message.success.stop_multiple_or_user_talk_stopped';
                        }
                        if ($numberDataNotRegister == $totalDataRequest) {
                            $status = false;
                        }

                        // Todo: status = [93, {null, 0, 90}]
                        // Check additional process
                    }

                    if (isset($resultCallToRocketChat)) {
                        $status = $resultCallToRocketChat['status'];
                        $message = $resultCallToRocketChat['message'];
                        if (isset($resultCallToRocketChat['option']) &&  $resultCallToRocketChat['option']) {
                            $option = $resultCallToRocketChat['option'];
                        }
                    }

                } else if ($type == ChatUtils::ACTION_TYPE_UNSTOP) {
                    $isSingleData = true;
                    $dataForNextProcess = [];
                    if (count($ids) > 1) {
                        $isSingleData = false;
                    }

                    foreach ($ids as $id) {
                        $resultPrepare = $this->unstopSingleTalk($id, $isSingleData);
                        if ($resultPrepare['result']) {
                            if ($resultPrepare['isUpdate']) {
                                DB::table('chat_server_users')
                                    ->where('id', $resultPrepare['chat_server_user_id'])
                                    ->update($resultPrepare['dataUpdate']);
                            }
                            if ($resultPrepare['isNextStep']) {
                                array_push($dataForNextProcess, $resultPrepare['chat_server_user_id']);
                            }
                        } else {
                            $message = $resultPrepare['message'];
                            return response()->json(['status' => false, 'message' => [__( $message)]]);
                        }
                    }
                    DB::commit();
                    if (count($dataForNextProcess)) {
                        $resultCallToRocketChat = $this->unstopToRocketChat($mstChatId);
                    }
                    if (isset($resultCallToRocketChat)) {
                        $status = $resultCallToRocketChat['status'];
                        $message = $resultCallToRocketChat['message'];
                        if (isset($resultCallToRocketChat['option']) &&  $resultCallToRocketChat['option']) {
                            $option = $resultCallToRocketChat['option'];
                        }
                    }

                }

                if (isset($option)) {
                    return response()->json(['status' => $status, 'message' => [__($message, $option)]]);
                } else {
                    return response()->json(['status' => $status, 'message' => [__( $message)]]);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . $e->getTraceAsString());
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
            }
        }
    }

    public function registerSingleUser($id, $companyId, $mstChatId, $createUser, $action, $isSingle = false, $data = null)
    {
        try {
            $dataInsert = [];
            $dataUpdate = [];
            $result = true;
            $message = '';
            $needCheckChatEmail = false;
            $needCheckChatUserName = false;
            $needCheckChatUserStatusRequest = false;
            $insertToDB = false;
            $isNextStep = false;
            $isUpdateDB = false;
            $userActive = false;
            $withoutInsertOrUpdate = false;

            $emailOption = null;
            $userInfo = null;
            $chatServerUserStatus = null;
            $chatRoleFlg = null;
            $isExisted = false;
            $chatServerUserId = null;
            $key = null;
            // Single register
            if ($data) {
                $chatUserName = $data['chat_user_name'];
                $emailOption = $data['email_option'];
                $chatServerUserStatus = $data['status'];
                $chatRoleFlg = $data['chat_role_flg'];
                $personalName = $data['username'];
            }
            if (empty($chatUserName)) {
                $userInfo = DB::table('mst_user')
                    ->where('id', $id)
                    ->select(DB::raw('email,
                CASE WHEN mst_user.option_flg = 0 THEN mst_user.email
                WHEN mst_user.option_flg = 1 THEN mst_user.notification_email
                END as email_option,
                CONCAT(mst_user.family_name, " ", mst_user.given_name) AS username
                '))
                    ->first();
                $emailInfo =  explode('@', $userInfo->email);
                if (str_contains($emailInfo[0], '+')) {
                    $nameElements = explode('+', $emailInfo[0]);
                    $chatUserName = $nameElements[0];
                } else {
                    $chatUserName = $emailInfo[0];
                }

                if (strlen($chatUserName) > 80 ) {
                    $chatUserName = substr($chatUserName, 0, 80);
                }
                $personalName = $userInfo->username;
            }

            if (is_null($chatServerUserStatus)) {
                $chatServerUserStatus = AppUtils::CHAT_SERVER_USER_STATUS_VALID;
            }
            $chatRoleFlgDefault = AppUtils::CHAT_SERVER_USER_CHAT_ROLE_USER;

            if (empty($emailOption) && $userInfo) {
                $emailOption = $userInfo->email_option;
            }

            // check chat_server_users.mst_user_id existed
            $userRegister = DB::table('chat_server_users')
                ->where('chat_server_users.mst_user_id', $id)
                ->where('chat_server_users.status', '!=',
                    AppUtils::CHAT_SERVER_USER_STATUS_DELETED)
                ->select('chat_server_users.status', 'chat_server_users.id',
                    'chat_server_users.chat_user_name', 'chat_server_users.chat_role_flg')
                ->first();
            // status = [0, 1, 2, 10, 11, 12, 13, 90, 91, 92, 93]

            if($userRegister) {
                // Check chat_server_users.status

                // status = 1, 2, 91, 92, 93
                if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_VALID ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOPPED ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR )
                {
                    $userActive = true;
                    //
                    if ($isSingle) {
                        $withoutInsertOrUpdate = true;
                    }
                } else {
                    // status = [0, 10, 11, 12, 13, 90]
                    if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_INVALID ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR) {

                        // status = 0, 90
                        $isExisted = true;
                        $chatServerUserId = $userRegister->id;
                        if (is_null($chatRoleFlg)) {
                            $chatRoleFlg =  $userRegister->chat_role_flg;
                        }
                        $needCheckChatUserName = true;
                    } else {
                        // status = [10, 11, 12, 13]
                        $result = false;
                        $message = $this->getMessageErrorWhenProcessing($userRegister->status);
                    }
                }
            } else {
                $needCheckChatEmail = true;
            }
            if (!$result) {
                return [
                    'result' => $result,
                    'message' => $message,
                ];
            }
            if (is_null($chatRoleFlg)) {
                $chatRoleFlg = $chatRoleFlgDefault;
            }

            if ($needCheckChatEmail) {
                // check chat_server_users.chat_email existed
                $emailChatIsUsed = DB::table('chat_server_users')
                    ->where('chat_server_users.chat_email', $emailOption)
                    ->where('chat_server_users.status', '!=',
                        AppUtils::CHAT_SERVER_USER_STATUS_DELETED)
                    ->count();
                if ($emailChatIsUsed) {
                    $result = false;
                    $message = 'message.false.register_user_talk_chat_email_used';
                } else {
                    $needCheckChatUserName = true;
                }
            }

            if (!$result) {
                return [
                    'result' => $result,
                    'message' => $message,
                ];
            }

            if ($needCheckChatUserName) {

                // check chat_user_name exited ?
                $chatUserNameExisted = DB::table('chat_server_users')
                    ->where('chat_user_name', $chatUserName)
                    ->where('chat_server_users.status', '!=',
                        AppUtils::CHAT_SERVER_USER_STATUS_DELETED)
                    ->first();
                if ($chatUserNameExisted) {
                    $resultError = true;
                    if ($chatUserNameExisted->mst_user_id == $id) {
                        if ($chatUserNameExisted->status == AppUtils::CHAT_SERVER_USER_STATUS_INVALID ||
                            $chatUserNameExisted->status == AppUtils::CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR) {
                            $needCheckChatUserStatusRequest = true;
                            if ($chatUserNameExisted->chat_role_flg != $chatRoleFlg) {
                                $dataUpdate['chat_role_flg'] = $chatRoleFlg;
                            }
                            $resultError = false;
                        }
                    }
                    if ($resultError) {
                        $result = false;
                        $message = 'message.false.register_user_talk_chat_user_name_existed';
                        $key = 'chat_user_name';
                    }

                } else {
                    // chat_user_name can use
                    // Check user registered with other chat_user_name
                    if ($isExisted) {
                        // update chat_user_name, chat_role_flg
                        if ($userRegister->chat_user_name != $chatUserName && $action == ChatUtils::ACTION_SINGLE_REGISTER ) {
                            $dataUpdate['chat_user_name'] = $chatUserName;
                        }
                        if ($userRegister->chat_role_flg != $chatRoleFlg) {
                            $dataUpdate['chat_role_flg'] = $chatRoleFlg;
                        }
                    }


                    $needCheckChatUserStatusRequest = true;
                }

            }

            if (!$result) {
                return [
                    'result' => $result,
                    'message' => $message,
                    'option' => [
                        'key' => $key,
                        'value' => $chatUserName
                    ]
                ];
            }


            if ($needCheckChatUserStatusRequest) {
                // chat_server_users.status request
                if (!$isExisted) {

                    $insertToDB = true;
                    $dataInsert = [
                        'mst_company_id' => $companyId,
                        'mst_user_id' => $id,
                        'chat_user_id' => null,
                        'mst_chat_id' => $mstChatId,
                        'chat_personal_name' => $personalName,
                        'chat_user_name' => $chatUserName,
                        'chat_role_flg' => $chatRoleFlg,
                        'chat_email' => $emailOption,
                        'status' => $chatServerUserStatus,
                        'system_remark' => null,
                        'create_at' => Carbon::now(),
                        'create_user' => $createUser
                    ];
                } else {
                    $isUpdateDB = true;
                }

                if ($chatServerUserStatus == AppUtils::CHAT_SERVER_USER_STATUS_VALID) {
                    // status = 1
                    $isNextStep = true;
                    if ($insertToDB) {
                        $dataInsert['status'] = AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_REGISTRATION;
                    } elseif ($isUpdateDB) {
                        $dataUpdate['status'] = AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_REGISTRATION;
                    }

                }else if ($chatServerUserStatus == AppUtils::CHAT_SERVER_USER_STATUS_INVALID){
                    if ($insertToDB) {
                        $dataInsert['status'] = $chatServerUserStatus;
                    } elseif ($isUpdateDB) {
                        $dataUpdate['status'] = $chatServerUserStatus;
                    }
                }

            }

            return [
                'result' => $result,
                'userActive' => $userActive,
                'isExisted' => $withoutInsertOrUpdate,
                'insertToDB' => $insertToDB,
                'updateDB' => $isUpdateDB,
                'nextStep' => $isNextStep,
                'dataInsert' => $dataInsert,
                'dataUpdate'=> $dataUpdate,
                'chat_server_users_id' => $chatServerUserId
            ];
        } catch (\Exception $e) {
            Log::error("ChatController@registerSingleUser");
            Log::error($e->getMessage() . $e->getTraceAsString());
        }

    }

    public function deleteSingleTalk($id, $isDeleteSingle = false, $isMultipleRegister = false)
    {
        try {
            $dataUpdate = [];
            $isUpdate = false;
            $isNextStep = false;
            $chatServerUserId = null;
            $result = true;
            $message = '';
            $registerWithoutUseService = false;

            // check chat_server_users.mst_user_id existed
            $userRegister = DB::table('chat_server_users')
                ->where('chat_server_users.mst_user_id', $id)
                ->where('chat_server_users.status', '!=',
                    AppUtils::CHAT_SERVER_USER_STATUS_DELETED)
                ->first();
            // status = [0, 1, 2, 10, 11, 12, 13, 90, 91, 92, 93]

            if ($userRegister) {

                // status = 1, 2, 91, 92, 93
                if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_VALID ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOPPED ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR)
                {
                    $isUpdate = true;
                    $isNextStep = true;
                    $chatServerUserId = $userRegister->id;
                    $dataUpdate['status'] = AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_DELETION;

                } else if($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_INVALID ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR) {
                    // status = 0 | 90
                    $isUpdate = true;
                    $chatServerUserId = $userRegister->id;
                    $dataUpdate['status'] = AppUtils::CHAT_SERVER_USER_STATUS_DELETED;
                    if ($isDeleteSingle) {
                        $registerWithoutUseService = true;
                    }
                } else {
                    $result = false;
                    // status = [10, 11, 12, 13]
                    $message = $this->getMessageErrorWhenProcessing($userRegister->status);

                }
            } else if ($isDeleteSingle) {
                if ($isMultipleRegister) {
                    $message = 'message.false.bulk_delete_user_talk_success';
                } else {
                    $result = false;
                    $message = 'message.false.delete_user_talk_user_not_exist';
                }


            }
            if (!$result) {
                return [
                    'result' => $result,
                    'message' => $message,
                ];
            }
            return [
                'result' => $result,
                'registerWithoutUserService' => $registerWithoutUseService,
                'isUpdate' => $isUpdate,
                'chat_server_user_id' => $chatServerUserId,
                'dataUpdate' => $dataUpdate,
                'isNextStep' => $isNextStep
            ];
        } catch (\Exception $e) {
            Log::error("ChatController@deleteSingleTalk");
            Log::error($e->getMessage() . $e->getTraceAsString());
        }

    }

    public function stopSingleTalk($id, $isStopSingle = false)
    {
        try {
            $dataUpdate = [];
            $isUpdate = false;
            $isStopped = false;
            $isNextStep = false;
            $isNotRegister = false;
            $chatServerUserId = null;
            $result = true;
            $message = '';

            $stopWithoutUseService = false;
            // check chat_server_users.mst_user_id existed
            $userRegister = DB::table('chat_server_users')
                ->where('chat_server_users.mst_user_id', $id)
                ->where('chat_server_users.status', '!=',
                    AppUtils::CHAT_SERVER_USER_STATUS_DELETED)
                ->first();
            // status = [0, 1, 2, 10, 11, 12, 13, 90, 91, 92, 93]

            if ($userRegister) {
                if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR) {
                    // status = 93 update without call api to rocketChat
                    $isUpdate = true;
                    $chatServerUserId = $userRegister->id;
                    $dataUpdate['status'] = AppUtils::CHAT_SERVER_USER_STATUS_STOPPED;
                    $stopWithoutUseService = true;
                } elseif ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_VALID ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR ) {
                    // status = 1, 91, 92 update then call api to rocketChat

                    $isUpdate = true;
                    $isNextStep = true;
                    $chatServerUserId = $userRegister->id;
                    $dataUpdate['status'] = AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_STOP;
                } else if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOPPED) {
                    // status = 2
                    $isStopped = true;
                } else {

                    if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_REGISTRATION ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_DELETION ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_STOP ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_UNSTOP) {
                        $result = false;

                        // status = [10, 11, 12, 13]
                        $message = $this->getMessageErrorWhenProcessing($userRegister->status);
                    } else {
                        // status = [0, 90]
                        $isNotRegister = true;
                    }


                }

            } else {
               $isNotRegister = true;
            }

            if (!$result) {
                return [
                    'result' => $result,
                    'message' => $message,
                ];
            }

            return [
                'result' => $result,
                'isStopped' => $isStopped,
                'stopWithoutUseService' => $stopWithoutUseService,
                'isNotRegister' => $isNotRegister,
                'isUpdate' => $isUpdate,
                'chat_server_user_id' => $chatServerUserId,
                'dataUpdate' => $dataUpdate,
                'isNextStep' => $isNextStep
            ];

        } catch (\Exception $e) {
            Log::error("ChatController@stopSingleTalk");
            Log::error($e->getMessage() . $e->getTraceAsString());
        }
    }

    public function unstopSingleTalk($id, $isUnstopSingle = false)
    {
        try {
            $dataUpdate = [];
            $isUpdate = false;
            $isNextStep = false;
            $chatServerUserId = null;
            $result = true;
            $message = '';

            // check chat_server_users.mst_user_id existed

            $userRegister = DB::table('chat_server_users')
                ->where('chat_server_users.mst_user_id', $id)
                ->where('chat_server_users.status', '!=',
                    AppUtils::CHAT_SERVER_USER_STATUS_DELETED)
                ->first();
            // status = [0, 1, 2, 10, 11, 12, 13, 90, 91, 92, 93]

            if ($userRegister) {
                if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOPPED ||
                    $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_UNSTOP_ERROR) {

                    $isUpdate = true;
                    $isNextStep = true;
                    $chatServerUserId = $userRegister->id;
                    $dataUpdate['status'] = AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_UNSTOP;
                } else {
                    $result = false;
                    $message = 'message.false.unstop_user_not_registered';

                    if ($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_VALID ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_DELETION_ERROR ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_STOP_ERROR) {

                        $message = 'message.success.unstop_single_user_was_active';

                    } else if($userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_REGISTRATION ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_DELETION ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_STOP ||
                        $userRegister->status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_UNSTOP) {

                        $message = $this->getMessageErrorWhenProcessing($userRegister->status);
                    }
                }

            } else if($isUnstopSingle) {
                // not register
                $result = false;
                $message = 'message.false.unstop_user_not_registered';
            }
            if (!$result) {
                return [
                    'result' => $result,
                    'message' => $message,
                ];
            }

            return [
                'result' => $result,
                'isUpdate' => $isUpdate,
                'chat_server_user_id' => $chatServerUserId,
                'dataUpdate' => $dataUpdate,
                'isNextStep' => $isNextStep
            ];

        } catch (\Exception $e) {
            Log::error("ChatController@unstopSingleTalk");
            Log::error($e->getMessage() . $e->getTraceAsString());
        }
    }


    // PAC_5-2663
    public function registerToRocketChat($mstChatId, $isMultipleRegister = false)
    {
        $chatService = new ChatService();
        $resultAction =  $chatService->addUser($mstChatId);
        $result = [
            'target' => null,
            'success' => null,
            'failure' => null,
        ];

        if ($resultAction && isset($resultAction['target']) &&
            isset($resultAction['success']) && isset($resultAction['failure'])) {
            $result = $resultAction;
        }
        return $this->getMessageRegisterRocketChat($result, $isMultipleRegister);

    }

    public function getMessageRegisterRocketChat($resultCallToRocketChat, $isMultipleRegister = false)
    {
        $status = true;
        $target = $resultCallToRocketChat['target'];
        $success = $resultCallToRocketChat['success'];
        $failure = $resultCallToRocketChat['failure'];
        $numberDataSuccess = $target - $failure;
        $result = [];
        if (!$success) {
            $status = false;
            if ($failure == ChatUtils::SINGLE_DATA && !$isMultipleRegister) {
                $message = 'message.false.register_user_talk_single_user';
            } else {
                // error multiple register
                $message = 'message.false.register_user_talk_multiple_user';
            }

        } else {
            // success
            if ($failure == ChatUtils::NONE_DATA) {
                if (!$isMultipleRegister) {
                    $message = 'message.success.register_single_user_talk_success';
                } else {
                    $message = 'message.success.bulk_register_user_talk_success';
                }
            } else {
                $message = 'message.success.register_multiple_user_talk_success';
                $option = [
                    'success' => $numberDataSuccess,
                    'failure' => $failure,
                ];
            }
        }
        $result['status'] = $status;
        $result['message'] = $message;
        if (isset($option)) {
            $result['option'] = $option;
        }
        return $result;

    }

    // PAC_5-2663
    public function deleteToRocketChat($mstChatId)
    {
        $chatService = new ChatService();
        $resultAction =  $chatService->deleteUser($mstChatId);
        $result = [
            'target' => null,
            'success' => null,
            'failure' => null,
        ];

        if ($resultAction && isset($resultAction['target']) &&
            isset($resultAction['success']) && isset($resultAction['failure'])) {
            $result = $resultAction;
        }
        return $result;

    }

    public function stopToRocketChat($mstChatId, $totalDataUpdate, $isMultipleStop = false)
    {
        $chatService = new ChatService();
        $resultAction =  $chatService->stopUser($mstChatId);

        $result = [
            'target' => null,
            'success' => null,
            'failure' => null,
        ];

        if ($resultAction && isset($resultAction['target']) &&
            isset($resultAction['success']) && isset($resultAction['failure'])) {
            $result = $resultAction;
        }
        return $this->getMessageStopRocketChat($result, $totalDataUpdate, $isMultipleStop);
    }

    public function getMessageStopRocketChat($resultCallToRocketChat, $totalDataUpdate, $isMultipleStop = false)
    {
        $status = true;
        $target = $resultCallToRocketChat['target'];
        $success = $resultCallToRocketChat['success'];
        $failure = $resultCallToRocketChat['failure'];
        $numberDataSuccess = $totalDataUpdate - $failure;
        $result = [];
        if (!$success) {
            $status = false;
            // error
            if ($failure == ChatUtils::SINGLE_DATA && !$isMultipleStop) {
                // error single register
                $message = 'message.false.stop_single_user_to_rocket_chat_error';
            } else {
                // error multiple register
                $message = 'message.false.stop_multiple_user_to_rocket_chat_error';
            }
        } else {
            // success
            if ($failure == ChatUtils::NONE_DATA) {
                if (!$isMultipleStop) {
                    $message = 'message.success.stop_single_user_talk_call_to_rocket_chat';
                } else {
                    $message = 'message.success.bulk_stop_user_talk_success';
                }
            } else {
                $message = 'message.success.stop_multiple_user_talk_success';
                $option = [
                    'success' => $numberDataSuccess,
                    'failure' => $failure,
                ];
            }

        }
        $result['status'] = $status;
        $result['message'] = $message;
        if (isset($option)) {
            $result['option'] = $option;
        }
        return $result;
    }

    public function unstopToRocketChat($mstChatId)
    {
        $chatService = new ChatService();

        $resultAction =  $chatService->unstopUser($mstChatId);

        $result = [
            'target' => null,
            'success' => null,
            'failure' => null,
        ];

        if ($resultAction && isset($resultAction['target']) &&
            isset($resultAction['success']) && isset($resultAction['failure'])) {
            $result = $resultAction;
        }
        return $this->getMessageUnstopRocketChat($result);
    }

    public function getMessageUnstopRocketChat($resultCallToRocketChat)
    {
        $status = true;
        $target = $resultCallToRocketChat['target'];
        $success = $resultCallToRocketChat['success'];
        $failure = $resultCallToRocketChat['failure'];
        $numberDataSuccess = $target - $failure;
        $result = [];
        if (!$success) {
            $status = false;
            // error
            if ($failure == 1) {
                // error single data
                $message = 'message.false.unstop_single_user_to_rocket_chat_error';
            } else {
                // error multiple data
//                $message = 'message.false.stop_multiple_user_to_rocket_chat_error';
            }
        } else {
            // success
            if ($numberDataSuccess == 1 && $failure == 0) {
                // success single data
                $message = 'message.success.unstop_single_user_talk';
            } else {
                // multiple data

            }
        }

        $result['status'] = $status;
        $result['message'] = $message;

        return $result;

    }

    public function validateChatUserName($string)
    {
        $regex = '/^[a-zA-Z0-9][a-zA-Z0-9_.\-]*$/';
        return preg_match($regex, $string);

    }

    public function checkChatUserNameConsistAtSignChar($string)
    {
        $character = '@';
        $result = strpos($string, $character);
        if ($result === false) {
            return false;
        }
        return true;
    }

    public function getMessageErrorWhenProcessing($status)
    {
        $message = '';
        // status = 10, 11, 12, 13
        if ($status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_REGISTRATION ||
            $status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_DELETION ||
            $status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_STOP ||
            $status == AppUtils::CHAT_SERVER_USER_STATUS_WAITING_FOR_UNSTOP) {

            $message = 'message.false.user_talk_processing';
        }
        return $message;
    }

}
