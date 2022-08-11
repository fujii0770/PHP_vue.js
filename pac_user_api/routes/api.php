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
Route::get('/v1/bizcard/showByLinkPageURL', 'API\BizcardAPIController@showByLinkPageURL');
Route::get('/v1/bizcard/fromPDF/{id}', 'API\BizcardAPIController@show');
Route::get('/v1/bizcard/showPublic/{id}', 'API\BizcardAPIController@show');
Route::get('/v1/bizcard/showBizcardById/{id}', 'API\BizcardAPIController@showBizcardById');
Route::get('/v1/user/getExternalBizcardId/{email}', 'API\CircularUserAPIController@getExternalBizcardId');
Route::get('/v1/setting/getBizcardFlgById/{mst_company_id}', 'API\SettingAPIController@getBizcardFlgById');
Route::post('/before_login', 'API\PasswordController@before_login');
Route::post('/form-issuances/upload-contract-csv', 'API\FormIssuance\FormIssuanceAPIController@uploadContractCsv');
Route::post('/form-issuances/list/get-download-list', 'API\FormIssuance\FormIssuanceAPIController@getDownloadList');
Route::post('/form-issuances/list/get-download-data', 'API\FormIssuance\FormIssuanceAPIController@getDownloadData');
Route::middleware(['LogOperation'])->group(function () {

    Route::group(['prefix' => 'v1'], function(){
        //Route::any('/login', 'API\AuthController@login')->name('login'); //ログイン
        Route::middleware(['cors'])->group(function () {
            Route::options('login', function () {
                return response()->json();
            });
            Route::any('/login', 'API\AuthController@login')->name('login'); //ログイン
        });
        Route::any('/appLogin', 'API\AuthController@appLogin')->name('appLogin'); //APPログイン
        Route::post('/recall', 'API\AuthController@recall'); //リフレッシュログイン
        Route::post('/appRecall', 'API\AuthController@appRecall'); //APPリフレッシュログイン
        Route::post('passwords/init/checkInit', 'API\PasswordController@checkInit');
        Route::post('passwords/init/checkInit-outdate', 'API\PasswordController@checkInitOutDate');
        Route::post('passwords/init/setPassword', 'API\PasswordController@setPassword');
        Route::post('passwords/init/sendFinishMail', 'API\PasswordController@sendFinishMail');

        Route::post('code/codeCheckedUser', 'API\PasswordController@codeCheckedUser'); //コードチェック(利用者)
        Route::post('code/codeCheckedAdmin', 'API\PasswordController@codeCheckedAdmin'); //コードチェック(管理者)
        Route::post('passwords/init/code/checkedCodeTime', 'API\PasswordController@checkedCodeTime'); //コード有効期限チェック
        Route::post('passwords/init/code/setPassword', 'API\PasswordController@setPasswordForCode'); //コードからのパスワード変更

        Route::post('passwords/resetPassword', 'API\PasswordController@resetPassword'); // パスワード更新(現行側利用中、削除不可)

        Route::post('/send-mail', 'API\MailSendAPIController@mailSend');

        Route::get('/checkUser', 'API\UserAPIController@checkUser');    // ユーザ有効チェック
        Route::post('/mailFileDownload','API\Portal\GroupWare\DiskMailFileAPIController@mailFileDownload');//ファイルメール便一覧
        Route::post('passwords/sendReentryMail', 'API\PasswordController@sendReentryMail'); // パスワード変更メール(新エディション ログイン画面用)
        Route::post('passwords/checkEmailSamlEnabledCompanies', 'API\PasswordController@chekSamlEnabledCompanies');
        Route::post('passwords/checkReentryHash', 'API\PasswordController@checkReentryHash'); // パスワード変更チェック(新エディション ログイン画面用)
        Route::post('passwords/resetLoginPassword', 'API\PasswordController@resetLoginPassword'); // パスワード変更(新エディション ログイン画面用)
        Route::post('assign_stamp_info/addAssignStampInfo', 'API\StampInfoAPIController@addToAssignStampInfo'); // 印章使用記録追加
        Route::middleware(['client.credentials'])->group(function () {
            Route::any('/sso-login', 'API\AuthController@samlLogin');
            Route::get('/getPhoneAppFlg', 'API\SettingAPIController@getPhoneAppFlg'); // 携帯アプリ情報取得
            Route::post('/storeCircular', 'API\CircularAPIController@storeTransfer');
            Route::post('/updateStatus', 'API\CircularUserAPIController@receiveUpdateTransferredStatus');
            Route::put('/circularUsers/updatesTransferred', 'API\CircularUserAPIController@updatesTransferred');
            Route::get('/getCompanyByDomain/{url_domain_id}', 'API\CompanyAPIController@getCompanyByDomain');

            //Route::get('/getStamps', 'API\UserAPIController@getStamps'); // 印面リスト取得
            Route::get('/getStamps', 'API\UserAPIController@getStamps')->middleware('cors'); // 印面リスト取得
            Route::options('/getStamps', function () {
                return response()->json();
            });
            Route::get('/getCurrentDepartments', 'API\UserAPIController@getCurrentDepartments'); // 部署取得
            Route::get('/getCurrentContacts', 'API\ContactsAPIController@getCurrentContacts'); // 個人共通アドレス帳取得
			Route::post('/updateDefaultStamp', 'API\UserAPIController@updateDefaultStampByOperation'); // 現行側より、デフォルト印面設定
            Route::get('/getAddress', 'API\ContactsAPIController@getAddresses');
            Route::get('/getDocuments', 'API\CircularAPIController@downloadDocument');
            Route::put('/getDocuments', 'API\CircularAPIController@deleteCircularUser');
            Route::post('/getEnvDocuments', 'API\CircularAPIController@transferDocumentData');
            Route::post('/longTermStoreCircular', 'API\CircularAPIController@longTermStoreCircular');//回覧の長期保存
            Route::post('/updateEnvStatus', 'API\CircularAPIController@updateEnvStatus'); // ファイルステータスの更新
            Route::post('/getEnvDocumentsData', 'API\CircularAPIController@getEnvDocumentsData');
            Route::post('/getEnvCircularHistoryAndOtherData', 'API\CircularAPIController@getEnvCircularHistoryAndOtherData');
            Route::post('/getMyLongTermFolders', 'API\LongTermDocumentApiController@getMyLongTermFolders'); //フォルダ取得

            Route::post('add-viewing-user', 'API\ViewingUserAPIController@storeTransfer');
            Route::get('/getViewingUser', 'API\ViewingUserAPIController@getTransfer');
            /*PAC_5-2331 S*/
            Route::get('/getViewingUsers', 'API\ViewingUserAPIController@getAllTransfer');
            /*PAC_5-2331 S*/
            Route::get('/getFirstPageData', 'API\CircularDocumentAPIController@getFirstPageData');
            Route::get('/updateViewingUser', 'API\ViewingUserAPIController@updateTransfer');
            Route::post('/usage-situation', 'API\UsageSituationAPIController@storeTransfer');
            Route::post('/usage-situation-detail', 'API\UsageSituationDetailAPIController@storeTransfer');

            Route::get('/getCompany/{company_id}', 'API\CompanyAPIController@getCompany');
            Route::get('/getCompanies', 'API\CompanyAPIController@getCompanies');
            Route::get('/getTimestamp/{company_id}', 'API\CompanyAPIController@getTimestamp');
            Route::get('/getUserInfo/{email}', 'API\UserInfoAPIController@getUserInfo');
            Route::get('/getTimestamps', 'API\CompanyAPIController@getTimestamps');
            Route::get('/getUserInfos', 'API\UserInfoAPIController@getUserInfos');
            Route::get('/timestamp/countByMonthAndEnv', 'API\TimeStampInfoAPIController@countByMonthAndEnv');
            Route::get('/timestamp/countByDayAndEnv', 'API\TimeStampInfoAPIController@countByDayAndEnv');

            Route::post('/default-stamp', 'API\UserAPIController@storeTransferDefaultStamp');
            Route::post('/usages-daily', 'API\UsagesDailyAPIController@storeTransfer');
            Route::post('/usages-range', 'API\UsagesRangeAPIController@storeTransfer');
            Route::post('/deleteOtherCircular', 'API\CircularAPIController@deleteOtherCircular'); // そのた環境のCircularを削除

            Route::get('/count-circular-number', 'API\CircularUserAPIController@getCountCircularNumber'); // 他環境の通知数取得用
            Route::post('/store-env-log', 'API\OperationHistoryAPIController@storeEnv'); // 他環境からのログ格納
            Route::post('/box-auto-storage', 'API\AutoStorageApiController@storeBoxAutoStorageRequest'); //BOX自動保存リクエスト
            Route::post('/re-auto-storage', 'API\AutoStorageApiController@reBoxAutoStorageRequest');//Box自動保管失敗後再保存

            Route::get('getEnvLongTermIndex', 'API\LongTermDocumentApiController@getEnvLongTermIndex');//長期保管インデックス取得（他環境用）
            Route::post('setEnvApprovalIndex', 'API\LongTermDocumentApiController@setEnvApprovalIndex');//長期保管インデックス設定（他環境用）
            Route::post('/updateTextSettings', 'API\UserAPIController@updateTextSetting'); //テキスト設定更新
            Route::get('/circulars/find_other_attachment_info','API\CircularDocumentAPIController@findOtherAttachmentInfo'); // GET other server  ALL attachment
            Route::post('/updateSpecialSiteUserStatus', 'API\CircularUserAPIController@updateSpecialSiteUserStatus');
        });

        Route::group(['prefix' => 'public'], function() {
            Route::middleware(['checkHashing'])->group(function () {
                Route::get('setting/getMyCompany', 'API\SettingAPIController@getDetailCompany');
                Route::get('setting/getCreateCircularCompany','API\SettingAPIController@getCreateCircularCompany');//回覧申請者の企業 添付ファイル機能　
                Route::get('setting/getBizcardFlg', 'API\SettingAPIController@getBizcardFlg');
                Route::delete('/favorites/{favorite_no}', 'API\FavoritesAPIController@destroy');
                Route::get('/favorites', 'API\FavoritesAPIController@index');
                Route::put('/favorites/{favorite_no}', 'API\FavoritesAPIController@update');
                Route::post('/favorites/sort', 'API\FavoritesAPIController@sort');
                Route::post('/favorites/sortFavoriteItem', 'API\FavoritesAPIController@sortFavoriteItem');
                Route::delete('/favorites/deleteFavoriteItem/{favorite_route_id}', 'API\FavoritesAPIController@deleteFavoriteItem');
                Route::get('/getContactsByHash', 'API\ContactsAPIController@getContactsByHash'); //他の環境個人共通アドレス帳取得
                // Route for R10 screen
                Route::get('/userByHashing', 'API\CircularAPIController@getUserByHash');
				Route::post('/checkOutsideAccessCodeByHash', 'API\CircularAPIController@checkOutsideAccessCodeByHash');

                Route::get('/circulars/getByHash', 'API\CircularAPIController@getByHash');
                Route::get('/generateStamp', 'API\PublicAPIController@generateStamp');
                Route::get('/getStampsByHash', 'API\UserAPIController@getStampsByHash');

                Route::post('/circulars', 'API\CircularAPIController@store');

                Route::delete('/circulars/{id}', 'API\CircularAPIController@discard');

                Route::get('/getDepartmentsByHash', 'API\UserAPIController@getDepartmentsByHash'); //他の環境部署取得
                Route::get('users', 'API\UserAPIController@index');
                Route::post('/timestampinfo', 'API\TimeStampInfoAPIController@store');

                Route::get('/userView/checkemail/{email}', 'API\CircularUserAPIController@checkEmailView');
                Route::post('/default-stamp', 'API\UserAPIController@setDefaultStampId');
                Route::post('/user/checkemail', 'API\UserAPIController@checkEmail');//メールアドレスチェック

                Route::post('/store-log', 'API\OperationHistoryAPIController@store');
                //Route::get('/myStamps', 'API\UserAPIController@myStamps');
                Route::get('/getAttachment/{circular_id}','API\CircularAttachmentAPIController@show');//回覧中のすべての添付ファイルを取得します。
                Route::get('/attachmentDelete','API\CircularAttachmentAPIController@destroy');//選択した添付ファイルを削除します。
                Route::get('/attachmentDownload','API\CircularAttachmentAPIController@download');//選択した添付ファイルをダウンロードします。
//                Route::post('/changeAttachmentConfidentialFlg','API\CircularAttachmentAPIController@changeAttachmentConfidentialFlg');//選択した添付ファイルの「社外秘に設定」を修正します。
                Route::post('/attachment','API\CircularAttachmentAPIController@store');//添付ファイルをアップロード

                Route::get('long-term/{circular_id}', 'API\LongTermDocumentApiController@show');
                Route::post('/circulars/{circular_id}/store', 'API\CircularAPIController@storeCircular');
                Route::post('/long-term/getMyFolders', 'API\LongTermDocumentApiController@getMyFolders');
                Route::get('/longTermIndex/getLongTermIndex', 'API\LongTermDocumentApiController@getLongTermIndex');//長期保管インデックス取得
                Route::post('/longTermIndex/setApproval', 'API\LongTermDocumentApiController@setApprovalIndex');//長期保管インデックス設定

                Route::get('user/getBizcardId', 'API\CircularUserAPIController@getBizcardId');
                /*PAC_5-1698 S*/
                Route::get('/circulars/{circular_id}/get_plan_by_hash','API\CircularUserAPIController@planList');
                /*PAC_5-1698 E*/
                Route::post('/user/checkFavoriteUserStatus', 'API\CircularUserAPIController@checkFavoriteUserStatus');// 気に入り登録の場合、有効利用者チェック
                Route::middleware(['check_circular_view_permission'])->group(function () {
                    Route::get('/circulars/{circular_id}/stamp_infos/findByCircularDocumentId', 'API\StampInfoAPIController@findByCircularDocumentId');
                    // PAC_5-1988
                    Route::get('/circulars/{circular_id}/stamp_infos/findStampAndTextByCircularDocumentId', 'API\StampInfoAPIController@findTextByCircularDocumentId');

                    Route::post('/circulars/{circular_id}/checkAccessCode', 'API\CircularAPIController@checkAccessCode');

                    Route::post('/circulars/{circular_id}/users/sendViewedMail', 'API\CircularUserAPIController@sendViewedMail');
                    Route::post('/circulars/{circular_id}/users/sendBack', 'API\CircularUserAPIController@sendBack');
                    Route::get('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@show');
                    Route::get('/circulars/{circular_id}/documentsAll', 'API\CircularDocumentAPIController@getAllDocument');
                    Route::get('/circulars/{circular_id}/checkUsingTasSave', 'API\CircularDocumentAPIController@checkUsingTasSaveFile');
                    Route::get('/circulars/{circular_id}/checkHasSignatureSaveFile', 'API\CircularDocumentAPIController@checkHasSignatureSaveFile');
                    Route::get('/circulars/{circular_id}/checkUsingTasDownload/{circular_document_id}', 'API\CircularDocumentAPIController@checkUsingTasDownloadFile');
                    Route::get('/circulars/{circular_id}/checkUsingTasDownloadNoAddHistory/{circular_document_id}', 'API\CircularDocumentAPIController@checkUsingTasDownloadFileNoAddHistory');

                    Route::post('/circulars/{circular_id}/memo', 'API\ViewingUserAPIController@updateMemo');
                });

                Route::middleware(['check_circular_update_permission'])->group(function () {

                    Route::patch('/circulars/{circular_id}/updateStatus', 'API\CircularAPIController@updateStatus');
                    Route::put('/circulars/{circular_id}/documents/updateList', 'API\CircularDocumentAPIController@updateList');
                    Route::put('/circulars/{circular_id}/users/updates', 'API\CircularUserAPIController@updates');
                    Route::put('/circulars/{circular_id}/users/{id}', 'API\CircularUserAPIController@update');
                    Route::delete('/circulars/{circular_id}/users/{id}', 'API\CircularUserAPIController@destroy');
                    Route::delete('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@destroy');
                    Route::post('/circulars/{circular_id}/users/sendNotifyContinue', 'API\CircularUserAPIController@sendNotifyContinue');
                    Route::delete('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@destroy');
                    Route::post('/circulars/{circular_id}/users/addChild', 'API\CircularUserAPIController@storeChildren');
                    Route::patch('/circulars/{circular_id}/users/{id}/updateReturnflg', 'API\CircularUserAPIController@updateReturnflg');
					Route::put('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@rename'); // PDFファイル名変更
                    Route::put('/circulars/{circular_id}/documents/{id}/replacePdf', 'API\CircularDocumentAPIController@replacePdf')->where(['circular_id' => '[0-9]+', 'id' => '[0-9]+']); // 改ページ調整したPDFの置き換え

                });
                //Route::post('/users/updateStampsOrder', 'API\UserAPIController@updateAssignStampsOrder');

                Route::middleware(['check_circular_approval_sendback_permission'])->group(function () {
                    Route::post('/circulars/{circular_id}/users/approvalRequestSendBack', 'API\CircularUserAPIController@approvalReqSendBack');
                });

                Route::post('autoStorageBox', 'API\AutoStorageApiController@autoStorageBox'); // Boxの自動保存処理
                // api for テンプレート
                Route::get('templates-route', 'API\TemplatesRouteAPIController@getList'); // テンプレートリスト
                Route::post('/updateTextSetting', 'API\UserAPIController@updateTextSetting'); //テキスト設定更新
                Route::get('route-list', 'API\TemplatesRouteAPIController@getTemplateRouteList'); // 承認ルート一覧

                //テンプレート編集用
                Route::post('/templates/saveTemplateEditStamp', 'API\TemplateAPIController@saveTemplateEditStamp');
                Route::post('/templates/saveTemplateEditText', 'API\TemplateAPIController@saveTemplateEditText');
                Route::post('/templates/getTemplateNextUserCompletedFlg', 'API\TemplateAPIController@getTemplateNextUserCompletedFlg');
                Route::post('/templates/getTemplateInputComplete', 'API\TemplateAPIController@getTemplateInputComplete');
                Route::post('/templates/edit/{templateId}', 'API\TemplateAPIController@edit');
                Route::post('/templates/getTemplateEditStamp', 'API\TemplateAPIController@getTemplateEditStamp');
                Route::post('/templates/getTemplateEditText', 'API\TemplateAPIController@getTemplateEditText');
                Route::post('/templates/templateStampInfoDelete', 'API\TemplateAPIController@templateStampInfoDelete');
                Route::post('/templates/tempEditStampInfoFix', 'API\TemplateAPIController@tempEditStampInfoFix');
                Route::post('/templates/releaseTemplateEditFlg', 'API\TemplateAPIController@releaseTemplateEditFlg');
                Route::post('/templates/templateEditS3delete', 'API\TemplateAPIController@templateEditS3delete');
                Route::post('/templates/updateTemplateRoute/{templateId}', 'API\TemplateAPIController@updateTemplateRoute');
            });
            Route::post('/circulars/{origin_circular_id}/autoStorageUpdateStatus', 'API\AutoStorageApiController@autoStorageUpdateStatus');
            Route::get('setting/getMyCompanyConstraintsMaxDocumentSize/{company_id}/{circular_id}',
                'API\SettingAPIController@getMyCompanyConstraintsMaxDocumentSize')->where(['company_id' => '[0-9]+', 'circular_id' => '[0-9]+']);


        });
        Route::middleware(['auth:api', 'JsonApiMiddleware', 'scope:user,audit'])->group(function () {
            Route::get('/logout', 'API\AuthController@logout');
            Route::get('/user', function (Request $request) {
                return $request->user();
            });

            Route::get('setting/getMyCompany', 'API\SettingAPIController@getDetailCompany');
            Route::get('setting/getBizcardFlg', 'API\SettingAPIController@getBizcardFlg');
            Route::get('setting/getMyCompanyConstraintsMaxDocumentSize/{company_id}/{circular_id}',
                'API\SettingAPIController@getMyCompanyConstraintsMaxDocumentSize')->where(['company_id' => '[0-9]+', 'circular_id' => '[0-9]+']);
            Route::get('setting/getBizcardFlg', 'API\SettingAPIController@getBizcardFlg');
            //add new route to get work detail
            Route::get('work/getWorkDetail/{working_month}', 'API\HRWorkDetailAPIController@getWorkDetail');
            Route::get('work/getWorkList/{working_month}', 'API\HRWorkDetailAPIController@getWorkList');

            Route::post('/long-term/document', 'API\LongTermDocumentApiController@index');// document list
            Route::post('/long-term/download', 'API\LongTermDocumentApiController@download');
            Route::post('/long-term/downloadList', 'API\LongTermDocumentApiController@downloadList');
            Route::get('/long-term/{circular_id}', 'API\LongTermDocumentApiController@show');
            Route::put('/long-term/{id}', 'API\LongTermDocumentApiController@update');
            Route::post('/long-term/automatic-update-timestamp', 'API\LongTermDocumentApiController@automaticUpdateTimestamp');
            Route::get('/long-term/index/list', 'API\LongTermDocumentApiController@listIndex');
            Route::get('/long-term/index/list/Option', 'API\LongTermDocumentApiController@listIndexOption');
            Route::get('/long-term/index/list/getLongTermIndex', 'API\LongTermDocumentApiController@getLongTermIndexValue');
            Route::post('/long-term/index/set', 'API\LongTermDocumentApiController@setIndex');
            Route::post('/long-term/downloadattachment', 'API\LongTermDocumentApiController@downloadattachment'); //PAC_5-2377
            Route::post('/long-term/saveLongTermDocument', 'API\LongTermDocumentApiController@saveLongTermDocument'); //PAC_5-2395
            Route::post('/long-term/longTermUpload', 'API\LongTermDocumentApiController@longTermUpload'); //PAC_5-2395
            Route::post('/long-term/getMyFolders', 'API\LongTermDocumentApiController@getMyFolders'); //PAC_5-2279
            Route::post('/long-term/updateFolderId', 'API\LongTermDocumentApiController@updateFolderId'); //PAC_5-2279
            Route::post('/long-term/getCircularPageData', 'API\LongTermDocumentApiController@getCircularPageData');
            Route::post('/download-request/index', 'API\DownloadRequestApiController@index');           // ダウンロード申請一覧
            Route::post('/download-request/delete', 'API\DownloadRequestApiController@delete');         // ダウンロード申請削除
            Route::post('/download-request/download', 'API\DownloadRequestApiController@download');     // ダウンロード
            Route::post('/download-request/rerequest', 'API\DownloadRequestApiController@rerequest');   // ダウンロード申請再申請
            Route::post('/download-request/updateCircularStatus', 'API\DownloadRequestApiController@updateCircularStatus'); // 回覧状態は保存済みに更新
            // PAC_5-2874
            Route::post('/download-request/sanitizingUpdate', 'API\DownloadRequestApiController@sanitizingUpdate'); // 無害化状態は、無害化待ちに更新

            Route::post('/mfa/authByEmail', 'API\MfaController@authByEmail');
            Route::post('/mfa/authByQrCode', 'API\MfaController@authByQrCode');
            Route::post('/mfa/checkQrCodeAuth', 'API\MfaController@checkQrCodeAuth');
            Route::post('/mfa/resendAuthMail', 'API\MfaController@resendAuthMail');

            Route::get('/myinfo', 'API\UserAPIController@myInfo');
            Route::get('/userinfo', 'API\UserAPIController@getUserInfo');
            Route::post('/myinfo', 'API\UserAPIController@updateMyInfo');
            Route::post('/user/update-comment', 'API\UserAPIController@updateMyInfo');
            Route::post('/user/update-display-setting', 'API\UserAPIController@updateMyInfo');
            Route::post('/user/update-password', 'API\UserAPIController@updatePassword');
            Route::get('getCompanyDepartment', 'API\UserAPIController@getDepartment');// getDepartment

            Route::post('/store-log', 'API\OperationHistoryAPIController@store');
            Route::post('/updateTextSetting', 'API\UserAPIController@updateTextSetting'); //テキスト設定更新
            Route::get('setting/limit', 'API\SettingAPIController@getLimit');


            // Download Reserve
            Route::post('/circulars/reserve', 'API\DownloadRequestApiController@reserve');
            // Download Reserve
            Route::post('/circulars/downloadLongTerm', 'API\DownloadRequestApiController@downloadLongTerm');
            Route::middleware(['check_circular_view_permission'])->group(function () {
                Route::get('/circulars/{circular_id}/detail-user', 'API\CircularUserAPIController@detailUser'); // 一覧画面から詳細内容表示エリア取得
            });

            // PAC_5-2302 長期保管 - 監査用アカウントでログインしたときに設定を表示するとエラーが発生
            Route::get('setting/password-policy', 'API\SettingAPIController@getPasswordPolicy');
        });

        Route::middleware(['auth:api', 'JsonApiMiddleware', 'scope:user'])->group(function () {
            //header("Access-Control-Allow-Origin: *");
            //header("Access-Control-Allow-Headers: *");
            Route::post('user/updateOperationNotice', 'API\UserAPIController@updateOperationNotice');
            Route::get('user/getTemplates', 'API\UserAPIController@getTemplates');
            Route::post('user/updateTemplates', 'API\UserAPIController@updateTemplates');

            Route::get('/userView/checkemail/{email}', 'API\CircularUserAPIController@checkEmailView');
            Route::get('/myStamps', 'API\UserAPIController@myStamps');
            Route::get('/myCompanyStamps', 'API\UserAPIController@myCompanyStamps');
            Route::post('/user/checkemail', 'API\UserAPIController@checkEmail');
            Route::get('user/getBizcardId', 'API\CircularUserAPIController@getBizcardId');
            Route::get('/users-departments', 'API\UserAPIController@getUsersDepartments'); //本環境部署取得

            Route::resource('users', 'API\UserAPIController');
            Route::resource('contacts', 'API\ContactsAPIController'); //個人共通アドレス帳取得
            Route::resource('viewing-user', 'API\ViewingUserAPIController');
            Route::resource('favorites', 'API\FavoritesAPIController');
            Route::post('/favorites/sort', 'API\FavoritesAPIController@sort');
            Route::post('/favorites/sortFavoriteItem', 'API\FavoritesAPIController@sortFavoriteItem');
            Route::delete('/favorites/deleteFavoriteItem/{favorite_route_id}', 'API\FavoritesAPIController@deleteFavoriteItem');

            // API for screen favorite
            Route::resource('favorite', 'API\FavoriteServiceController');
            Route::resource('internalsv', 'API\MstFavoriteServiceAPIController');

            // API advertisement
            Route::get('advertisementmg', 'API\AdvertisementManagementAPIController@index');

            // API movie
            Route::get('moviemg', 'API\MovieManagementAPIController@index');
            Route::get('movietheme', 'API\MovieManagementAPIController@getThemeList');
            Route::get('moviemgtop', 'API\MovieManagementAPIController@getTopList');
            Route::post('movieaddplaycount', 'API\MovieManagementAPIController@addPlayCount');

            // API customize area
            Route::get('customizemg', 'API\CustomizeManagementAPIController@index');

            Route::post('/long-term/delete', 'API\LongTermDocumentApiController@delete');
            // API for notification
            Route::get('/loginat', 'API\OperationHistoryAPIController@lastLoginAt');
            Route::resource('noticemg', 'API\NoticeManagementAPIController');
            Route::post('noticeread', 'API\NoticeReadManagementAPIController@store');
            Route::get('noticeunread', 'API\NoticeManagementAPIController@unread');
            Route::resource('noticehistory', 'API\NoticeHistoryAPIController');
            Route::get('/getUserInfoById/{id}', 'API\UserInfoAPIController@getUserInfoById');

            Route::get('bbslist' , 'API\BbsAPIController@getTopicList');
            Route::get('bbscategorylist' , 'API\BbsAPIController@getBbsCategories');
            Route::get('bbsAuth' , 'API\BbsAPIController@getBbsAuth');
            Route::get('bbsMember' , 'API\BbsAPIController@getBbsMember');
            Route::get('bbsMemberForPage' , 'API\BbsAPIController@getBbsMemberForPage');
            Route::get('bbsMemberByIds' , 'API\BbsAPIController@getBbsMemberByIds');
            Route::get('getBbsTopicLikes', 'API\BbsAPIController@getTopicLikes');
            Route::post('addBbsTopicLike', 'API\BbsAPIController@addTopicLike');
            Route::post('deleteBbsTopicLike', 'API\BbsAPIController@deleteLikeTopic');

            Route::get('faqbbslist' , 'API\FaqBbsApiController@getTopicList');

            Route::get('faqbbslist' , 'API\FaqBbsApiController@getTopicList');
            Route::get('faqbbscategorylist' , 'API\FaqBbsApiController@getBbsCategories');
            Route::get('faqbbsAuth' , 'API\FaqBbsApiController@getBbsAuth');
            Route::get('faqbbsMember' , 'API\FaqBbsApiController@getBbsMember');
            Route::post('getFaqBbsFile' , 'API\FaqBbsApiController@getFile');
            /*PAC_5-1807 S*/
            Route::post('getBbsFile' , 'API\BbsAPIController@getFile');
            Route::get('getBbsSetting' , 'API\BbsAPIController@getBbsSetting');
            /*PAC_5-1807 E*/
            /*PAC_5-1846 S*/
            Route::get('bbs_unread_cnt' , 'API\BbsAPIController@unReadCnt');
            Route::put('bbs_notice_read/{notice_id}' , 'API\BbsAPIController@makeNoticeRead');
            Route::put('bbs_all_notice_read' , 'API\BbsAPIController@makeAllNoticeRead');
            Route::get('bbs_notice_list' , 'API\BbsAPIController@getNoticeList');
            /*PAC_5-1846 E*/

            Route::get('faq_bbs_unread_cnt' , 'API\FaqBbsApiController@unReadCnt');
            Route::put('faq_bbs_notice_read/{notice_id}' , 'API\FaqBbsApiController@makeNoticeRead');
            Route::put('faq_bbs_notice_read_by_bbs/{bbs_id}' , 'API\FaqBbsApiController@makeNoticeReadByBbsId');
            Route::put('faq_bbs_all_notice_read' , 'API\FaqBbsApiController@makeAllNoticeRead');
            Route::get('faq_bbs_notice_list' , 'API\FaqBbsApiController@getNoticeList');

            Route::post('delbbstopic', 'API\BbsAPIController@deleteTopic');
            Route::post('updbbstopic', 'API\BbsAPIController@updateTopic');
            Route::post('addbbstopic', 'API\BbsAPIController@addTopic');

            Route::post('addBbsDraftTopic', 'API\BbsAPIController@addDraftTopic');
            Route::post('updateBbsDraftTopic', 'API\BbsAPIController@updateDraftTopic');
            Route::post('delBbsDraftTopic', 'API\BbsAPIController@deleteDraftTopic');

            Route::post('addfaqbbstopic', 'API\FaqBbsApiController@addTopic');
            Route::post('updfaqbbstopic', 'API\FaqBbsApiController@updateTopic');
            Route::post('delfaqbbstopic', 'API\FaqBbsApiController@deleteTopic');

            Route::post('delbbscomment', 'API\BbsAPIController@deleteComment');
            Route::post('updbbscomment', 'API\BbsAPIController@updateComment');
            Route::post('addbbscomment', 'API\BbsAPIController@addComment');

            Route::post('delfaqbbscomment', 'API\FaqBbsApiController@deleteComment');
            Route::post('addfaqbbscomment', 'API\FaqBbsApiController@addComment');
            Route::post('updfaqbbscomment', 'API\FaqBbsApiController@updateComment');

            Route::post('delbbscategory', 'API\BbsAPIController@deleteCategory');
            Route::post('updbbscategory', 'API\BbsAPIController@updateCategory');
            Route::post('addbbscategory', 'API\BbsAPIController@addCategory');

            Route::get('timecard', 'API\TimeCardController@index');
            Route::post('timecard/{type}', 'API\TimeCardController@store');
            Route::put('timecard/update', 'API\TimeCardController@update');
            Route::get('timecard/search-list', 'API\TimeCardController@searchList');
            Route::get('timecard/show-detail', 'API\TimeCardController@showDetail');
            Route::get('timecard/csvDownload', 'API\TimeCardController@csvDownload');
            Route::post('/userimage', 'API\UserInfoAPIController@userImage');

            // Group API HR
            Route::get('work-list', 'API\WorkListAPIController@index');
            Route::resource('time-card', 'API\@index');
            Route::post('timecard-detail/export-work-list', 'API\HRWorkDetailAPIController@exportHrWorkListToCSV');
            Route::get('worktime', 'API\HRWorkDetailAPIController@workTime');
            Route::get('hours_work_time/{id}', 'API\HRWorkDetailAPIController@showWorkTime');
            Route::get('hr-info', 'API\MstHrInfoAPIController@index');
            Route::get('/timecard-detail/export-to-new-edition', 'API\CircularAPIController@exportWorkListToPdf');
            Route::resource('timecard-detail', 'API\HRWorkDetailAPIController');
            Route::get('/register-new-time-card', 'API\HRWorkDetailAPIController@registerNewTimeCardDetail');
            Route::get('/user-hr-info/{id}', 'API\HRWorkDetailAPIController@getWorkDetailHrInfo');
            Route::get('/hr-mail-setting', 'API\HRWorkDetailAPIController@getHrMailSetting');//勤怠連絡設定情報取得
            Route::post('/hr-mail-setting/update', 'API\HRWorkDetailAPIController@updateHrMailSetting');//勤怠連絡設定更新
            Route::post('/hr-mail-send', 'API\HRWorkDetailAPIController@hrMailSend');//勤怠連絡送信

            Route::put('/leave-work/{id}', 'API\HRWorkDetailAPIController@leaveWork');
            Route::put('/break-work/{id}', 'API\HRWorkDetailAPIController@breakWork');
            Route::get('/detail-work-by-timecard/{id}', 'API\HRWorkDetailAPIController@getWorkDetailByTimecard');
            Route::resource('daily-report', 'API\MstHrDailyReportAPIController');
            Route::post('/updateSubmissionState/{working_month}', 'API\WorkListAPIController@updateSubmissionState');
            Route::get('user-work-list', 'API\UserWorkListAPIController@index');
            Route::post('/user-work-list/updateApprovalState', 'API\UserWorkListAPIController@bulkApproval');
            Route::post('/user-work-list/export-join-wk-list-to-pdf', 'API\UserWorkListAPIController@exportJoinWkListToPDF');
            Route::post('/user-work-list/export-to-csv', 'API\UserWorkListAPIController@exportListToCSV');
            Route::post('/user-work-list/updateSubmissionState', 'API\UserWorkListAPIController@bulkRemand');
            Route::get('/user-work-detail/user/{id}', 'API\UserWorkDetailAPIController@getUser');
            Route::get('/user-work-detail/{id}', 'API\UserWorkDetailAPIController@getUserWorkDetailByTimecard');
            Route::get('/user-work-detail/{id}/{working_month}', 'API\UserWorkDetailAPIController@getUserWorkDetail');
            Route::get('/user-work-detail―get-hr-info/{id}', 'API\UserWorkDetailAPIController@getUserHrInfo');
            Route::post('user-work-detail/updateApprovalState', 'API\UserWorkDetailAPIController@bulkApproval');
            Route::post('user-work-detail/export-to-csv', 'API\UserWorkDetailAPIController@exportListToCSV');
            Route::post('user-work-detail/updateUserSubmissionState', 'API\UserWorkDetailAPIController@bulkRemand');
            Route::resource('user-work-detail', 'API\UserWorkDetailAPIController');
            // PAC_5-3036 HR機能 - 勤務状況確認 start
            Route::get('user-work-status-list', 'API\UserWorkStatusListAPIController@index');
            // PAC_5-3036 HR機能 - 勤務状況確認 end
            Route::resource('user-daily-report', 'API\UserDailyReportAPIController');

            // API for screen edit document
            Route::post('/circulars', 'API\CircularAPIController@store');
            Route::post('/timestampinfo', 'API\TimeStampInfoAPIController@store');

            Route::get('setting/getCreateCircularCompany','API\SettingAPIController@getCreateCircularCompany');//回覧申請者の企業 添付ファイル機能　
            Route::post('/attachment','API\CircularAttachmentAPIController@store');//添付ファイルをアップロード
            Route::get('/attachmentDownload','API\CircularAttachmentAPIController@download');//選択した添付ファイルをダウンロードします。
            Route::get('/attachmentDelete','API\CircularAttachmentAPIController@destroy');//選択した添付ファイルを削除します。
//            Route::post('/changeAttachmentConfidentialFlg','API\CircularAttachmentAPIController@changeAttachmentConfidentialFlg');///選択した添付ファイルの「社外秘に設定」を修正します。
            Route::get('/getAttachment/{circular_id}','API\CircularAttachmentAPIController@show');//回覧中のすべての添付ファイルを取得します。
            Route::resource('mypage', 'API\MyPageController');
//            Route::get('mstmypage', 'API\MstMyPageLayoutAPIController@index');
            Route::get('mstmypage', 'API\MyPageController@getMyPageList');
            Route::post('/storeMailFile','API\Portal\GroupWare\DiskMailFileAPIController@storeMailFile');//ファイルメール便をアップロード
            Route::post('/deleteMailFile','API\Portal\GroupWare\DiskMailFileAPIController@deleteMailFile');//ファイルメール便を削除
            Route::post('/getMailFileList','API\Portal\GroupWare\DiskMailFileAPIController@getMailFileList');//ファイルメール便一覧
            Route::post('/sendMailFile','API\Portal\GroupWare\DiskMailFileAPIController@sendMailFile');//ファイルメール便一覧
            Route::post('/deleteMailItem','API\Portal\GroupWare\DiskMailFileAPIController@deleteMailItem');//ファイルメール便一覧
            Route::get('/getDiskMailItem/{mail_id}','API\Portal\GroupWare\DiskMailFileAPIController@getDiskMailItem');//ファイルメール便一覧　送信詳細内容
            Route::post('/downloadDiskMailItem','API\Portal\GroupWare\DiskMailFileAPIController@downloadItem');//ファイルメール便一覧 ダウンロード
            Route::post('/getDiskMailInfo','API\Portal\GroupWare\DiskMailFileAPIController@getDiskMailInfo');//ファイルメール便 テンプレート機能
            Route::post('/updateDiskMailInfo','API\Portal\GroupWare\DiskMailFileAPIController@updateDiskMailInfo');//ファイルメール便 テンプレート更新
            Route::post('/sendMailFileAgain','API\Portal\GroupWare\DiskMailFileAPIController@sendMailFileAgain');//ファイルメール便 再送信

            Route::delete('/circulars/{id}', 'API\CircularAPIController@discard');
            Route::patch('/circulars/{circular_id}/updateStatus', 'API\CircularAPIController@updateStatus'); // ファイルステータスの更新
            Route::post('/user/checkFavoriteUserStatus', 'API\CircularUserAPIController@checkFavoriteUserStatus'); // 気に入り登録の場合、有効利用者チェック
            Route::get('/receive_plan/get_url', 'API\ReceivePlanAPIController@getUrl'); // 受信専用のURL
            // To Do List Api
            Route::get('/to-do-list/circulars', 'API\ToDoListAPIController@getCircularList');
            Route::get('/to-do-list/circular/{circular_user_id}', 'API\ToDoListAPIController@getCircularTaskDetail');
            Route::put('/to-do-list/circular/{circular_user_id}', 'API\ToDoListAPIController@updateCircularTask');
            Route::get('/to-do-list/list', 'API\ToDoListAPIController@getToDoList');
            Route::get('/to-do-list/list/{to_do_list_id}', 'API\ToDoListAPIController@getToDoListDetail');
            Route::post('/to-do-list/list', 'API\ToDoListAPIController@addToDoList');
            Route::put('/to-do-list/list/{to_do_list_id}', 'API\ToDoListAPIController@updateToDoList');
            Route::delete('/to-do-list/list/{to_do_list_id}', 'API\ToDoListAPIController@deleteToDoList');
            Route::get('/to-do-list/{to_do_list_id}/task', 'API\ToDoListAPIController@getTaskList');
            Route::get('/to-do-list/task/{task_id}', 'API\ToDoListAPIController@getTaskDetail');
            Route::post('/to-do-list/task', 'API\ToDoListAPIController@addTask');
            Route::put('/to-do-list/task/{task_id}', 'API\ToDoListAPIController@updateTask');
            Route::delete('/to-do-list/task/{task_id}', 'API\ToDoListAPIController@deleteTask');
            Route::get('/to-do-list/group', 'API\ToDoListAPIController@getGroupList');
            Route::get('/to-do-list/group-list', 'API\ToDoListAPIController@getGroupSimpleList');
            Route::get('/to-do-list/group/{group_id}', 'API\ToDoListAPIController@getGroupDetail');
            Route::get('/to-do-list/department', 'API\ToDoListAPIController@getDepartmentList');
            Route::get('/to-do-list/users', 'API\ToDoListAPIController@getUserList');
            Route::post('/to-do-list/group', 'API\ToDoListAPIController@addGroup');
            Route::put('/to-do-list/group/{group_id}', 'API\ToDoListAPIController@updateGroup');
            Route::delete('/to-do-list/group/{group_id}', 'API\ToDoListAPIController@deleteGroup');
            Route::post('/to-do-list/task/done/{task_id}', 'API\ToDoListAPIController@doneTask');
            Route::post('/to-do-list/task/revoke/{task_id}', 'API\ToDoListAPIController@revokeTask');
            Route::get('/to-do-list/scheduler', 'API\ToDoListAPIController@getSchedulerList');
            Route::get('/to-do-list/notice', 'API\ToDoListAPIController@getNoticeConfig');
            Route::put('/to-do-list/notice', 'API\ToDoListAPIController@settingNoticeConfig');
            Route::get('/to-do-list/notice/list', 'API\ToDoListAPIController@getNoticeList');
            Route::get('/to-do-list/unread', 'API\ToDoListAPIController@getUnReadList');
            Route::get('/to-do-list/unread/count', 'API\ToDoListAPIController@countUnRead');
            Route::post('/to-do-list/read/{notice_id}', 'API\ToDoListAPIController@readNotice');
            Route::post('/to-do-list/read-all', 'API\ToDoListAPIController@readNoticeAll');


            // feature/expense_settlement_vn
            Route::middleware(['check_expense_permission'])->prefix('expense_settlement')->group(function () {
                Route::get('', 'API\ExpenseSettlementAPIController@index');
                Route::get('/getMPurposeDataSelect', 'API\EpsMAPIController@getMFormPurposeDataSelect');
                Route::get('/getEpsMPurposeInfo', 'API\EpsMAPIController@getEpsMPurposeInfo');
                Route::post('/updateExpenseCircularInfo', 'API\ExpenseSettlementAPIController@updateExpenseCircularInfo');
                Route::post('/updateExpenseCircularContent', 'API\ExpenseSettlementAPIController@updateExpenseCircularContent');

                Route::post('/updateExpenseFormInput', 'API\EpsMAPIController@updateExpenseFormInput');
                Route::get('/getMFormPurposeDataSelect', 'API\EpsMAPIController@getMFormPurposeDataSelect');
                Route::post('/saveExpense', 'API\ExpenseSettlementAPIController@saveExpense');
                Route::post('/saveExpenseInputData', 'API\ExpenseSettlementAPIController@saveExpenseInputData');
                Route::post('/updateExpense/{id}', 'API\ExpenseSettlementAPIController@updateExpense');

                Route::get('/getEpsMWtsmName', 'API\EpsMAPIController@getEpsMWtsmName');
                Route::get('/getCurrentUserDepartmentInfo', 'API\UserInfoAPIController@getCurrentUserDepartmentInfo');
                Route::get('/getListTAppItems', 'API\ExpenseSettlementAPIController@getListTAppItems');

                Route::get('/files/{id}', 'API\ExpenseSettlementAPIController@downloadFile');
                Route::delete('/files/{id}', 'API\ExpenseSettlementAPIController@deleteFile');
                Route::get('/getEpsMFormRelation', 'API\EpsMAPIController@getEpsMFormRelation');
               Route::put('/deleteEpsTAppAndItems/{id}', 'API\EpsTAPIController@deleteEpsTAppAndItems');
            });




            Route::middleware(['check_circular_view_permission'])->group(function () {
                Route::get('/circulars/{circular_id}/checkUsingTasSave', 'API\CircularDocumentAPIController@checkUsingTasSaveFile');
                Route::get('/circulars/{circular_id}/checkHasSignatureSaveFile', 'API\CircularDocumentAPIController@checkHasSignatureSaveFile');
                Route::get('/circulars/{circular_id}/checkUsingTasDownload/{circular_document_id}', 'API\CircularDocumentAPIController@checkUsingTasDownloadFile');
                Route::get('/circulars/{circular_id}/checkUsingTasDownloadNoAddHistory/{circular_document_id}', 'API\CircularDocumentAPIController@checkUsingTasDownloadFileNoAddHistory');
                Route::get('/circulars/{circular_id}/documentsAll', 'API\CircularDocumentAPIController@getAllDocument');

                Route::get('/circulars/{circular_id}', 'API\CircularAPIController@show');
                Route::get('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@show');
                Route::post('/circulars/{circular_id}/checkAccessCode', 'API\CircularAPIController@checkAccessCode');
                Route::get('/circulars/{circular_id}/stamp_infos/findByCircularDocumentId', 'API\StampInfoAPIController@findByCircularDocumentId');
                // PAC_5-1988
                Route::get('/circulars/{circular_id}/stamp_infos/findStampAndTextByCircularDocumentId', 'API\StampInfoAPIController@findTextByCircularDocumentId');

                Route::post('/circulars/{circular_id}/users/sendViewedMail', 'API\CircularUserAPIController@sendViewedMail');
                Route::post('/circulars/{circular_id}/users/sendBack', 'API\CircularUserAPIController@sendBack');
                Route::post('/circulars/{circular_id}/store', 'API\CircularAPIController@storeCircular');
                Route::get('/circulars/{circular_id}/origin-circular-url', 'API\CircularUserAPIController@getOriginCircularUrl');

                Route::post('/circulars/{circular_id}/memo', 'API\ViewingUserAPIController@updateMemo');
            });

            Route::middleware(['check_circular_update_permission'])->group(function () {

                Route::put('/circulars/{circular_id}', 'API\CircularAPIController@update');

                Route::post('/circulars/{circular_id}/users/sendNotifyFirst', 'API\CircularUserAPIController@sendNotifyFirst');
                Route::post('/circulars/{circular_id}/users/sendAllUserFirst', 'API\CircularUserAPIController@handlerCircularUserSendNotifyFirst');
                Route::post('/circulars/{circular_id}/users/handlerCircularUserInsert', 'API\CircularUserAPIController@handlerCircularUserInsert');
                Route::post('/circulars/{circular_id}/users/sendNotifyContinue', 'API\CircularUserAPIController@sendNotifyContinue');
                Route::post('/circulars/{circular_id}/users/addChild', 'API\CircularUserAPIController@storeChildren');
                Route::delete('/circulars/{circular_id}/users/clear', 'API\CircularUserAPIController@clear');
                Route::put('/circulars/{circular_id}/users/updates', 'API\CircularUserAPIController@updates');
                Route::put('/circulars/{circular_id}/documents/updateList', 'API\CircularDocumentAPIController@updateList');
                Route::delete('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@destroy');
				Route::put('/circulars/{circular_id}/documents/{id}', 'API\CircularDocumentAPIController@rename'); // PDFファイル名変更
                Route::put('/circulars/{circular_id}/documents/{id}/replacePdf', 'API\CircularDocumentAPIController@replacePdf')->where(['circular_id' => '[0-9]+', 'id' => '[0-9]+']); // 改ページ調整したPDFの置き換え
                Route::resource('/circulars/{circular_id}/users', 'API\CircularUserAPIController');
                //Route::post('/circulars/{circular_id}/stamp_infos', 'API\StampInfoAPIController@store');
                Route::patch('/circulars/{circular_id}/users/{id}/updateReturnflg', 'API\CircularUserAPIController@updateReturnflg');
                /*PAC_5-1698 S*/
                Route::post('/circulars/{circular_id}/plan','API\CircularUserAPIController@updatePlan');
                Route::get('/circulars/{circular_id}/get_plan','API\CircularUserAPIController@planList');
                Route::get('/circulars/{circular_id}/del_plan/{id}','API\CircularUserAPIController@planDelete');
                /*PAC_5-1698 E*/

                Route::post('/circulars/{circular_id}/users/saveCircularSetting', 'API\CircularUserAPIController@saveCircularSetting');//文書申請の回覧設定保存
                Route::post('/circulars/{circular_id}/users/getCircularSetting', 'API\CircularUserAPIController@getCircularSetting');//文書申請の回覧設定取得
            });
            Route::middleware(['check_circular_pullback_permission'])->group(function () {
                Route::get('/circulars/{circular_id}/pullback', 'API\CircularUserAPIController@pullback');
            });

            Route::middleware(['check_circular_request_sendback_permission'])->group(function () {
                Route::post('/circulars/{circular_id}/requestSendBack', 'API\CircularUserAPIController@requestSendBack');
            });

            Route::middleware(['check_circular_approval_sendback_permission'])->group(function () {
                Route::post('/circulars/{circular_id}/users/approvalRequestSendBack', 'API\CircularUserAPIController@approvalReqSendBack');
            });


            Route::post('/users/updateStampsOrder', 'API\UserAPIController@updateAssignStampsOrder');

            // API for save/receive/send/complete list
            Route::post('/circulars/saved', 'API\CircularDocumentAPIController@index');// save list
            Route::post('/circulars/sent', 'API\CircularUserAPIController@indexSent'); // search in send list

            Route::get('/circulars/sent/{circular_id}', 'API\CircularUserAPIController@getCircularSentById');

            Route::post('/circulars/received', 'API\CircularUserAPIController@indexReceived'); // search in receive list
            Route::post('/circulars/completed', 'API\CircularUserAPIController@indexCompleted'); // search in complete list
            Route::post('/circulars/viewing', 'API\CircularUserAPIController@indexViewing'); // search in viewing list

            Route::post('/expense/received', 'API\ExpenseAPIController@indexReceived');

            Route::post('/circulars/csv-reserve', 'API\CircularCsvDownloadAPIController@csvDownload');

            // 特設サイト
            Route::post('/special/received', 'API\SpecialAPIController@indexReceived'); // search in receive list
            Route::post('/special/template', 'API\SpecialAPIController@indexTemplate'); // search in template list

            Route::post('/bbsAttachment/reserve', 'API\DownloadRequestApiController@bbsAttachmentReserve');//添付ファイルダウンロード予約
            Route::post('/previewFile/reserve', 'API\DownloadRequestApiController@previewFileReserve');//文書プレビューダウンロード予約
            Route::post('/attachment/reserve', 'API\DownloadRequestApiController@attachmentReserve');//添付ファイルダウンロード予約

            Route::get('/circulars/received/unread', 'API\CircularAPIController@getTotalCircularUnread');

            // API for mobile
            Route::get('/myCirculars/count-status', 'API\CircularUserAPIController@countCircularStatus'); // count circular status for user

            Route::middleware(['check_multiple_circular_permission'])->group(function () {
                Route::post('/circulars/actionMultiple/{action}', 'API\CircularAPIController@actionMultiple'); // delete, renotification, downloadFile in list circular
            });
            Route::get('setting/protection', 'API\SettingAPIController@getProtection');

            Route::post('autoStorageBox', 'API\AutoStorageApiController@autoStorageBox'); // Boxの自動保存処理
            //API for template
            Route::middleware(['check_template_permission'])->group(function () {
                Route::get('/templates', 'API\TemplateAPIController@index');
                Route::get('/templates/indexEdit', 'API\TemplateAPIController@indexEdit');
                Route::post('/templates/delete', 'API\TemplateAPIController@delete');
                Route::post('/templates/upload', 'API\TemplateAPIController@uploadTemplate');
                Route::get('/templates/get/{templateId}', 'API\TemplateAPIController@getFile');
                Route::post('/templates/edit/{templateId}', 'API\TemplateAPIController@edit');
                Route::get('/templates/{templateId}', 'API\TemplateAPIController@getContentTemplate');
                Route::get('/templates_middle_edit/{circularId}', 'API\TemplateAPIController@getContentTemplateEdit');
                Route::post('/templates/save/inputData', 'API\TemplateAPIController@saveInputData');
                Route::post('/templates/save/saveInputEditTemplate', 'API\TemplateAPIController@saveInputEditTemplate');
                Route::post('/templates/CsvDownloadUserForm', 'API\TemplateAPIController@CsvDownloadUserForm');
                Route::post('/templates/getCsvFlg', 'API\TemplateAPIController@getCsvFlg');
                Route::post('/templates/templateCsvCheckEmail', 'API\TemplateAPIController@templateCsvCheckEmail');
                Route::get('/templates_special/{templateId}', 'API\TemplateAPIController@getContentTemplateSpecial');
                Route::post('/templates/getTemplateInfo', 'API\TemplateAPIController@getTemplateInfo');
                Route::post('/templates/saveTemplateEditStamp', 'API\TemplateAPIController@saveTemplateEditStamp');
                Route::post('/templates/saveTemplateEditText', 'API\TemplateAPIController@saveTemplateEditText');
                Route::post('/templates/getTemplateEditStamp', 'API\TemplateAPIController@getTemplateEditStamp');
                Route::post('/templates/getTemplateEditText', 'API\TemplateAPIController@getTemplateEditText');
                Route::post('/templates/sendTemplateEditFlg', 'API\TemplateAPIController@sendTemplateEditFlg');
                Route::post('/templates/releaseTemplateEditFlg', 'API\TemplateAPIController@releaseTemplateEditFlg');
                Route::post('/templates/getCircularTempEdit', 'API\TemplateAPIController@getCircularTempEdit');
                Route::post('/templates/getTemplateInputComplete', 'API\TemplateAPIController@getTemplateInputComplete');
                Route::post('/templates/getTemplateNextUserCompletedFlg', 'API\TemplateAPIController@getTemplateNextUserCompletedFlg');
                Route::post('/templates/templateEditS3delete', 'API\TemplateAPIController@templateEditS3delete');
                Route::post('/templates/templateStampInfoDelete', 'API\TemplateAPIController@templateStampInfoDelete');
                Route::post('/templates/tempEditStampInfoFix', 'API\TemplateAPIController@tempEditStampInfoFix');
                Route::post('/templates/updateTemplateRoute/{templateId}', 'API\TemplateAPIController@updateTemplateRoute');
                Route::post('/templates/getTemplateRouteInfo/{templateId}', 'API\TemplateAPIController@getTemplateRouteInfo');
            });
            Route::middleware(['check_template_csv_permission'])->group(function () {
                Route::post('/templatecsv', 'API\CsvTemplateDownloadController@index');
                Route::post('/templatecsv/csvDownloadReserve', 'API\CsvTemplateDownloadController@csvDownloadReserve');
            });
            // api for テンプレート
            Route::get('templates-route', 'API\TemplatesRouteAPIController@getList'); // テンプレートリスト
            Route::get('route-list', 'API\TemplatesRouteAPIController@getTemplateRouteList'); // 承認ルート一覧

            //app role
            Route::get('groupware/app_role', 'API\GroupwareAppRoleAPIController@app_role');

            //API for form_issuance
            Route::middleware(['check_form_issuance_permission'])->group(function () {
                Route::get('/form-issuances', 'API\FormIssuance\FormIssuanceAPIController@index');
                Route::post('/form-issuances/upload', 'API\FormIssuance\FormIssuanceAPIController@uploadTemplate');
                Route::post('/form-issuances/list/getListReport', 'API\FormIssuance\FormIssuanceListAPIController@indexReport'); // 請求書一覧取得
                Route::post('/form-issuances/list/getListReportOther', 'API\FormIssuance\FormIssuanceListAPIController@indexReportOther'); // その他帳票一覧取得
                Route::get('/form-issuances/list/{report_id}/detail', 'API\FormIssuance\FormIssuanceListAPIController@detailShowInvoice'); // 一覧画面から詳細内容表示エリア取得
                Route::get('/form-issuances/list/{report_id}/detailOther', 'API\FormIssuance\FormIssuanceListAPIController@detailShowOther'); // 一覧画面から詳細内容表示エリア取得
                Route::post('/form-issuances/list/getListTemplate', 'API\FormIssuance\FormIssuanceListAPIController@indexTemplate'); // 請求書テンプレート一覧取得
                Route::post('/form-issuances/list/getListTemplateOther', 'API\FormIssuance\FormIssuanceListAPIController@indexTemplateOther'); // その他テンプレート一覧取得
                Route::post('/form-issuances/list/actionMultipleIssuance/{action}', 'API\FormIssuance\FormIssuanceListAPIController@actionMultiple'); // delete, downloadFile
                Route::post('/form-issuances/list/export-list', 'API\FormIssuance\FormIssuanceListAPIController@exportFormIssuanceListToCSV'); // CSVエクスポート
                Route::post('/form-issuances/uploadExpTemplate', 'API\FormIssuance\FormIssuanceAPIController@uploadExpTemplate');
                Route::get('/form-issuances/exp-template-list', 'API\FormIssuance\FormIssuanceAPIController@getListExpTemplate');
                // PAC_5-2280
                Route::delete('/form-issuances/template/{template_id}/users/clear', 'API\FormIssuance\FormIssuanceUserAPIController@clear');
                Route::resource('/form-issuances/template/{template_id}/users', 'API\FormIssuance\FormIssuanceUserAPIController');
                Route::post('/form-issuances/template/{template_id}/users/addChild', 'API\FormIssuance\FormIssuanceUserAPIController@storeChildren');
                Route::put('/form-issuances/template/{template_id}/users/{id}', 'API\FormIssuance\FormIssuanceUserAPIController@update');
                Route::patch('/form-issuances/template/{template_id}/users/{id}/updateReturnflg', 'API\FormIssuance\FormIssuanceUserAPIController@updateReturnflg');
                Route::get('/form-issuances/template/{template_id}/getSavedCircularUsers', 'API\FormIssuance\FormIssuanceUserAPIController@getSavedCircularUsers');
                Route::get('/form-issuances/template/{template_id}/getSavedViewingUsers', 'API\FormIssuance\FormIssuanceUserAPIController@getSavedViewingUsers');
                Route::post('/form-issuances/template/{template_id}/viewing/add', 'API\FormIssuance\FormIssuanceUserAPIController@addViewuser');
                Route::post('/form-issuances/template/{template_id}/viewing/remove', 'API\FormIssuance\FormIssuanceUserAPIController@removeViewuser');
                // PAC_5-2280
                Route::post('/form-issuances/template/{template_id}/autoSave/{circular_id}', 'API\FormIssuance\FormIssuanceAPIController@autoCircularSave');
                Route::get('/form-issuances/getFrmIndex', 'API\FormIssuance\FormIssuanceAPIController@getFrmIndex');

                Route::middleware(['check_exp_template_action_permission'])->group(function () {
                    Route::get('/form-issuances/showExpTemplate/{formId}', 'API\FormIssuance\FormIssuanceAPIController@showExpTemplate');
                    Route::post('/form-issuances/{formId}/deleteExpTemplate', 'API\FormIssuance\FormIssuanceAPIController@deleteExpTemplate');
                    Route::get('/form-issuances/getExpTemplate/{formId}', 'API\FormIssuance\FormIssuanceAPIController@getExpTemplate');
                });

                Route::middleware(['check_form_issuance_action_permission'])->group(function () {
                    Route::get('/form-issuances/getTemplateDepartment/{formId}', 'API\FormIssuance\FormIssuanceAPIController@getTemplateDepartment');
                    Route::get('/form-issuances/show/{formId}', 'API\FormIssuance\FormIssuanceAPIController@show');
                    Route::post('/form-issuances/{formId}/delete', 'API\FormIssuance\FormIssuanceAPIController@delete');
                    Route::get('/form-issuances/get/{formId}', 'API\FormIssuance\FormIssuanceAPIController@getFile');
                    Route::post('/form-issuances/edit/{formId}', 'API\FormIssuance\FormIssuanceAPIController@edit')->name('usingFormIssuance');
                    Route::post('/form-issuances/status/{formId}', 'API\FormIssuance\FormIssuanceAPIController@updateTemplateStatus');
                    Route::get('/form-issuances/{formId}', 'API\FormIssuance\FormIssuanceAPIController@getContentTemplate');
                    Route::get('/form-issuances/{formId}/stamp', 'API\FormIssuance\FormIssuanceAPIController@getTemplateStamp');
                    Route::get('/form-issuances/{formId}/placeholder/{frmType}', 'API\FormIssuance\FormIssuanceAPIController@getTemplatePlaceholder');
                    Route::post('/form-issuances/{formId}/save/inputData', 'API\FormIssuance\FormIssuanceAPIController@saveInputData')->name('saveInputFormIssuance');
                    Route::get('/form-issuances/templateUseHistory/{formId}', 'API\FormIssuance\FormIssuanceAPIController@templateUseHistory');
                    Route::post('/form-issuances/{formId}/setting', 'API\FormIssuance\FormIssuanceAPIController@settingTemplate');
                    Route::get('/form-issuances/{formId}/getFileCSVImport/{csvId}', 'API\FormIssuance\FormIssuanceAPIController@getFileCSVImport');
                    Route::get('/form-issuances/{formId}/getLogTemplateCSV/{logId}', 'API\FormIssuance\FormIssuanceAPIController@getLogTemplateCSV');
                    Route::post('/form-issuances/{formId}/upload-csv-import', 'API\FormIssuance\FormIssuanceAPIController@uploadCSVImport')->name('uploadCSVImport');
                    Route::get('/form-issuances/{formId}/upload-csv-import/{csvId}', 'API\FormIssuance\FormIssuanceAPIController@getCSVFormImportUploadStatus')->name('getCSVFormImportUploadStatus');
                });
            });
        });

        Route::middleware(['auth:api', 'JsonApiMiddleware', 'scope:audit'])->group(function () {

        });

        Route::get('stamp_infos/{id}', 'API\StampInfoAPIController@show');

        Route::middleware(['auth:api', 'scope:user'])->group(function () {
            // TODO 要確認
            Route::get('bizcard/getAllVersions/{id}', 'API\BizcardAPIController@getAllVersions');
            Route::get('bizcard/getLinkPageURL/{id}', 'API\BizcardAPIController@getLinkPageURL');
            Route::get('bizcard/getMyBizcard', 'API\BizcardAPIController@getMyBizcard');
            Route::resource('bizcard', 'API\BizcardAPIController');
            Route::post('scan', 'API\BizcardAPIController@scan');
            Route::post('detect_card', 'API\BizcardAPIController@detectCard');
            Route::get('image_processing_definition', 'API\BizcardAPIController@getImageProcessingDefinition');
            Route::post('process_image_use_pattern_default', 'API\BizcardAPIController@processImageUsePatternDefault');
            Route::post('multipleBizcard/acceptZip', 'API\BizcardAPIController@acceptZip');
            Route::post('multipleBizcard/deleteZipContents/{zip_upload_time}', 'API\BizcardAPIController@deleteZipContents');
            Route::post('multipleBizcard/acceptCsv', 'API\BizcardAPIController@acceptCsv');
            Route::post('multipleBizcard/register', 'API\BizcardAPIController@multipleRegister');
        });
        //PAC_5-1723
        Route::get('/getUserView', 'API\CircularUserAPIController@getUserView');
        Route::post('/deleteUserView', 'API\CircularUserAPIController@deleteUserView');
    });

});

//外部連携API等々
Route::prefix('externalCircular')->group(function () {
    Route::post('/getCircularCounts','API\External\CircularAPIController@getCircularCounts'); //マイページの件数をAPI連携する
});
