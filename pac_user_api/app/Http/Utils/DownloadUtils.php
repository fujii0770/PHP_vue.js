<?php

namespace App\Http\Utils;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

use App\Jobs\DownloadJob;
use App\Jobs\ReDownloadJob;

use App\Models\User;
use App\Models\Company;
use App\Models\DownloadRequest;
use App\Models\DownloadWaitData;
use App\Models\DownloadProcWaitData;

/**
 * ダウンロード要求処理
 * Class DownloadRequestUtils
 * @package App\Http\Utils
 */
class DownloadUtils
{
    // 削除
    const DELETE_STATE = 9;
    // 期限切れ
    const EXPIRED_STATE = 10;

    /* download_request状態 */
    const REQUEST_PROCESS_WAIT = 0;                     //  0：処理待ち
    const REQUEST_CREATING = 1;                         //  1：作成中
    const REQUEST_DOWNLOAD_WAIT = 2;                    //  2：ダウンロード待ち
    const REQUEST_REQUEST_DOWNLOAD_COMPLETE = 3;        //  3：ダウンロード完了
    const REQUEST_DOWNLOAD_END = 4;                     //  4：ダウンロード済み
    const REQUEST_DELETED = 9;                          //  9：削除
    const REQUEST_EXPIRED = 10;                         // 10：期限切れ
    const REQUEST_SANITIZING_WAIT = 11;                 // 11：無害化待ち
    const REQUEST_SANITIZING_PROC = 12;                 // 12：無害化中
    const REQUEST_SANITIZING_GETTING = 13;              // 13：データ取得中
    const REQUEST_FAILED = -1;                          // -1：失敗
    
    /* ダウンロード前処理待ちデータ保管テーブル状態 */
    const PROC_PROCESS_WAIT = 0;                        //０：処理待ち
    const PROC_PROCESS_END = 1;                         //１：処理済み

    //PAC_5-2874 S
    /*
     * ①無害化無効、又は、LGWAN private環境で、ダウンロード予約すれば、無害化状態に0：無害化不要を登録します。
     * ②2：無害化待ちのレコード、無害化したら、0：無害化不要を更新します。
     */
    const SANITIZING_UNNEEDED = 0;                      //0：無害化不要
    /*
     * 無害化有効、且つ、LGWAN public環境で、ダウンロード予約すれば、無害化状態に1：無害化要を登録します。
     */
    const SANITIZING_NEED = 1;                          //1：無害化要
    /*
     * LGWAN publicダウンロード予約、LGWAN private環境を表示すれば、無害化必要です。
     * LGWAN private環境をダウンロードすれば、2：無害化待ちを更新します。
     */
    const SANITIZING_WAIT = 2;                          //2：無害化待ち
    //PAC_5-2874 E

    //実際のファイルサイズの倍数
    const MULTIPLE_SIZE = 1.77778;

	/**
     * ダウンロード要求
     * @param User $user 利用者情報
     * @param string $class_path クラスのフルパス
     * @param string $function_name 関数名
     * @param string $file_name ファイル名
     * 
     * @throws \Exception
     */
    public static function downloadRequest($user, $class_path, $function_name, $file_name, ...$param){
        try{
            // ダウンロード処理可能か確認
            self::checkEnableDownload($user, $class_path, $function_name);

            // 無害化するかを確認
            $is_sanitizing = Company::where('id', $user->mst_company_id)->first()->sanitizing_flg;

            // Job登録
            $job = (new DownloadJob(
                $user, $class_path, $function_name, $file_name, $is_sanitizing, $param
                ));
            dispatch($job);
            return true;
		}catch(\Exception $e){
            return $e->getMessage();
		}
    }

	/**
     * ダウンロード要求
     * @param User $user 利用者情報
     * @param string $class_path クラスのフルパス
     * @param string $function_name 関数名
     * @param string $file_name ファイル名
     * @param bool $is_sanitizing 無害化の有無
     * 
     * @throws \Exception
     */
    public static function downloadRequestSelectableSanitizing($user, $class_path, $function_name, $file_name, $is_sanitizing, ...$param){
		try{
            // ダウンロード処理可能か確認
            self::checkEnableDownload($user, $class_path, $function_name);

			// Job登録
			$job = (new DownloadJob(
				$user, $class_path, $function_name, $file_name, $is_sanitizing, $param
				));
			dispatch($job);
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
    }

    /**
     * ダウンロード再要求
     * @param User $user 利用者情報
     * @param int $request_id ダウンロード要求ID
     * 
     * @throws \Exception
     */
    public static function reDownloadRequest($user, $request_id){
        try{
            $dl_request = DownloadRequest::find($request_id);
            
            // ダウンロード処理可能か確認
            self::checkEnableDownload($user, $dl_request->class_path, $dl_request->function_name);

            // 無害化するかを確認
            $is_sanitizing = Company::where('id', $user->mst_company_id)->first()->sanitizing_flg;

            // Job登録
            $job = (new ReDownloadJob(
                $user, $request_id, $is_sanitizing
                ));
            dispatch($job);

		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
    }

    /**
     * ダウンロード再要求
     * @param User $user 利用者情報
     * @param string $class_path クラスのフルパス
     * @param string $function_name 関数名
     * @param string $file_name ファイル名
     * @param bool $is_sanitizing 無害化の有無
     * 
     * @throws \Exception
     */
    public static function reDownloadRequestSelectableSanitizing($user, $request_id, $class_path, $function_name, $file_name, $is_sanitizing, ...$param){
		try{
            $dl_request = DownloadRequest::find($request_id);
            
            // ダウンロード処理可能か確認
            self::checkEnableDownload($user, $dl_request->class_path, $dl_request->function_name);
	
			// Job登録
			$job = (new DownloadJob(
				$user, $class_path, $function_name, $file_name, $is_sanitizing, $param
				));
			dispatch($job);

            DownloadRequest::find($request_id)->delete();

		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
    }

    /**
     * ダウンロード処理が可能かを判定
     * 不可の場合、例外発生
     * 
     * @param User $user 利用者情報
     * @param string $class_path クラスのフルパス
     * @param string $function_name 関数名
     * 
     * @throws \Exception
     */
    public static function checkEnableDownload($user, $class_path, $function_name)
    {
        if(!$user){
            throw new \Exception(__('message.false.download_request.login'));
        }

        if(!is_callable(array($class_path, $function_name))){
            throw new \Exception(__('message.false.download_request.method',['attribute' => '']));
        }

        $limit = DB::table('mst_constraints')
            ->where('mst_company_id', $user->mst_company_id)
            ->select('dl_max_keep_days', 'dl_request_limit', 'dl_request_limit_per_one_hour', 'dl_file_total_size_limit')
            ->first();

        if (!$limit) {
            throw new \Exception(__('message.false.download_request.limit_setting_get'));
        }

        // 保有ダウンロード要求数 上限チェック
        if (DownloadRequest::enableDownloadRequestLimit($user->id, $limit->dl_request_limit) === false) {
            throw new \Exception(__('message.warning.download_request.req_file_max'));
        }

        // 1時間当たりのダウンロード要求数 上限チェック
        if (DownloadRequest::enableDownloadRequestLimitPerOneHour($user->id, $limit->dl_request_limit_per_one_hour) === false) {
            throw new \Exception(__('message.warning.download_request.download_file_max'));
        }

        // 総容量チェック
        $dl_wait_data_sum = DB::table('download_wait_data as dwd')
            ->where('mst_company_id', $user->mst_company_id)
            ->join('download_request as dr', 'dwd.download_request_id', '=', 'dr.id')
            ->select(DB::raw('sum(dwd.file_size) as file_sizes'))->value('file_sizes');

        $dl_proc_wait_data_sum = DB::table('download_proc_wait_data as dpwd')
            ->where('mst_company_id', $user->mst_company_id)
            ->join('download_request as dr', 'dpwd.download_request_id', '=', 'dr.id')
            ->select(DB::raw('sum(dpwd.file_size)  as file_sizes'))->value('file_sizes');

        if ($dl_wait_data_sum + $dl_proc_wait_data_sum > $limit->dl_file_total_size_limit * 1024 * 1024) {
            throw new \Exception(__('message.warning.download_request.download_size_max'));
        }
    }

    /**
     * ダウンロード要求テーブル, ダウンロード待ちデータテーブルのレコード更新
     *
     * @param int $dl_request_id ダウンロード要求ID (download_request.id)
     * @param string $data 更新するダウンロードデータ (dowmload_wait_data.data)
     * @param int $is_sanitizing 無害化処理フラグ (mst_company.sanitizing_flg)
     * @return array
     * 
     * @throws \Exception
     */
    public static function updateDownloadData($dl_request_id, $data, $is_sanitizing)
    {
        $size = AppUtils::getFileSize(base64_encode($data));
        // ファイルデータエンコード
        $data = AppUtils::encrypt(base64_encode($data));

        // PAC_5-2874 S
        // LGWAN publicの場合
        $is_private = config('app.app_lgwan_flg');
        // 無害化するかを確認
        $state = $is_sanitizing == 1 ? ($is_private ? self::REQUEST_SANITIZING_WAIT : self::REQUEST_DOWNLOAD_WAIT) : self::REQUEST_DOWNLOAD_WAIT;
        $sanitizing_state = $is_sanitizing == 1 ? ($is_private ? self::SANITIZING_UNNEEDED : self::SANITIZING_NEED) : self::SANITIZING_UNNEEDED;
        // PAC_5-2874 E

        try{
            
            DB::beginTransaction();
            
            $dl_request                 = DownloadRequest::where('id', $dl_request_id)->first();
            $dl_request->state          = $state;
            $dl_request->sanitizing_state = $sanitizing_state;// PAC_5-2874
            $dl_request->save();

            $dl_wait_data               = DownloadWaitData::where('download_request_id', $dl_request_id)->first();
            $dl_wait_data->data         = $data;
            $dl_wait_data->file_size    = $size;
            $dl_wait_data->create_at    = Carbon::now();
            $dl_wait_data->save();

            DB::commit();

        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            DB::rollback();
        }
    }

    /**
     * ダウンロード要求に付随するデータのみを削除
     * @param $id
     * @param $user
     * 
     * @throws \Exception
     */
    public static function RemoveRequestData($id, $user)
    {
        try {
            DB::beginTransaction();
            DB::table('download_request')
                ->where('mst_user_id', $user->id)
                ->where('user_auth', AppUtils::AUTH_FLG_USER)
                ->where('id', $id)
                ->update([
                    'state' => DownloadUtils::DELETE_STATE,
                ]);
            DB::table('download_proc_wait_data')
                ->where('download_request_id', $id)
                ->update([
                    'document_data' => null,
                    'file_size' => 0,
                ]);
            DB::table('download_wait_data')
                ->where('download_request_id', $id)
                ->update([
                    'data' => null,
                    'update_at' => Carbon::now(),
                    'file_size' => 0,
                ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage() . $e->getTraceAsString());
            throw new \Exception($e);
        }

    }

    /**
     * 要求の中から期限切れのものを更新
     * 要求自体は削除しない
     * @param $user
     * 
     * @throws \Exception
     */
    public static function removeExpiredRequestData($user)
    {
        $period_request = DownloadRequest::where('mst_user_id', $user->id)
            ->where('user_auth', AppUtils::AUTH_FLG_USER)
            ->where('download_period', '<=', Carbon::now())
            ->where('state', '!=', DownloadUtils::DELETE_STATE);

        if (!$period_request) {
            return;
        }

        DB::beginTransaction();
        try {
            $period_request
                ->update([
                    'state' => DownloadUtils::EXPIRED_STATE,
                ]);

            $download_req_ids = [];
            foreach ($period_request->get() as $period_request_row) {
                $download_req_ids[] = $period_request_row->id;
            }

            DownloadProcWaitData::wherein('download_request_id', $download_req_ids)
                ->update([
                    'document_data' => null,
                    'file_size' => 0,
                ]);

            DownloadWaitData::wherein('download_request_id', $download_req_ids)
                ->update([
                    'data' => null,
                    'update_at' => Carbon::now(),
                    'file_size' => 0,
                ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            throw new \Exception($ex);
        }
    }

    /**
     * ステータスが削除 且つ 要求が一時間以上過ぎた ダウンロード要求を削除
     * @param $user
     * 
     * @throws \Exception
     */
    public static function removeRequestAnHourAgoAndDeleteState($user)
    {
        try {
            $now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;

            $remove_request = DB::table('download_request')
                ->where('mst_user_id', $user->id)
                ->where('user_auth', AppUtils::AUTH_FLG_USER)
                ->where('state', DownloadUtils::DELETE_STATE)
                ->where('request_date', '<', (new Carbon($now_db_timezone))->subHour());

            if (!$remove_request) {
                return;
            }

            $download_req_ids = [];
            foreach ($remove_request->get() as $remove_request_row) {
                $download_req_ids[] = $remove_request_row->id;
            }

            DB::beginTransaction();
            // ダウンロード要求
            DownloadRequest::wherein('id', $download_req_ids)
                ->delete();

            // 圧縮用データ保管
            DownloadProcWaitData::wherein('download_request_id', $download_req_ids)
                ->delete();

            // ダウンロード待ちデータ
            DownloadWaitData::wherein('download_request_id', $download_req_ids)
                ->delete();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            throw new \Exception($ex);
        }
    }
}