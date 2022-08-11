<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\EnvApiUtils;
use App\Models\LongTermDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateLongTermAttachmentJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:LongTermAttachmentJson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'LongTermAttachmentJson';

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
        Log::channel('cron-daily')->debug('LongTermAttachmentJson_START');

        $boolFlg = true;
        // OSS  Storage directory
        $strOssPath = config('app.server_env') ? config('app.k5_storage_attachment_root_folder') : config('app.s3_storage_attachment_root_folder');
        // Long term storage directory prefix
        $folderPath = $strOssPath . ("/" . config("app.long_term_back_attachment_folder_pre"));
        // Data slicing with attachments
        $arrIds = [];
        while (true == $boolFlg) {
            $arrAttachment = LongTermDocument::where("circular_attachment_json", "like", "%" . explode('/', $strOssPath)[0] . "%")->when($arrIds,function ($query) use ($arrIds){
                $query->whereNotExists(function($query) use ($arrIds){
                    $query->select("ltd.id")->from("long_term_document as ltd")->whereRaw("ltd.id = long_term_document.id")->whereIn('ltd.id',$arrIds);
                });
            })
                ->where("circular_attachment_json", 'not like', "%" . config("app.long_term_back_attachment_folder_pre") . "%")->orderBy("id", 'desc')->limit(200)->get();

            if ($arrAttachment->isEmpty()) {
                $boolFlg = false;
                Log::channel('cron-daily')->debug(print_r("change this", true));
                continue;
            }
            $this->handlerAttachmentJson($arrAttachment,$folderPath);
            $arrIds = array_merge($arrAttachment->pluck('id')->toArray());
        }
        Log::channel('cron-daily')->debug('LongTermAttachmentJson_END');
    }

    private function handlerAttachmentJson($arrAttachment,$folderPath){

        foreach ($arrAttachment as $attachment) {
            // Format the stored JSON and take out all the attachment storage information
            $arrAttachmentJsonData = json_decode($attachment->circular_attachment_json, true);

            if (empty($arrAttachmentJsonData)) {
                continue;
            }
            // Get all stored attachment information of the current circular
            $arrCircularAttachmentData = DB::table("circular_attachment")->where("circular_id", $attachment->circular_id)->get();
            if ($arrCircularAttachmentData->isEmpty()) {
                continue;
            }
            // Get This Circular Attachment Dir Path
            $strOriginDirBase = dirname($arrCircularAttachmentData[0]->server_url);
            // The path format is incorrect
            if (mb_strlen($strOriginDirBase, "UTF-8") <= 26) {
                continue;
            }
            $this->handlerCopyAttachmentJson($attachment,$folderPath,$strOriginDirBase,$arrAttachmentJsonData,$arrCircularAttachmentData);
        }
    }

    private function handlerCopyAttachmentJson(&$attachment,$folderPath,$strOriginDirBase,$arrAttachmentJsonData,$arrCircularAttachmentData){
        // JSON to be updated
        $arrNewJsonData = [];
        DB::beginTransaction();
        try {
            // Get all files in the attachment directory
            $arrAllAttachmentFile = Storage::disk(config('app.server_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5' : 's3')->files($strOriginDirBase);
            // Convert an array to a collection
            $collectAttachmentJsonData = collect($arrAttachmentJsonData);
            // Traverse file list
            foreach ($arrAllAttachmentFile as $attachmentFilePath) {
                // Find the data that meets the path conditions in the collection
                $objTempItem = $arrCircularAttachmentData->firstWhere("server_url", $attachmentFilePath);
                if (empty($objTempItem)) {
                    continue;
                }

                $objFindData = $collectAttachmentJsonData
                    ->firstWhere("file_name", $objTempItem->file_name);
                if (empty($objFindData)) {
                    continue;
                }

                // Get this new file name
                $file_name = $objTempItem->id . '_' . substr(md5(time()), 0, 8) . '.' . substr(strrchr($objTempItem->file_name, '.'), 1);
                // Get this new save path
                $server_url = sprintf("%s/%s/%s/%s", $folderPath, $objFindData['edition_flg'] . $objFindData['env_flg'] . ($objFindData['server_flg'] ?? $objTempItem->server_flg), $attachment->mst_company_id, $attachment->circular_id);
                $strNewFilePath = $server_url . '/' . $file_name;

                if(isset($objFindData['server_url']) && mb_strlen($objFindData['server_url'],'UTF-8') > 26){
                    Storage::disk(config('app.server_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5' : 's3')->delete($objFindData['server_url']);
                }

                // Determine whether the directory on the OSS exists. If it does not exist, create it
                $isDirectory = Storage::disk(config('app.server_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5' : 's3')->exists($server_url);
                if (!$isDirectory) {
                    Storage::disk(config('app.server_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5' : 's3')->makeDirectory($server_url);
                }
                // Copy attachment to new directory
                Storage::disk(config('app.server_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5' : 's3')->copy($attachmentFilePath, $strNewFilePath);
                $arrNewJsonData[] = [
                    'server_url' => $strNewFilePath,
                    'server_path' => $server_url,
                    'file_path_name' => $file_name,
                    'server_flg' => ($objFindData['server_flg'] ?? $objTempItem->server_flg),
                    'env_flg' => $objFindData['env_flg'],
                    'edition_flg' => $objFindData['edition_flg'],
                    'file_name' => $objTempItem->file_name,
                    'file_size' => $objTempItem->file_size,
                    'name' => $objTempItem->name,
                    'create_user' => $objTempItem->create_user,
                    'create_at' => $objTempItem->create_at,
                    'id' => $objTempItem->id,
                    'server_path_url' => $objTempItem->server_url,
                    'company_id'=>$objTempItem->create_company_id,
                    'status'=>$objTempItem->status,
                    'confidential_flg'=>$objTempItem->confidential_flg,
                    'create_user_id'=>$objTempItem->create_user_id,
                    'type' => config('app.server_env') == EnvApiUtils::ENV_FLG_K5 ? 'k5' : 's3',
                ];
            }
            $attachment->circular_attachment_json = $arrNewJsonData ? json_encode($arrNewJsonData) : '';
            $attachment->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('cron-daily')->debug('LongTermAttachmentJson更新異常発生しました。');
            Log::channel('cron-daily')->info("LongTermAttachmentJson -> current circular ID = " . $attachment->circular_id);
        }
    }
}
