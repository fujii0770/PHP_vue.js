<?php

namespace App\Jobs;

use App\Models\Circular;
use App\Models\CircularAttachment;
use App\Models\CircularDocument;
use App\Models\CircularOperationHistory;
use App\Models\CircularUser;
use App\Models\DocumentCommentInfo;
use App\Models\DocumentData;
use App\Models\StampInfo;
use App\Models\TextInfo;
use Illuminate\Console\Command;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\EnvApiUtils;

class SendAllUserCircular implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request = null;
    private $intCircularID = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($intCircularID, $request)
    {
        $this->request = $request;
        $this->intCircularID = $intCircularID;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set("memory_limit", '1024M');
        $circular_id = $this->intCircularID;
        Log::channel('cron-daily')->debug("all user send start");

        try {
            Log::channel('cron-daily')->debug("find data");

            // Get Current Circular
            $objCircularData = Circular::where("id", $circular_id)->first();
            // Get Current Circular Document
            $objCircularDocument = CircularDocument::where("circular_id", $circular_id)->get();
            // Get Current Circular Document Data
            $objCircularDocumentData = DocumentData::whereIn("circular_document_id", $objCircularDocument->pluck("id"))->get();
            //Log::channel('cron-daily')->warning(print_r($objCircularDocumentData->pluck("id"), true));
            // Get Current Circular Operation History
            $objCircularOperationHistory = CircularOperationHistory::where("circular_id", $circular_id)->get();
            // Get Current Circular Attachment
            $objCircularAttachment = CircularAttachment::where("circular_id", $circular_id)->get();
            // Get Current Circular Stamp info
            $objCircularStampInfo = StampInfo::whereIn("circular_document_id", $objCircularDocument->pluck("id"))->get();
            // Get Current Circular Text info
            $objCircularTextInfo = TextInfo::where("circular_document_id", $objCircularDocument->pluck("id"))->get();
            // GET Current Circular DocumentCommentInfo
            $objDocumentCommentInfos = DocumentCommentInfo::whereIn("circular_document_id",$objCircularDocument->pluck("id"))->get();

            // Get Current Circular User current Login User
            $objLoginUser = CircularUser::where("circular_id", $circular_id)
                ->where('parent_send_order', 0)->where('child_send_order', 0)->first();
            // Attachment total size
            $intAttachmentTotalSize = DB::table('circular_attachment')
                ->select(DB::raw(' SUM(file_size) as total_size'))
                ->where('create_company_id', $objLoginUser->mst_company_id)
                ->where('edition_flg', $objLoginUser->edition_flg)
                ->where('env_flg', $objLoginUser->env_flg)
                ->where('server_flg', $objLoginUser->server_flg)
                ->where('circular_id',$circular_id)
                ->where('status', '!=', CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                ->value('total_size');
            // Current Attachment total size
            $intCurrentAttachmentTotalSize = 0;
            $objCircularAttachment->map(function ($item, $key) use (&$intCurrentAttachmentTotalSize) {
                $intCurrentAttachmentTotalSize += $item->file_size;
            });

            $objRequest = json_decode($this->request, true);
            $arrAllSendUser = $objRequest['allSendUser'][0];

            $constraints = DB::table('mst_constraints')
                ->select('max_total_attachment_size', 'max_attachment_count', 'max_attachment_size')
                ->where('mst_company_id', $objLoginUser->mst_company_id)
                ->first();


        } catch (\Exception $e) {
            Log::channel('cron-daily')->warning($e->getMessage() . $e->getTraceAsString());
            return;
        }


        $arrError = [];
        $intAllUserCount = count($arrAllSendUser);

        if (($constraints->max_total_attachment_size * 1024 * 1024 * 1024) <= $intAttachmentTotalSize) {
            return;
        }
        $intCurrentGB = ($constraints->max_total_attachment_size * 1024 * 1024 * 1024) - ($intAllUserCount * $intCurrentAttachmentTotalSize + $intAttachmentTotalSize);
        if ($intCurrentGB < 0) {
            $intSurplus = abs($intCurrentGB);
            $arrAllSendUser = array_slice($arrAllSendUser, 0, (int)($intSurplus / $intCurrentAttachmentTotalSize));
            $intAllUserCount = count($arrAllSendUser);
        }

        $arrLastUser = array_pop($arrAllSendUser);

        $strTextTitle = trim($objRequest['title']) ? trim($objRequest['title']) : $objRequest['filename'];

        foreach ($arrAllSendUser as $sendUserKey => $sendUser) {
            DB::beginTransaction();
            try {
                $circular = $objCircularData->replicate();
                $circular->save();
                $intCurrentID = $circular->id;

                $arrCircularDocumentIDs = [];
                $arrCircularOperationHistoryID = [];
                // copy current circular document
                $objCircularDocument->map(function ($item, $key) use ($intCurrentID, &$arrCircularDocumentIDs) {
                    $circularDocument = $item->replicate();
                    $intTempCircularDocument = $item->id;
                    $circularDocument->circular_id = $intCurrentID;
                    $circularDocument->save();
                    $arrCircularDocumentIDs[$intTempCircularDocument] = $circularDocument->id;
                });
                // copy current circular document data
                $objCircularDocumentData->map(function ($item, $key) use ($intCurrentID, $arrCircularDocumentIDs) {
                    $circularDocumentData = $item->replicate();
                    $circularDocumentData->circular_document_id = $arrCircularDocumentIDs[$circularDocumentData->circular_document_id];
                    $circularDocumentData->save();
                });
                // copy current circular operation history
                $objCircularOperationHistory->map(function ($item, $key) use ($intCurrentID, $arrCircularDocumentIDs, &$arrCircularOperationHistoryID) {
                    $intOldOperationHistoryId = $item->id;
                    $circularOperationHistory = $item->replicate();
                    $circularOperationHistory->circular_document_id = $arrCircularDocumentIDs[$circularOperationHistory->circular_document_id];
                    $circularOperationHistory->circular_id = $intCurrentID;
                    $circularOperationHistory->save();
                    $arrCircularOperationHistoryID[$intOldOperationHistoryId] = $circularOperationHistory->id;
                });

                $arrAttachmentIDS = [];
                // copy current circular Attachment  @todo
                $objCircularAttachment->map(function ($item, $key) use ($intCurrentID, $arrCircularDocumentIDs, $objLoginUser,&$arrAttachmentIDS) {
                    $circularAttachment = $item->replicate();
                    $circularAttachment->circular_id = $intCurrentID;
                    $circularAttachment->save();
                    $arrAttachmentIDS[] = $circularAttachment->id;
                });

                $this->copyCurrentCircularAllAttachment($intCurrentID, $arrAttachmentIDS, $objLoginUser);
                // copy current circular Stamp info  @todo
                $objCircularStampInfo->map(function ($item, $key) use ($intCurrentID, $arrCircularDocumentIDs, $arrCircularOperationHistoryID) {
                    $circularStampInfo = $item->replicate();
                    $circularStampInfo->circular_document_id = $arrCircularDocumentIDs[$circularStampInfo->circular_document_id];
                    $circularStampInfo->circular_operation_id = $arrCircularOperationHistoryID[$item->circular_operation_id];
                    if(isset($circularStampInfo->updated_at)){
                        unset($circularStampInfo->updated_at);
                    }
                    $circularStampInfo->save();
                });
                // copy current circular text info  @todo
                $objCircularTextInfo->map(function ($item, $key) use ($intCurrentID, $arrCircularDocumentIDs, $arrCircularOperationHistoryID) {
                    $circularTextInfo = $item->replicate();
                    $circularTextInfo->circular_document_id = $arrCircularDocumentIDs[$circularTextInfo->circular_document_id];
                    $circularTextInfo->circular_operation_id = $arrCircularOperationHistoryID[$item->circular_operation_id];
                    $circularTextInfo->save();
                });
                
                // copy current circular DocumentCommentInfos
                $objDocumentCommentInfos->map(function ($item, $key) use ($intCurrentID, $arrCircularDocumentIDs, $arrCircularOperationHistoryID) {
                    $documentCommentInfos = $item->replicate();
                    $documentCommentInfos->circular_document_id = $arrCircularDocumentIDs[$documentCommentInfos->circular_document_id];
                    $documentCommentInfos->circular_operation_id = $arrCircularOperationHistoryID[$item->circular_operation_id];
                    $documentCommentInfos->save();
                });

                $objLoginUser = CircularUser::where("circular_id", $circular_id)
                    ->where("parent_send_order", 0)
                    ->where('child_send_order', 0)
                    ->first();
                $this->handlerCircularUserInsert($objLoginUser, $sendUser, $intCurrentID);
                
                DB::commit();
                $objRequest['title'] = sprintf("「%s_%s」回覧ID%d",$strTextTitle,$sendUser['email'],$sendUserKey + 1);
                $objRequest['circular_id'] = $intCurrentID;
                \Artisan::call("circular:SendAllUserCircularToFirstComtinue", [
                    'circular_id' => $intCurrentID,
                    'params' => json_encode($objRequest),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::channel('cron-daily')->warning($e->getMessage() . $e->getTraceAsString());
            }
        }

        DB::beginTransaction();
        try {
            CircularUser::where("circular_id", $circular_id)
                ->whereNotIn('id', [$objLoginUser->id, $arrLastUser['id']])->delete();
            CircularUser::where("circular_id", $circular_id)
                ->where('id', $arrLastUser['id'])
                ->update($arrLastUser['edition_flg'] != config('app.edition_flg')
                || $arrLastUser['env_flg'] != config('app.server_env')
                || $arrLastUser['server_flg'] != config('app.server_flg') ? [
                    'parent_send_order' => 1,
                    'child_send_order' => 1,
                ] : ['parent_send_order' => 0, 'child_send_order' => 1]);
            DB::commit();

            $objRequest['circular_id'] = $circular_id;
            $objRequest['title'] = sprintf("「%s_%s」回覧ID%d",$strTextTitle,$arrLastUser['email'],$intAllUserCount);
            \Artisan::call("circular:SendAllUserCircularToFirstComtinue", [
                'circular_id' => $circular_id,
                'params' => json_encode($objRequest),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('cron-daily')->warning($e->getMessage() . $e->getTraceAsString());
        }
        Log::channel('cron-daily')->warning("END");
    }

    private function handlerCircularUserInsert($objLoginUser, $arrUserInfo, $intCircularID)
    {

        $arrUserInfo = CircularUser::where("id", $arrUserInfo['id'])->first();
        $objCurrentUser = $arrUserInfo->replicate();
        $objFirstUser = $objLoginUser->replicate();
        $objFirstUser->circular_id = $intCircularID;
        $objFirstUser->save();
        $objCurrentUser->circular_id = $intCircularID;
        if (
            $objCurrentUser->edition_flg != config('app.edition_flg')
            || $objCurrentUser->env_flg != config('app.server_env')
            || $objCurrentUser->server_flg != config('app.server_flg')
        ) {
            $objCurrentUser->parent_send_order = 1;
            $objCurrentUser->child_send_order = 1;
        } else {
            $objCurrentUser->parent_send_order = 0;
            $objCurrentUser->child_send_order = 1;
        }
        $objCurrentUser->save();
    }


    /**
     * copy AllAttachment
     * @param $intCircularID
     * @param $finishedDate
     * @return false|int|string
     */
    private function copyCurrentCircularAllAttachment($intCircularID, $arrAttachmentIDS, $objLoginUser)
    {
        if(empty($arrAttachmentIDS)){
            Log::info("fuck this is");
            return ;
        }

        try {

            $folderPath = config('app.server_env') ? config('app.k5_storage_attachment_root_folder') : config('app.s3_storage_attachment_root_folder');

            $arrAllAttachment = DB::table("circular_attachment")->whereIn("id", $arrAttachmentIDS)->get();


            foreach ($arrAllAttachment as $item) {

                $server_url = sprintf("%s/%s/%s/%s", $folderPath, $objLoginUser->edition_flg . $objLoginUser->env_flg . $objLoginUser->server_flg, $objLoginUser->mst_company_id, $item->circular_id);
                $server_url = config('filesystems.prefix_path') . '/' .$server_url;
                $isDirectory = Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5' : 's3')->exists($server_url);

                if (!$isDirectory) {
                    Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5' : 's3')->makeDirectory($server_url);
                }

                // Get this file name
                $file_name = $item->id . '_' . substr(md5(time()), 0, 8) . '.' . substr(strrchr($item->file_name, '.'), 1);

                $file_data = chunk_split(base64_encode(Storage::disk(config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5 ? 'k5' : 's3')->get($item->server_url)));

                // decode
                $file_data = base64_decode($file_data);
                // K5 SERVER
                if (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5) {
                    Storage::disk('k5')->putfileAs($server_url, $file_data, $file_name);
                }
                // AWS SERVER
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                    Storage::disk('s3')->put($server_url . '/' . $file_name, $file_data, 'pub');
                }
                $item->server_url = $server_url . '/' . $file_name;
                DB::table("circular_attachment")->where("id",$item->id)->update([
                    'server_url' => $server_url . '/' . $file_name
                ]);
            }
        } catch (\Exception $ex) {
            Log::channel('cron-daily')->warning("message :" . $ex->getMessage() . " Line :" . $ex->getLine() . " Line :" . $ex->getFile());
        }

    }
}
