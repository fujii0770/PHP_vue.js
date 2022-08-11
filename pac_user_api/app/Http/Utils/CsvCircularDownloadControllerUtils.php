<?php

namespace App\Http\Utils;

use DB;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\CircularDocumentUtils;
use App\Http\Utils\StatusCodeUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\DownloadRequestUtils;
use App\Http\Utils\CommonUtils;

/**
 * Class CsvCircularDownloadControllerUtils
 * @package App\Http\Utils
 */
class CsvCircularDownloadControllerUtils
{
    /**
     * 非同期ダウンロード用回覧完了テンプレートダウンロードファイル取得
     *
     * @param $param
     * @param $user
     * @param $download_req_id
     * @return string
     */
    public static function getCompletedCircularData($user, $params, $dl_request_id)
    {
        // ダウンロード要求情報取得
        $dl_req = DB::table('download_request')
            ->where('id', $dl_request_id)->first();
        
        $user_info = DB::table('mst_user')
            ->where('id', $dl_req->mst_user_id)
            ->select(['id', 'email', 'mst_company_id'])
            ->first();
        $selected_ids = $params['selected_ids'] ?? [];
        $kind = $params['kind'] ?? '';
        $filename = CircularDocumentUtils::charactersReplace($params['filename'] ?? '');
        $senderName = $params['senderName'] ?? '';
        $senderEmail = $params['senderEmail'] ?? '';
        $destEnv = $params['destEnv'] ?? '';
        $fromdate = $params['fromdate'] ?? '';
        $todate = $params['todate'] ?? '';
        $receiverName = $params['receiverName'] ?? '';
        $receiverEmail = $params['receiverEmail'] ?? '';
        $orderBy = $params['orderBy'] ?? 'update_at';
        $orderBy = empty($orderBy) ? 'update_at' : $orderBy;
        $orderDir = $params['orderDir'] ?? 'DESC';
        $orderDir = empty($orderDir) ? 'DESC' : $orderDir;
        $orderDir = AppUtils::normalizeOrderDir($orderDir);
        $keyword = CircularDocumentUtils::charactersReplace($params['keyword'] ?? '');
        $system_env_flg = config('app.server_env');
        $system_edition_flg = config('app.edition_flg');
        $templateFrom = $params['templateFrom'] ?? '';
        $templateTo = $params['templateTo'] ?? '';
        $templateNum = $params['templateNum'] ?? '';
        $templateText = $params['templateText'] ?? '';
        $useTemplate = false;
        $system_server_flg = config('app.server_flg');
        
        // 回覧完了日時
        $finishedDateKey = $params['finishedDate'] ?? '';
        // 当月
        if (!$finishedDateKey) {
            $finishedDate = '';
        } else {
            $finishedDate = \Illuminate\Support\Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
        }
        
        $arrOrder = ['circular_kind' => 'circular_kind', 'file_names' => 'file_names', 'sender' => 'sender',
            'emails' => 'emails', 'update_at' => 'update_at'];
        $orderBy = !empty($arrOrder[$orderBy]) ? $arrOrder[$orderBy] : 'update_at';
        
        $where = [];
        $where_arg = [];
        $where_temp = [];
        $where_arg_temp = [];
        
        if (!empty($kind) && $kind === '1') {
            $where[] = "(U.email = ? and C.mst_user_id = U.mst_user_id AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?)";
            $where_arg[] = $user->email;
            $where_arg[] = CircularUserUtils::NOT_DELETE;
            $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
            $where_arg[] = $system_edition_flg;
            $where_arg[] = $system_env_flg;
            $where_arg[] = $system_server_flg;
        } else if ($kind === '0') {
            $where[] = "(U.email = ? and C.mst_user_id != U.mst_user_id AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?)";
            $where_arg[] = $user->email;
            $where_arg[] = CircularUserUtils::NOT_DELETE;
            $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
            $where_arg[] = $system_edition_flg;
            $where_arg[] = $system_env_flg;
            $where_arg[] = $system_server_flg;
        } else {
            $where[] = "(U.email = ? AND U.del_flg = ? AND U.circular_status != ? and U.edition_flg = ? and U.env_flg = ? and U.server_flg = ?)";
            $where_arg[] = $user->email;
            $where_arg[] = CircularUserUtils::NOT_DELETE;
            $where_arg[] = CircularUserUtils::NOT_NOTIFY_STATUS;
            $where_arg[] = $system_edition_flg;
            $where_arg[] = $system_env_flg;
            $where_arg[] = $system_server_flg;
        }
        if (!empty($filename)) {
            $where[] = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?))';
            $where_arg[] = "%$filename%";
            $where_arg[] = "%$filename%";
        }
        if (!empty($senderName)) {
            $where[] = 'U.sender_name like ?';
            $where_arg[] = "%$senderName%";
        }
        if (!empty($senderEmail)) {
            $where[] = 'U.sender_email like ?';
            $where_arg[] = "%$senderEmail%";
        }
        if (!empty($receiverName)) {
            $where[] = 'U.receiver_name like ?';
            $where_arg[] = "%$receiverName%";
        }
        if (!empty($receiverEmail)) {
            $where[] = 'U.receiver_email like ?';
            $where_arg[] = "%$receiverEmail%";
        }
        if (!empty($fromdate)) {
            $where[] = 'C.completed_date >= ?';
            $where_arg[] = $fromdate;
        }
        if (!empty($todate)) {
            $where[] = 'C.completed_date < ?';
            $where_arg[] = (new \DateTime($todate))->modify('+1 day')->format('Y-m-d');
        }
        if (!empty($destEnv)) {
            $destenv_flgs = str_split($destEnv);
            $where[] = 'U.edition_flg = ?';
            $where_arg[] = $destenv_flgs[0];
            $where[] = 'U.env_flg = ?';
            $where_arg[] = $destenv_flgs[1];
            $where[] = 'U.server_flg = ?';
            $where_arg[] = $destenv_flgs[2];
        }
        if (!empty($keyword)) {
            $where[] = '(U.title like ? OR ((U.title IS NULL OR trim(U.title)=\'\') and U.receiver_title like ?) OR U.sender_name like ? OR U.receiver_name like ? OR U.sender_email like ? OR U.receiver_email like ?)';
            $where_arg[] = "%$keyword%";
            $where_arg[] = "%$keyword%";
            $where_arg[] = "%$keyword%";
            $where_arg[] = "%$keyword%";
            $where_arg[] = "%$keyword%";
            $where_arg[] = "%$keyword%";
        }
        if (!empty($templateFrom) || !empty($templateTo) || !empty($templateNum) || !empty($templateText)) {
            $useTemplate = true;
        }
        if (!empty($templateFrom)) {
            $where_temp[] = 'date_data >= ?';
            $where_arg_temp[] = $templateFrom;
        }
        if (!empty($templateTo)) {
            $where_temp[] = 'date_data < ?';
            $where_arg_temp[] = $templateTo;
        }
        if (!empty($templateNum)) {
            $where_temp[] = 'num_data = ?';
            $where_arg_temp[] = $templateNum;
        }
        if (!empty($templateText)) {
            $where_temp[] = "text_data like ?";
            $where_arg_temp[] = "%" . $templateText . "%";
        }
        
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            throw new \Exception('Cannot connect to ID App');
        }
        
        $id_app_user_id = 0;
        $response = $client->post("users/checkEmail", [
            RequestOptions::JSON => ['email' => $user->email]
        ]);
        if ($response->getStatusCode() == StatusCodeUtils::HTTP_OK) {
            $resData = json_decode((string)$response->getBody());
            if (!empty($resData) && !empty($resData->data)) {
                $id_app_users = $resData->data;
                // 統合ID返す結果と回覧ユーザー比較、現在の回覧者回覧位置確認
                foreach ($id_app_users as $id_app_user) {
                    if ($user->mst_company_id == $id_app_user->company_id && config('app.edition_flg') == $id_app_user->edition_flg && config('app.server_env') == $id_app_user->env_flg && config('app.server_flg') == $id_app_user->server_flg) {
                        $id_app_user_id = $id_app_user->id;
                        break;
                    }
                }
            }
        }
        
        $query_sub = \Illuminate\Support\Facades\DB::table("circular$finishedDate as C")
            ->join("circular_user$finishedDate as U", function ($join) {
                $join->on('C.id', 'U.circular_id');
                $join->on('U.parent_send_order', DB::raw('0'));
                $join->on('U.child_send_order', DB::raw('0'));
            })
            ->join("circular_document$finishedDate as D", function ($join) use ($user) {
                $join->on('C.id', '=', 'D.circular_id');
                $join->on(function ($condition) use ($user) {
                    $condition->on('confidential_flg', DB::raw('0'));
                    $condition->orOn(function ($condition1) use ($user) {
                        $condition1->on('confidential_flg', DB::raw('1'));
                        $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                    });
                });
                $join->on(function ($condition) use ($user) {
                    $condition->on('origin_document_id', DB::raw('0'));
                    $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                });
            })
            ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as files'))
            ->groupBy(['C.id', 'U.title']);
        $query = \Illuminate\Support\Facades\DB::table("circular$finishedDate as C")
            ->join("circular_user$finishedDate as U", function ($join) use ($user) {
                $join->on('C.id', 'U.circular_id')
                    ->on('U.mst_company_id', DB::raw($user->mst_company_id));
            })
            ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                $query->on('C.id', 'auto_his.circular_id')
                    ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
            })
            ->leftJoinSub($query_sub, 'D', function ($join) {
                $join->on('C.id', '=', 'D.id');
            })
            ->selectRaw('C.id, CASE WHEN C.mst_user_id = ' . $user->id .
                ' and C.edition_flg = ' . config('app.edition_flg') . ' and C.env_flg = ' . config('app.server_env') . ' and C.server_flg = ' . config('app.server_flg') . ' THEN 1 ELSE 0 END AS circular_kind,
            CONCAT(C.edition_flg, C.env_flg, C.server_flg) as sender_env,C.completed_date as update_at, C.circular_status, U.title as file_names,
            U.receiver_title as d_file_names,CONCAT(U.sender_name, \' &lt;\',U.sender_email, \'&gt;\') as sender, U.receiver_name_email AS emails,
            U.sender_name as sender_name, D.files')
            ->whereRaw(implode(" AND ", $where), $where_arg);
        //PAC_5-2114 Start
        $data = $query->where(function ($query) use ($kind, $user, $id_app_user_id) {
            if (!empty($kind) && $kind === '1') {
                $query->where('U.mst_user_id', $user->id);
            } else {
                $query->whereIn('U.mst_user_id', array_filter([$user->id, $id_app_user_id]));
            }
        })
            // PAC_5-2114 End
            ->whereNotNull("C.completed_date")
            // PAC_5-1664:回覧破棄をすると削除ステータスになり、利用者からは見えないくなる    期待した結果  完了一覧にはいる
            ->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS, CircularUtils::DELETE_STATUS]);
        
        // 当月又は上月コピーなし条件追加
        if (0 == $finishedDateKey || null == $finishedDateKey) {
            $data->whereRaw("DATE_FORMAT( C.completed_date, '%Y%m' ) = " . date('Ym'));
        }
        
        if ($useTemplate) {
            $idByTemplates = DB::table('template_input_data')
                ->select('circular_id')
                ->whereRaw(implode(" AND ", $where_temp), $where_arg_temp)
                ->distinct()
                ->get();
            
            $ids = array();
            foreach ($idByTemplates as $value) {
                $ids[] = $value->circular_id;
            }
            $data->whereIn('C.id', $ids);
        }
        
        if (!empty($selected_ids)) {
            $selected_ids = !is_array($selected_ids) ? (array)$selected_ids : $selected_ids;
            $data->whereIn('C.id', $selected_ids);
        }
        $data = $data->groupByRaw('C.id, U.sender_name, U.sender_email, U.title, U.receiver_title, U.receiver_name_email, result, D.files')
            ->orderBy($orderBy, $orderDir)
            ->get()
            ->toArray();
        
        // 状態更新 ( 処理待ち:0 => 作成中:1)
        DB::table('download_request')
            ->where('id', $dl_request_id)
            ->update([
                'state' => DownloadRequestUtils::REQUEST_CREATING
            ]);
        $dir = '/var/www/pac/pac_user_api/storage/csv/';
        $csv_path = '/var/www/pac/pac_user_api/storage/csv/' . $dl_req->file_name;
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
        $stream = fopen($csv_path, 'w');
        
        fwrite($stream, pack('C*', 0xEF, 0xBB, 0xBF));
        foreach ($data as $item) {
            if (!$item->file_names || trim($item->file_names, ' ') == '') {
                $fileNames = explode(', ', $item->d_file_names);
                $item->file_names = mb_strlen(reset($fileNames)) > 100 ? mb_substr(reset($fileNames), 0, 100) : reset($fileNames);
            }
            if (strpos($item->sender, '&lt;') !== false || strpos($item->sender, '&gt;') !== false) {
                $item->sender = CommonUtils::replaceCharacter($item->sender);
            }
            if (strpos($item->emails, '&lt;') !== false || strpos($item->emails, '&gt;') !== false) {
                $item->emails = CommonUtils::replaceCharacter($item->emails);
            }
            $item->emails = str_replace('<br />', PHP_EOL, $item->emails);
            $item->files = str_replace(', ', PHP_EOL, $item->files);
            
            $row = [
                $item->sender,
                $item->file_names,
                $item->files,
                $item->emails
            ];
            
            fputcsv($stream, $row);
        }
        
        fclose($stream);
        
        //ダウンロードデータDB保存
        $csv_data = \file_get_contents($csv_path);
        
        // 無害化サーバで無害化処理するか
        $isSanitizing = DB::table('mst_company')
            ->where('id', $dl_req->mst_company_id)->first()
            ->sanitizing_flg;
        if ($isSanitizing == 1) {
            // 状態更新 ( 作成中:1 => 無害化待ち:11)
            $state = DownloadRequestUtils::REQUEST_SANITIZING_WAIT;
        } else {
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
        if ($isSanitizing != 1) {
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
}
