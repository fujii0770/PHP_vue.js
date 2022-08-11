<?php

namespace App\Http\Controllers\API\Portal\GroupWare;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AdvertisementUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\ContactUtils;
use App\Http\Utils\EnvApiUtils;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class DiskMailFileAPIController
 * @package App\Http\Controllers\API
 */
class DiskMailFileAPIController extends AppBaseController
{
    /**
     * ファイル登録
     * @param Request $request
     * @return mixed
     */
    public function storeMailFile(Request $request)
    {
        try {
            $user = $request->user();
            $mst_user_id = $user->id;
            $mst_company_id = $user->mst_company_id;
            $disk_mail_id = $request['disk_mail_id'];
            $file_name = $request['file_name'];
            $file_size = $request['file_size'];
            $server_url = $request['server_url'];

            // 企業の使用容量取得（前日夜間バッチ算出したもの）
            $disk_usage_situation = DB::table('usage_situation_detail')
                ->select('storage_convenient_file_re')
                ->where('mst_company_id', $mst_company_id)
                ->whereNull('guest_company_id')
                ->orderBy('target_date', 'desc')
                ->first();
            $storage_size = $disk_usage_situation ? $disk_usage_situation->storage_convenient_file_re : 0;

            // ユーザー数
            $user_valid_num = DB::table('mst_user')
                ->join('mst_user_info', 'mst_user.id', '=', 'mst_user_info.mst_user_id')
                ->where('mst_company_id', $mst_company_id)
                ->where(function ($query) use ($mst_company_id) {
                    if (config('app.fujitsu_company_id') && config('app.fujitsu_company_id') == $mst_company_id) {
                        $query->whereNotNull('password_change_date');
                    }
                    $query->whereIn('state_flg', [AppUtils::STATE_VALID]);
                })
                ->where(function ($query) {
                    $query->where('mst_user.option_flg', AppUtils::USER_NORMAL)
                        ->orWhere(function ($query){
                            $query->where('mst_user.option_flg', AppUtils::USER_OPTION)
                                ->where('mst_user_info.gw_flg', 1);
                        });
                })
                ->count();
            $company = DB::table('mst_company')->where('id', $mst_company_id)->first();
            // 容量チェック（バッチでの計算値：MB）
            if (($storage_size > ($user_valid_num + $company->add_file_limit) * 1024)) {
                $size = $user_valid_num + $company->add_file_limit . " GB";
                return $this->sendError(__('message.warning.attachment_request.storage_upper_limit', ['size' => $size]), Response::HTTP_FORBIDDEN);
            }

            //ファイル初回登録時
            if (!$disk_mail_id) {
                $disk_mail_id = DB::table('disk_mail')->insertGetId([
                    'mst_user_id' => $mst_user_id,
                    'status' => AppUtils::DISK_MAIL_TEMP_STATUS,
                    'create_at' => Carbon::now(),
                    'create_user' => $user->email,
                ]);
            }

            $disK_mail = DB::table('disk_mail')->where('id', $disk_mail_id)->first();
            if ($disK_mail->create_user != $user->email) {
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }

            $file_count = DB::table('disk_mail_file')
                ->where('disk_mail_id', $disk_mail_id)
                ->select('file_size')
                ->count();
            $constraints = DB::table('mst_constraints')
                ->select('file_mail_size_single', 'file_mail_size_total', 'file_mail_count')
                ->where('mst_company_id', $mst_company_id)
                ->first();
            $max_file_mail_count = $constraints ? $constraints->file_mail_count : 10;
            $max_file_mail_size_total = $constraints ? $constraints->file_mail_size_total : 5;

            // 一回申請最大アップロードファイル数チェック
            if ($file_count >= $max_file_mail_count) {
                return $this->sendError(__('message.warning.attachment_request.upload_attachment_count_max',
                    ['max_attachment_count' => $max_file_mail_count]), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $total_size = DB::table('disk_mail_file as ml')
                ->join('disk_mail as cm', 'ml.disk_mail_id', 'cm.id')
                ->join('mst_user as mu', 'mu.id', 'cm.mst_user_id')
                ->select(DB::raw(' SUM(file_size) as total_size'))
                ->where('mu.mst_company_id', $mst_company_id)
                ->value('total_size');

            // 企業の合計の最大ファイルサイズチェック
            if (($total_size + $request['file_size']) >= ($max_file_mail_size_total * 1024 * 1024 * 1024)) {
                return $this->sendError(__('message.warning.attachment_request.upload_attachment_size_max',
                    ['max_total_attachment_size' => $max_file_mail_size_total]), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            //s3に保存されたファイルパス
            $folder_path = config('filesystems.prefix_path') . '/' . 'disk_mail/' . config('app.edition_flg') . '/' . config('app.server_env') . '/' . config('app.server_flg') . '/' . $user->mst_company_id . '/' . $user->id;
            //s3に保存されたファイル名
            $s3_file_name = $disk_mail_id . '_' . substr(md5(time()), 0, 8) . '.' . substr(strrchr($file_name, '.'), 1);

            if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                Storage::disk('s3')->putfileAs($folder_path, new File($server_url), $s3_file_name);
            } else if (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                Storage::disk('k5')->putfileAs($folder_path, new File($server_url), $s3_file_name);
            }

            $server_url = $folder_path . '/' . $s3_file_name;

            $disk_mail_file = [
                'disk_mail_id' => $disk_mail_id,
                'file_name' => $file_name,
                'file_size' => $file_size,
                'file_url' => $server_url,
                'status' => 1,
                'create_at' => Carbon::now(),
                'create_user' => $user->email
            ];
            $id = DB::table('disk_mail_file')->insertGetId($disk_mail_file);
            $disk_mail_file['file_id'] = $id;
            unset($disk_mail_file['file_url']);
            return $this->sendResponse(['disk_mail_id' => $disk_mail_id, 'disk_mail_file' => $disk_mail_file], 'ファイルアップロード処理に成功しました。');
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.upload'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * アップロード　ファイル削除
     * @param Request $request
     * @return mixed
     */
    public function deleteMailFile(Request $request)
    {
        try {
            $user = $request->user();
            $disk_mail_file_id = $request['file_id'];
            $disk_file = DB::table('disk_mail_file')->select('file_url', 'create_user')->where('id', $disk_mail_file_id)->first();

            if ($disk_file) {
                if ($user->email != $disk_file->create_user) {
                    return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
                }

                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                    Storage::disk('s3')->delete($disk_file->file_url);
                } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                    Storage::disk('k5')->delete($disk_file->file_url);
                }

                DB::table('disk_mail_file')->where('id', $disk_mail_file_id)->delete();
            }

            return $this->sendResponse([], __('message.success.disk_mail.delete'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.delete'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 一覧取得
     * @param Request $request
     * @return mixed
     */
    public function getMailFileList(Request $request)
    {
        try {
            $user = $request->user();


            $limit = AppUtils::normalizeLimit($request->get('limit', 10), 10);
            $data = DB::table('disk_mail as dm')
                ->join('disk_mail_file as dmf', 'dm.id', 'dmf.disk_mail_id')
                ->where('dm.mst_user_id', $user->id)
                ->where('dm.status', '!=', AppUtils::DISK_MAIL_TEMP_STATUS)
                ->select(DB::raw('dm.id,dm.receiver_email, dm.title, dm.applied_date, dm.expiration_date, GROUP_CONCAT(dmf.file_name) AS file_names, dm.access_code,
                 dm.download_limit, dm.download_count'))
                ->groupBy(['dm.id'])
                ->orderBy('dm.id', 'desc')
                ->paginate($limit)
                ->appends(request()->input());
            foreach ($data as $item) {
                $item->id = base64_encode($item->id);
                $item->send_date = Carbon::now()->diffInMinutes(Carbon::parse($item->applied_date)->addMinutes(10), false) > 0 ? '' :
                    Carbon::parse($item->applied_date)->addMinutes(10)->toDateTimeString('minute');
                $item->count = $item->download_limit == -1 ? $item->download_limit : ($item->download_limit - $item->download_count);
                $item->state = $item->count == 0 ? 1 : (Carbon::now()->diffInMinutes(Carbon::parse($item->expiration_date), false) < 0 ? 2 : 0);
            }
            return $this->sendResponse($data, __('message.success.disk_mail.get'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.get'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 送信
     * @param Request $request
     * @return mixed
     */
    public function sendMailFile(Request $request)
    {
        try {
            $user = $request->user();
            $mail_id = $request->get('id');
            if (!$mail_id) {
                return $this->sendError('送信文書を見つかりません。', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $disK_mail = DB::table('disk_mail')->where('id', $mail_id)->first();
            if (!$disK_mail || $disK_mail->create_user != $user->email) {
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            $emails = $request->get('emails');
            $title = $request->get('title');
            $message = $request->get('message');
            $mail_text = explode(PHP_EOL,$message);
            $admin_text = nl2br($message);
            $accessCode = $request->get('accessCode');
            $expire = $request->get('expire');
            $count = $request->get('count');
            $files = $request->get('files');
            $contactsFlg = $request->get('addToContactsFlg');
            $expire_day = $request->get('expire_day');

            $email_to_str = implode(',', array_column($emails, 'email'));

            $download_link = str_replace('site/approval', 'groupware/file_mail/download', CircularUtils::generateApprovalUrl($user->email, config('app.edition_flg'),
                config('app.server_env'), config('app.server_flg'), $mail_id));
            $file_names = [];
            foreach ($files as $file) {
                $file_names[] = $file['file_name'];
            }
            $advertisement = AdvertisementUtils::getDiskMailAdvertisement($user->mst_company_id);
            $data_file_unset = [
                'email' => $user->email,
                'name' => $user->family_name . ' ' . $user->given_name,
                'title' => $title,
                'mail_text' => $mail_text,
                'file_names' => $file_names,
                'file_names_text' => implode('\r\n', $file_names),
                'download_link' => $download_link,
                'mst_company_id' => $user->mst_company_id,
                'top_advertisement' => $advertisement['top_advertisement'],
                'middle_advertisement' => $advertisement['middle_advertisement'],
                'end_advertisement' => $advertisement['end_advertisement']
            ];
            $data_file = $data_file_unset;
            $data_file_unset['mail_text'] = $admin_text;
            unset($data_file_unset['file_names']);
            unset($data_file_unset['top_advertisement']);
            unset($data_file_unset['middle_advertisement']);
            unset($data_file_unset['end_advertisement']);
            $data_access = [
                'title' => $title ?: $file_names[0],
                'access_code' => $accessCode,
            ];

            //アドレス帳に追加
            $arrInsert = [];
            if ($contactsFlg){

                $contacts = DB::table('address')
                    ->where('mst_user_id', $user->id)
                    ->where('type',ContactUtils::TYPE_PERSONAL)
                    ->select(DB::raw('email, id'))
                    ->pluck('id','email');

                foreach ($emails as $email){
                    if (!isset($contacts[$email['email']])){
                        $arrInsert[] = [
                            'name' => '',
                            'email' => $email['email'],
                            'mst_company_id' => $user->mst_company_id,
                            'mst_user_id' => $user->id,
                            'type' => ContactUtils::TYPE_PERSONAL,
                            'state' => ContactUtils::STATE_ENABLE,
                            'create_at' => Carbon::now(),
                            'create_user' => $user->email,
                            'update_at' => Carbon::now(),
                            'update_user' => $user->email
                        ];
                    }
                }
            }

            DB::beginTransaction();

            $mail_file_resume_id = MailUtils::InsertMailSendResume(
                $email_to_str,
                MailUtils::MAIL_DICTIONARY['SEND_DISK_FILE_MAIL']['CODE'],
                json_encode($data_file, JSON_UNESCAPED_UNICODE),
                AppUtils::MAIL_TYPE_USER,
                trans('mail.prefix.user') . trans('mail.SendDiskFileMail.subject', ['email' => $user->email, 'name' => $user->family_name . ' ' . $user->given_name]),
                trans('mail.SendDiskFileMail.body', $data_file_unset), AppUtils::MAIL_STATE_DELAY);

            $access_resume_id = MailUtils::InsertMailSendResume(
                $email_to_str,
                MailUtils::MAIL_DICTIONARY['SEND_DISK_FILE_ACCESS_CODE_MAIL']['CODE'],
                json_encode($data_access, JSON_UNESCAPED_UNICODE),
                AppUtils::MAIL_TYPE_USER,
                trans('mail.prefix.user') . trans('mail.SendDiskFileAccessCodeMail.subject'),
                trans('mail.SendDiskFileAccessCodeMail.body', $data_access), AppUtils::MAIL_STATE_DELAY);

            DB::table('disk_mail')->where('id', $mail_id)
                ->update([
                    'access_code' => $accessCode,
                    'receiver_email' => implode(',', array_column($emails, 'email')),
                    'title' => $title,
                    'message' => $message,
                    'status' => AppUtils::DISK_MAIL_VALID_STATUS,
                    'applied_date' => Carbon::now(),
                    'expiration_date' => Carbon::now()->addDays($expire_day)->addHours($expire)->addMinutes(10),
                    'download_limit' => $count ?: -1,
                    'download_count' => 0,
                    'download_link' => $download_link,
                    'file_mail_resume_id' => $mail_file_resume_id,
                    'access_mail_resume_id' => $access_resume_id,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now(),
                ]);

            if ($contactsFlg) DB::table('address')->insert($arrInsert);

            DB::commit();
            return $this->sendResponse(true, __('message.success.disk_mail.send'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.send'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 一覧画面メール削除
     * @param Request $request
     * @return mixed
     */
    public function deleteMailItem(Request $request)
    {
        try {
            $user = $request->user();
            foreach ($request['mail_id'] as $id_base) {
                $disk_mail_id = base64_decode($id_base);
                $disk_mail = DB::table('disk_mail')->where('id', $disk_mail_id)->first();
                if (!$disk_mail || $disk_mail->create_user != $user->email) {
                    return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
                }
                DB::beginTransaction();
                $disk_files = DB::table('disk_mail_file')->select('file_url')->where('disk_mail_id', $disk_mail_id)->get();
                foreach ($disk_files as $disk_file) {
                    if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                        Storage::disk('s3')->delete($disk_file->file_url);
                    } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                        Storage::disk('k5')->delete($disk_file->file_url);
                    }
                }
                DB::table('mail_send_resume')
                    ->whereIn('id', [$disk_mail->file_mail_resume_id, $disk_mail->access_mail_resume_id])
                    ->where('state', AppUtils::MAIL_STATE_DELAY)
                    ->delete();
                DB::table('disk_mail')->where('id', $disk_mail_id)->delete();
                DB::table('disk_mail_file')->where('disk_mail_id', $disk_mail_id)->delete();
                DB::commit();
            }
            return $this->sendResponse([], __('message.success.disk_mail.delete'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.delete'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ダウンロード
     * @param Request $request
     * @return mixed
     */
    public function mailFileDownload(Request $request)
    {
        try {
            $email = $request->get('email');
            $access_code = $request->get('code');
            $disk_mail_id = $request->get('disk_mail_id');

            $disk_mail = DB::table('disk_mail')
                ->where('id', $disk_mail_id)
                ->where('create_user', $email)
                ->first();
            if ($disk_mail) {
                if ($disk_mail->access_code != $access_code) {
                    return $this->sendError(__('message.warning.disk_mail.access_code'), Response::HTTP_UNAUTHORIZED);
                } elseif ($disk_mail->expiration_date < Carbon::now()) {
                    return $this->sendError(__('message.warning.disk_mail.expiration_date'), Response::HTTP_FORBIDDEN);
                } elseif ($disk_mail->download_limit != -1 && $disk_mail->download_limit <= $disk_mail->download_count) {
                    return $this->sendError(__('message.warning.disk_mail.download_limit'), Response::HTTP_FORBIDDEN);
                }
            } else {
                return $this->sendError(__('message.warning.disk_mail.not_exit'), Response::HTTP_FORBIDDEN);
            }

            $files = DB::table('disk_mail as dm')
                ->join('disk_mail_file as dmf', 'dm.id', 'dmf.disk_mail_id')
                ->where('dm.id', $disk_mail_id)
                ->where('dm.status', AppUtils::DISK_MAIL_VALID_STATUS)
                ->select(DB::raw('dm.title,dmf.file_name, dmf.file_url'))
                ->get();
            if (count($files) < 1) {
                return $this->sendError(__('message.warning.disk_mail.not_exit'), Response::HTTP_FORBIDDEN);
            } elseif (count($files) == 1) {
                $file_name = $files[0]->file_name;
                $contents = base64_encode(Storage::disk('s3')->get($files[0]->file_url));
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                    $contents = base64_encode(Storage::disk('s3')->get($files[0]->file_url));
                } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                    $contents = base64_encode(Storage::disk('k5')->get($files[0]->file_url));
                }
            } else {
                $file_name = $files[0]->title ?: explode('.', $files[0]->file_name)[0] . Carbon::now()->format('Ymd');
                $file_name .= '.zip';
                $path = sys_get_temp_dir() . "/download-disk-file-" . AppUtils::getUniqueName(config('app.edition_flg'), config('app.server_env'), config('app.server_flg'),
                        $disk_mail->mst_user_id, $disk_mail_id) . ".zip";
                $zip = new \ZipArchive();
                $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                foreach ($files as $file) {
                    if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                        $zip->addFromString($file->file_name, Storage::disk('s3')->get($file->file_url));
                    } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                        $zip->addFromString($file->file_name, Storage::disk('k5')->get($file->file_url));
                    }
                }

                $zip->close();
                $contents = \base64_encode(\file_get_contents($path));
            }
            DB::table('disk_mail')->where('id', $disk_mail_id)->update([
                'download_count' => $disk_mail->download_count + 1
            ]);
            return $this->sendResponse(['file_name' => $file_name, 'data' => $contents], __('message.success.disk_mail.download'));

        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.download'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ファイル送信一覧の該当の送信ファイルクリックで送信内容詳細をモーダルで表示
     * @param $mail_id string base 64暗号化後のdisk_mail_id
     * @param Request $request
     * @return mixed
     */
    public function getDiskMailItem($mail_id,Request $request){
        try {
            $user = $request->user();
            $disk_mail_id = base64_decode($mail_id);
            $extend_mail_flg = DB::table('mst_application_companies')
                ->where('mst_application_companies.mst_application_id',AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND)
                ->where('mst_company_id', $user->mst_company_id)
                ->first();
            $disk_mail = DB::table('disk_mail')
                ->select('id','receiver_email','title','message','access_code','create_user','expiration_date','applied_date','download_limit','download_count')
                ->where('id', $disk_mail_id)
                ->first();
            $disk_mail_files = DB::table('disk_mail_file')
                ->select('id','file_name')
                ->where('disk_mail_id',$disk_mail_id)
                ->get()
                ->map(function ($item){
                    $item->id = base64_encode($item->id);
                    return $item;
                });
            $can_download = $extend_mail_flg || ($disk_mail->expiration_date > Carbon::now() && ($disk_mail->download_limit > $disk_mail->download_count || $disk_mail->download_limit == -1));

            if (!$disk_mail || $disk_mail->create_user != $user->email) {
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }

            $day = Carbon::parse($disk_mail->expiration_date)->diffInDays($disk_mail->applied_date);
            $hours = Carbon::parse($disk_mail->expiration_date)->diffInHours($disk_mail->applied_date);
            $data =[
                'id' => base64_encode($disk_mail->id),
                'emails' => explode(',',$disk_mail->receiver_email),
                'title' => $disk_mail->title,
                'message' => $disk_mail->message,
                'file_names' => $disk_mail_files,
                'access_code' => $disk_mail->access_code,
                'download_limit' => $disk_mail->download_limit,
                'expire_day' => $day,
                'expire' =>  $hours - ($day * 24),
                'canDownload' => $can_download,
            ];
            return $this->sendResponse($data, __('message.success.disk_mail.get'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.get'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ファイル送信一覧 ダウンロード
     * @param Request $request
     * @return mixed
     */
    public function downloadItem(Request $request){
        try {

            $disk_mail_id = base64_decode($request->get('disk_mail_id',0));

            $disk_mail_file = DB::table('disk_mail_file')->select('file_url','file_name')->where('id',$disk_mail_id)->first();

            if (!$disk_mail_file){
                return $this->sendError(__('message.warning.disk_mail.not_exit'), Response::HTTP_FORBIDDEN);
            }

            if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                $contents = base64_encode(Storage::disk('s3')->get($disk_mail_file->file_url));
            } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                $contents = base64_encode(Storage::disk('k5')->get($disk_mail_file->file_url));
            }
            return $this->sendResponse(['file_name' => $disk_mail_file->file_name, 'data' => $contents], __('message.success.disk_mail.download'));
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.download'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * テンプレート取得
     * @param Request $request
     * @return mixed
     */
    public function getDiskMailInfo(Request $request)
    {
        try {
            $user = $request->user();
            $info = DB::table('disk_mail_user_info')
                ->where('mst_user_id',$user->id)
                ->first();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.getTemplate'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse($info, __('message.success.disk_mail.getTemplate'));
    }

    /**
     * テンプレート 更新
     * @param Request $request
     * @return mixed
     */
    function updateDiskMailInfo(Request $request){
        $user = $request->user();
        $info = $request->get('info');

        try{
            DB::table('disk_mail_user_info')
                ->where('mst_user_id',$user->id)
                ->update([
                    'update_at'=>Carbon::now()
                    ,'update_user'=>$user->email
                    ,'comment1'=>$info['comment1']
                    ,'comment2'=>$info['comment2']
                    ,'comment3'=>$info['comment3']
                    ,'comment4'=>$info['comment4']
                    ,'comment5'=>$info['comment5']
                    ,'comment6'=>$info['comment6']
                    ,'comment7'=>$info['comment7']
                ]);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.updateTemplate'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse($info, __('message.success.disk_mail.updateTemplate'));
    }

    /**
     * 再送信
     * @param Request $request
     * @return mixed
     */
    public function sendMailFileAgain(Request $request)
    {
        try {
            $user = $request->user();
            $mail_id = base64_decode($request->get('id'));
            if (!$mail_id) {
                return $this->sendError('送信文書を見つかりません。', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $disK_mail = DB::table('disk_mail')->where('id', $mail_id)->first();
            if (!$disK_mail || $disK_mail->create_user != $user->email) {
                return $this->sendError(__('message.warning.not_permission_access'), Response::HTTP_FORBIDDEN);
            }
            $disK_mail_files = DB::table('disk_mail_file')->where('disk_mail_id', $mail_id)->get();
            $title = $disK_mail->title;
            $message = $disK_mail->message;
            $mail_text = explode(PHP_EOL,$message);
            $admin_text = nl2br($message);
            $accessCode = $disK_mail->access_code;
            $count = $disK_mail->download_limit;
            $day = Carbon::parse($disK_mail->expiration_date)->diffInDays($disK_mail->applied_date);
            $hours = Carbon::parse($disK_mail->expiration_date)->diffInHours($disK_mail->applied_date);
            $surplus_hours = $hours - ($day * 24);
            $files = $request->get('file_names');
            $file_names = [];
            foreach ($files as $file) {
                $file_names[] = $file['file_name'];
            }

            $email_to_str = $disK_mail->receiver_email;

            DB::beginTransaction();

            $mail_again_id = DB::table('disk_mail')->insertGetId([
                'mst_user_id' => $user->id,
                'status' => AppUtils::DISK_MAIL_TEMP_STATUS,
                'create_at' => Carbon::now(),
                'create_user' => $user->email,
            ]);

            $download_link = str_replace('site/approval', 'groupware/file_mail/download', CircularUtils::generateApprovalUrl($user->email, config('app.edition_flg'),
                config('app.server_env'), config('app.server_flg'), $mail_again_id));
            $advertisement = AdvertisementUtils::getDiskMailAdvertisement($user->mst_company_id);

            $data_file_unset = [
                'email' => $user->email,
                'name' => $user->family_name . ' ' . $user->given_name,
                'title' => $title,
                'mail_text' => $mail_text,
                'file_names' => $file_names,
                'file_names_text' => implode('\r\n', $file_names),
                'download_link' => $download_link,
                'top_advertisement' => $advertisement['top_advertisement'],
                'middle_advertisement' => $advertisement['middle_advertisement'],
                'end_advertisement' => $advertisement['end_advertisement']
            ];
            $data_file = $data_file_unset;
            $data_file_unset['mail_text'] = $admin_text;
            unset($data_file_unset['file_names']);
            unset($data_file_unset['top_advertisement']);
            unset($data_file_unset['middle_advertisement']);
            unset($data_file_unset['end_advertisement']);
            $data_access = [
                'title' => $title ?: $file_names[0],
                'access_code' => $accessCode,
            ];

            $mail_file_resume_id = MailUtils::InsertMailSendResume(
                $email_to_str,
                MailUtils::MAIL_DICTIONARY['SEND_DISK_FILE_MAIL']['CODE'],
                json_encode($data_file, JSON_UNESCAPED_UNICODE),
                AppUtils::MAIL_TYPE_USER,
                trans('mail.prefix.user') . trans('mail.SendDiskFileMail.subject', ['email' => $user->email, 'name' => $user->family_name . ' ' . $user->given_name]),
                trans('mail.SendDiskFileMail.body', $data_file_unset), AppUtils::MAIL_STATE_DELAY);

            $access_resume_id = MailUtils::InsertMailSendResume(
                $email_to_str,
                MailUtils::MAIL_DICTIONARY['SEND_DISK_FILE_ACCESS_CODE_MAIL']['CODE'],
                json_encode($data_access, JSON_UNESCAPED_UNICODE),
                AppUtils::MAIL_TYPE_USER,
                trans('mail.prefix.user') . trans('mail.SendDiskFileAccessCodeMail.subject'),
                trans('mail.SendDiskFileAccessCodeMail.body', $data_access), AppUtils::MAIL_STATE_DELAY);

            DB::table('disk_mail')->where('id', $mail_again_id)
                ->update([
                    'access_code' => $accessCode,
                    'receiver_email' => $email_to_str,
                    'title' => $title,
                    'message' => $message,
                    'status' => AppUtils::DISK_MAIL_VALID_STATUS,
                    'applied_date' => Carbon::now(),
                    'expiration_date' => Carbon::now()->addDays($day)->addHours($surplus_hours)->addMinutes(10),
                    'download_limit' => $count ?: -1,
                    'download_count' => 0,
                    'download_link' => $download_link,
                    'file_mail_resume_id' => $mail_file_resume_id,
                    'access_mail_resume_id' => $access_resume_id,
                    'update_user' => $user->email,
                    'update_at' => Carbon::now(),
                ]);
            foreach ($disK_mail_files as $disK_mail_file){
                //s3に保存されたファイルパス
                $folder_path = config('filesystems.prefix_path') . '/' . 'disk_mail/' . config('app.edition_flg') . '/' . config('app.server_env') . '/' . config('app.server_flg') . '/' . $user->mst_company_id . '/' . $user->id;
                //s3に保存されたファイル名
                $s3_file_name = $mail_again_id . '_' . substr(md5(time()), 0, 8) . '.' . substr(strrchr($disK_mail_file->file_name, '.'), 1);

                $server_url = $folder_path . '/' . $s3_file_name;

                //s3 copy
                if (config('app.server_env') == EnvApiUtils::ENV_FLG_AWS) {
                    Storage::disk('s3')->copy($disK_mail_file->file_url,$server_url);
                } elseif (config('app.server_env') == EnvApiUtils::ENV_FLG_K5) {
                    Storage::disk('k5')->copy($disK_mail_file->file_url,$server_url);
                }

                DB::table('disk_mail_file')->insert([
                    'disk_mail_id' => $mail_again_id,
                    'file_name' => $disK_mail_file->file_name,
                    'file_size' => $disK_mail_file->file_size,
                    'file_url' => $server_url,
                    'status' => 1,
                    'create_at' => Carbon::now(),
                    'create_user' => $user->email
                ]);
            }
            DB::commit();
            return $this->sendResponse(true, __('message.success.disk_mail.sendAgain'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(__('message.false.disk_mail.sendAgain'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}