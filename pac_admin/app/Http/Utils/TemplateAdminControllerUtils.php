<?php

namespace App\Http\Utils;

use DB;
use Carbon\Carbon;
use App\CompanyAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\DownloadUtils;

/**
 * 回覧完了テンプレート
 * Class DownloadRequestUtils
 * @package App\Http\Utils
 */
class TemplateAdminControllerUtils {
	
    /**
     * 非同期ダウンロード用回覧完了テンプレートダウンロードファイル取得
     *
     * @param $param
     * @param $user
     * @param $download_req_id
     * @return string
     */
	public static function getCircularCompleteTemplateData($param, CompanyAdmin $user, $download_req_id){
		try{
			$contents = !empty($param["contents"]) ? $param['contents'] : "0,1,2";
			TemplateAdminControllerUtils::setCircularCompleteTemplateData($param, $user, $contents, $download_req_id);

            $finishedDateKey = !empty($param["finishedDate"]) ? $param['finishedDate'] : null;
            Log::info('$contents' . var_export($contents, true));

            // 当月
            if (!$finishedDateKey) {
                $finishedDate = '';
            } else {
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            }

            // ダウンロード要求情報取得
            $dl_req = DB::table('download_request')
            ->where('id', $download_req_id)->first();

            $template_csv_data = array();
            $temp_download_data = array();
            $csv_path = '/var/www/pac/pac_admin/storage/app/template-csv-download-' . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $dl_req->mst_company_id, $dl_req->id) .'.csv';

			// 回覧文書ID 
			$dl_proc_wait_datas = DB::table('download_proc_wait_data')
				->where('download_request_id', $download_req_id)
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
                    $template_input_datas = DB::table('template_input_data')
                        ->select('create_user','create_at','template_placeholder_name','template_placeholder_data')
                        ->where('circular_id',$circular_id)
                        ->get();
                    $template_input_data = json_decode(json_encode($template_input_datas),true);
                    $template_input_data_first = $template_input_data['0'];

                    $names = explode(",", $long_term_document_info->destination_name);

                    //全パターン共通情報登録
                    array_push($document_data_array,
                               $long_term_document_info->file_name,
                               $long_term_document_info->sender_name,
                               $long_term_document_info->request_at);

                    //回覧情報選択時
                    if(preg_match('/1/',$contents)){
                        foreach($names as $name){
                            array_push($document_data_array, $name);
                        }
                        array_push($document_data_array,$long_term_document_info->completed_at);
                        array_push($template_csv_data, $document_data_array);
                        
                    }

                    //テンプレート情報選択時
                    if(preg_match('/2/',$contents)){
                        $template_csv_data = array();
                        
                        foreach($template_input_data as $csv_data){
                            
                            $csv_data_array = [$csv_data['template_placeholder_name'],$csv_data['template_placeholder_data']];

                            $result_row = array_merge($document_data_array, $csv_data_array);
                            array_push($template_csv_data,$result_row);
                        }   
                        Log::info('$template_input_data' . var_export($template_input_data, true));
                    }

                    if(!preg_match('/1/',$contents) && !preg_match('/2/',$contents)){
                        array_push($template_csv_data,$document_data_array);
                        Log::info('$contents33333333');
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
                    $circular_names = json_decode(json_encode($circular_users),true);
                    foreach($circular_names as $name_array){
                        foreach($name_array as $name){
                            array_push($names,$name);
                        }
                    }
                    
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
                    array_push($document_data_array,$circular_document->file_name,$circular_user->name,$circular->create_at);

                    //回覧情報選択時
                    if(preg_match('/1/',$contents)){
                        foreach($names as $name){
                            array_push($document_data_array, $name);
                        }
                        array_push($document_data_array,$circular->completed_date);
                        array_push($template_csv_data, $document_data_array);
                        Log::info('$document_data_array' . var_export($document_data_array, true));
                    }

                    //テンプレート情報選択時
                    if(preg_match('/2/',$contents)){
                        $template_csv_data = array();
                        
                        foreach($template_input_data as $csv_data){
                            
                            $csv_data_array = [$csv_data['template_placeholder_name'],$csv_data['template_placeholder_data']];

                            $result_row = array_merge($document_data_array, $csv_data_array);
                            array_push($template_csv_data,$result_row);
                        }
                        Log::info('$document_data_array' . var_export($document_data_array, true));
                    }

                    if(!preg_match('/1/',$contents) && !preg_match('/2/',$contents)){
                        array_push($template_csv_data,$document_data_array);
                    }

                    array_push($temp_download_data,$template_csv_data);
                }
            }
			$stream = fopen($csv_path,'w');

			fwrite($stream, pack('C*',0xEF,0xBB,0xBF));

			foreach($temp_download_data as $data){
				foreach($data as $d){
					fputcsv($stream, $d);
				}
			}

			fclose($stream);

            // 完了お知らせ
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

			return \file_get_contents($csv_path);

        }catch(\Exception $e) {
            // リトライ
            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::REQUEST_PROCESS_WAIT
                ]);
            Log::error($e->getMessage());
            return null;
        }
	}
	
    /**
     * 回覧完了テンプレートダウンロードファイル用のデータをダウンロード待ちデータテーブルに保存
     *
     * @param $param
     * @param $user
     * @param $content
     * @param $download_req_id
     */
	public static function setCircularCompleteTemplateData($param, $user, $content, $download_req_id){
		$request_ids = $param['cids'];
		$input_file_name = $param['fileName'];
		Log::info('$request_ids' . var_export($request_ids, true));
		Log::info('$input_file_name' . $param['fileName']);
		
		// 回覧完了日時
		$finishedDateKey = !empty($param["finishedDate"]) ? $param['finishedDate'] : null;

		// 当月
		if (!$finishedDateKey) {
			$finishedDate = '';
		} else {
			$finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
		}
		
		if($input_file_name == NULL){
			$input_file_name = 'template.csv';
		}else{
			$input_file_name = $input_file_name.'.csv';
		}

		$mst_company_id = $user->mst_company_id;
		$user_id = $user->id;

		//設定上限の確認
		$limit = DB::table('mst_constraints')
				->where('mst_company_id', $user->mst_company_id)
				->select('dl_max_keep_days', 'dl_request_limit', 'dl_file_total_size_limit')
				->first();

		DB::beginTransaction();
		
		//長期保存対応
		if($finishedDateKey === 12){
			//Download Requestテーブル挿入
			$now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;

			foreach($request_ids as $long_term_id){
				$long_term_info = DB::table('long_term_document')
					->where('id',$long_term_id)
					->first();
				$destination_name = $long_term_info->destination_name;

					$count = 1;
					//download proc wait dataテーブルに挿入
					DB::table('download_proc_wait_data')->insert([
						'state' => 0,
						'download_request_id' => $download_req_id,
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
			$now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;

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
					'download_request_id' => $download_req_id,
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