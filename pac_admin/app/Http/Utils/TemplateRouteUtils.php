<?php namespace App\Http\Utils;


/**
 * Created by PhpStorm.
 * User: wangc
 * Date: 3/5/2021
 * Time: 12:12 PM
 */

class TemplateRouteUtils
{
    // 承認方法
    const TEMPLATE_MODE_ALL_MUST = 1; // 全員必須
    const TEMPLATE_MODE_MORE_THAN = 3; // n人以上承認

    // 全承認者の処理を待つか
    const TEMPLATE_MODE_ALL_MUST_WAIT = 1;
    const TEMPLATE_MODE_ALL_MUST_NOT_WAIT = 0;

    // 承認ルート状態
    const TEMPLATE_ROUTE_STATE_INVALID = 0; // 無効
    const TEMPLATE_ROUTE_STATE_VALID = 1;   // 有効
    const TEMPLATE_ROUTE_STATE_DELETES = 9; // 削除

    // 承認方法  全承認者の処理を待つか  対応関係
    const MODE_WAIT = [1 => 1, 3 => 0];

    //PAC_5-2608
    const AUTH_FLG = 1;
    const CREATE_APP = 1;
}