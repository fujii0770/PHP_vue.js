<?php

namespace App\Http\Controllers\API;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\EnvApiUtils;
use App\Jobs\StoreAttachmentToS3;
use App\Models\CircularAttachment;
use App\Http\Controllers\AppBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CircularAttachmentAPIController extends AppBaseController
{

    private $model;

    public function __construct(CircularAttachment $circularAttachment)
    {
        $this->model = $circularAttachment;
    }

    /**
     * 添付ファイルの有効データをすべて取得します。
     * @param $circular_id int 回覧ID
     * @param Request $request
     * @return mixed 添付ファイルのデータ
     */
    public function show(int $circular_id, Request $request){

        if(isset($request['usingHash']) && $request['usingHash']) {
            if (isset($request['current_circular_user']) && $request['current_circular_user'] != null){
                $user = $request['current_circular_user'];
                $edition_flg = $user->edition_flg;
                $env_flg = $user->env_flg;
                $server_flg = $user->server_flg;
            }else{
                $user = $request['current_viewing_user'];
                $edition_flg = $request['current_edition_flg'];
                $env_flg = $request['current_env_flg'];
                $server_flg = $request['current_server_flg'];
            }
        }else{
            $user = $request->user();
            $edition_flg = config('app.edition_flg');
            $env_flg = config('app.server_env');
            $server_flg = config('app.server_flg');
        }
        try{
            $mst_company_id = $user->mst_company_id;

            $attachments = DB::table('circular_attachment')
                ->where('circular_id',$circular_id)
                ->where(function ($query) use($mst_company_id,$edition_flg,$env_flg,$server_flg){
                    $query->where('confidential_flg',CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_FALSE);
                    $query->orWhere(function ($query1) use($mst_company_id,$edition_flg,$env_flg,$server_flg){
                        $query1->where('confidential_flg',CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_TRUE)
                            ->where('create_company_id',$mst_company_id)
                            ->where('edition_flg',$edition_flg)
                            ->where('env_flg',$env_flg)
                            ->where('server_flg',$server_flg);
                    });
                })
                ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                ->select('id','circular_id','confidential_flg','file_name','create_user_id','create_company_id','status','create_user','create_at')
                ->get();

        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.attachment_request.get_data'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
        return $this->sendResponse($attachments,__('message.success.attachment_request.get_data'));
    }

    /**
     * アップロードの添付ファイルを保存します。
     * @param Request $request 添付ファイルのデータをアップロードします。
     * @return mixed
     */
    public function store(Request $request){
        try{

            if ($request['usingHash']) {
                $user = $request['user'];
                $user_name = $request['current_name'];
                if ($request['current_circular_user']) {
                    $edition_flg = $request['current_circular_user']->edition_flg;
                    $env_flg = $request['current_circular_user']->env_flg;
                    $server_flg = $request['current_circular_user']->server_flg;
                }
            } else {
                $user = $request->user();
                $user_name = $user->getFullName();
                $edition_flg = config('app.edition_flg');
                $env_flg = config('app.server_env');
                $server_flg = config('app.server_flg');
            }
            if (!$user->id){
                $mst_user_id = $request['current_circular_user']->mst_user_id;
                $mst_company_id = $request['current_circular_user']->mst_company_id;
            }else{
                $mst_user_id = $user->id;
                $mst_company_id = $user->mst_company_id;
            }
            $circular_id = $request['circular_id'];
            $circular_user = DB::table('circular_user')
                ->where('circular_id',$circular_id)
                ->where('parent_send_order',0)
                ->where('child_send_order',0)
                ->first();

            if (!$circular_user){
                $create_company_id = $user->mst_company_id;
                $create_user_id = $user->id;
                $title = '';
            }else{
                $create_company_id = $circular_user->mst_company_id;
                $create_user_id = $circular_user->mst_user_id;
                $title = $circular_user->title;
            }

            $constraints = DB::table('mst_constraints')
                ->select('max_total_attachment_size','max_attachment_count','max_attachment_size')
                ->where('mst_company_id',$create_company_id)
                ->first();

            //携帯アプリへの添付ファイル アップロード
            if ($request->has('upload_type') && $request->get('upload_type') == 'app'){
                $file_name = $request->get('file_name');
                $file_content = $request->get('file');
                $uniquePath =  Carbon::now()->format('Y/m/d/') . config('app.edition_flg') . config('app.server_env') . config('app.server_flg') . '/' . $mst_company_id . '/';
                if (strrpos($file_name, '.')){
                    $ext= strtolower(substr($file_name,strrpos($file_name, '.')+1,strlen($file_name)));
                    $stored_path = 'app/attachmentUploads/'.$uniquePath . AppUtils::getUnique() . '.'.$ext;
                }else{
                    $stored_path = 'app/attachmentUploads/'.$uniquePath . AppUtils::getUnique();
                }

                if (!File::exists(storage_path("app/attachmentUploads/$uniquePath"))){
                    File::makeDirectory(storage_path("app/attachmentUploads/$uniquePath"), 0777, true);
                }
                $server_url = storage_path($stored_path);
                file_put_contents($server_url, base64_decode($file_content));
                // ファイルサイズチェック
                $file_size = filesize($server_url);
                if ($file_size > $constraints->max_attachment_size * 1024 * 1024){
                    return $this->sendError(__('upload_attachment_upper_limit',['file_max_attachment_size' => $constraints->max_attachment_size]),Response::HTTP_FORBIDDEN);
                }
            }else{
                $file_name = $request['file_name'];
                $file_size = $request['file_size'];
                $server_url = $request['server_url'];
            }

            // 企業の使用容量取得（前日夜間バッチ算出したもの）
            $disk_usage_situation = DB::table('usage_situation_detail')
                ->select('storage_sum_re')
                ->where('mst_company_id', $create_company_id)
                ->whereNull('guest_company_id')
                ->orderBy('target_date','desc')
                ->first();
            if($disk_usage_situation){
                $storage_size = $disk_usage_situation->storage_sum_re;
            }else{
                $storage_size = 0;
            }

            // ユーザー数
            $user_valid_num = DB::table('mst_user')
                ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                ->where('mst_company_id', $create_company_id)
                ->where(function ($query) use ($create_company_id){
                    if(config('app.fujitsu_company_id') && config('app.fujitsu_company_id') == $create_company_id){
                        // 富士通(K5)場合、
                        // 有効でパスワードが設定してあるユーザー
                        $query->whereNotNull('password_change_date');
                    }
                    $query->whereIn('state_flg',[AppUtils::STATE_VALID]);
                })
                ->where(function ($query) {
                    $query->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                        ->orWhere(function ($query){
                        $query->where('mst_user.option_flg', AppUtils::USER_OPTION)
                            ->where('mst_user_info.gw_flg', 1);
                    });
                })
                ->count();
            $company = DB::table('mst_company')->where('id',$create_company_id)->first();
            // 容量チェック（バッチでの計算値：MB）
            if( ($storage_size > ($user_valid_num + $company->add_file_limit) * 1024 )) {
                $size = $user_valid_num + $company->add_file_limit. " GB";
                return $this->sendError(__('message.warning.attachment_request.storage_upper_limit',['size' => $size]),Response::HTTP_FORBIDDEN);
            }

            $attachment_num = DB::table('circular_attachment')
                ->where('circular_id',$circular_id)
                ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                ->count();


            $max_attachment_count = $constraints->max_attachment_count;
            $max_total_attachment_size = $constraints->max_total_attachment_size;

            if ($attachment_num >= $max_attachment_count){
                return $this->sendError(__('message.warning.attachment_request.upload_attachment_count_max',['max_attachment_count' => $max_attachment_count]),Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $attachment_total_size = DB::table('circular_attachment')
                ->select(DB::raw(' SUM(file_size) as total_size'))
                ->where('create_company_id',$user->mst_company_id)
                ->where('edition_flg',$edition_flg)
                ->where('env_flg',$env_flg)
                ->where('server_flg',$server_flg)
                ->where('circular_id',$circular_id)
                ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
                ->value('total_size');

            if (($attachment_total_size+$request['file_size']) >= ($max_total_attachment_size * 1024 * 1024 * 1024)){
                return $this->sendError(__('message.warning.attachment_request.upload_attachment_size_max',['max_total_attachment_size' => $max_total_attachment_size]),Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $attachment = [
                'circular_id' => $circular_id,
                'confidential_flg' => CircularAttachmentUtils::ATTACHMENT_CONFIDENTIAL_TRUE,
                'file_name' => $file_name,
                'file_size' => $file_size,
                'create_user_id' => $mst_user_id,
                'create_company_id' => $mst_company_id,
                'server_url' => $server_url,
                'status' => CircularAttachmentUtils::ATTACHMENT_NOT_CHECK_STATUS,
                'edition_flg' => $edition_flg,
                'env_flg' => $env_flg,
                'server_flg' => $server_flg,
                'apply_user_id' => $create_user_id,
                'name' => $user_name,
                'title' => $title,
                'create_at' => Carbon::now(),
                'create_user' =>$user->email
            ];

            $attachment_id = DB::table('circular_attachment')->insertGetId($attachment);

            $job = (new StoreAttachmentToS3($attachment_id))
                ->onConnection('database');
            dispatch($job);
            $attachment['server_url'] = null;
            return $this->sendResponse([ 'circular_attachment_id'=> $attachment_id,'circular_attachment' => $attachment],__('message.success.attachment_request.store_attachment'));
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.attachment_request.store_attachment'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 保存済みの添付ファイルを削除（倫理削除）
     * @param Request $request
     * @return mixed
     */
    public function destroy(Request $request){
        if (isset($request['usingHash']) && $request['usingHash']) {
            $user = $request['user'];
            $edition_flg = $request['current_edition_flg'];
            $env_flg = $request['current_env_flg'];
            $server_flg = $request['current_server_flg'];
            if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                $mst_company_id = $request['current_circular_user']->mst_company_id;
            } else {
                $mst_company_id = $request['current_viewing_user']->mst_company_id;
            }
        } else {
            $user = $request->user();
            $edition_flg = config('app.edition_flg');
            $env_flg = config('app.server_env');
            $server_flg = config('app.server_flg');
            $mst_company_id = $user->mst_company_id;
        }

        $circular_attachment_id = $request['circular_attachment_id'];

        $attachment = DB::table('circular_attachment')
            ->where('id',$circular_attachment_id)
            ->first();

        if (empty($attachment)){
            return $this->sendError(__('message.false.attachment_request.delete_attachment'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // PAC_5-2995認可制御の不備対応案
        if ($mst_company_id != $attachment->create_company_id || $edition_flg != $attachment->edition_flg || $env_flg != $attachment->env_flg
            || $server_flg != $attachment->server_flg) {
            return $this->sendError(__('message.warning.attachment_request.not_exist'), Response::HTTP_UNAUTHORIZED);
        }

        try{
            if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_CHECK_SUCCESS_STATUS){
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS){
                    Storage::disk('s3')->delete($attachment->server_url);
                }elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5){
                    Storage::disk('k5')->delete($attachment->server_url);
                }
            }
            DB::table('circular_attachment')
                ->where('id',$circular_attachment_id)
                ->update([
                    'status' => CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS,
                    'update_user' => $user->email,
                    'update_at' =>Carbon::now()
                ]);
            return $this->sendResponse('', __('message.success.attachment_request.delete_attachment'));
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.attachment_request.delete_attachment'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 添付ファイルの「社外秘に設定」を修正します。
     * @param Request $request
     * @return mixed
     */
//    public function changeAttachmentConfidentialFlg(Request $request){
//
//        if ($request['usingHash']) {
//            $user = $request['user'];
//        } else {
//            $user = $request->user();
//        }
//
//        try{
//            $attachment = $this->model->find($request['circular_attachment_id']);
//            $attachment->confidential_flg = $request['confidentialFlg'];
//            $attachment->update_user = $user->email;
//            $attachment->save();
//
//        }catch (\Exception $ex){
//            Log::error($ex->getMessage().$ex->getTraceAsString());
//            return $this->sendError(__('message.false.attachment_request.chang_confidential_flg'), Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }

    /**
     * 添付ファイルをダウンロードします。
     * @param Request $request
     * @return mixed
     */
    public function download(Request $request){

        if (!isset($request['circular_attachment_id']) && empty($request['circular_attachment_id'])){
            return $this->sendError(__('message.false.attachment_request.download_attachment'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request['usingHash']) && $request['usingHash']) {
            $user = $request['user'];
            $edition_flg = $request['current_edition_flg'];
            $env_flg = $request['current_env_flg'];
            $server_flg = $request['current_server_flg'];
            if (isset($request['current_circular_user']) && $request['current_circular_user'] != null) {
                $mst_company_id = $request['current_circular_user']->mst_company_id;
            } else {
                $mst_company_id = $request['current_viewing_user']->mst_company_id;
            }
        } else {
            $user = $request->user();
            $edition_flg = config('app.edition_flg');
            $env_flg = config('app.server_env');
            $server_flg = config('app.server_flg');
            $mst_company_id = $user->mst_company_id;
        }

        $attachment_id = $request['circular_attachment_id'];

        try{

            $attachment = DB::table('circular_attachment')
                ->where('id',$attachment_id)
                ->first();

            if (!$attachment){
                return $this->sendError(__('message.false.attachment_request.download_attachment'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // PAC_5-2995認可制御の不備対応案
            if ($mst_company_id != $attachment->create_company_id || $edition_flg != $attachment->edition_flg || $env_flg != $attachment->env_flg
                || $server_flg != $attachment->server_flg) {
                return $this->sendError(__('message.warning.attachment_request.not_exist'), Response::HTTP_UNAUTHORIZED);
            }

            if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_NOT_CHECK_STATUS){
                return $this->sendError(__('message.false.attachment_request.is_checking'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }else if ($attachment->status == CircularAttachmentUtils::ATTACHMENT_CHECK_FAIL_STATUS){
                return $this->sendError(__('message.false.attachment_request.check_fail'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            //携帯アプリへの添付ファイル ダウンロード
            if ($request->has('download_type') && $request->get('download_type') == 'app'){
                $file_data = null;
                if (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_AWS){
                    $file_data = chunk_split(base64_encode(Storage::disk('s3')->get($attachment->server_url)));
                }elseif (config('app.server_env') == CircularAttachmentUtils::ENV_FLG_K5){
                    $file_data = chunk_split(base64_encode(Storage::disk('k5')->get($attachment->server_url)));
                }
                return $this->sendResponse(['circular_attachment_id' => $attachment_id,'file_data' => $file_data ,'file_name' => $attachment->file_name],__('message.success.attachment_request.download_attachment'));
            }

            return $this->sendResponse($attachment, __('message.success.attachment_request.download_attachment'));
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.attachment_request.download_attachment'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
