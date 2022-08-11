<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 11/13/19
 * Time: 14:45
 */

namespace App\Http\Utils;


class StatusCodeUtils
{
    //2 **の開始：リクエストは成功しました
    const HTTP_OK = 200; //成功
    const HTTP_CREATED = 201; //（作成済み）サーバーは新しいリソースを作成しました。
    const HTTP_NO_CONTENT = 204; //（コンテンツなし）サーバーは要求を正常に処理しましたが、コンテンツを返しませんでした。

    // 3 **の開始：リクエストはリダイレクトされます
    const HTTP_MOVED_PERMANENTLY = 301; //恒久的に移動する
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_TEMPORARY_REDIRECT = 307; //一時的なリダイレクト

    // 4 **の開始：リクエストエラー
    const HTTP_BAD_REQUEST = 400; //要求の形式が正しくありません
    const HTTP_UNAUTHORIZED = 401; //認証失敗
    const HTTP_FORBIDDEN = 403; //サーバーはリクエストを拒否しました
    const HTTP_NOT_FOUND = 404; // not found
    const HTTP_REQUEST_TIMEOUT = 408; //タイムアウト
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_UNPROCESSABLE_ENTITY = 422;
    const HTTP_TOO_MANY_REQUESTS = 429;

    // 5 **の開始：サーバーエラー
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_SERVICE_UNAVAILABLE = 503;
}