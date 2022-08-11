<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\UserAPIController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUserUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\UserApiUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Http\Utils\MailUtils;


class SendReNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'circular:reNotify';

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
        Log::channel('cron-daily')->debug('Run to reNotify');

        try {
            // 再通知対象　（circular：回覧中　circular_user：通知済/未読、既読、引戻し　再通知日 ≦ 当日）
            $query_circulars = DB::table('circular')
                ->leftJoin('circular_user', 'circular.id', '=', 'circular_user.circular_id')
                ->where('circular.edition_flg', config('app.edition_flg'))
                ->where('circular.env_flg', config('app.server_env'))
                ->where('circular.server_flg', config('app.server_flg'))
                ->where('circular.circular_status', CircularUtils::CIRCULATING_STATUS)
                ->where('circular.re_notification_day', '<=', Carbon::now())
                ->whereIn('circular_user.circular_status', [CircularUserUtils::NOTIFIED_UNREAD_STATUS, CircularUserUtils::READ_STATUS, CircularUserUtils::PULL_BACK_TO_USER_STATUS])
                ->select('circular.id','circular.hide_thumbnail_flg','circular.first_page_data','circular.access_code_flg','circular.access_code','circular.outside_access_code_flg',
                    'circular.outside_access_code','circular_user.*');

            $circulars_users = $query_circulars->get();

            $circulars = $circulars_users->keyBy('circular_id');

            // 再通知対象(circular_id)
            $circular_ids = $circulars->keys();

            Log::channel('cron-daily')->debug('circular_ids: '.json_encode($circular_ids));

            // 対象回覧の全ファイル
            $filenames = DB::table('circular_document')
                ->whereIn('circular_id', $circular_ids)
                ->select('circular_id','file_name','confidential_flg','origin_edition_flg','origin_env_flg','origin_server_flg','create_company_id','origin_document_id','parent_send_order')
                ->orderBy('id')
                ->get();

            $arrCircularDoc = [];
            foreach($filenames as $circular_doc ){
                // 各回覧の文書を編集
                $arrCircularDoc[$circular_doc->circular_id][] = $circular_doc;
            }

            // 受信者情報　&　最新メールメッセージ取得
            $circular_users = DB::table('circular_user')
                ->leftJoin('mail_text', function($join){
                    $join->on('circular_user.id', '=', 'mail_text.circular_user_id');
                    $join->on('mail_text.id', '=', DB::raw("(select max(id) from mail_text WHERE mail_text.circular_user_id = circular_user.id)"));
                })
                ->whereIn('circular_id', $circular_ids)
                ->select('circular_id', 'parent_send_order', 'child_send_order', 'name', 'title', DB::raw('mail_text.text as text'))
                ->get();

            foreach ($circulars_users as $index => $circular) {

                // ゲストユーザ又はユーザ有効チェック
                if (!$circular->mst_company_id || !UserApiUtils::checkUser($circular->email, $circular->mst_company_id, $circular->env_flg, $circular->server_flg, $circular->edition_flg)) {
                    continue;
                }

                // 受信者
                $received_user = [
                    'circular_id' => $circular->circular_id,
                    'name' => $circular->name,
                    'email' => $circular->email,
                    'edition_flg' => $circular->edition_flg,
                    'env_flg' => $circular->env_flg,
                    'server_flg' => $circular->server_flg,
                    'mst_company_id' => $circular->mst_company_id,
                    'title' => $circular->title,
                    'parent_send_order' => $circular->parent_send_order,
                    'child_send_order' => $circular->child_send_order
                ];

                // 送信者
                $send_user = $circular_users->first(function ($user) use ($received_user) {
                    if ($user->circular_id == $received_user['circular_id']) {
                        if ($received_user['child_send_order'] > 1) {
                            return $user->parent_send_order == $received_user['parent_send_order'] && ($user->child_send_order == $received_user['child_send_order'] - 1);
                        }else if ($received_user['child_send_order'] == 1) {
                            if ($received_user['parent_send_order'] == 1 || $received_user['parent_send_order'] == 0) {
                                return $user->parent_send_order == 0 && $user->child_send_order == 0;
                            }else{
                                return $user->parent_send_order == $received_user['parent_send_order'] - 1 && $user->child_send_order == 1;
                            }
                        }
                    }
                    return false;
                });

                $user_name = $send_user ? $send_user->name : '';
                $text = $send_user? $send_user->text:'';
                $filterDocuments    = isset($arrCircularDoc[$circular->circular_id]) ? $arrCircularDoc[$circular->circular_id] : [];
                $filterDocuments    = array_filter($filterDocuments, function($item) use($received_user){

                    if ($item->confidential_flg
                        && $item->origin_edition_flg == $received_user['edition_flg']
                        && $item->origin_env_flg == $received_user['env_flg']
                        && $item->origin_server_flg == $received_user['server_flg']
                        && $item->create_company_id == $received_user['mst_company_id']){
                        // 社外秘：origin_document_idが-1固定
                        // 同社メンバー参照可
                        return true;
                    }else if (!$item->confidential_flg
                        && (!$item->origin_document_id || $item->parent_send_order == $received_user['parent_send_order'])){
                        // 社外秘ではない、origin_document_id：UPLOAD時：-1、会社変更時：コピー元ID(parent_send_order：変更後circular_userの値)、回覧終了時：0（他レコード削除）
                        // 回覧終了時：origin_document_id＝0のレコード
                        // 回覧中　時：送信宛先と同じparent_send_orderのレコード
                        return true;
                    }
                    return false;
                });
                $file_name = array_column($filterDocuments, 'file_name');

                $data = [];
                if (!$circular->hide_thumbnail_flg) {
                    $canSeePreview = false;

                    if(isset($arrCircularDoc[$circular->circular_id]) && is_array($arrCircularDoc[$circular->circular_id])){
                        $firstDocument = $arrCircularDoc[$circular->circular_id][0];

                        if ($firstDocument && $firstDocument->confidential_flg
                            && $firstDocument->origin_edition_flg == $received_user['edition_flg']
                            && $firstDocument->origin_env_flg == $received_user['env_flg']
                            && $firstDocument->origin_server_flg == $received_user['server_flg']
                            && $firstDocument->create_company_id == $received_user['mst_company_id']) {
                            // 一ページ目が社外秘　＋　upload会社＝宛先会社
                            $canSeePreview = true;
                        } else if ($firstDocument && !$firstDocument->confidential_flg) {
                            // 一ページ目が社外秘ではない
                            $canSeePreview = true;
                        }
                        if ($canSeePreview && $circular->first_page_data) {
                            $previewPath = AppUtils::getPreviewPagePath($received_user['edition_flg'], $received_user['env_flg'], $received_user['server_flg'], $received_user['mst_company_id'], $circular->id);
                            file_put_contents($previewPath, base64_decode(AppUtils::decrypt($circular->first_page_data)));
                            $data['image_path'] = $previewPath;
                        } else {
                            $data['image_path'] = public_path() . "/images/no-preview.png";
                        }
                    }else{
                        $data['image_path'] = public_path() . "/images/no-preview.png";
                    }
                } else {
                    $data['image_path'] = '';
                }

                // hide_circular_approval_url false:表示 true:非表示
                // ・社外秘ファイルが存在しない場合、「回覧文書を見る」をどのメールでも表示
                // ・社外秘ファイルが存在する場合、社外秘設定ファイルをアップロードした企業：「回覧文書を見る」の表示しない
                $data['hide_circular_approval_url'] = false;
                if(isset($arrCircularDoc[$circular->circular_id]) && is_array($arrCircularDoc[$circular->circular_id])){
                    foreach($arrCircularDoc[$circular->circular_id] as $document){
                        if($document->confidential_flg) {
                            if($document->origin_edition_flg == $received_user['edition_flg']
                                && $document->origin_env_flg == $received_user['env_flg']
                                && $document->origin_server_flg == $received_user['server_flg']
                                && $document->create_company_id == $received_user['mst_company_id']){
                                $data['hide_circular_approval_url'] = true;
                            }
                        }
                    }
                }

                $data['user_name'] = $user_name;
                $data['circular_id'] = $circular->circular_id;
                $title = $circular->title;
                if(!trim($title)) {
                    $title = $file_name[0];
                }
                $data['filenames'] = $file_name;
                if(count($data['filenames'])){
                    $data['filenamestext'] = '';
                    foreach($data['filenames'] as $filename){
                        if ($data['filenamestext'] == '') {
                            $data['filenamestext'] .= $filename;
                            continue;
                        }
                        $data['filenamestext'] .= '\r\n'.'　　　　　　'.$filename;
                    }
                }else{
                    $data['filenamestext'] = '';
                }
                $data['text'] = $text;

                // 件名
                $data['mail_name'] = $received_user['title'];
                if(!trim($received_user['title'])){
                    $data['mail_name'] = $data['filenames'][0];
                }
                $data['hide_thumbnail_flg'] = $circular->hide_thumbnail_flg;
                $data['circular_approval_url'] = CircularUtils::generateApprovalUrl($received_user['email'], $received_user['edition_flg'], $received_user['env_flg'], $received_user['server_flg'], $circular->circular_id);
                if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                    $data['circular_approval_url_text'] = '回覧文書をみる:' . $data['circular_approval_url'] . '\r\n\r\n';
                }else{
                    $data['circular_approval_url_text'] = '';
                }
                // 受信者
                $data['receiver_name'] = $received_user['name'];
                // 申請者
                $creator = DB::table('circular_user')
                    ->where('circular_id', $circular->circular_id)
                    ->where('parent_send_order', 0)
                    ->where('child_send_order', 0)
                    ->first();
                $data['creator_name'] = $creator->name;

                Log::channel('cron-daily')->debug('Send reNotify to email '.$received_user['email']);

                $param = json_encode($data,JSON_UNESCAPED_UNICODE);
                unset($data['filenames']);

                //利用者:回覧文書の再送通知
                MailUtils::InsertMailSendResume(
                    // 送信先メールアドレス
                    $received_user['email'],
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['CIRCULAR_USER_RENOTIFY']['CODE'],
                    // パラメータ
                    $param,
                    // タイプ
                    AppUtils::MAIL_TYPE_USER,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.circular_user_renotify_template.subject', ['title' => $title, 'creator_name' => $data['creator_name']]),
                    // メールボディ
                    trans('mail.circular_user_renotify_template.body', $data)
                );

                // アクセスコードメール送信（「回覧文書をみる」リンク表示場合のみ）
                if(isset($data['hide_circular_approval_url']) && !$data['hide_circular_approval_url']){
                    if ($circular->access_code_flg == CircularUtils::ACCESS_CODE_VALID
                        && $creator->mst_company_id == $received_user['mst_company_id']
                        && $creator->edition_flg == $received_user['edition_flg']
                        && $creator->env_flg == $received_user['env_flg']
                        && $creator->server_flg == $received_user['server_flg']) {
                        $access_data['title'] = $data['mail_name'];
                        $access_data['access_code'] = $circular->access_code;

                        //利用者:アクセスコードのお知らせ
                        MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                            $received_user['email'],
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                            // パラメータ
                            json_encode($access_data,JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                            // メールボディ
                            trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                        );
                    }elseif ($circular->outside_access_code_flg === CircularUtils::OUTSIDE_ACCESS_CODE_VALID
                        && ($creator->mst_company_id != $received_user['mst_company_id']
                            || $creator->edition_flg != $received_user['edition_flg']
                            || $creator->env_flg != $received_user['env_flg']
                            || $creator->server_flg != $received_user['server_flg'])) {
                        // 次の宛先が社外の場合
                        $access_data['title'] = $data['mail_name'];
                        $access_data['access_code'] = $circular->outside_access_code;

                        //利用者:アクセスコードのお知らせ
                        MailUtils::InsertMailSendResume(
                            // 送信先メールアドレス
                            $received_user['email'],
                            // メールテンプレート
                            MailUtils::MAIL_DICTIONARY['ACCESS_CODE_NOTIFY']['CODE'],
                            // パラメータ
                            json_encode($access_data,JSON_UNESCAPED_UNICODE),
                            // タイプ
                            AppUtils::MAIL_TYPE_USER,
                            // 件名
                            trans('mail.prefix.user') . trans('mail.SendAccessCodeNoticeMail.subject', ['title' => $access_data['title']]),
                            // メールボディ
                            trans('mail.SendAccessCodeNoticeMail.body', $access_data)
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('cron-daily')->error('Run to reNotify failed');
            Log::channel('cron-daily')->error($e->getMessage() . $e->getTraceAsString());
            throw $e;
        }
        Log::channel('cron-daily')->debug('Run to reNotify finished');
    }
}
