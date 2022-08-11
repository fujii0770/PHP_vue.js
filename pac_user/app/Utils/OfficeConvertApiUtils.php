<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Psr\Log\LogLevel;

class OfficeConvertApiUtils {
    // OfficeConvertApi は、Office ファイルの処理を行うサーバー
    // 改ページプレビューと改ページプレビューなし変換で、参照する接続先設定が異なる

    // 使い方
    // - 改ページプレビュー
    //   - getApiClientForPageBreak() で得た Client を使う
    // - 改ページプレビューなし変換 (Word/Excel → PDF 変換)
    //   - convertInstantly を使う

    // このファイルは、pac_user, pac_user_api にある
    // どちらも同じ内容とする

    /**
     * Client を作成し、返す
     *
     * 作成された Client は、ステータスコードが 4xx, 5xx 等の場合は例外を投げる
     *
     * @return Client
     */
    private static function newClient(string $base_uri) {
        return new Client([
            'base_uri' => $base_uri,
            // timeout は、OfficeConvertApi がレスポンスを返すまでの時間よりも大きい必要がある
            // そうしなければ、OfficeConvertApi で処理をしている最中にタイムアウト判定されうる
            // レスポンスを返すのに、最長 60 + 8 秒程度かかると想定される
            // 余裕を見てこの値とする (無期限とはしない)
            'timeout' => 75,
            'connect_timeout' => config('app.guzzle_connect_timeout'),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    private static function getApiClientForConvertInstantly(): Client {
        $api_host = rtrim(config('app.office_convert_api_host'), "/");
        $api_base = ltrim(config('app.office_convert_api_base'), "/");
        return self::newClient($api_host."/".$api_base);
    }

    /**
     * Word, Excel ファイル等を PDF に変換する
     *
     * 出力パスにファイルが存在する場合は上書きする
     * 処理失敗時は出力パスにあるファイルを削除し、例外を投げる
     *
     * @param string $officeFilePath 入力ファイルパス
     * @param string $pdfFilePath PDF ファイル出力パス
     * @return void
     */
    public static function convertInstantly(string $officeFilePath, string $pdfFilePath): void {
        $client = self::getApiClientForConvertInstantly();

        $isSucceeded = false; // 失敗時の削除用
        $outResource = fopen($pdfFilePath, 'w+'); // finally を通るときにファイルが存在している必要があるため、この時点で作成
        try {
            $client->post('convert_instantly', [
                RequestOptions::MULTIPART => [
                    [
                        "name" => "file",
                        "contents" => fopen($officeFilePath, 'r'),
                    ],
                ],
                RequestOptions::SINK => $outResource,
            ]);

            $isSucceeded = true;
        } finally {
            if (!$isSucceeded) {
                // 中身はエラーレスポンスであり不要なため、削除する
                unlink($pdfFilePath);
            }
        }
    }

    /**
     * 改ページプレビューのための OfficeConvertApi Client を作成し、返す
     *
     * 作成された Client は、ステータスコードが 4xx, 5xx 等の場合は例外を投げます
     *
     * @return Client
     */
    public static function getApiClientForPageBreak(): Client {
        // 改ページプレビューなしの変換には、convertInstantly() を使用してください
        // これを使って convert_instantly を呼べますが、接続先を別々にする意味がなくなるため呼ばないでください
        $api_host = rtrim(config('app.page_break_api_host'), "/");
        $api_base = ltrim(config('app.page_break_api_base'), "/");
        return self::newClient($api_host."/".$api_base);
    }

    /**
     * ServerException (5xx エラー) を処理する
     * ログ出力し、クライアントへ返すためのエラーレスポンスを返す
     */
    public static function logAndGenerateErrorResponse(ServerException $e): JsonResponse {
        // pac_user 用

        // ClientException (4xx) はこの関数の処理対象外とする
        // ClientException は本サーバー (pac) のコードに不備があると示すものであり、通常起こらないため
        $requestPath = $e->getRequest()->getUri()->getPath();
        $response = $e->getResponse();

        $responseString = (string) $response->getBody();

        // エラーコード
        $json = json_decode($responseString);
        $code = $json ? $json->detail : null;

        // ログ用
        $logContext = [
            "operation" => $requestPath,
            "statusCode" => $response->getStatusCode(),
        ];
        // $code もしくは レスポンスの一部もしくは全部を出力
        if ($code) {
            $logContext["applicationCode"] = $code;
        } else {
            $response->getBody()->seek(0);
            $logContext["response"] = Message::bodySummary($response);
        }

        // ログレベルとレスポンスを決定
        $whenUnexpected = [LogLevel::ERROR, "処理できませんでした (不明)", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR];
        $expectedCodeMap = [
            "insufficient_resources" => [LogLevel::WARNING, "混み合っているため処理できませんでした", \Illuminate\Http\Response::HTTP_SERVICE_UNAVAILABLE],
            "timeout" => [LogLevel::WARNING, "処理がタイムアウトしました", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],

            // convert_instantly のみ
            "cannot_load" => [LogLevel::INFO, "ファイルを開けませんでした。次をご確認ください。\n・パスワード保護されていないか\n・破損していないか", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
        ];

        [$logLevel, $message, $responseStatusCode] = $expectedCodeMap[$code] ?? $whenUnexpected;

        Log::{$logLevel}("OfficeConvertApi failed", $logContext);
        return Response::json(['status' => false, 'message' => $message, 'data' => null], $responseStatusCode);
    }
}
