<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class OfficeConvertApiUtils {
    // OfficeConvertApi は、次の処理を行うサーバー
    // - Word/Excel → PDF 変換
    // - 改ページプレビュー

    // このファイルは、pac_user, pac_user_api にある
    // どちらも同じ内容とする

    /**
     * OfficeConvertApi の Client を作成し、返す
     *
     * 作成された Client は、ステータスコードが 4xx, 5xx 等の場合は例外を投げます
     *
     * @return Client
     */
    public static function getApiClient(): Client {
        $api_host = rtrim(config('app.office_convert_api_host'), "/");
        $api_base = ltrim(config('app.office_convert_api_base'), "/");
        return new Client([
            'base_uri' => $api_host."/".$api_base,
            // timeout は、OfficeConvertApi がレスポンスを返すまでの時間よりも大きい必要がある
            // そうしなければ、OfficeConvertApi で処理をしている最中にタイムアウト判定されうる
            // レスポンスを返すのに、最長 60 + 8 秒程度かかると想定される
            // 余裕を見てこの値とする (無期限とはしない)
            'timeout' => 75,
            'connect_timeout' => config('app.guzzle_connect_timeout'),
            'headers' => ['Content-Type' => 'application/json']
        ]);
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
        // pac_user, pac_user_api の両方で使用される
        $client = self::getApiClient();

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

        // Log::warning で出力するもの 兼 クライアントへエラー原因を伝えるもの
        // ここになければ Log:error で出力する
        $warnCodeMap = [
            "insufficient_resources" => ["サーバーリソースの不足", \Illuminate\Http\Response::HTTP_SERVICE_UNAVAILABLE],
            "timeout" => ["タイムアウト", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR],
        ];

        $warnResponse = $warnCodeMap[$code] ?? null;
        if ($warnResponse) {
            Log::warning("OfficeConvertApi response", $logContext);
            [$responseCause, $responseStatusCode] = $warnResponse;
        } else {
            Log::error("OfficeConvertApi response", $logContext);
            [$responseCause, $responseStatusCode] = ["不明", \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR];
        }

        return Response::json(['status' => false, 'message' => "処理できませんでした ($responseCause)", 'data' => null], $responseStatusCode);
    }

}