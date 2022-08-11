<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeStampInfoAPIController extends AppBaseController
{
    public function store(Request $request)
    {
        try {
            if (!$request['timestamps']){
                return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $timestamps = [];
            foreach ($request['timestamps'] as $key => $value){
                $timestamp = $value;
                $timestamp['create_at'] = Carbon::now();
                if ($timestamp['app_env'] === '' || $timestamp['app_env'] === null){
                    $timestamp['app_env'] = config('app.server_env');
                }
                if ($timestamp['contract_server'] === '' || $timestamp['contract_server'] === null){
                    $timestamp['contract_server'] = config('app.server_flg');
                }
                $timestamps[] = $timestamp;
            }
            DB::table('time_stamp_info')->insert($timestamps);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function countByMonthAndEnv(Request $request)
    {
        try {
            if (!isset($request['appEnv']) || !isset($request['contractServer']) || !isset($request['targetMonth'])){
                return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $totalTimestamps = DB::table('time_stamp_info')
                ->select(['mst_company_id', DB::raw('COUNT(id) as count_timestamp')])
                ->where(DB::raw("DATE_FORMAT(create_at, '%Y%m')"), '=', $request['targetMonth'])
                ->where('app_env', '=', $request['appEnv'])
                ->where('contract_server', '=', $request['contractServer'])
                ->groupBy('mst_company_id')
                ->get();

            return $this->sendResponse($totalTimestamps, '');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function countByDayAndEnv(Request $request)
    {
        try {
            if (!isset($request['appEnv']) || !isset($request['contractServer']) || !isset($request['targetDay'])){
                return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $totalTimestamps = DB::table('time_stamp_info')
                ->select(['mst_company_id', DB::raw('COUNT(id) as count_timestamp')])
                ->where(DB::raw("DATE_FORMAT(create_at, '%Y-%m-%d')"), '=', $request['targetDay'])
                ->where('app_env', '=', $request['appEnv'])
                ->where('contract_server', '=', $request['contractServer'])
                ->groupBy('mst_company_id')
                ->get();

            return $this->sendResponse($totalTimestamps, '');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError('回覧登録処理に失敗しました。', \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
