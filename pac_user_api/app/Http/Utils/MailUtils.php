<?php


namespace App\Http\Utils;

use App\Models\ApiUsers;
use App\Models\RequestInfo;
use DB;
use App\Http\Utils\AppUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MailUtils
{
    const MAIL_DICTIONARY = [
        'EXPORT_DEPARTMENT_ALERT' => [
            'CODE' => '001',
            'FUNCTION' => '管理者:部署CSVダウンロード出力通知',
            'SERVICE' => 'SendExportDepartmentAlert',
            'TEMPLATE' => 'email_template.SendExportDepartmentAlert',
        ],
        'TRIAL_DUPLICATE_FAILED' => [
            'CODE' => '002',
            'FUNCTION' => 'トライアル:重複登録エラー',
            'SERVICE' => 'SendTrialDuplicateErrorMail',
            'TEMPLATE' => 'email_template.trialDuplicateErrorMail',
        ],
        'TRIAL_SUCCESS' => [
            'CODE' => '003',
            'FUNCTION' => 'トライアル:登録成功通知',
            'SERVICE' => 'SendTrialSuccessMail',
            'TEMPLATE' => 'email_template.trialSuccessMail',
        ],
        'USER_PASSWORD_SET_REQUEST' => [
            'CODE' => '004',
            'FUNCTION' => '利用者:初期パスワードの通知',
            'SERVICE' => 'SendMailInitPassword',
            'TEMPLATE' => 'email_template.email_reset_link_user',
        ],
        'AUDIT_PASSWORD_SET_REQUEST' => [
            'CODE' => '005',
            'FUNCTION' => '監査者:初期パスワードの通知',
            'SERVICE' => 'SendMailInitPassword',
            'TEMPLATE' => 'email_template.email_reset_link_audit',
        ],
        'ADMIN_PASSWORD_SET_REQUEST' => [
            'CODE' => '006',
            'FUNCTION' => '管理者:初期パスワードの通知',
            'SERVICE' => 'SendMailInitPassword',
            'TEMPLATE' => 'email_template.email_reset_link_admin',
        ],
        'CIRCULAR_DELETE_ALERT' => [
            'CODE' => '007',
            'FUNCTION' => '利用者:回覧削除通知',
            'SERVICE' => 'SendCircularDeleteMail',
            'TEMPLATE' => 'email_template.SendMailDeleteCircular',
        ],
        'ADMIN_PASSWORD_CHANGED_NOTIFY' => [
            'CODE' => '008',
            'FUNCTION' => '管理者:パスワード設定完了通知',
            'SERVICE' => 'SendChangePasswordMail',
            'TEMPLATE' => 'email_template.SendChangePasswordMail',
        ],
        'SAVED_CIRCULAR_DELETE_ALERT' => [
            'CODE' => '009',
            'FUNCTION' => '利用者:保存文書削除通知',
            'SERVICE' => 'SendCircularDeleteMail',
            'TEMPLATE' => 'email_template.SendMailDeleteCircular',
        ],
        'COMPANY_STAMP_UPLOAD_ALERT' => [
            'CODE' => '010',
            'FUNCTION' => '管理者:共通印の一括登録通知',
            'SERVICE' => 'SendAssignCompanyStamp',
            'TEMPLATE' => 'email_template.SendMailAssignCompanyStamp',
        ],
        'ADMIN_MFA_CODE_RELEASE' => [
            'CODE' => '011',
            'FUNCTION' => '管理者:認証コードの発行',
            'SERVICE' => 'SendMfaMail',
            'TEMPLATE' => 'email_template.SendMfaMail',
        ],
        'ADMIN_IP_RESTRICTION_ALERT' => [
            'CODE' => '012',
            'FUNCTION' => '管理者:IP制限_ログイン通知',
            'SERVICE' => 'SendIpRestrictionMail',
            'TEMPLATE' => 'email_template.SendIpRestrictionMail',
        ],
        'LONG_TERM_STORAGE_ALERT' => [
            'CODE' => '013',
            'FUNCTION' => '管理者:長期保管ディスク容量通知',
            'SERVICE' => 'SendMailAlertLongTermStorage',
            'TEMPLATE' => 'email_template.SendMailAlertLongTermStorage',
        ],
        'ADMIN_INIT_PASSWORD_ALERT' => [
            'CODE' => '014',
            'FUNCTION' => '管理者:初期パスワード発行の通知',
            'SERVICE' => 'SendInitPasswordMail',
            'TEMPLATE' => 'email_template.SendAdminInitPasswordMail',
        ],
        'DEPARTMENT_STAMP_ACTIVATE_SUCCESS' => [
            'CODE' => '015',
            'FUNCTION' => '管理者:部署名入り日付印有効化完了通知',
            'SERVICE' => 'SendDepartmentStampActivateSuccessMail',
            'TEMPLATE' => 'email_template.department_stamp_activate_success_template',
        ],
        'DEPARTMENT_STAMP_ACTIVATE_FAILED' => [
            'CODE' => '016',
            'FUNCTION' => 'マスター管理者:部署名入り日付印有効化失敗通知',
            'SERVICE' => 'SendDepartmentStampActivateFailedMail',
            'TEMPLATE' => 'email_template.department_stamp_activate_failed_template',
        ],
        'BATCH_HISTORY_MAIL_SEND' => [
            'CODE' => '017',
            'FUNCTION' => '管理者:バッチ稼働状況',
            'SERVICE' => 'SendBatchHistoryMail',
            'TEMPLATE' => 'email_template.SendBatchHistoryMail',
        ],
        'USER_REGISTRATION_COMPLETE_NOTIFY' => [
            'CODE' => '018',
            'FUNCTION' => 'シングルサインオンユーザ登録完了通知',
            'SERVICE' => 'UserRegistrationCompleteMail',
            'TEMPLATE' => 'email_template.UserRegistrationCompleteMail',
        ],
        'SEND_DOWNLOAD_RESERVE_COMPLETED' => [
            'CODE' => '019',
            'FUNCTION' => '管理者:ダウンロード処理完了通知',
            'SERVICE' => 'SendDownloadReserveCompletedMail',
            'TEMPLATE' => 'email_template.download_reserve_completed_template',
        ],
        'BOX_REFRESH_TOKEN_UPDATE_FAILED' => [
            'CODE' => '020',
            'FUNCTION' => 'BOX更新トークン自動更新失敗通知',
            'SERVICE' => 'SendBoxRefreshTokenUpdateFailedMail',
            'TEMPLATE' => 'email_template.box_refresh_token_update_failed_template',
        ],
        'CSV_SEND_SUCCESS' => [
            'CODE' => '021',
            'FUNCTION' => '情報取得リクエスト（正常終了）',
            'SERVICE' => 'SendCsvSuccessMail',
            'TEMPLATE' => 'email_template.pe_send_csv_success_template',
        ],
        'CSV_SEND_FAILED' => [
            'CODE' => '022',
            'FUNCTION' => '情報取得リクエスト（失敗）',
            'SERVICE' => 'SendCsvFailedMail',
            'TEMPLATE' => 'email_template.pe_send_csv_failed_template',
        ],
        'SIMPLE_USER_PASSWORD_SET_REQUEST' => [
            'CODE' => '023',
            'FUNCTION' => '利用者:初期パスワードの通知',
            'SERVICE' => 'SendMailInitPassword',
            'TEMPLATE' => 'email_template.email_reset_link_simple_user',
        ],


        'CIRCULAR_ARRIVED_NOTIFY' => [
            'CODE' => '101',
            'FUNCTION' => '利用者:回覧文書が届いています',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_user_template',
        ],
        'ACCESS_CODE_NOTIFY' => [
            'CODE' => '102',
            'FUNCTION' => '利用者:アクセスコードのお知らせ',
            'SERVICE' => 'SendAccessCodeNoticeMail',
            'TEMPLATE' => 'email_template.SendAccessCodeNoticeMail',
        ],
        'CIRCULAR_ENDED_NOTIFY' => [
            'CODE' => '103',
            'FUNCTION' => '利用者:回覧文書の完了通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_has_ended_template',
        ],
        'CIRCULAR_PULLBACK_NOTIFY' => [
            'CODE' => '104',
            'FUNCTION' => '利用者:回覧文書の引戻し通知',
            'SERVICE' => 'SendCircularPullBackMail',
            'TEMPLATE' => 'email_template.circular_pullback_template',
        ],
        'CIRCULAR_SEND_BACK_REQUEST_NOTIFY' => [
            'CODE' => '105',
            'FUNCTION' => '利用者:回覧文書の差戻し依頼通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_user_request_sendback_template',
        ],
        'CIRCULAR_USER_VIEWED_NOTIFY' => [
            'CODE' => '106',
            'FUNCTION' => '利用者:回覧文書の閲覧通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_user_viewed_template',
        ],
        'CIRCULAR_SEND_BACK_NOTIFY' => [
            'CODE' => '107',
            'FUNCTION' => '利用者:回覧文書の差戻し通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_user_sendback_template',
        ],
        'USER_PASSWORD_CHANGED_NOTIFY' => [
            'CODE' => '108',
            'FUNCTION' => '利用者:パスワード設定完了通知',
            'SERVICE' => 'SendFinishMail',
            'TEMPLATE' => 'email_template.SendFinishMail',
        ],
        'CIRCULAR_RE_NOTIFICATION' => [
            'CODE' => '109',
            'FUNCTION' => '利用者:回覧文書の送信（再送）',
            'SERVICE' => 'SendCircularReNotificationMail',
            'TEMPLATE' => 'email_template.circular_notify_template',
        ],
        'FILE_EXPIRED_ALERT' => [
            'CODE' => '110',
            'FUNCTION' => 'ファイル保存期間の通知',
            'SERVICE' => 'SendMailAlertFileExpired',
            'TEMPLATE' => 'email_template.SendMailAlertFileExpired',
        ],
        'DISK_QUOTA_ALERT' => [
            'CODE' => '111',
            'FUNCTION' => '管理者:ディスク容量使用通知',
            'SERVICE' => 'SendMailAlertDiskQuota',
            'TEMPLATE' => 'email_template.SendMailAlertDiskQuota',
        ],
        'CIRCULAR_USER_RENOTIFY' => [
            'CODE' => '112',
            'FUNCTION' => '利用者:回覧文書の再送通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_user_renotify_template',
        ],
        'USER_IP_RESTRICTION_ALERT' => [
            'CODE' => '113',
            'FUNCTION' => '利用者:IP制限_ログイン通知',
            'SERVICE' => 'SendIpRestrictionMail',
            'TEMPLATE' => 'email_template.SendIpRestrictionMail',
        ],
        'USER_MFA_CODE_RELEASE' => [
            'CODE' => '114',
            'FUNCTION' => '利用者:認証コードの発行',
            'SERVICE' => 'SendMfaMail',
            'TEMPLATE' => 'email_template.SendMfaMail',
        ],
        'CIRCULAR_UPDATED_NOTIFY' => [
            'CODE' => '115',
            'FUNCTION' => '利用者:回覧文書の更新通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_updated_notify_template',
        ],
        'USER_SEND_DOWNLOAD_RESERVE_COMPLETED' => [
            'CODE' => '117',
            'FUNCTION' => '利用者:ダウンロード処理完了通知',
            'SERVICE' => 'SendDownloadReserveCompletedMail',
            'TEMPLATE' => 'email_template.download_reserve_completed_template',
        ],
        'SEND_DISK_FILE_MAIL' => [
            'CODE' => '118',
            'FUNCTION' => '利用者:ファイルメール便作成通知',
            'SERVICE' => 'SendDiskFileMail',
            'TEMPLATE' => 'email_template.SendDiskFileMail',
        ],
        'SEND_DISK_FILE_ACCESS_CODE_MAIL' => [
            'CODE' => '119',
            'FUNCTION' => '利用者:ファイルメール便セキュリティコード通知',
            'SERVICE' => 'SendDiskFileAccessCodeMail',
            'TEMPLATE' => 'email_template.SendDiskFileAccessCodeMail',
        ],
        'SEND_TIMESTAMPS_COUNT_LESS_REMIND_MAIL' => [
            'CODE' => '120',
            'FUNCTION' => '管理者:タイムスタンプの利用通知',
            'SERVICE' => 'SendTimestampsNotifyMail',
            'TEMPLATE' => 'email_template.send_timestamps_count_less_remind_mail',
        ],
        'SEND_TIMESTAMPS_COUNT_UPPER_LIMIT_REMIND_MAIL' => [
            'CODE' => '121',
            'FUNCTION' => '管理者:タイムスタンプの発行数上限通知',
            'SERVICE' => 'SendTimestampsNotifyMail',
            'TEMPLATE' => 'email_template.send_timestamps_count_upper_limit_remind_mail',
        ],
        // PAC_5-2352 skip email
        'USER_SKIP_HANDLER_COMPLETED' => [
            'CODE' => '122',
            'FUNCTION' => '利用者:回覧文書のスキップ通知',
            'SERVICE' => 'SendCircularUserMail',
            'TEMPLATE' => 'email_template.circular_skip_handler_template',
        ],
        // PAC_5-1693 HR Work Report send back email
        'HR_WORK_REPORT_SEND_BACK_NOTIFY' => [
            'CODE' => '123',
            'FUNCTION' => '利用者:勤務詳細の差戻し通知',
            'SERVICE' => 'SendHRWorkReportMail',
            'TEMPLATE' => 'email_template.hr_work_report_sendback_template',
        ],
        'TO_DO_LIST_DEADLINE_NOTICE' => [
            'CODE' => '124',
            'FUNCTION' => '利用者:toDoリストタスクは通知の有効期限が近づいています',
            'SERVICE' => 'SendToDoListDeadlineNoticeMail',
            'TEMPLATE' => 'email_template.to_do_list_deadline_notice',
        ],
        'SEND_HR_WORK_NOTICE' => [
            'CODE' => '125',
            'FUNCTION' => '利用者:勤怠連絡',
            'SERVICE' => 'SendHrWorkNotice',
            'TEMPLATE' => 'email_template.SendHrWorkNotice',
        ],
        'SEND_WORK_DETAIL_SUBMISSION_MAIL' => [
            'CODE' => '126',
            'FUNCTION' => '利用者:作業詳細提出通知',
            'SERVICE' => 'SendWorkDetailSubmissionMail',
            'TEMPLATE' => 'email_template.SendWorkDetailSubmissionMail',
        ],
        'SEND_APPROVAL_WORK_DETAIL_MAIL' => [
            'CODE' => '127',
            'FUNCTION' => '利用者:承認作業の詳細',
            'SERVICE' => 'SendApprovalWorkDetailMail',
            'TEMPLATE' => 'email_template.SendApprovalWorkDetailMail',
        ],
        'SEND_REMAND_WORK_DETAIL_MAIL' => [
            'CODE' => '128',
            'FUNCTION' => '利用者:差し戻し作業の詳細',
            'SERVICE' => 'SendRemandWorkDetailMail',
            'TEMPLATE' => 'email_template.SendRemandWorkDetailMail',
        ],
    ];

    /**
     * メール用キューへの登録処理
     * メール送信条件が複雑化したため関数化
     */
    public static function InsertMailSendResume(
        $email,                                         // 送信対象のメールアドレス
        $template,                                      // メールテンプレート
        $param,                                         // パラメータ
        $type,                                          // メール送信種別
        $subject,                                       // 件名
        $body,                                          // メールボディ
        $state = AppUtils::MAIL_STATE_WAIT,             // 送信状態
        $send_times = AppUtils::MAIL_SEND_DEFAULT_TIMES, // 送信回数
        $notification_email = ''
    )
    {

        try {

            // メール送信判定の最低限情報
            if (is_null($type) && is_null($email)) {
                Log::error('Mail Send Error. Cannot get mail setting info.');
                return;
            }
            //無効企業、無効ユーザーに対してはすべてのメールの送信を停止させてほしい
            if (in_array($type,[AppUtils::MAIL_TYPE_AUDIT, AppUtils::MAIL_TYPE_USER])){
                $client = IdAppApiUtils::getAuthorizeClient();
                if (!$client) {
                    throw new \Exception('Cannot connect to ID App');
                }
                $response = $client->post("users/getEmailUsers", [
                    RequestOptions::JSON => ['emails' =>explode(',',$email)]
                ]);
                if ($response->getStatusCode() == 200) {
                    $result = json_decode((string) $response->getBody());
                    if(!empty($result)){
                        $email = implode(',',$result->data);
                    }
                }
                if (!$email){
                    return;
                }
            }
            switch ($type) {
                case AppUtils::MAIL_TYPE_AUDIT :
                    // 監査
                    $target = DB::table('mst_audit')->where('email', $email)->first();
                    $target->email_format = 1;
                    $target->enable_email = 1;
                    break;
                case AppUtils::MAIL_TYPE_ADMIN :
                    // 管理者
                    $target = DB::table('mst_admin')->where('email', $email)->first();
                    break;
                case AppUtils::MAIL_TYPE_USER :
                    // 利用者
                    $target = DB::table('mst_user as MU')
                        ->join('mst_user_info as MUI', 'MUI.mst_user_id', 'MU.id')
                        ->where('MU.email', $email)
                        ->select('MU.mst_company_id as mst_company_id', 'MUI.enable_email as enable_email', 'MUI.email_format as email_format',
                                'MU.notification_email as notification_email','MU.option_flg as option_flg')
                        ->first();
                    break;
                default :
                    Log::error('Mail Send Error. Type is Invalid argument.');
                    return;
            }

            if (!isset($target)) {
                // ゲストアカウントはHTMLメールを送信
                $enable_email = true;
                $email_format = 1;
                $company_id = 0;
            } else {
                $mst_company = DB::table('mst_company')->where('id', $target->mst_company_id)->first();
                $company_id = $mst_company->id;
                $email_format = $target->email_format;
                $enable_email = $mst_company->enable_email === 1 && $target->enable_email === 1;
            }

            // メール送信対象の受信が無効
            if (!$enable_email) {
                return;
            }
            //メールアドレス無ユーザー、メール通知不要
            if(substr($email,-4) == '.scs'){
                return;
            }
            //受信専用利用者通知メール
            if (isset($target) && $type == AppUtils::MAIL_TYPE_USER && $target->option_flg == AppUtils::USER_RECEIVE){
                $notification_email = $target->notification_email;
            }

            // メール用キューに登録
            $resume_id = DB::table('mail_send_resume')->insertGetId([
                'mst_company_id' => $company_id,
                'to_email' => $notification_email ?: $email,
                'template' => $template,
                'param' => $param,
                'type' => $type,
                'subject' => $subject,
                'body' => $body,
                'state' => $state,
                'send_times' => $send_times,
                'create_at' => Carbon::now(),
                'update_at' => Carbon::now(),
                'email_format' => $email_format,
            ]);
            return $resume_id;
        } catch (\Exception $e) {
            Log::info('[ Email Send Error. ]' . $e->getMessage() . $e->getTraceAsString());
        }
    }

}