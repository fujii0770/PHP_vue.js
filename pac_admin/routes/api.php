<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/user_api', 'Csv\CsvController@handleCsv');
Route::prefix('/trial')->group(function () {
    Route::post('/createdomain','API\CompanyAPIController@createDomain'); //トライアル企業登録API
    Route::post('/gettrialinfo','API\CompanyAPIController@getTrialInfo'); //トライアル情報取得
    Route::post('/getDomainsTrialState','API\CompanyAPIController@getDomainsTrialState'); //トライアル状態を取得するAPI
    Route::post('/updateDomainsTrialState','API\CompanyAPIController@updateDomainsTrialState'); //トライアル登録中の企業の契約状態を本契約に変更するAPI「Standard」or「Business」
    Route::post('/updateDomainsMaxUserStamps','API\CompanyAPIController@updateDomainsMaxUserStamps'); //登録可能印面数の更新API
    Route::post('/createContractDomain','API\CompanyAPIController@createContractDomain'); //トライアル踏まずに本契約登録するAPI「Standard」or「Business」
    Route::post('/updateDomainSetting','API\CompanyAPIController@updateDomainSetting'); //企業機能設定一括更新
    Route::post('/updateAppendFileCapacity','API\CompanyAPIController@updateAppendFileCapacity'); //追加ファイル容量の更新API
    Route::post('/updateDomainSettingSecond','API\CompanyAPIController@updateDomainSettingSecond'); //企業機能設定更新API(オプションの契約数、タイムスタンプ数)
    Route::post('/getDomainInfo','API\CompanyAPIController@getDomainInfo'); //有効ユーザー数と登録印面数を取得するAPI
    Route::post('/getPdfNumber','API\CompanyAPIController@getPdfNumber'); //共通印申込書codeリストを取得するAPI
});

Route::post('/chat/insertChatInfo','API\Chat\ChatCompanyAPIController@insertChatInfo');//ロケットチャット登録
Route::post('/chat/updateChatInfo','API\Chat\ChatCompanyAPIController@updateChatInfo'); //契約更新
Route::post('/chat/getChatInfo','API\Chat\ChatCompanyAPIController@getChatInfo'); //チャットサーバー管理情報の取得
Route::post('/chat/getChatSubDomain','API\Chat\ChatCompanyAPIController@getChatSubDomain'); //サブドメインの使用可否判定
Route::get('/chat/initServer','API\Chat\ChatCompanyAPIController@initServer'); //サーバー初期セットアップ


