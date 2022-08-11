<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\BoxUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Jobs\AutoStorage;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use Session;

class AutoStorageApiController extends AppBaseController
{
    /**
     * Boxの自動保存処理
     * @param Request $request
     * @return mixed
     */
    public function autoStorageBox(Request $request)
    {
        try {
            $circular_id = $request->get('circular_id');

            // 回覧企業について、BOX自動保管設定権限取得
            $circular_users = DB::table('circular_user')->where('circular_id', $circular_id)->select('edition_flg', 'env_flg', 'server_flg', 'mst_company_id')->distinct()->get();
            foreach ($circular_users as $circular_user) {
                if ($circular_user->edition_flg != config('app.edition_flg')) {
                    continue;
                }

                // 本環境連携要のデータ確認
                if ($circular_user->edition_flg == config('app.edition_flg') && $circular_user->env_flg == config('app.server_env') && $circular_user->server_flg == config('app.server_flg')) {
                    if (BoxUtils::getAutoStorageBoxIsValid($circular_user->mst_company_id)) {
                        Log::info(__('message.info.auto_storage.local_request', ['company_id' => $circular_user->mst_company_id, 'circular_id' => $circular_id]));
                        $this->dispatch((new AutoStorage($circular_user->mst_company_id, $circular_id))->onQueue('default'));
                    }
                } else {
                    // 他環境連携要のデータ確認
                    // api呼出
                    $client = EnvApiUtils::getAuthorizeClient($circular_user->env_flg, $circular_user->server_flg);
                    if (!$client) {
                        Log::error(__('message.false.auth_client'));
                    }
                    $response = $client->post("box-auto-storage", [
                        RequestOptions::JSON => [
                            'company_id' => $circular_user->mst_company_id,
                            'edition_flg' => config('app.edition_flg'),
                            'env_flg' => config('app.server_env'),
                            'server_flg' => config('app.server_flg'),
                            'origin_circular_id' => $circular_id,
                        ]
                    ]);
                    if ($response->getStatusCode() != StatusCodeUtils::HTTP_OK) {
                        Log::error(__('message.false.box_auto_storage.bad_request', ['circular_id' => $circular_id]));
                        Log::error($response->getBody());
                    }
                }
            }

            return $this->sendResponse(['status' => true, 'data' => null], 'success');
        } catch (\Exception $ex) {
            Log::error('自動保存:autoStorageBox exception:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(['status' => false, 'data' => null], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 回覧状態を更新
     * @param Request $request
     * @return mixed
     */
    public function autoStorageUpdateStatus($origin_circular_id, Request $request)
    {
        try {
            $edition_flg = $request->get('edition_flg');
            $env_flg = $request->get('env_flg');
            $server_flg = $request->get('server_flg');
            $circular_status = $request->get('circular_status');
            $finishedDate = isset($request['finishedDate']) ? $request['finishedDate'] : ''; //完了日時
            DB::table("circular$finishedDate")->where('origin_circular_id', $origin_circular_id)
                ->where('edition_flg', $edition_flg)
                ->where('env_flg', $env_flg)
                ->where('server_flg', $server_flg)
                ->update([
                    'circular_status' => $circular_status
                ]);
            return $this->sendResponse(['status' => true, 'data' => null], 'success');
        } catch (\Exception $ex) {
            Log::error('自動保存: autoStorageUpdateStatus:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(['status' => false, 'data' => null], StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeBoxAutoStorageRequest(Request $request)
    {
        try {
            $company_id = $request->get('company_id');
            $edition_flg = $request->get('edition_flg');
            $env_flg = $request->get('env_flg');
            $server_flg = $request->get('server_flg');
            $origin_circular_id = $request->get('origin_circular_id');

            if (BoxUtils::getAutoStorageBoxIsValid($company_id)) {
                $circular = DB::table('circular')->where('origin_circular_id', $origin_circular_id)
                    ->where('edition_flg', $edition_flg)
                    ->where('env_flg', $env_flg)
                    ->where('server_flg', $server_flg)
                    ->first();
                if (!$circular) {
                    Log::error(__('message.false.auto_storage.circular_not_exist'));
                    return $this->sendError(__('message.false.auto_storage.circular_not_exist'), StatusCodeUtils::HTTP_NOT_FOUND);
                }
                Log::info(__('message.info.auto_storage.local_request', ['company_id' => $company_id, 'circular_id' => $circular->id]));
                $this->dispatch((new AutoStorage($company_id, $circular->id))->onQueue('default'));
            }
            return $this->sendResponse(['status' => true, 'data' => null], 'success');
        } catch (\Exception $ex) {
            Log::error('自動保存システムエラー発生しました。:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('自動保存システムエラー発生しました。', StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reBoxAutoStorageRequest(Request $request)
    {
        try {
            $company_id = $request->get('company_id');
            $circular_auto_storage_historys = $request->get('auto_storage_history');
            if (BoxUtils::getAutoStorageBoxIsValid($company_id)) {
                foreach ($circular_auto_storage_historys as $circular_auto_storage_history){
                    DB::table('circular_auto_storage_history')
                        ->where('id', $circular_auto_storage_history['id'])
                        ->update([
                            'result' => 0,
                        ]);
                    $this->dispatch((new AutoStorage($company_id, $circular_auto_storage_history['circular_id']))->onQueue('default'));
                }
            }
            return $this->sendResponse(['status' => true, 'data' => null], 'success');
        } catch (\Exception $ex) {
            Log::error('自動保存システムエラー発生しました。:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('自動保存システムエラー発生しました。', StatusCodeUtils::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
