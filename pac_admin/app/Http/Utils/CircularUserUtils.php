<?php
/**
 * Created by PhpStorm.
 * User: Ma
 * Date: 20/11/09
 * Time: 15:45
 */

namespace App\Http\Utils;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CircularUserUtils
{
    //status
    const NOT_NOTIFY_STATUS = 0; // 未通知
    const NOTIFIED_UNREAD_STATUS = 1; // 通知済/未読
    const READ_STATUS = 2; // 既読
    const APPROVED_WITH_STAMP_STATUS = 3; // 承認(捺印あり)
    const APPROVED_WITHOUT_STAMP_STATUS = 4; // 承認(捺印なし)
    const SEND_BACK_STATUS = 5; // 差戻し
    const END_OF_REQUEST_SEND_BACK = 6; // 差戻し(未読)
    const SUBMIT_REQUEST_SEND_BACK = 7; // 差戻依頼
    const PULL_BACK_TO_USER_STATUS = 8; // 引戻し(下書き一覧に入る)
    const CIRCULAR_DELETED = 9; // 引戻し(下書き一覧に入る)
    const REVIEWING_STATUS = 10; // 窓口再承認待ち

    const SEPERATOR = '#,,,#';

    //ENV
    const ENV_AWS = 0;
    const K5_AWS = 1;

    //EDITION
    const CURRENT_EDITION = 0;
    const NEW_EDITION = 1;

    //DEL_FLG

    const NOT_DELETE = 0;
    const DELETED = 1;
    const DEFAULT_OPERATION_NOTICE_FLG = 0;
}