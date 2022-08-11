<?php

namespace App\Console\Commands;

use App\Http\Utils\BoxUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Jobs\AutoStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RemoveBoxDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:boxDocument';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $circular_auto_storage_historys = DB::table('circular_auto_storage_history')
            ->select('id','circular_id','mst_company_id')
            ->where('result', 0)
            ->groupBy('id','circular_id','mst_company_id')
            ->get();
        foreach ($circular_auto_storage_historys as $history){
            //ファイルの生成
            dispatch((new AutoStorage($history->mst_company_id, $history->circular_id))->onQueue('default'));
        }

//        $circular_auto_storage_history = [];
//        $circular_auto_storage_historys = DB::table('circular_auto_storage_history')
//            ->select('id','circular_id','mst_company_id','route')
//            ->where('result', 1)
//            ->where('create_at', '>', Carbon::create(2022, 3, 9, 17))
//            ->where('create_at', '<', Carbon::create(2022, 3, 10, 19))
//            ->groupBy('id','circular_id','mst_company_id','route')
//            ->get();
//        $history_ids = [];
//        foreach ($circular_auto_storage_historys as $history){
//            $circular_auto_storage_history[$history->mst_company_id][] = $history;
//            $history_ids[] = $history->id;
//        }
//        Log::channel('cron-daily')->debug('all circular_auto_storage_history ids ' . json_encode($history_ids));
//
//        //file save
//        $file_path = storage_path('app/removeBoxDocument.txt');
//        $executed_histories = [];
//        if (File::exists($file_path)) {
//            $content = File::get($file_path);
//            $str_encoding = mb_convert_encoding($content, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//转换字符集（编码）
//            $executed_histories = explode(PHP_EOL, $str_encoding);
//        }
//
//        foreach ($circular_auto_storage_history as $company_id => $items){
//            //refresh_token
//            $limit = DB::table('mst_limit')->where('mst_company_id',$company_id)->first();
//            if(!$limit){
//                Log::channel('cron-daily')->debug("企業ID存在しない".$company_id);
//                continue;
//            }
//            $box_refresh_token = $limit->box_refresh_token;
//            $box_parent_id = $limit->box_auto_save_folder_id;
//            $box_parent_folder_name = $limit->box_enabled_folder_to_store . "\\";
//            $access_token = BoxUtils::refreshAccessToken($box_refresh_token, $company_id, true);
//            if (!$access_token){
//                $circular_auto_storage_history_ids = [];
//                foreach ($items as $item){
//                    $circular_auto_storage_history_ids[] = $item->id;
//                }
//                Log::channel('cron-daily')->debug('get box access token failed,circular_auto_storage_history_ids = ' . json_encode($circular_auto_storage_history_ids));
//                continue;
//            }
//            Cache::put('access_token', $access_token, 3000);
//
//            //ファイルとフォルダの削除
//            foreach ($items as $item){
//                if (!in_array($item->id,$executed_histories)){
//                    try {
//                        //delete folder and files
//                        $delete_folder_name = $item->route ? substr($item->route,strlen($box_parent_folder_name)) : '';
//                        if ($delete_folder_name){
//                            if (!Cache::has('access_token')){
//                                $limit = DB::table('mst_limit')->where('mst_company_id',$company_id)->first();
//                                $access_token = BoxUtils::refreshAccessToken($limit->box_refresh_token, $company_id, true);
//                            }
//                            $result = self::deleteFoldersOrFiles($access_token, $box_parent_id, $delete_folder_name,$item->id);
//                            if ($result){
//                                DB::table('circular_auto_storage_history')
//                                    ->where('id',$item->id)->update([
//                                        'result' => 0
//                                    ]);
//                                //ファイルの生成
//                                dispatch(new AutoStorage($company_id,$item->circular_id));
//                                Storage::append('removeBoxDocument.txt',$item->id);
//                            }
//                        }
//                    }catch (\Exception $e){
//                        Log::channel('cron-daily')->warning($e->getMessage().$e->getTraceAsString());
//                    }
//                }
//            }
//        }

    }


    private function deleteFoldersOrFiles($access_token, $parent_id, $folder_name, $circular_auto_history_id)
    {
        $client = BoxUtils::getAuthorizedApiClient($access_token, false, ['Content-Type' => 'application/json', 'Retry-After' => 30]);
        $result = $client->get('folders/' . $parent_id . "?limit=1000");
        $resData = json_decode((string)$result->getBody());
        if ($result->getStatusCode() == StatusCodeUtils::HTTP_OK) {
            foreach ($resData->item_collection->entries as $data_item) {
                if ($data_item->type == 'file' && substr($data_item->name,0,strpos($data_item->name,'_署名')) == $folder_name){
                    //delete file
                    $delete_result = self::deleteFile($client, $data_item->id, $data_item->name, $circular_auto_history_id);
                    if (!$delete_result) return false;

                }
            }
            $files_total = $resData->item_collection->total_count;
            $api_limit = $resData->item_collection->limit;
            if ($files_total > $api_limit) {
                $max_page = ceil($files_total * 1.0 / $api_limit);
                for ($page = 1; $page < $max_page; $page++) {
                    $result = $client->get('folders/' . $parent_id . '?offset=' . ($api_limit * $page) . '&limit=' . $api_limit);
                    $resData = json_decode((string)$result->getBody());
                    if ($result->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                        foreach ($resData->item_collection->entries as $data_item) {
                            //delete file
                            if ($data_item->type == 'file' && substr($data_item->name,0,strpos($data_item->name,'_署名')) == $folder_name){
                                $delete_result = self::deleteFile($client, $data_item->id, $data_item->name, $circular_auto_history_id);
                                if (!$delete_result) return false;
                            }
                        }
                    } else {
                        Log::channel('cron-daily')->warning($result->getStatusCode());
                        Log::channel('cron-daily')->error('"BOX自動保存：フォルダ取得に失敗しました。' . $result->getBody());
                        return false;
                    }
                }
            }
            return true;
        }else{
            Log::channel('cron-daily')->warning($result->getStatusCode());
            Log::channel('cron-daily')->error('"BOX自動保存：フォルダ取得に失敗しました。' . $result->getBody());
            return false;
        }
    }

    private function deleteFolder($client, $folder_id, $folder_name, $circular_auto_history_id)
    {
        $result = $client->delete('folders/' . $folder_id);
        if ($result->getStatusCode() == StatusCodeUtils::HTTP_NO_CONTENT) {
            Log::channel('cron-daily')->info("circular_auto_history_id=$circular_auto_history_id , delete folder success folder_name =$folder_name");
            return true;
        } else {
            Log::channel('cron-daily')->warning($result->getStatusCode());
            Log::channel('cron-daily')->warning("circular_auto_history_id=$circular_auto_history_id , delete folder failed folder_name =$folder_name");
            Log::channel('cron-daily')->warning($result->getBody());
            return false;
        }
    }

    private function deleteFile($client, $file_id, $file_name, $circular_auto_history_id){
        $result = $client->delete('files/' . $file_id);
        if ($result->getStatusCode() == StatusCodeUtils::HTTP_NO_CONTENT) {
            Log::channel('cron-daily')->info("circular_auto_history_id=$circular_auto_history_id , delete file success file_name=$file_name");
            return true;
        } else {
            Log::channel('cron-daily')->warning($result->getStatusCode());
            Log::channel('cron-daily')->warning("circular_auto_history_id=$circular_auto_history_id , delete file failed  file_name=$file_name");
            Log::channel('cron-daily')->warning($result->getBody());
            return false;
        }
    }
}
