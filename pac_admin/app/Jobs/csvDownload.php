<?php
namespace App\Jobs;

use App\Http\Utils\CommonUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\MailUtils;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use DB;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;
use App\CompanyAdmin;
use Illuminate\Support\Facades\Storage;

class csvDownload implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $id;
    private $content;
    private $finishedDateKey;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $content, $finishedDateKey)
    {
        $this->id = $id;
        $this->content = $content;
        $this->finishedDateKey = $finishedDateKey;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $download_req_id = $this->id;
            $contents = $this->content;
            $finishedDateKey = $this->finishedDateKey;
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


            // 状態更新 ( 処理待ち:0 => 作成中:1)
            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::REQUEST_CREATING
                ]);


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

            //ダウンロードデータDB保存
            $csv_data = AppUtils::encrypt(base64_encode(\file_get_contents($csv_path)));
            $size = filesize($csv_path);

            DB::table('download_wait_data')
                ->where('download_request_id',$download_req_id )
                ->update([
                    'data' => $csv_data,
                    'update_at' => Carbon::now(),
                    'file_size' => $size,
            ]);

            // 無害化サーバで無害化処理するか
            $isSanitizing = DB::table('mst_company')
                ->where('id', $dl_req->mst_company_id)->first()
                ->sanitizing_flg;
            if($isSanitizing == 1){
                // 状態更新 ( 作成中:1 => 無害化待ち:11)
                $state = DownloadUtils::REQUEST_SANITIZING_WAIT;
            }else{
                // 状態更新 ( 作成中:1 => ダウンロード待ち:2)
                $state = DownloadUtils::REQUEST_DOWNLOAD_WAIT;
            }

            // 状態更新
            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => $state,
                    'contents_create_at' => Carbon::now()
                ]);

            // 状態更新 ( 処理待ち:0 => 処理済み:1)
            DB::table('download_proc_wait_data')
                ->where('download_request_id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::PROC_PROCESS_END,
                ]);


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

            //ファイル削除
            //array_map('unlink', glob($csv_path));

        }catch(\Exception $e) {
            // リトライ
            DB::table('download_request')
                ->where('id', $download_req_id)
                ->update([
                    'state' => DownloadUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $e;
        }
    }
}