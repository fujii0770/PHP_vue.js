<?php
namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EpsTAppFilesUtils
{
    /**
     * 添付ファイルダウンロードファイルデータ取得
     * @param $cids array
     * @param $user
     */
    public static function getEpsTAppFileData($cid){

        $eps_t_app_files = DB::table('eps_t_app_files')
            ->where('id',$cid)
            ->first();

        if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_AWS){
            $data =  Storage::disk('s3')->get($eps_t_app_files->saved_file_name);
        }else if (config('app.pac_app_env') == EnvApiUtils::ENV_FLG_K5){
            $data =  Storage::disk('k5')->get($eps_t_app_files->saved_file_name);
        }

        return $data;
    }
}