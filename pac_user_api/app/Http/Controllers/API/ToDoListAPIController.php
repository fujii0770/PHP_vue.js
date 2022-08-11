<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\ToDoListUtils;
use App\Models\ToDoCircularTask;
use App\Models\ToDoList;
use App\Models\ToDoTask;
use App\Models\ToDoNotice;
use App\Models\ToDoNoticeConfig;
use App\Models\ToDoGroup;
use App\Models\ToDoGroupAuth;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class ToDoListAPIController
 * @package App\Http\Controllers\API
 */
class ToDoListAPIController extends AppBaseController
{
    private $toDoListModel;
    private $toDoTaskModel;
    private $toDoCircularTaskModel;
    private $toDoNoticeModel;
    private $toDoNoticeConfigModel;
    private $toDoGroupModel;
    private $toDoGroupAuthModel;
    private $gw_use;

    public function __construct(ToDoList $toDoList, ToDoTask $toDoTask, ToDoCircularTask $toDoCircularTask, ToDoNotice $toDoNotice
        , ToDoNoticeConfig $toDoNoticeConfig, ToDoGroup $toDoGroupModel, ToDoGroupAuth $toDoGroupAuthModel)
    {
        $this->toDoListModel = $toDoList;
        $this->toDoTaskModel = $toDoTask;
        $this->toDoCircularTaskModel = $toDoCircularTask;
        $this->toDoNoticeModel = $toDoNotice;
        $this->toDoNoticeConfigModel = $toDoNoticeConfig;
        $this->toDoGroupModel = $toDoGroupModel;
        $this->toDoGroupAuthModel = $toDoGroupAuthModel;
        $this->gw_use = config('app.gw_use') == 1 && config('app.gw_domain');
    }

    /**
     * 受信一覧を取得
     * @param Request $request
     * @return mixed
     */
    public function getCircularList(Request $request)
    {
        $user = $request->user();
        $limit = AppUtils::normalizeLimit($request->get('limit', 10), 10);
        $orderBy    = $request->get('orderBy', "update_at");
        $orderDir   = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));

        $arrOrder   = ['title' => 'title','A.email' => 'A.email', 'update_at' => 'U.received_date',
            'U.circular_status' => 'U.circular_status', 'tdct.deadline' => 'IFNULL(tdct.deadline, "9999/12/30 23:59:59")', 'tdct.important' => 'tdct.important'];
        $orderBy = [isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'update_at'];
        try {
            $this->doneCircularTask($user);
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
                $resData = json_decode((string)$response->getBody());
                if (!empty($resData) && !empty($resData->data)) {
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
                ->where(function ($query) use ($user, $id_app_user_id) {
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
                ->groupBy(['C.id', 'U.parent_send_order', 'U.circular_id', 'U.receiver_title']);

            $data_query = DB::table('circular as C')
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->join('circular_user as U', function ($join) {
                    $join->on('U.circular_id', '=', 'C.id');
                    $join->on('D.parent_send_order', '=', 'U.parent_send_order');
                })
                ->leftjoin('circular_user as A', function ($join) {
                    $join->on('A.circular_id', '=', 'C.id');
                    $join->on('A.parent_send_order', '=', DB::raw("0"));
                    $join->on('A.child_send_order', '=', DB::raw("0"));
                })
                ->select(DB::raw('C.id, U.id as circular_user_id, U.received_date as update_at, C.circular_status status, U.circular_status,U.title as subject, D.file_names, IF(U.title IS NULL or trim(U.title) = \'\', D.file_names, U.title) as title, CONCAT(A.name, \' &lt;\',A.email, \'&gt;\') as email,
                tdct.id AS task_id, tdct.content, tdct.deadline, tdct.important, tdct.scheduler_id'));

            $data_query->where(function ($query) use ($user) {
                $query->where('U.email', $user->email);
                $query->where(function ($query1) {
                    $query1->where(function ($query2) {
                        $query2->where('U.parent_send_order', 0);
                        $query2->where('U.child_send_order', 0);
                        $query2->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
                    });
                    $query1->orWhere(function ($query2) {
                        $query2->where('U.child_send_order', '>', 0);
                        $query2->whereIn('U.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS]);
                    });
                })
                    ->whereIn('C.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS]);
            });

            $data_query = $data_query->leftJoin('to_do_circular_task AS tdct', function ($query) use ($user) {
                $query->on('tdct.circular_user_id', '=', 'U.id')
                    ->on('tdct.mst_user_id', '=', DB::raw($user->id))
                    ->whereIn('tdct.state', [ToDoListUtils::NOT_NOTIFY_STATUS, ToDoListUtils::NOTIFIED_STATUS]);
            });

            foreach ($orderBy as $order) {
                $data_query = $data_query->orderBy(DB::raw($order), $orderDir)->orderBy('U.circular_status');
            }
            $data = $data_query->paginate($limit)->appends(request()->input());

            if (!$data->isEmpty()) {
                foreach ($data as $item) {
                    // PAC_5-634 自身のメールアドレスを宛先に追加して申請後、受信一覧で同じ文書名が連続して表示される, process file name
                    $fileNames = explode(CircularUserUtils::SEPERATOR, $item->file_names);
                    if (!trim($item->subject)) {
                        $item->title = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames), 0, 100) : reset($fileNames);
                    }
                    if (empty($item->title)) {
                        $circular_info = DB::table('circular_user')
                            ->where('circular_id', $item->id)
                            ->whereNotNull('receiver_title')
                            ->orderBy('parent_send_order', 'DESC')
                            ->orderBy('child_send_order', 'DESC')
                            ->select('receiver_title')
                            ->first();
                        if ($circular_info && $circular_info->receiver_title) {
                            $item->title = $circular_info->receiver_title;
                        }
                    }
                }
            }
            return $this->sendResponse(['data' => $data], __('message.success.data_get', ['attribute' => '受信文書']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * ToDoリストを取得
     * @param Request $request
     * @return mixed
     */
    public function getToDoList(Request $request)
    {
        $user = $request->user();
        $limit = AppUtils::normalizeLimit($request->get('limit', 5), 5);
        try {
            $type = $request->type;
            $query = $this->toDoListModel
                ->from('to_do_list', 'tdl');
            $filed_str = 'tdl.id, tdl.title, tdl.type, tdl.group_id';
            if ($type != ToDoListUtils::PUBLIC_LIST) {
                $query->where('tdl.mst_user_id', $user->id);
            } else {
                $filed_str .= ',tdg.title AS group_title';
                $user_info = DB::table('mst_user_info')->where('mst_user_id', $user->id)->select('mst_user_id', 'mst_department_id')->first();
                $query->leftJoin('to_do_group AS tdg', 'tdl.group_id', '=', 'tdg.id')
                    ->leftJoin('to_do_group_auth AS tdga', 'tdg.id', '=', 'tdga.group_id')
                    ->where(function ($query_sub) use ($user_info) {
                        $query_sub->where('tdl.group_id', DB::raw(0))
                            ->orWhere(function ($query_sub_1) use ($user_info) {
                                $query_sub_1->where(function ($query_sub_2) use ($user_info) {
                                    $query_sub_2->where('tdga.auth_type', DB::raw(ToDoListUtils::DEPARTMENT_AUTH))
                                            ->where('tdga.auth_department_id', DB::raw($user_info->mst_department_id ?? 0));
                                    })
                                    ->orWhere(function ($query_sub_2) use ($user_info) {
                                        $query_sub_2->where('tdga.auth_type', DB::raw(ToDoListUtils::USER_AUTH))
                                            ->where('tdga.auth_user_id', DB::raw($user_info->mst_user_id));
                                    });
                            });
                    });
            }
            $query->where('tdl.mst_company_id', $user->mst_company_id)
                ->where('tdl.type', $type);

            $data = $query->selectRaw($filed_str)->groupBy('tdl.id')->orderBy('tdl.created_at', 'desc')->paginate($limit)->appends(request()->input());

            return $this->sendResponse($data, __('message.success.data_get', ['attribute' => 'ToDoリスト']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * ToDoリストの詳細を取得する
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function getToDoListDetail($id, Request $request)
    {
        try {
            $user = $request->user();
            $type = $request->type;
            
            $query = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id);
            
            if ($type == ToDoListUtils::PERSONAL_LIST) {
                $query->where('mst_user_id', $user->id);
            }
            $info = $query->where('id', $id)
                ->select('id', 'title', 'type', 'group_id')
                ->first();
            
            return $this->sendResponse(['data' => $info], __('message.success.detail', ['attribute' => 'ToDoリストの詳細']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * ToDoリストを追加
     * @param Request $request
     * @return mixed
     */
    public function addToDoList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric|in:1,2',
            'title' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $type = $request->type;
            $data = [
                'mst_user_id' => $user->id,
                'mst_company_id' => $user->mst_company_id,
                'type' => $type,
                'title' => $request->title,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user' => $user->family_name . ' ' . $user->given_name
            ];
            $to_do_list_id = $this->toDoListModel->insertGetId($data);
            
            return $this->sendResponse(['to_do_list_id' => $to_do_list_id], __('message.success.to_do_list.add', ['attribute' => 'リスト']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * ToDoリストを更新する
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function updateToDoList($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric|in:1,2',
            'title' => 'required|string|max:50',
            'group_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();
            if (!$this->authGroup($request->type, $id, $user)) {
                DB::rollBack();
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            $data = [
                'title' => $request->title,
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user' => $user->family_name . ' ' . $user->given_name,
            ];
            if (isset($request->group_id)) $data['group_id'] = $request->group_id;
            $this->toDoListModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where(function ($query) use ($user) {
                    $query->orWhere(function ($query1) use ($user) {
                        $query1->where('type', ToDoListUtils::PERSONAL_LIST)
                            ->where('mst_user_id', $user->id);
                    });
                    $query->orWhere('type', ToDoListUtils::PUBLIC_LIST);
                })
                ->where('id', $id)
                ->update($data);
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.update', ['attribute' => 'リスト']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * ToDoリストを削除する
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function deleteToDoList($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $to_do_info = $this->toDoListModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where(function ($query) use ($user) {
                    $query->orWhere(function ($query1) use ($user) {
                        $query1->where('type', ToDoListUtils::PERSONAL_LIST)
                            ->where('mst_user_id', $user->id);
                    });
                    $query->orWhere('type', ToDoListUtils::PUBLIC_LIST);
                })
                ->where('id', $id)
                ->first();

            if (!$to_do_info) {
                DB::rollBack();
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            if (!$this->authGroup($to_do_info->type, $id, $user)) {
                DB::rollBack();
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
    
            $scheduler_task_id_arr = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('to_do_list_id', $id)
                ->where('scheduler_task_id', '>', DB::raw(0))
                ->pluck('scheduler_task_id')
                ->toArray();

            $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('to_do_list_id', $id)
                ->delete();
            
            $this->toDoListModel
                ->where('id', $id)
                ->where('mst_company_id', $user->mst_company_id)
                ->delete();

            $token = ToDoListUtils::getGroupwareToken($request);
            if (count($scheduler_task_id_arr) > 0 && $this->gw_use && $token) {
                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {
                    $result = ToDoListUtils::deleteSchedulerTask($token, $scheduler_task_id_arr);
                    if (!$result->status) {
                        DB::rollBack();
                        return $this->sendError($result->message, $result->code);
                    }
                }
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.delete', ['attribute' => 'リスト']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * タスクを取得
     * @param $to_do_list_id
     * @param Request $request
     * @return mixed
     */
    public function getTaskList($to_do_list_id, Request $request)
    {
        $user = $request->user();
        $limit = AppUtils::normalizeLimit($request->get('limit', 5), 5);
        $orderBy    = $request->get('orderBy', "update_at");
        $orderDir   = AppUtils::normalizeOrderDir($request->get('orderDir', "DESC"));
    
        $arrOrder   = ['created_at' => 'created_at', 'deadline' => 'IFNULL(deadline, "9999/12/30 23:59:59")','important' => 'important'];
        $orderBy = [isset($arrOrder[$orderBy])?$arrOrder[$orderBy] : 'created_at'];
        $done = $request->get('done', 0);
        try {
            $to_do_info = $this->toDoListModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('id', $to_do_list_id)
                ->select('id', 'type')
                ->first();

            if (!$to_do_info) {
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
    
            if (!$this->authGroup($to_do_info->type, $to_do_info->id, $user)) return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
    
            $data_query = $this->toDoTaskModel
                ->from('to_do_task', 'tdt')
                ->where('to_do_list_id', $to_do_list_id)
                ->where(function ($query) use ($done) {
                    if ($done == 1) {
                        $query->where('state', ToDoListUtils::DONE_STATUS)
                            ->where(function ($query_sub) {
                                $query_sub->where('parent_id', DB::raw(0))
                                    ->orWhere(function ($query_sub_1) {
                                        $query_sub_1->where('parent_id', '>', DB::raw(0))
                                        ->whereExists(function ($query_sub_2) {
                                            $query_sub_2->select('id')
                                                ->from('to_do_task')
                                                ->where('id', DB::raw('tdt.parent_id'))
                                                ->where('state', '<', DB::raw(ToDoListUtils::DONE_STATUS));
                                        });
                                    });
                            });
                    } else {
                        $query->whereIn('state', [ToDoListUtils::NOT_NOTIFY_STATUS, ToDoListUtils::NOTIFIED_STATUS])
                            ->where(function ($query_sub) {
                                $query_sub->where('parent_id', DB::raw(0))
                                    ->orWhere(function ($query_sub_1) {
                                        $query_sub_1->where('parent_id', '>', DB::raw(0))
                                            ->whereExists(function ($query_sub_2) {
                                                $query_sub_2->select('id')
                                                    ->from('to_do_task')
                                                    ->where('id', DB::raw('tdt.parent_id'))
                                                    ->where('state', DB::raw(ToDoListUtils::DONE_STATUS));
                                            });
                                    });
                            });
                    }
                })
                ->where(function ($query) use ($to_do_info, $user) {
                    $query->where('mst_company_id', $user->mst_company_id);
                    if ($to_do_info->type == ToDoListUtils::PERSONAL_LIST) {
                        $query->where('mst_user_id', $user->id);
                    }
                })
                ->select('id', 'parent_id', 'title', 'content', 'important', 'deadline', 'created_at', 'updated_at', 'scheduler_id');
            foreach ($orderBy as $order) {
                $data_query = $data_query->orderBy(DB::raw($order), $orderDir);
            }
            $data = $data_query->paginate($limit)->appends(request()->input());
    
            if (!$data->isEmpty()) {
                $child_arr = [];
                $child_data = $this->toDoTaskModel
                    ->where('to_do_list_id', $to_do_list_id)
                    ->where('parent_id', '>', DB::raw(0))
                    ->where(function ($query) use ($done) {
                        if ($done == 1) {
                            $query->where('state', ToDoListUtils::DONE_STATUS);
                        } else {
                            $query->whereIn('state', [ToDoListUtils::NOT_NOTIFY_STATUS, ToDoListUtils::NOTIFIED_STATUS]);
                        }
                    })
                    ->where(function ($query) use ($to_do_info, $user) {
                        $query->where('mst_company_id', $user->mst_company_id);
                        if ($to_do_info->type == ToDoListUtils::PERSONAL_LIST) {
                            $query->where('mst_user_id', $user->id);
                        }
                    })
                    ->select('id', 'parent_id', 'title', 'content', 'important', 'deadline', 'created_at', 'updated_at', 'scheduler_id')
                    ->get()
                    ->toArray();
    
                array_map(function ($item) use (&$child_arr) {
                    $parent_id = $item['parent_id'];
                    if (!isset($child_arr[$parent_id])) {
                        $child_arr[$parent_id] = [];
                    }
                    $child_arr[$parent_id][] = $item;
                }, $child_data);

                foreach ($data as $item) {
                    $item->child_task = $child_arr[$item->id] ?? [];
                }
            }
            return $this->sendResponse($data, __('message.success.data_get', ['attribute' => 'タスク']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * タスクの詳細を取得
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function getTaskDetail($id, Request $request)
    {
        try {
            $user = $request->user();

            $task_data = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('to_do_list AS tdl')
                        ->where(function ($query) use ($user) {
                            $query->orWhere(function ($query1) use ($user) {
                                $query1->where('tdl.type', ToDoListUtils::PERSONAL_LIST)
                                    ->where('tdl.mst_user_id', $user->id);
                            });
                            $query->orWhere('tdl.type', ToDoListUtils::PUBLIC_LIST);
                        })
                        ->where('tdl.id', DB::raw('to_do_task.to_do_list_id'));
                })
                ->where('id', $id)
                ->select('id', 'title', 'content', 'important', 'deadline', 'parent_id', 'scheduler_id', 'scheduler_task_id', 'state', 'created_at')
                ->first();
            if (!$task_data) {
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            $task_data->scheduler_title = '';
            $token = ToDoListUtils::getGroupwareToken($request);
            if ($task_data->scheduler_task_id > 0 && $this->gw_use && $token) {
                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {
                    $scheduler_id = 0;
                    $scheduler_task_id = 0;
                    $result = ToDoListUtils::getSchedulerTaskInfo($token, $task_data->scheduler_task_id);
                    if ($result->status) {
                        if ($result->data['createUser']['id'] == $task_data->scheduler_id) {
                            $task_data->scheduler_title = $result->data['createUser']['name'];
                            $scheduler_id = $task_data->scheduler_id;
                            $scheduler_task_id = $task_data->scheduler_task_id;
                        }
                    }
                    $task_data->scheduler_id = $scheduler_id;
                    $task_data->scheduler_task_id = $scheduler_task_id;
                }
            }

            return $this->sendResponse(['data' => $task_data], __('message.success.detail', ['attribute' => 'タスクの詳細']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * タスクの詳細を取得--文書
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function getCircularTaskDetail($id, Request $request)
    {
        try {
            $user = $request->user();

            $task_data = DB::table('to_do_circular_task AS tdct')
                ->leftJoin('circular_user AS cu', 'cu.id', '=', 'tdct.circular_user_id')
                ->leftJoin('circular AS c', 'c.id', '=', 'cu.circular_id')
                ->where('tdct.circular_user_id', $id)
                ->where('tdct.mst_user_id', '=', $user->id)
                ->whereIn('tdct.state', [ToDoListUtils::NOT_NOTIFY_STATUS, ToDoListUtils::NOTIFIED_STATUS])
                ->whereIn('c.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                ->whereIn('cu.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS])
                ->where('cu.del_flg', DB::raw(0))
                ->select('tdct.id', 'tdct.title', 'tdct.content', 'tdct.important', 'tdct.deadline', 'tdct.created_at', 'tdct.circular_user_id', 'tdct.scheduler_id', 'tdct.scheduler_task_id')
                ->first();

            if (!$task_data) {
                DB::table('to_do_circular_task')->where('circular_user_id', $id)->update(['state' => ToDoListUtils::DONE_STATUS]);
                $hasCircular = DB::table('circular_user AS cu')
                    ->leftJoin('circular AS c', 'c.id', '=', 'cu.circular_id')
                    ->where('cu.id', $id)
                    ->whereIn('c.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                    ->whereIn('cu.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->where('cu.del_flg', DB::raw(0))
                    ->count();
                if (!$hasCircular) {
                    return $this->sendError(__('message.false.to_do_list.circular_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
                }
                $task_data = (object) [
                    'id' => 0,
                    'scheduler_id' => 0,
                    'scheduler_task_id' => 0,
                ];
            }
            $task_data->scheduler_title = '';
            $token = ToDoListUtils::getGroupwareToken($request);
            if ($task_data->scheduler_task_id > 0 && $this->gw_use && $token) {
                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {
                    $scheduler_id = 0;
                    $scheduler_task_id = 0;
                    $result = ToDoListUtils::getSchedulerTaskInfo($token, $task_data->scheduler_task_id);
                    if ($result->status) {
                        if ($result->data['createUser']['id'] == $task_data->scheduler_id) {
                            $task_data->scheduler_title = $result->data['createUser']['name'];
                            $scheduler_id = $task_data->scheduler_id;
                            $scheduler_task_id = $task_data->scheduler_task_id;
                        }
                    }
                    $task_data->scheduler_id = $scheduler_id;
                    $task_data->scheduler_task_id = $scheduler_task_id;
                }
            }
            return $this->sendResponse(['data' => $task_data], __('message.success.detail', ['attribute' => 'タスクの詳細']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * タスクを追加
     * @param Request $request
     * @return mixed
     */
    public function addTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_do_list_id' => 'required|numeric',
            'title' => 'required|string|max:50',
            'task_content' => 'string',
            'important' => 'numeric|in:0,1,2,3',
            'scheduler_id' => 'numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();
            if (!$this->authGroup(null, $request->to_do_list_id, $user)) {
                DB::rollBack();
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            $data = [
                'to_do_list_id' => $request->to_do_list_id,
                'mst_user_id' => $user->id,
                'mst_company_id' => $user->mst_company_id,
                'title' => $request->title,
                'content' => $request->task_content,
                'deadline' => $request->deadline === '' ? null : $request->deadline,
                'important' => $request->important,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user' => $user->family_name . ' ' . $user->given_name,
                'scheduler_id' => $request->scheduler_id,
            ];
            $task_id = $this->toDoTaskModel->insertGetId($data);

            $token = ToDoListUtils::getGroupwareToken($request);
            if (isset($request->scheduler_id) && $request->scheduler_id > 0 && $this->gw_use && $token) {
                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {
                    // スケジューラー連携
                    $data['participant_ids'] = $request->participant_ids;
                    $result = ToDoListUtils::addSchedulerTask($token, $data);
                    if (!$result->status) {
                        DB::rollBack();
                        return $this->sendError($result->message, $result->code);
                    }
                    $this->toDoTaskModel->where('id', $task_id)->update([
                        'scheduler_task_id' => $result->data['id'],
                    ]);
                }
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.add', ['attribute' => 'タスク']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * タスクの更新
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function updateTask($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'task_content' => 'string',
            'important' => 'numeric|in:0,1,2,3',
            'scheduler_id' => 'numeric',
            'parent_id' => 'numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();

            $data = [
                'title' => $request->title,
                'content' => $request->task_content,
                'deadline' => $request->deadline === '' ? null : $request->deadline,
                'important' => $request->important,
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user' => $user->family_name . ' ' . $user->given_name,
            ];

            $task_data = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('to_do_list AS tdl')
                        ->where(function ($query) use ($user) {
                            $query->orWhere(function ($query1) use ($user) {
                                $query1->where('tdl.type', ToDoListUtils::PERSONAL_LIST)
                                    ->where('tdl.mst_user_id', $user->id);
                            });
                            $query->orWhere('tdl.type', ToDoListUtils::PUBLIC_LIST);
                        })
                        ->where('tdl.id', DB::raw('to_do_task.to_do_list_id'));
                })
                ->whereIn('state', [ToDoListUtils::NOT_NOTIFY_STATUS, ToDoListUtils::NOTIFIED_STATUS])
                ->where('id', $id)
                ->select('id', 'scheduler_id', 'parent_id', 'to_do_list_id', 'scheduler_task_id', 'created_at')
                ->first();

            if (!$task_data) {
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            if (!$this->authGroup(null, $task_data->to_do_list_id, $user)) {
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            $this->toDoTaskModel->where('id', $id)->update($data);

            if (isset($request->sub_task) && is_array($request->sub_task) && $task_data->parent_id == 0) {
                $sub_task_arr = $request->sub_task;
                $sub_data = [];
                foreach ($sub_task_arr as $item) {
                    $validator = Validator::make(['title' => $item], [
                        'title' => 'required|string|max:50',
                    ]);
                    if ($validator->fails()) {
                        DB::rollBack();
                        return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                    }
                    $sub_data[] = [
                        'to_do_list_id' =>$task_data->to_do_list_id,
                        'parent_id' => $task_data->id,
                        'mst_user_id' => $user->id,
                        'mst_company_id' => $user->mst_company_id,
                        'title' => $item,
                        'content' => '',
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'create_user' => $user->family_name . ' ' . $user->given_name
                    ];
                }
                $this->toDoTaskModel->insert($sub_data);
            }

            $token = ToDoListUtils::getGroupwareToken($request);
            if (isset($request->scheduler_id) && $this->gw_use && $token){

                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {

                    $data['participant_ids'] = $request->participant_ids;
                    $data['scheduler_id'] = $request->scheduler_id;
                    $scheduler_task_id = -1;

                    if ($request->scheduler_id != $task_data->scheduler_id) {
                        // スケジューラリンケージの更新
                        if ($task_data->scheduler_id > 0) {
                            $result = ToDoListUtils::deleteSchedulerTask($token, [$task_data->scheduler_task_id]);
                            if (!$result->status) {
                                DB::rollBack();
                                return $this->sendError($result->message, $result->code);
                            }
                            $scheduler_task_id = 0;
                        }
                        if ($request->scheduler_id > 0) {
                            $result = ToDoListUtils::addSchedulerTask($token, $data);
                            if (!$result->status) {
                                DB::rollBack();
                                return $this->sendError($result->message, $result->code);
                            }
                            $scheduler_task_id = $result->data['id'];
                        }
                    } else if ($request->scheduler_id > 0 && $request->scheduler_id == $task_data->scheduler_id) {
                        $result = ToDoListUtils::getSchedulerTaskInfo($token, $task_data->scheduler_task_id);

                        if ($result->status) {
                            $data['scheduler_task_id'] = $task_data->scheduler_task_id;
                            $data['participants'] = array_column($result->data['scheduleParticipationUsersList'], 'id');
                            $result = ToDoListUtils::updateSchedulerTask($token, $data);
                        } else if ($result->code == StatusCodeUtils::HTTP_NOT_FOUND) {
                            $result = ToDoListUtils::addSchedulerTask($token, $data);
                            if ($result->status) $scheduler_task_id = $result->data['id'];
                        }

                        if (!$result->status) {
                            DB::rollBack();
                            return $this->sendError($result->message, $result->code);
                        }
                    }
                    if ($scheduler_task_id > -1) $this->toDoTaskModel->where('id', $id)->update([
                        'scheduler_task_id' => $scheduler_task_id,
                        'scheduler_id' => $request->scheduler_id
                    ]);
                }
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.update', ['attribute' => 'タスク']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * サブタスクを削除する
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function deleteTask($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            // validator data
            $task_data = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('to_do_list AS tdl')
                        ->where(function ($query) use ($user) {
                            $query->orWhere(function ($query1) use ($user) {
                                $query1->where('tdl.type', ToDoListUtils::PERSONAL_LIST)
                                    ->where('tdl.mst_user_id', $user->id);
                            });
                            $query->orWhere('tdl.type', ToDoListUtils::PUBLIC_LIST);
                        })
                        ->where('tdl.id', DB::raw('to_do_task.to_do_list_id'));
                })
                ->where('id', $id)
                ->select('id', 'scheduler_id', 'parent_id', 'to_do_list_id', 'scheduler_task_id')
                ->first();
            
            if (!$task_data) {
                DB::rollBack();
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            if (!$this->authGroup(null, $task_data->to_do_list_id, $user)) {
                DB::rollBack();
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            // サブタスクの検出
            $sub_task_scheduler_ids = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('parent_id', $task_data->id)
                ->where('scheduler_task_id', '>', DB::raw(0))
                ->pluck('scheduler_task_id')
                ->toArray();
            
            $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where(function ($query) use ($id) {
                    // Subtasksで削除
                    $query->orWhere('id', $id)
                        ->orWhere('parent_id', $id);
                })
                ->delete();
            
            if ($task_data->scheduler_id > 0) $sub_task_scheduler_ids[] = $task_data->scheduler_task_id;
            $token = ToDoListUtils::getGroupwareToken($request);
            if (count($sub_task_scheduler_ids) > 0 && $this->gw_use && $token) {
                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {
                    $result = ToDoListUtils::deleteSchedulerTask($token, $sub_task_scheduler_ids);
                    if (!$result->status) {
                        DB::rollBack();
                        return $this->sendError($result->message, $result->code);
                    }
                }
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.delete', ['attribute' => 'タスク']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * タスクが完了しました(スケジューラリンケージ保持)
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function doneTask($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            // validator data
            $task_data = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('to_do_list AS tdl')
                        ->where(function ($query) use ($user) {
                            $query->orWhere(function ($query1) use ($user) {
                                $query1->where('tdl.type', ToDoListUtils::PERSONAL_LIST)
                                    ->where('tdl.mst_user_id', $user->id);
                            });
                            $query->orWhere('tdl.type', ToDoListUtils::PUBLIC_LIST);
                        })
                        ->where('tdl.id', DB::raw('to_do_task.to_do_list_id'));
                })
                ->where('id', $id)
                ->select('id', 'scheduler_id', 'parent_id', 'to_do_list_id')
                ->first();
    
            if (!$task_data) {
                DB::rollBack();
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            if (!$this->authGroup(null, $task_data->to_do_list_id, $user)) {
                DB::rollBack();
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            $this->toDoTaskModel
                ->where(function ($query) use ($id) {
                    $query->where('id', $id)
                        ->orWhere('parent_id', $id);
                })
                ->where('mst_company_id', $user->mst_company_id)
                ->update([
                    'state' => ToDoListUtils::DONE_STATUS,
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'update_user' => $user->family_name . ' ' . $user->given_name,
                ]);

            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.done'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * タスクを元に戻す(スケジューラリンケージ保持)
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function revokeTask($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            // validator data
            $task_data = $this->toDoTaskModel
                ->where('mst_company_id', $user->mst_company_id)
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('to_do_list AS tdl')
                        ->where(function ($query) use ($user) {
                            $query->orWhere(function ($query1) use ($user) {
                                $query1->where('tdl.type', ToDoListUtils::PERSONAL_LIST)
                                    ->where('tdl.mst_user_id', $user->id);
                            });
                            $query->orWhere('tdl.type', ToDoListUtils::PUBLIC_LIST);
                        })
                        ->where('tdl.id', DB::raw('to_do_task.to_do_list_id'));
                })
                ->where('id', $id)
                ->select('id', 'scheduler_id', 'parent_id', 'to_do_list_id', 'state')
                ->first();
            
            if (!$task_data) {
                DB::rollBack();
                return $this->sendError(__('message.false.to_do_list.task_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            if (!$this->authGroup(null, $task_data->to_do_list_id, $user)) {
                DB::rollBack();
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            if ($task_data->state == ToDoListUtils::DONE_STATUS) {
                $this->toDoTaskModel
                    ->where('id', $id)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->update([
                        'state' => ToDoListUtils::NOT_NOTIFY_STATUS,
                        'renotify_flg' => ToDoListUtils::NOT_RENOTIFY_FLG,
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'update_user' => $user->family_name . ' ' . $user->given_name,
                    ]);
            }

            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.revoke'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * タスクの更新
     * @param $circular_user_id
     * @param Request $request
     * @return mixed
     */
    public function updateCircularTask($circular_user_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'task_content' => 'string',
            'important' => 'numeric|in:0,1,2,3',
            'scheduler_id' => 'numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();

            $data = [
                'title' => $request->title,
                'content' => $request->task_content,
                'deadline' => $request->deadline === '' ? null : $request->deadline,
                'important' => $request->important,
            ];

            $task_data = $this->toDoCircularTaskModel
                ->where('mst_user_id', $user->id)
                ->where('circular_user_id', $circular_user_id)
                ->select('id', 'state',  'scheduler_id', 'scheduler_task_id', 'created_at')
                ->first();

            $query_action = $this->toDoCircularTaskModel
                ->where('mst_user_id', $user->id)
                ->where('circular_user_id', $circular_user_id);
            if (!$task_data) {
                $task_data = (object) ['id' => 0, 'scheduler_id' => 0];
                $data['circular_user_id'] = $circular_user_id;
                $data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                $data['mst_user_id'] = $user->id;
                $task_data->id = $query_action->insertGetId($data);
            } else {
                $data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if ($task_data->state == ToDoListUtils::DONE_STATUS) {
                    $data['state'] = ToDoListUtils::NOT_NOTIFY_STATUS;
                    $data['renotify_flg'] = ToDoListUtils::NOT_NOTIFY_STATUS;
                    $data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    $data['scheduler_id'] = 0;
                    $data['updated_at'] = null;
                    $task_data->scheduler_id = 0;
                }
                $query_action->update($data);
            }

            $token = ToDoListUtils::getGroupwareToken($request);
            if (isset($request->scheduler_id) && $this->gw_use && $token) {
                if (ToDoListUtils::checkCompanyGroupwareAuth($user)) {
                    $data['participant_ids'] = $request->participant_ids;
                    $data['scheduler_id'] = $request->scheduler_id;
                    $scheduler_task_id = -1;
                    if ($request->scheduler_id != $task_data->scheduler_id) {
                        // スケジューラリンケージの更新
                        if ($task_data->scheduler_id > 0) {
                            $result = ToDoListUtils::deleteSchedulerTask($token, [$task_data->scheduler_task_id]);
                            if (!$result->status) {
                                DB::rollBack();
                                return $this->sendError($result->message, $result->code);
                            }
                            $scheduler_task_id = 0;
                        }
                        if ($request->scheduler_id > 0) {
                            $result = ToDoListUtils::addSchedulerTask($token, $data);
                            if (!$result->status) {
                                DB::rollBack();
                                return $this->sendError($result->message, $result->code);
                            }
                            $scheduler_task_id = $result->data['id'];
                        }
                    } else if ($request->scheduler_id > 0 && $request->scheduler_id == $task_data->scheduler_id) {
                        $result = ToDoListUtils::getSchedulerTaskInfo($token, $task_data->scheduler_task_id);
                        if ($result->status) {
                            $data['scheduler_task_id'] = $task_data->scheduler_task_id;
                            $data['participants'] = array_column($result->data['scheduleParticipationUsersList'], 'id');
                            $result = ToDoListUtils::updateSchedulerTask($token, $data);
                        } else if ($result->code == StatusCodeUtils::HTTP_NOT_FOUND) {
                            $result = ToDoListUtils::addSchedulerTask($token, $data);
                            if ($result->status) $scheduler_task_id = $result->data['id'];
                        }
                        if (!$result->status) {
                            DB::rollBack();
                            return $this->sendError($result->message, $result->code);
                        }
                    }
                    if ($scheduler_task_id > -1) {
                        DB::table('to_do_circular_task')->where('id', $task_data->id)->update([
                            'scheduler_task_id' => $scheduler_task_id,
                            'scheduler_id' => $request->scheduler_id
                        ]);
                    }
                }
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.update', ['attribute' => 'タスク']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * スケジューラーリストを取得
     * @param Request $request
     * @return mixed
     */
    public function getSchedulerList(Request $request)
    {
        try {
            $token = ToDoListUtils::getGroupwareToken($request);
            if (!$token) {
                return $this->sendError(__('message.false.gw.failed'), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $type = $request->type;
            $result = ToDoListUtils::getSchedulerList($token);
            if (!$result->status) {
                return $this->sendError($result->message, $result->code);
            }
            if ($type == ToDoListUtils::PUBLIC_LIST) {
                $index = 0;
                $result->data = array_filter($result->data, function ($item) use($index) {
                    $index++;
                    return ($index > 1 || $item['name'] !== 'Ｍｙスケジューラ');
                });
                $result->data = array_values($result->data);
            } else {
                $filter_flg = false;
                $result->data = array_filter($result->data, function ($item) use(&$filter_flg) {
                    if (!$filter_flg && $item['name'] === 'Ｍｙスケジューラ') {
                        $filter_flg = true;
                        return $item;
                    }
                });
                $result->data = array_values($result->data);
            }
            return $this->sendResponse(['data' => $result->data], $result->message);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * グループを取得
     * @param Request $request
     * @return mixed
     */
    public function getGroupList(Request $request)
    {
        $user = $request->user();

        try {
            $querySub = $this->toDoGroupModel
                ->from('to_do_group AS tdg')
                ->leftJoin('to_do_group_auth AS tdga', 'tdg.id', '=', 'tdga.group_id')
                ->leftJoin('mst_department AS md', function ($query) {
                    $query->on('tdga.auth_department_id', '=', 'md.id')
                        ->on('tdga.auth_type', DB::raw(ToDoListUtils::DEPARTMENT_AUTH));
                })
                ->leftJoin('mst_user AS mu', function ($query) {
                    $query->on('tdga.auth_user_id', '=', 'mu.id')
                        ->on('tdga.auth_type', DB::raw(ToDoListUtils::USER_AUTH));
                })
                ->select(DB::raw(' tdg.id, tdg.title,
                    IF(tdga.auth_type=1,md.department_name,CONCAT(mu.family_name, mu.given_name)) AS name,
                    ROW_NUMBER() OVER (PARTITION BY tdg.id ORDER BY tdga.id ASC) as rank_num'))
                ->where('tdg.mst_company_id', $user->mst_company_id)
                ->where('tdg.mst_user_id', $user->id)
                ->orderBy('tdg.created_at', 'desc');

            $data = $this->toDoGroupModel->fromSub($querySub->getQuery(), 'group_auth')
                ->select(DB::raw('group_auth.id,
                    ANY_VALUE(group_auth.title) AS title,
                    ANY_VALUE(GROUP_CONCAT(group_auth.name)) AS name,
                    ANY_VALUE((SELECT COUNT(id) FROM to_do_group_auth tdga WHERE tdga.group_id=group_auth.id)) AS num'))
                ->where('rank_num', '<=', 3)
                ->groupBy('group_auth.id')
                ->get()
                ->toArray();
            
            return $this->sendResponse(['data' => $data], __('message.success.data_get', ['attribute' => 'グループ']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * グループを取得
     * @param Request $request
     * @return mixed
     */
    public function getGroupSimpleList(Request $request)
    {
        $user = $request->user();
        try {
            $data = $this->toDoGroupModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('mst_user_id', $user->id)
                ->select('id', 'title')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            return $this->sendResponse(['data' => $data], __('message.success.data_get', ['attribute' => 'グループ']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * グループの詳細を取得
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function getGroupDetail($id, Request $request)
    {
        try {
            $user = $request->user();
            
            $group_data = $this->toDoGroupModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('mst_user_id', $user->id)
                ->where('id', $id)
                ->select('id', 'title', 'created_at')
                ->first();
            if (!$group_data) {
                return $this->sendError(__('message.false.to_do_list.group_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            $group_auth = $this->toDoGroupAuthModel
                ->where('group_id', $id)
                ->select('auth_type', 'auth_department_id', 'auth_user_id')
                ->get()
                ->toArray();
            $group_data['group_auth'] = $group_auth;
            
            return $this->sendResponse(['data' => $group_data], __('message.success.detail', ['attribute' => 'グループの詳細']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 部署リスト
     * @param Request $request
     * @return mixed
     */
    public function getDepartmentList(Request $request)
    {
        $user = $request->user();
    
        try {
            $listDepartment = DB::table('mst_department')
                ->select('id','parent_id' , 'department_name as name')
                ->where('mst_company_id', $user->mst_company_id)
                ->get()
                ->keyBy('id');

            $listDepartmentTree = self::arrToTree($listDepartment);

            return $this->sendResponse(['data' => $listDepartmentTree], __('message.success.data_get', ['attribute' => '部署リスト']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ユーザーリスト
     * @param Request $request
     * @return mixed
     */
    public function getUserList(Request $request)
    {
        $user = $request->user();

        try {
            $user_list = DB::table('mst_user')
                ->select(DB::raw('id, concat(family_name, given_name) as username'))
                ->where('state_flg', AppUtils::STATE_VALID)
                ->where('mst_company_id', $user->mst_company_id)
                ->get()
                ->toArray();

            return $this->sendResponse(['data' => $user_list], __('message.success.data_get', ['attribute' => 'ユーザーリスト']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * グループを追加
     * @param Request $request
     * @return mixed
     */
    public function addGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'users' => 'array',
            'department' => 'array',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();

            $data = [
                'mst_user_id' => $user->id,
                'mst_company_id' => $user->mst_company_id,
                'title' => $request->title,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $group_id = $this->toDoGroupModel->insertGetId($data);
            if (!empty($request->users)) {
                $user_auth_data = [];
                array_map(function ($id) use (&$user_auth_data, $group_id, $user) {
                    $user_auth_data[] = [
                        'group_id' => $group_id,
                        'auth_type' => ToDoListUtils::USER_AUTH,
                        'auth_user_id' => $id,
                        'mst_user_id' => $user->id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];
                }, $request->users);
                $this->toDoGroupAuthModel->insert($user_auth_data);
            }
            if (!empty($request->department)) {
                $department_auth_data = [];
                array_map(function ($id) use (&$department_auth_data, $group_id, $user) {
                    $department_auth_data[] = [
                        'group_id' => $group_id,
                        'auth_type' => ToDoListUtils::DEPARTMENT_AUTH,
                        'auth_department_id' => $id,
                        'mst_user_id' => $user->id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];
                }, $request->department);
                $this->toDoGroupAuthModel->insert($department_auth_data);
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.add', ['attribute' => 'グループ']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * グループの更新
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function updateGroup($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'users' => 'array',
            'department' => 'array',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = $request->user();

            $data = [
                'title' => $request->title,
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];

            $group_data = $this->toDoGroupModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('mst_user_id', $user->id)
                ->where('id', $id)
                ->select('id')
                ->first();

            if (!$group_data) {
                return $this->sendError(__('message.false.to_do_list.group_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }

            $this->toDoGroupAuthModel->where('group_id', $group_data->id)->delete();
            $this->toDoGroupModel->where('id', $id)->update($data);

            if (!empty($request->users)) {
                $user_auth_data = [];
                array_map(function ($id) use (&$user_auth_data, $group_data, $user) {
                    $user_auth_data[] = [
                        'group_id' => $group_data->id,
                        'auth_type' => ToDoListUtils::USER_AUTH,
                        'auth_user_id' => $id,
                        'mst_user_id' => $user->id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];
                }, $request->users);
                $this->toDoGroupAuthModel->insert($user_auth_data);
            }
            if (!empty($request->department)) {
                $department_auth_data = [];
                array_map(function ($id) use (&$department_auth_data, $group_data, $user) {
                    $department_auth_data[] = [
                        'group_id' => $group_data->id,
                        'auth_type' => ToDoListUtils::DEPARTMENT_AUTH,
                        'auth_department_id' => $id,
                        'mst_user_id' => $user->id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];
                }, $request->department);
                $this->toDoGroupAuthModel->insert($department_auth_data);
            }
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.update', ['attribute' => 'グループ']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * グループを削除
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function deleteGroup($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            // validator data
            $group_data = $this->toDoGroupModel
                ->where('mst_company_id', $user->mst_company_id)
                ->where('mst_user_id', $user->id)
                ->where('id', $id)
                ->select('id')
                ->first();

            if (!$group_data) {
                DB::rollBack();
                return $this->sendError(__('message.false.to_do_list.group_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
            }
            // このグループの割り当て権限をクリアする
            $this->toDoListModel->where('group_id', $group_data->id)->update(['group_id' => DB::raw(0)]);
    
            $this->toDoGroupAuthModel->where('group_id', $group_data->id)->delete();
            $this->toDoGroupModel->where('id', $group_data->id)->delete();
            DB::commit();
            return $this->sendSuccess(__('message.success.to_do_list.delete', ['attribute' => 'グループ']));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * 通知設定の変更
     * @param Request $request
     * @return mixed
     */
    public function settingNoticeConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_flg' => 'numeric|in:0,1',
            'notice_flg' => 'numeric|in:0,1',
            'state' => 'required|numeric|in:0,1',
            'advance_time' => 'numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError(\implode('<br />',$validator->messages()->all()), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        try {
            $user = $request->user();

            $data = [
                'state' => $request->state,
            ];
            if ($request->state == 1) {
                $data['email_flg'] = $request->email_flg;
                $data['notice_flg'] = $request->notice_flg;
                $data['advance_time'] = $request->advance_time;
            }
            $data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $this->toDoNoticeConfigModel
                ->where('mst_user_id', $user->id)
                ->update($data);
            return $this->sendSuccess(__('message.success.to_do_list.update', ['attribute' => 'To-Do通知設定']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * 通知設定の取得
     * @param Request $request
     * @return mixed
     */
    public function getNoticeConfig(Request $request)
    {
        try {
            $user = $request->user();
            $data = $this->toDoNoticeConfigModel
                ->where('mst_user_id', $user->id)
                ->select('email_flg', 'notice_flg', 'state', 'advance_time')
                ->first();
            if (empty($data)) {
                $data = [
                    'mst_user_id' => $user->id,
                    'state' => 1,
                    'email_flg' => 0,
                    'notice_flg' => 1,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ];
                $this->toDoNoticeConfigModel->insert($data);
                $data = $this->toDoNoticeConfigModel
                    ->where('mst_user_id', $user->id)
                    ->select('email_flg', 'notice_flg', 'state', 'advance_time')
                    ->first();
            }
            return $this->sendResponse(['data' => $data], __('message.success.data_get', ['attribute' => 'To-Do通知設定']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * 通知をすべて取得
     * @param Request $request
     * @return mixed
     */
    public function getNoticeList(Request $request)
    {
        try {
            $user = $request->user();
            $res = [];
            $data = $this->toDoNoticeModel
                ->leftJoin('mst_user', 'to_do_notice.mst_user_id', '=', 'mst_user.id')
                ->where('to_do_notice.mst_user_id', $user->id)
                ->selectRaw('to_do_notice.id, to_do_notice.mst_user_id, to_do_notice.title, to_do_notice.from_type, to_do_notice.created_at AS createdAt, to_do_notice.updated_at AS updatedAt
                ,to_do_notice.is_read AS isRead, CONCAT(mst_user.family_name,mst_user.given_name) AS user_name')
                ->get()
                ->each(function ($item) use (&$res) {
                    $res[] = [
                        'id' => $item->id,
                        'createdAt' => $item->createdAt,
                        'updatedAt' => $item->updatedAt,
                        'isRead' => $item->isRead,
                        'notice' => [
                            'contents' => '',
                            'createdAt' => $item->createdAt,
                            'id' => $item->id,
                            'link' => '',
                            'subject' => $item->title,
                            'type' => 'to_do_list',
                            'updatedAt' => $item->updatedAt,
                            'mstUser' => [
                                'id' => $item->mst_user_id,
                                'name' => $item->user_name,
                                'userProfileData' => null,
                            ]
                        ],
                    ];
                });
            
            return $this->sendResponse($res, __('message.success.data_get', ['attribute' => 'To-Do通知リスト']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * 未読の通知をすべて取得
     * @param Request $request
     * @return mixed
     */
    public function getUnReadList(Request $request)
    {
        try {
            $user = $request->user();
            
            $data = $this->toDoNoticeModel
                ->where('mst_user_id', $user->id)
                ->where('is_read', DB::raw(0))
                ->select('id', 'title', 'from_type', 'created_at')
                ->get();
            
            return $this->sendResponse(['data' => $data], __('message.success.data_get', ['attribute' => '未読通知のリスト']));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * 未読通知の数を取得する
     * @param Request $request
     * @return mixed
     */
    public function countUnRead(Request $request)
    {
        $user = $request->user();
        $unread_num = $this->toDoNoticeModel
            ->where('mst_user_id', $user->id)
            ->where('is_read', DB::raw(0))
            ->count();
        return $unread_num;
    }
    
    /**
     * 読んだ通知
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function readNotice($id, Request $request)
    {
        try {
            $user = $request->user();
            
            DB::table('to_do_notice')
                ->where('id', $id)
                ->where('mst_user_id', $user->id)
                ->update(
                    [
                        'is_read' => ToDoListUtils::READ_NOTICCE,
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]
                );
            return $this->sendResponse([], '');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * すべての通知を読みました
     * @param Request $request
     * @return mixed
     */
    public function readNoticeAll(Request $request)
    {
        try {
            $user = $request->user();

            $this->toDoNoticeModel
                ->where('mst_user_id', $user->id)
                ->update(
                    [
                        'is_read' => ToDoListUtils::READ_NOTICCE,
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]
                );
            return $this->sendResponse([], '');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 更新ステータス
     * @param $user
     */
    private function doneCircularTask($user)
    {
        try {
            DB::table('to_do_circular_task AS tdct')
                ->leftJoin('circular_user AS cu', 'cu.id', '=', 'tdct.circular_user_id')
                ->leftJoin('circular AS c', 'c.id', '=', 'cu.circular_id')
                ->where('tdct.mst_user_id', '=', $user->id)
                ->whereIn('tdct.state', [ToDoListUtils::NOT_NOTIFY_STATUS, ToDoListUtils::NOTIFIED_STATUS])
                ->where(function ($query) {
                    $query->whereNotIn('c.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                        ->orWhereNotIn('cu.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS])
                        ->orWhere('cu.del_flg', DB::raw(1));
                })
                ->update(['tdct.state' => ToDoListUtils::DONE_STATUS]);
        } catch (\Exception $ex) {
            Log::info(__('message.false.to_do_list.done_circular') . '  mst_user_id: ' . $user->id);
        }
    }

    private static function arrToTree($items)
    {
        if (!count($items)) return $items;
        $childs = [];
        $rootItems = [];
        foreach ($items as $item) {
            if ($item->parent_id == null) $item->parent_id = 0;
            $childs[$item->parent_id][] = $item;

            if (!$item->parent_id) {
                $rootItems[] = $item;
            }
        }

        foreach ($items as $item) {
            if (isset($childs[$item->id]))
                $item->children = $childs[$item->id];
        }
        if (count($childs)) {
            $items = $rootItems;
        } else {
            $items = [];
        }
        return $items;
    }
    
    private function authGroup($type, $to_do_id, $user)
    {
        if ($type === null) $type = DB::table('to_do_list')->where('id', $to_do_id)->value('type');
        if ($type === ToDoListUtils::PERSONAL_LIST) return true;
        $user_info = DB::table('mst_user_info')->where('mst_user_id', $user->id)->select('mst_user_id', 'mst_department_id')->first();
        $have_permission = $this->toDoListModel
            ->where('to_do_list.mst_company_id', $user->mst_company_id)
            ->where('to_do_list.id', $to_do_id)
            ->where(function ($query_sub) use ($user_info) {
                $query_sub->where('group_id', DB::raw(0))
                    ->orWhere(function ($query_sub_1) use ($user_info) {
                        $query_sub_1->whereExists(function ($query) use ($user_info) {
                            $query->select(DB::raw('tdg.id'))
                                ->from('to_do_group AS tdg')
                                ->leftJoin('to_do_group_auth AS tdga', function ($query_sub_join) {
                                    $query_sub_join->on('tdg.id', '=', DB::raw('to_do_list.group_id'))
                                        ->on('tdg.id', '=', DB::raw('tdga.group_id'));
                                })
                                ->where('tdg.id', DB::raw('to_do_list.group_id'))
                                ->where(function ($query_sub_2) use ($user_info) {
                                    $query_sub_2->where(function ($query_sub_3) use ($user_info) {
                                        $query_sub_3->where('tdga.auth_type', DB::raw(ToDoListUtils::DEPARTMENT_AUTH))
                                            ->where('tdga.auth_department_id', DB::raw($user_info->mst_department_id ?? 0));
                                    })
                                        ->orWhere(function ($query_sub_3) use ($user_info) {
                                            $query_sub_3->where('tdga.auth_type', DB::raw(ToDoListUtils::USER_AUTH))
                                                ->where('tdga.auth_user_id', DB::raw($user_info->mst_user_id));
                                        });
                                });
                        });
                    });
            })
            ->count();
        if ($have_permission > 0) return true;
        return false;
    }
}