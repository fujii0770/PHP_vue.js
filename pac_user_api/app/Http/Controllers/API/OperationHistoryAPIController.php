<?php

namespace App\Http\Controllers\API;

use App\AuditUser;
use App\Http\Controllers\AppBaseController;
use App\Http\Middleware\CheckHashing;
use App\Http\Requests\API\GetLastLoginAtOperationHistoryAPIRequest;
use App\Http\Requests\API\StoreOperationHistoryEnvRequest;
use App\Models\OperationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\OperationsHistoryUtils;
use Illuminate\Support\Carbon;
use Response;

/**
 * Class OperationHistoryAPIController
 * @package App\Http\Controllers\API
 */

class OperationHistoryAPIController extends AppBaseController
{
    var $table = 'operation_history';
    var $model = null;

    public function __construct(OperationHistory $operationHistory)
    {
        $this->model = $operationHistory;
    }

    /**
     * Update the specified  ViewingUser in storage.
     * POST /store-log
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        if (!is_array($input) || empty(array_filter($input))){
            return response()->json(['status' => true]);
        }
        $isAuditUser = false;
        try{            
            $hashUserInfo = $request->attributes->get(CheckHashing::ATTRIBUTES_KEY_USER, null);
            $is_public = $hashUserInfo !== null;
            if ($is_public) {
                $log_destination = OperationsHistoryUtils::getPublicLogDestination($hashUserInfo);
                $not_logged = $log_destination == OperationsHistoryUtils::DESTINATION_NULL;
                if ($not_logged) {
                    return response()->json(['status' => true]);
                }

                $is_transfer = $log_destination == OperationsHistoryUtils::DESTINATION_OTHER_SERVER;
            } else {
                // 通常
                $userId = $request->user()->id;
                $isAuditUser = $request->user()->isAuditUser();
            }

            $ip_address =  $request->server->get('HTTP_X_FORWARDED_FOR') ? $request->server->get('HTTP_X_FORWARDED_FOR'):($request->get('ip_address')? $request->get('ip_address'):$request->getClientIp());
            $create_at =  $request->get('create_at')? $request->get('create_at'):date("Y-m-d H:i:s");

            $record = [
                'auth_flg' => $isAuditUser ? OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER : $request->get('auth_flg'),
                'mst_display_id' => $request->get('mst_display_id'),
                'mst_operation_id' => $request->get('mst_operation_id'),
                'result' => $request->get('result'),
                'detail_info' => $request->get('detail_info'),
                'ip_address' => $ip_address,
                'create_at' => $create_at,
            ];

            if ($is_public) {
                $is_transfer_allowed_id = in_array((int)$record['mst_operation_id'], OperationsHistoryUtils::STORE_LOG_TRANSFER_ALLOWED_OPERATION_IDS, true);
                if (!$is_transfer || $is_transfer_allowed_id) {
                    OperationsHistoryUtils::storeRecordsPublic([$record], $hashUserInfo, false);
                }
            } else {
                OperationsHistoryUtils::storeRecordsToCurrentEnv([$record], $userId, OperationsHistoryUtils::DESTINATION_DATABASE);
            }

            return response()->json(['status' => true]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            Log::error("Request: ",[$input]);
            return response()->json(['status' => false]);
        }
    }

    /**
     * 他環境からのログ格納
     * POST /store-env-log
     *
     * @param StoreOperationHistoryEnvRequest $request
     *
     * @return Response
     */
    public function storeEnv(StoreOperationHistoryEnvRequest $request) {
        $params = $request->validated();

        $records = array_map(function ($record) {
            return [
                'auth_flg' => $record['auth_flg'],
                'mst_display_id' => $record['mst_display_id'],
                'mst_operation_id' => $record['mst_operation_id'],
                'result' => $record['result'],
                'detail_info' => $record['detail_info'],
                'ip_address' => $record['ip_address'],
                'create_at' => Carbon::parse($record['create_at'])
                    ->setTimezone(date_default_timezone_get())
            ];
        }, $params['records']);

        OperationsHistoryUtils::storeRecordsToCurrentEnvEmail($records, $params['email'], OperationsHistoryUtils::DESTINATION_DATABASE);

        return response()->json(['status' => true]);
    }

    /**
     * Get last login success user
     * Get /loginat
     *
     * @param GetLastLoginAtOperationHistoryAPIRequest $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function lastLoginAt(Request $request)
    {
        $user = $request->user();
        try {
            $lastLoginAt = DB::table('operation_history')->where([
                'user_id' => $user->id,
                'auth_flg' => OperationsHistoryUtils::HISTORY_FLG_USER,
                'mst_display_id' => OperationsHistoryUtils::LOG_INFO['Auth']['login']['common'][0],
                'mst_operation_id' => OperationsHistoryUtils::LOG_INFO['Auth']['login']['common'][1],
                'result' => 0
            ])->orderBy('create_at', 'DESC')->first();
            if (!empty($lastLoginAt)) {
                $lastLoginAt = $lastLoginAt->create_at;
            }
            return $this->sendResponse(['create_at' => $lastLoginAt], 'Last login_at get successfully');
        } catch (Exception $ex){
            Log::error('OperationHistoryAPIController@lastLoginAt:' . $ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
