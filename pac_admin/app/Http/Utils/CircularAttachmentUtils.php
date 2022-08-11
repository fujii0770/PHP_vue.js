<?php
/**
 * Created by PhpStorm.
 * User: bmc33
 * Date: 2021/6/4
 * Time: 10:03
 */

namespace App\Http\Utils;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CircularAttachmentUtils
{

    const ENV_FLG_AWS = 0;
    const ENV_FLG_K5 = 1;
    //status
    const ATTACHMENT_NOT_CHECK_STATUS = 0; // 未ウイルススキャン
    const ATTACHMENT_CHECK_SUCCESS_STATUS = 1; // ウイルススキャン完了
    const ATTACHMENT_CHECK_FAIL_STATUS = 2; // ウイルスのスキャンに失敗しました
    const ATTACHMENT_DELETE_STATUS = 9; // ファイルの削除

    //confidential_flg 社外秘に設定
    const ATTACHMENT_CONFIDENTIAL_FALSE = 0;//無効
    const ATTACHMENT_CONFIDENTIAL_TRUE = 1;//有効

    /**
     * 添付ファイルダウンロードファイルデータ取得
     * @param $cids array 回覧ID
     * @param $user
     */
    public static function getCircularAttachmentData($cid){

        $attachment = DB::table('circular_attachment')
            ->whereIn('id',$cid)
            ->first();

        if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
            $data =  Storage::disk('s3')->get($attachment->server_url);
        }else if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
            $data =  Storage::disk('k5')->get($attachment->server_url);
        }

        return $data;
    }

    /**
     * 回覧中のすべての添付ファイルを削除します。
     * @param $cids array 回覧ID
     * @param $user
     */
    public static function deleteAttachments($cids){
        $attachments = DB::table('circular_attachment')
            ->whereIn('circular_id',$cids)
            ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
            ->get();
        if ($attachments){
            foreach ($attachments as $attachment){
                if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
                    if (Storage::disk('s3')->exists($attachment->server_url)){
                        Storage::disk('s3')->delete($attachment->server_url);
                    }
                }else if(config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                    if (Storage::disk('k5')->exists($attachment->server_url)){
                        Storage::disk('k5')->delete($attachment->server_url);
                    }
                }
            }

            DB::table('circular_attachment')
                ->whereIn('circular_id',$cids)
                ->update([
                    'status' => CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS,
                    'update_at' => Carbon::now()
                ]);
        }
    }

    /**
     * 回覧中のすべての添付ファイルを削除します、削除データ。
     * @param $cids array 回覧ID
     */
    public static function deleteAbsoluteAttachments($cids){
        $attachments = DB::table('circular_attachment')
            ->whereIn('circular_id',$cids)
            ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
            ->get();

        if ($attachments){
            foreach ($attachments as $attachment){
                if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
                    if (Storage::disk('s3')->exists($attachment->server_url)){
                        Storage::disk('s3')->delete($attachment->server_url);
                    }
                }else if(config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
                    if (Storage::disk('k5')->exists($attachment->server_url)){
                        Storage::disk('k5')->delete($attachment->server_url);
                    }
                }
            }
        }
        DB::table('circular_attachment')->whereIn('circular_id',$cids)->delete();
    }
}