<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */
namespace App\Utils;

use GuzzleHttp\Client;

class OperationsHistoryUtils
{
    const HISTORY_FLG_ADMIN = 0;
    const HISTORY_FLG_USER = 1;
    const HISTORY_FLG_API = 2;
    const STATUS = ['成功', '失敗'];

    /**
     * mst_display_id,  mst_operation_id, message_true, message_false, fields, fields multi
     */
    const  LOG_INFO = [
            'Password' => [
                'setPassword' => [54,104,'パスワードの変更に成功しました。','パスワードの変更に失敗しました。'],
                'sendReentryMail' => [54,106,'パスワード設定メール再送に成功しました。','パスワード設定メール再送に失敗しました。'],
            ],
            'Mfa' => [
                'verify' => [66,156,'認証コードによる認証に成功しました。','認証コードによる認証に失敗しました。'],
                'resend' => [66,157,'認証メール再送信に成功しました。','認証メール再送信に失敗しました。'],
            ],
        ];

}