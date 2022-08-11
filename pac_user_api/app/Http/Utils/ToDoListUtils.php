<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 11/13/19
 * Time: 15:45
 */

namespace App\Http\Utils;


use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ToDoListUtils
{
    // to do list type
    const PERSONAL_LIST = 1;
    const PUBLIC_LIST = 2;
    
    // task status
    const DELETED_STATUS = -1; // 削除
    const NOT_NOTIFY_STATUS = 0; // 未通知
    const NOTIFIED_STATUS = 1; // 通知済
    const DONE_STATUS = 2; // 完了

    const NOT_RENOTIFY_FLG = 0;
    const RENOTIFY_FLG = 1;
    
    // task notice
    const UNREAD_NOTICE = 0;
    const READ_NOTICCE = 1;
    
    // auth type
    const DEPARTMENT_AUTH = 1;
    const USER_AUTH = 2;
    
    const ADVANCE_TIME = [
        ['name' => '7日前', 'value' => 604800],
        ['name' => '6日前', 'value' => 518400],
        ['name' => '5日前', 'value' => 432000],
        ['name' => '4日前', 'value' => 345600],
        ['name' => '3日前', 'value' => 259200],
        ['name' => '2日前', 'value' => 172800],
        ['name' => '1日前', 'value' => 86400],
        ['name' => '12時間前', 'value' => 43200],
        ['name' => '6時間前', 'value' => 21600],
        ['name' => '3時間前', 'value' => 10800],
        ['name' => '1時間前', 'value' => 3600],
    ];

    private static function getAuthorizeClient($token): Client
    {
        $client = new Client(['base_uri' => 'https://' . config('app.gw_domain') . '/','timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest', 'Authorization' => 'Bearer ' . $token]
        ]);
        return $client;
    }

    public static function getSchedulerList($token)
    {
        $client = self::getAuthorizeClient($token);
        $response = $client->get('api/v1/mst-user/getCommonCalendarUser');
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK){
            return (object)['status' => true, 'data' => $response_decode, 'message' => __('message.success.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_OK];
        } else {
            Log::warning(__('message.false.to_do_list.get_scheduler_data'));
            Log::warning($response_decode);
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public static function getSchedulerTaskInfo($token, $scheduler_task_id)
    {
        $client = self::getAuthorizeClient($token);
        $response = $client->get('api/v1/schedule/' . $scheduler_task_id);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK && $response_decode){
            return (object)['status' => true, 'data' => $response_decode, 'message' => __('message.success.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_OK];
        } else if ($response->getStatusCode() == StatusCodeUtils::HTTP_NOT_FOUND && $response_decode['message'] && $response_decode['timestamp']) {
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_NOT_FOUND];
        } else {
            Log::warning(__('message.false.to_do_list.get_scheduler_data'));
            Log::warning($response_decode);
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR];
        }
    }
    
    public static function getSchedulerTitle($token, $scheduler_id)
    {
        $client = self::getAuthorizeClient($token);
        $response = $client->get('api/v1/mst-user/getSingleCommonCalendarUser/' . $scheduler_id);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK && $response_decode){
            return (object)['status' => true, 'data' => $response_decode, 'message' => __('message.success.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_OK];
        } else {
            Log::warning(__('message.false.to_do_list.get_scheduler_data'));
            Log::warning($response_decode);
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.get_scheduler_data'), 'code' => StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    /**
     * スケジューラー連携
     * @param $id
     * @param $data
     * @return mixed
     */
    public static function addSchedulerTask($token, $data)
    {
        $client = self::getAuthorizeClient($token);
        $deadline = self::timeConversion($data['deadline']);
        $data['title'] = mb_substr($data['title'], 0, 50);
        $response = $client->post('api/v1/schedule',
            [
                RequestOptions::JSON => [
                    "title" => $data['title'],
                    "content" => $data['content'],
                    "startDate" => $deadline->startDate,
                    "endDate" => $deadline->endDate,
                    "facilities" => [],
                    "isAllDay" => false,
                    "isEdit" => false,
                    "isEmail" => true,
                    "isNotice" => true,
                    "isPublic" => true,
                    "isPush" => true,
                    "isRepeat" => false,
                    "mstAlarmId" => 3,
                    "mstRepeatTypeCode" => 1,
                    "participants" => [$data['scheduler_id']],
                    "scheduleUserDisplay" => $data['participant_ids'],
                    "scheduleParticipationGroupsList" => [],
                    "scheduleParticipationDepartmentsList" => [],
                    "repeatCustom" => null,
                    "mstScheduleTypeId" => null
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK && $response_decode){
            return (object)['status' => true, 'data' => $response_decode, 'message' => __('message.success.to_do_list.add_scheduler'), 'code' => StatusCodeUtils::HTTP_OK];
        } else {
            Log::warning(__('message.false.to_do_list.add_scheduler'));
            Log::warning($response_decode);
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.add_scheduler'), 'code' => StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    /**
     * スケジューラーリンケージをキャンセルする
     * @param $id_arr
     * @param $mst_user_id
     * @param $mst_company_id
     * @return mixed
     */
    public static function updateSchedulerTask($token, $data)
    {
        $client = self::getAuthorizeClient($token);
        $deadline = self::timeConversion($data['deadline']);
        $data['title'] = mb_substr($data['title'], 0, 50);
        $response = $client->put('api/v1/schedule/' . $data['scheduler_task_id'],
            [
                RequestOptions::JSON => [
                    "title" => $data['title'],
                    "content" => $data['content'],
                    "startDate" => $deadline->startDate,
                    "endDate" => $deadline->endDate,
                    "type" => 'shachihata',
                    "exceptionDaye" => null,
                    "facilities" => [],
                    "isAllDay" => false,
                    "isEdit" => false,
                    "isEmail" => true,
                    "isException" => false,
                    "isNotice" => true,
                    "isPublic" => true,
                    "isPush" => true,
                    "isRepeat" => false,
                    "mstAlarmId" => 3,
                    "mstRepeatTypeCode" => 1,
                    "mstScheduleTypeId" => null,
                    "otherParticipantList" => [],
                    "participants" => $data['participants'],
                    "recurrenceId" => null,
                    "scheduleParticipationDepartmentsList" => [],
                    "scheduleParticipationGroupsList" => [],
                    "scheduleUserDisplay" => $data['participants'],
                    "exceptionDate" => null,
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK){
            return (object)['status' => true, 'data' => [], 'message' => __('message.success.to_do_list.update_scheduler'), 'code' => StatusCodeUtils::HTTP_OK];
        } else {
            Log::warning(__('message.false.to_do_list.update_scheduler'));
            Log::warning($response_decode);
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.update_scheduler'), 'code' => StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    /**
     * スケジューラーリンケージをキャンセルする
     * @param $id_arr
     * @param $mst_user_id
     * @param $mst_company_id
     * @return mixed
     */
    public static function deleteSchedulerTask($token, array $scheduler_task_ids)
    {
        $client = self::getAuthorizeClient($token);
        $response = $client->delete('api/v1/schedule/batchCommonCalendarDelete',
            [
                RequestOptions::JSON => [
                    "eventIds" => $scheduler_task_ids,
                ]
            ]);
        $response_decode = json_decode($response->getBody(), true);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK){
            return (object)['status' => true, 'data' => [], 'message' => __('message.success.to_do_list.delete_scheduler'), 'code' => StatusCodeUtils::HTTP_OK];
        } else {
            Log::warning(__('message.false.to_do_list.delete_scheduler'));
            Log::warning($response_decode);
            return (object)['status' => false, 'data' => [], 'message' => __('message.false.to_do_list.delete_scheduler'), 'code' => StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    private static function timeConversion($deadline)
    {
        $deadline = Carbon::parse($deadline)->format('Y-m-d H:i:s');
        $startDate = Carbon::parse($deadline)->format('Y-m-d') . ' 00:00:00';
        $endDate = $deadline;
        if (Carbon::parse($deadline)->format('H') == '00') {
            $data['endDate'] = Carbon::parse($deadline)->format('Y-m-d') . ' 01:' . Carbon::parse($deadline)->format('i:s');
        }
        $i = Carbon::parse($endDate)->format('i');
        if ((int)$i % 5 != 0) {
            $num = (int)$i % 5;
            $num = 5 - $num;
            $endDate = Carbon::parse($endDate)->addMinutes($num)->format('Y-m-d H:i:s');
        }
        if (Carbon::parse($endDate)->format('H') == '00') {
            $endDate = Carbon::parse($endDate)->format('Y-m-d') . ' 01:' . Carbon::parse($endDate)->format('i:s');
        }
        return (object)['startDate' => $startDate, 'endDate' => $endDate];
    }

    /**
     * @param $request
     * @return null
     */
    public static function getGroupwareToken($request)
    {
        if (!empty($request->headers->get('gwauthorization')) && $request->headers->get('gwauthorization') !== 'null') {
            return $request->headers->get('gwauthorization');
        }
        return null;
    }
    
    /**
     * @param $user
     * @return bool
     */
    public static function checkCompanyGroupwareAuth($user): bool
    {
        $gw_flg = DB::table('mst_company')
            ->where('id', $user->mst_company_id)
            ->value('gw_flg');
        return $gw_flg && $gw_flg == 1;
    }
}