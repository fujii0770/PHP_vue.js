<?php
/**
 * Created by PhpStorm.
 * User: bmc33
 * Date: 2021/5/31
 * Time: 9:40
 */

namespace App\Utils;


class CircularAttachmentUtils
{
    //status
    const ATTACHMENT_NOT_CHECK_STATUS = 0; // 未ウイルススキャン
    const ATTACHMENT_CHECK_SUCCESS_STATUS = 1; // ウイルススキャン完了
    const ATTACHMENT_CHECK_FAIL_STATUS = 2; // ウイルスのスキャンに失敗しました
    const ATTACHMENT_DELETE_STATUS = 9; // ファイルの削除

    //confidential_flg
    const ATTACHMENT_CONFIDENTIAL_FALSE = 0;
    const ATTACHMENT_CONFIDENTIAL_TRUE = 1;

    const ENV_FLG_AWS = 0;
    const ENV_FLG_K5 = 1;
}