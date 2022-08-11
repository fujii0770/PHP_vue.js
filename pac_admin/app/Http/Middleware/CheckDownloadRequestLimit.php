<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\DownloadUtils;

class CheckDownloadRequestLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            
            $user = \Auth::user();
            
            if(!$user){
                return response()->json([
                    'status' => false,
                    'message' => [__('未認証のアカウントです。ログインしてください。')]
                ]);
            }

            // ダウンロード制限確認
            $ret = $this->_enableDownloadRequest($user->mst_company_id);
            if($ret['status'] == false){
                return response()->json([
                    'status'    => false,
                    'message'   => $ret['message']
                ]);
            }

            return $next($request);
            
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 
                'message' => [__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()])]]);
        }
    }

    /**
     * ダウンロード制限確認
     *
     * @param $limit
     * @param $mst_company_id
     * @return array
     */
    private function _enableDownloadRequest($mst_company_id){
        
        $mst_company = DB::table('mst_company')
            ->where('id', $mst_company_id)
            ->first();

        // ダウンロード制限
        $limit = DB::table('mst_constraints')
            ->where('mst_company_id', $mst_company_id)
            ->select('dl_max_keep_days', 'dl_request_limit', 'dl_request_limit_per_one_hour', 'dl_file_total_size_limit', 'sanitize_request_limit')
            ->first();

        if (!$limit || !$mst_company) {
            return array(
                'status' => false,
                'message' => [__('message.false.download_request.limit_setting_get')]
            );
        }

        // 現在から一時間以内のダウンロード要求
        // timestamp のためタイムゾーン注意
        $now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;
        $dl_req = DB::table('download_request')
            ->where('mst_company_id', $mst_company_id)
            ->where('state', '!=', DownloadUtils::REQUEST_DELETED)
            ->where('request_date', '>', (new Carbon($now_db_timezone))->subHour())
            ->get();

        if ($limit->dl_request_limit !== 0 && $dl_req->count() >= $limit->dl_request_limit) {
            return array(
                'status' => false,
                'message' => [__('message.warning.download_request.download_file_max')]
            );
        }

        // 総容量チェック
        $dl_wait_data_sum = DB::table('download_wait_data as dwd')
            ->where('mst_company_id', $mst_company_id)
            ->join('download_request as dr', 'dwd.download_request_id', '=', 'dr.id')
            ->select(DB::raw('sum(dwd.file_size) as file_sizes'))->value('file_sizes');


        $dl_proc_wait_data_sum = DB::table('download_proc_wait_data as dpwd')
            ->where('mst_company_id', $mst_company_id)
            ->join('download_request as dr', 'dpwd.download_request_id', '=', 'dr.id')
            ->select(DB::raw('sum(dpwd.file_size)  as file_sizes'))->value('file_sizes');

        if ($dl_wait_data_sum + $dl_proc_wait_data_sum > $limit->dl_file_total_size_limit * 1024 * 1024) {
            return array(
                'status' => false,
                'message' => [__('message.warning.download_request.download_size_max')]
            );
        }

        return array('status' => true);
    }
}
