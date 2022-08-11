<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CsvUtils;
use App\Http\Utils\MailUtils;
use App\Jobs\PeCreateCsv;
use App\Jobs\PeUpdateData;
use App\Models\ApiUsers;
use App\Models\RequestInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CsvController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleCsv(Request $request){
        try {
            $login_id = $request->id;
            $login_password = $request->password;
            $mst_company_id = $request->mst_company_id;
            $command = $request->command;

            // コマンド正確チェック
            if ( $command != 'pe_create_csv' && $command != 'pe_update_data') {
                $data = ['command' => $command, 'result' => '0', 'message' => trans('message.false.csv_command')];
                Log::error('CsvController@handleCsv:' . '会社のID：' . $mst_company_id . ' ' . $command .' ' .trans('message.false.csv_command'));
                return response()->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            }

            $api_user = ApiUsers::where('login_id', $login_id)
                ->where('login_password',$login_password)
                ->where('mst_company_id',$mst_company_id)
                ->where('status',CsvUtils::STATE_VALID)
                ->first();

            // 認証失敗
            if (!$api_user) {
                $data = ['command' => $command, 'result' => '0', 'message' => trans('message.false.csv_api_authority')];
                Log::error('CsvController@handleCsv:' . '会社のID：' . $mst_company_id . ' ' .trans('message.false.csv_api_authority'));
                return response()->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            }

            // リクエスト情報登録
            $requestInfo = new RequestInfo();
            $requestInfo->request_datetime = Carbon::now();
            $requestInfo->mst_company_id = $mst_company_id;
            $requestInfo->command = $command;
            $requestInfo->save();
            // ジョブ執行
            if ($command == 'pe_create_csv') {
                $update_data_job = new PeCreateCsv($requestInfo->id, $login_id);
                $this->dispatch($update_data_job->onQueue('server1'));
            } elseif ($command == 'pe_update_data') {
                $update_data_job = new PeUpdateData($requestInfo->id, $login_id);
                $this->dispatch($update_data_job->onQueue('server1'));
            }
            $data = ['command' => $command, 'result' => '1', 'message' => ''];
            return response()->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        }catch (\Exception $e){
            Log::channel('errorlog')->error($e->getMessage());
            Log::channel('errorlog')->error($e->getTraceAsString());
            $data = ['command' => $command, 'result' => '0', 'message' => trans('message.false.csv_handle')];
            return response()->json($data)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }
    }

}
