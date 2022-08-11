<?php
namespace App\Http\Utils;

use DB;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\API\SearchCircularUserAPIRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;

/**
 * テンプレート処理ユーティリティクラス
 * Class DownloadRequestApiControllerUtils
 * @package App\Http\Utils
 */
class CsvTemplateDownloadControllerUtils
{
    /**
     * 非同期ダウンロード用回覧完了テンプレートダウンロードファイル取得
     *
     * @param $param
     * @param $user
     * @param $download_req_id
     * @return string
     */
	public static function getFinishedCircularTemplateData($user, $params, $dl_request_id){

		CsvTemplateDownloadControllerUtils::setFinishedCircularTemplateDataForDownloadProcWaitData($user, $params, $dl_request_id);

		$request_ids 		= $params['ids'];
		$content 			= $params['contents'];
		$finishedDateKey 	= $params['finishedDate'];
		// 回覧完了日時
		$finishedDateKey 	= $params['finishedDate'];
		// 当月
		$finishedDate 		= !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		$contents 			= json_decode(json_encode($content),true);
		$request_ids 		= json_decode(json_encode($request_ids),true);

		// 当月
		if (!$finishedDateKey) {
			$finishedDate = '';
		} else {
			$finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		}

		// ダウンロード要求情報取得
		$dl_req = DB::table('download_request')
						->where('id', $dl_request_id)->first();

		$template_csv_data = array();
		$temp_download_data = array();
		$csv_path = '/var/www/pac/pac_user_api/storage/app/template-csv-download-' . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $dl_req->mst_company_id, $dl_req->id) .'.csv';


		// 状態更新 ( 処理待ち:0 => 作成中:1)
		DB::table('download_request')
			->where('id', $dl_request_id)
			->update([
				'state' => DownloadRequestUtils::REQUEST_CREATING
			]);


			// 回覧文書ID 
			$dl_proc_wait_datas = DB::table('download_proc_wait_data')
				->where('download_request_id', $dl_request_id)
				->select('circular_id')->get();

			// 申請者情報
			$user_info = DB::table('mst_user')
				->where('id', $dl_req->mst_user_id)
				->select(['id', 'email', 'mst_company_id'])
				->first();

			$request_ids = json_decode(json_encode($dl_proc_wait_datas),true);

		//長期保存対応
		if($finishedDateKey === 12){
			foreach($request_ids as $circular_id){
				$document_data_array = array();
				$template_csv_data = array();
				$names = array();

				$long_term_document_info = DB::table("long_term_document")
					->where('circular_id',$circular_id)
					->first();

				//template_input_data情報取得
				$template_input_data = DB::table('template_input_data')
					->select('create_user','create_at','template_placeholder_name','template_placeholder_data')
					->where('circular_id',$circular_id)
					->get();

				$names = explode(",", $long_term_document_info->destination_name);

				//全パターン共通情報登録
				array_push($template_csv_data,
						   $long_term_document_info->file_name,
						   $long_term_document_info->sender_name,
						   $long_term_document_info->request_at);

				//回覧情報選択時
				if(preg_match('/1/',$contents)){
					foreach($names as $name){
						array_push($template_csv_data, $name);
					}
					array_push($template_csv_data,$long_term_document_info->completed_at);
				}

				//テンプレート情報選択時
				if(preg_match('/2/',$contents)){				
					foreach($template_input_data as $csv_data){
						array_push($template_csv_data,$csv_data->template_placeholder_data);
					} 
				}
				array_push($temp_download_data,$template_csv_data);

			}
		}else{
			foreach($request_ids as $circular_id){
				
				$document_data_array = array();
				$template_csv_data = array();
				$names = array();
				//circular情報取得
				$circular = DB::table("circular$finishedDate")
					->where('id',$circular_id)
					->select('create_user','completed_date','create_at','update_at')
					->first();

				//circular_user情報取得
				$circular_user = DB::table("circular_user$finishedDate")
					->where('circular_id',$circular_id)
					->select('id','title','name')
					->orderBy('parent_send_order')
					->orderBy('child_send_order')
					->first();

				$circular_users = DB::table("circular_user$finishedDate")
					->where('circular_id',$circular_id)
					->where('id','!=',$circular_user->id)
					->select('name')
					->get();
				
				//circular_document情報取得
				$circular_document = DB::table("circular_document$finishedDate")
					->where('circular_id',$circular_id)
					->select('id','file_name')
					->first();

				//template_input_data情報取得
				$template_input_datas = DB::table('template_input_data')
					->select('create_user','create_at','template_placeholder_name','template_placeholder_data')
					->where('circular_id',$circular_id)
					->get();
				$template_input_data = json_decode(json_encode($template_input_datas),true);
				$template_input_data_first = $template_input_data['0'];


				//全パターン共通情報登録
				array_push($template_csv_data,$circular_document->file_name,$circular_user->name,$circular->create_at);
				//回覧情報選択時
				if(preg_match('/1/',$contents)){
					foreach($circular_users as $name){
						array_push($template_csv_data,$name->name);
					}
					array_push($template_csv_data,$circular->completed_date);
				}

				//テンプレート情報選択時
				if(preg_match('/2/',$contents)){					
					foreach($template_input_data as $csv_data){
						array_push($template_csv_data, $csv_data['template_placeholder_data']);
					}
				}

				array_push($temp_download_data,$template_csv_data);
			}
		}

		$stream = fopen($csv_path,'w');

		fwrite($stream, pack('C*',0xEF,0xBB,0xBF));
		foreach($temp_download_data as $data){
			fputcsv($stream, $data);
		}

		fclose($stream);

		//ダウンロードデータDB保存
		$csv_data = \file_get_contents($csv_path);

		// 無害化サーバで無害化処理するか
		$isSanitizing = DB::table('mst_company')
			->where('id', $dl_req->mst_company_id)->first()
			->sanitizing_flg;
		if($isSanitizing == 1){
			// 状態更新 ( 作成中:1 => 無害化待ち:11)
			$state = DownloadRequestUtils::REQUEST_SANITIZING_WAIT;
		}else{
			// 状態更新 ( 作成中:1 => ダウンロード待ち:2)
			$state = DownloadRequestUtils::REQUEST_DOWNLOAD_WAIT;
		}

		// 状態更新 ( 処理待ち:0 => 処理済み:1)
		DB::table('download_proc_wait_data')
			->where('download_request_id', $dl_request_id)
			->update([
				'state' => DownloadRequestUtils::PROC_PROCESS_END,
			]);


		// 完了お知らせ
		// 無害化サーバ経由時はここでは通知無し
		if($isSanitizing != 1) {
			$data = [
				'file_name' => $dl_req->file_name,
				'dl_period' => $dl_req->download_period
			];

			DB::table('mail_send_resume')->insert([
				'mst_company_id' => $user_info->mst_company_id,
				'to_email' => $user_info->email,
				'template' => MailUtils::MAIL_DICTIONARY['USER_SEND_DOWNLOAD_RESERVE_COMPLETED']['CODE'],
				'param' => json_encode($data, JSON_UNESCAPED_UNICODE),
				'type' => 0,
				'subject' => config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.SendDownloadReserveCompletedMail.subject'),
				'body' => trans('mail.SendDownloadReserveCompletedMail.body', $data),
				'state' => 0,
				'send_times' => 0,
				'create_at' => Carbon::now(),
				'update_at' => Carbon::now(),
			]);
		}

		//ファイル削除
		array_map('unlink', glob($csv_path));

		return $csv_data;
	}

	/**
     * 回覧完了テンプレートダウンロードファイル用のデータをダウンロード待ちデータテーブルに保存
     *
     * @param $param
     * @param $params
     * @param $download_req_id
     */
	public static function setFinishedCircularTemplateDataForDownloadProcWaitData($user, $params, $dl_request_id){
		$request_ids 		= $params['ids'];
		$input_file_name 	= $params['filename'];
		$content 			= $params['contents'];
		$finishedDateKey 	= $params['finishedDate'];
		$input_file_name 	= $params['filename'];
		// 回覧完了日時
		$finishedDateKey 	= $params['finishedDate'];
		// 当月
		$finishedDate 		= !$finishedDateKey ? '' : Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		$contents 			= json_decode(json_encode($content),true);
		$request_ids 		= json_decode(json_encode($request_ids),true);

		Log::debug('downroadReserve start-' . $input_file_name);

		DB::beginTransaction();
		$now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;
		
		//長期保存対応
		if($finishedDateKey === 12){
			//Download Requestテーブル挿入

			foreach($request_ids as $long_term_id){
				$long_term_info = DB::table('long_term_document')
					->where('id',$long_term_id)
					->first();
				$destination_name = $long_term_info->destination_name;
				Log::debug('$destination_name' . var_export($destination_name, true));

					$count = 1;
					//download proc wait dataテーブルに挿入
					DB::table('download_proc_wait_data')->insert([
						'state' => 0,
						'download_request_id' => $dl_request_id,
						'num' => $count,
						'circular_document_id' => 0,
						'document_data_id' => 0,
						'document_data' => null,
						'create_at' => Carbon::now(),
						'create_user' => $user->id,
						'circular_id' => $long_term_info->circular_id,
						'file_name' => $input_file_name,
						'title' => $long_term_info->title,
						'circular_update_at' => $now_db_timezone
					]);
					$count++;
			}

		}else{
			//Download Requestテーブル挿入
			foreach($request_ids as $circular_id){
				//circular情報取得
				$circular = DB::table("circular$finishedDate")
					->where('id',$circular_id)
					->select('create_user','completed_date','create_at','update_at')
					->first();

				//circular_user情報取得
				$circular_user = DB::table("circular_user$finishedDate")
					->where('circular_id',$circular_id)
					->select('title','name')
					->first();

				//circular_document情報取得
				$circular_document = DB::table("circular_document$finishedDate")
					->where('circular_id',$request_ids)
					->select('id','file_name')
					->first();

				//document_data情報取得
				$document_data = DB::table("document_data$finishedDate")
					->where('circular_document_id', $circular_document->id)
					->select('id')
					->first();

				$count = 1;
				//download proc wait dataテーブルに挿入
				DB::table('download_proc_wait_data')->insert([
					'state' => 0,
					'download_request_id' => $dl_request_id,
					'num' => $count,
					'circular_document_id' => $circular_document->id,
					'document_data_id' => $document_data->id,
					'document_data' => null,
					'create_at' => Carbon::now(),
					'create_user' => $user->id,
					'circular_id' => $circular_id,
					'file_name' => $input_file_name,
					'title' => $circular_user->title,
					'circular_update_at' => $circular->update_at,
				'file_size' => 0
				]);
				$count++;
			}
		}

		DB::commit();
	}
}
