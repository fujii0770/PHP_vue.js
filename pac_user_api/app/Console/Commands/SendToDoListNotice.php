<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\MailUtils;
use App\Http\Utils\ToDoListUtils;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendToDoListNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:toDoListTask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send ToDo list notification.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @throws \Exception
     */
    public function handle()
    {
        try {
            Log::channel('cron-daily')->debug('Start sending ToDo list notifications');
            $now_date_time = Carbon::now()->format('YmdHi');

            $company_list = DB::table('mst_company AS mc')
                ->leftJoin('mst_application_companies AS mac', 'mc.id', '=', 'mac.mst_company_id')
                ->where('mc.state', AppUtils::STATE_VALID)
                ->where('mac.mst_application_id', AppUtils::GW_APPLICATION_ID_TO_DO_LIST)
                ->where(function($query){
                    $query->where('mac.is_infinite', AppUtils::STATE_VALID)
                        ->orWhere('mac.purchase_count', '>', DB::raw(0));
                })
                ->select('mc.id')
                ->get();

            foreach ($company_list as $company) {
                $joinSub = DB::table('mst_application_users')
                    ->where('mst_application_id', AppUtils::GW_APPLICATION_ID_TO_DO_LIST);
                $user_list = DB::table('mst_user')
                    ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                    ->leftJoinSub($joinSub, 'au', 'mst_user.id', '=', 'au.mst_user_id')
                    ->leftJoin('to_do_notice_config AS tdnc', 'tdnc.mst_user_id', '=', 'mst_user.id')
                    ->where('mst_user.mst_company_id', $company->id)
                    ->where('mst_user.state_flg', AppUtils::STATE_VALID)
                    ->whereIn('mst_user.option_flg',[AppUtils::USER_OPTION,AppUtils::USER_NORMAL])
                    ->whereNotNull('au.mst_user_id')
                    ->where(function ($query) {
                        $query->whereNull('tdnc.state')
                            ->orWhere('tdnc.state', DB::raw(1));
                    })
                    ->select('mst_user.id', 'mst_user.email', 'mst_user.notification_email', 'tdnc.id AS notice_config_id', 'tdnc.state', 'tdnc.email_flg', 'tdnc.notice_flg', 'tdnc.advance_time')
                    ->get()
                    ->toArray();

                if (empty($user_list)) continue;

                $user_ids = [];
                $user_array = [];

                array_map(function ($item) use (&$user_ids, &$user_array) {
                    $user_ids[] = $item->id;
                    $user_array[$item->id] = clone $item;
                    if (empty($user_array[$item->id]->notice_config_id)) {
                        $user_array[$item->id]->email_flg = 0;
                        $user_array[$item->id]->notice_flg = 1;
                        $user_array[$item->id]->advance_time = ToDoListUtils::ADVANCE_TIME[6]['value'];
                    }
                }, $user_list);

                $user_ids = array_unique($user_ids);

                // personal task
                $personal_task_list = DB::table('to_do_task AS tdt')
                    ->leftJoin('to_do_list AS tdl', 'tdl.id', '=', 'tdt.to_do_list_id')
                    ->where('tdl.type', ToDoListUtils::PERSONAL_LIST)
                    ->where(function ($query) {
                        $query->where('tdt.state', ToDoListUtils::NOT_NOTIFY_STATUS)
                            ->orWhere(function ($query_sub) {
                                $query_sub->where('tdt.state', ToDoListUtils::NOTIFIED_STATUS)
                                    ->where('tdt.renotify_flg', ToDoListUtils::NOT_RENOTIFY_FLG);
                            });
                    })
                    ->whereRaw("DATE_FORMAT( tdt.deadline, '%Y%m%d%H%i' ) >= ".$now_date_time)
                    ->whereIn('tdt.mst_user_id', $user_ids)
                    ->whereNotNull('tdt.deadline')
                    ->select('tdt.id', 'tdt.mst_user_id', 'tdt.title', 'tdt.content', 'tdt.deadline', 'tdt.important', 'tdt.state', 'tdt.renotify_flg')
                    ->get();

                foreach ($personal_task_list as $personal_task) {
                    $this->notify('to_do_task', 1, $personal_task->mst_user_id, $user_array, $personal_task);
                }
    
                // public task
                $public_sub_query = DB::table('to_do_task AS tdt')
                    ->leftJoin('to_do_list AS tdl', 'tdl.id', '=', 'tdt.to_do_list_id')
                    ->where('tdl.type', ToDoListUtils::PUBLIC_LIST)
                    ->where(function ($query) {
                        $query->where('tdt.state', ToDoListUtils::NOT_NOTIFY_STATUS)
                            ->orWhere(function ($query_sub) {
                                $query_sub->where('tdt.state', ToDoListUtils::NOTIFIED_STATUS)
                                    ->where('tdt.renotify_flg', ToDoListUtils::NOT_RENOTIFY_FLG);
                            });
                    })
                    ->whereRaw("DATE_FORMAT( tdt.deadline, '%Y%m%d%H%i' ) >= ".$now_date_time)
                    ->whereIn('tdt.mst_user_id', $user_ids)
                    ->whereNotNull('tdt.deadline')
                    ->select('tdt.id', 'tdt.mst_user_id', 'tdt.title', 'tdt.content', 'tdt.deadline', 'tdt.important', 'tdt.state', 'tdt.renotify_flg', 'tdl.group_id');

                $public_task_list = DB::query()->fromSub($public_sub_query, 'tasks')
                    ->crossJoin('mst_user AS mu')
                    ->join('mst_user_info AS mui', 'mu.id', '=', 'mui.mst_user_id')
                    ->whereIn('mu.id', $user_ids)
                    ->where(function ($query_sub) {
                        $query_sub->where('group_id', DB::raw(0))
                            ->orWhere(function ($query_sub_1) {
                                $query_sub_1->whereExists(function ($query) {
                                    $query->select(DB::raw('tdg.id'))
                                        ->from('to_do_group AS tdg')
                                        ->leftJoin('to_do_group_auth AS tdga', function ($query_sub_join) {
                                            $query_sub_join->on('tdg.id', '=', DB::raw('tasks.group_id'))
                                                ->on('tdg.id', '=', DB::raw('tdga.group_id'));
                                        })
                                        ->where('tdg.id', DB::raw('tasks.group_id'))
                                        ->where(function ($query_sub_2) {
                                            $query_sub_2->where(function ($query_sub_3) {
                                                $query_sub_3->where('tdga.auth_type', DB::raw(ToDoListUtils::DEPARTMENT_AUTH))
                                                    ->where('tdga.auth_department_id', DB::raw('mui.mst_department_id'));
                                            })
                                                ->orWhere(function ($query_sub_3) {
                                                    $query_sub_3->where('tdga.auth_type', DB::raw(ToDoListUtils::USER_AUTH))
                                                        ->where('tdga.auth_user_id', DB::raw('mu.id'));
                                                });
                                        });
                                });
                            });
                    })
                    ->selectRaw(DB::raw('mu.id AS user_id, tasks.*'))
                    ->get();

                foreach ($public_task_list as $public_task) {
                    $this->notify('to_do_task', 2, $public_task->user_id, $user_array, $public_task);
                }

                // circular task
                $circular_task_list = DB::table('to_do_circular_task AS tdt')
                    ->leftJoin('circular_user AS cu', 'cu.id', '=', 'tdt.circular_user_id')
                    ->leftJoin('circular AS c', 'c.id', '=', 'cu.circular_id')
                    ->where(function ($query) {
                        $query->where('tdt.state', ToDoListUtils::NOT_NOTIFY_STATUS)
                            ->orWhere(function ($query_sub) {
                                $query_sub->where('tdt.state', ToDoListUtils::NOTIFIED_STATUS)
                                    ->where('tdt.renotify_flg', ToDoListUtils::NOT_RENOTIFY_FLG);
                            });
                    })
                    ->whereIn('c.circular_status', [CircularUtils::CIRCULATING_STATUS, CircularUtils::SEND_BACK_STATUS])
                    ->whereIn('cu.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::REVIEWING_STATUS])
                    ->where('cu.del_flg', DB::raw(0))
                    ->whereRaw("DATE_FORMAT( tdt.deadline, '%Y%m%d%H%i' ) >= ".$now_date_time)
                    ->whereIn('tdt.mst_user_id', $user_ids)
                    ->whereNotNull('tdt.deadline')
                    ->select('tdt.id', 'tdt.mst_user_id', 'tdt.title', 'tdt.content', 'tdt.deadline', 'tdt.important', 'tdt.state', 'tdt.renotify_flg')
                    ->get();

                foreach ($circular_task_list as $circular_task) {
                    $this->notify('to_do_circular_task', 3, $circular_task->mst_user_id, $user_array, $circular_task);
                }
                $user_list = [];
            }
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('Running to send todo list notification fails');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
        Log::channel('cron-daily')->debug('Run Send To-Do List Notification Done');
    }

    /**
     * @param $table
     * @param $type
     * @param $user_id
     * @param $user_array
     * @param $task
     * @return false
     * @throws \Exception
     */
    private function notify($table, $type, $user_id, $user_array, $task)
    {
        DB::beginTransaction();
        try {
            $important_arr = [1 => '低', 2 => '中', 3 => '高'];
            $important = $task->important > 0 ? $important_arr[$task->important] : '';
            $email = !empty($user_array[$user_id]->notification_email) ? $user_array[$user_id]->notification_email : $user_array[$user_id]->email;

            $task_type = '個人';
            if ($type === 2) $task_type = '共有';
            if ($type === 3) $task_type = '文書';
            $deadline_time = Carbon::parse($task->deadline)->getTimestamp();
            $deadline_day = Carbon::parse($task->deadline)->format('Ymd');
            $now_time = Carbon::now()->getTimestamp();
            $now_date = Carbon::now()->format('Ymd');
            $notice_flg = false;
            $renotify_flg = false;
            if ($task->state == ToDoListUtils::NOT_NOTIFY_STATUS && ($deadline_time - $user_array[$user_id]->advance_time) <= $now_time) {
                $notice_flg = true;
            }
            if ($deadline_day == $now_date && $task->renotify_flg == ToDoListUtils::NOT_NOTIFY_STATUS) {
                $renotify_flg = true;
            }
            if (!$notice_flg && !$renotify_flg) {
                return false;
            }
            if ($notice_flg || $renotify_flg) {
                $has_notice = false;
                if ($user_array[$user_id]->email_flg == 1) {
                    $data = [
                        'task_type' => $task_type,
                        'title' => $task->title,
                        'content' => $task->content,
                        'deadline' => $task->deadline,
                        'important' => $important,
                    ];
                    $param = json_encode($data, JSON_UNESCAPED_UNICODE);
                    MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                        $email,
                        // メールテンプレート
                        MailUtils::MAIL_DICTIONARY['TO_DO_LIST_DEADLINE_NOTICE']['CODE'],
                        // パラメータ
                        $param,
                        // タイプ
                        AppUtils::MAIL_TYPE_USER,
                        // 件名
                        config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.to_do_list_deadline_notice.subject', ['title' => $task->title]),
                        // メールボディ
                        trans('mail.to_do_list_deadline_notice.body', $data)
                    );
                    $has_notice = true;
                }
                if ($user_array[$user_id]->notice_flg == 1) {
                    $insert_data = [
                        'from_type' => $type,
                        'from_id' => $task->id,
                        'mst_user_id' => $user_id,
                        'title' =>  $task_type . 'タスク「' . $task->title . '」は締め切り日に達しました。',
                        'created_at' => Carbon::now(),
                    ];
                    DB::table('to_do_notice')->insert($insert_data);
                    $has_notice = true;
                }

                if ($has_notice) {
                    $update_data = [];
                    if ($notice_flg) $update_data['state'] = 1;
                    if ($renotify_flg) $update_data['renotify_flg'] = 1;
                    DB::table(DB::raw($table))->where('id', DB::raw($task->id))->update($update_data);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('cron-daily')->error('タスク「' . $task->title . '」notify failed。TaskId: ' . $task->id . ' Type: ' . $task_type . 'Email: ' . $email . ' UserId' . $user_id);
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
    }
}
