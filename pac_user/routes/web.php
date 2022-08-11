<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/errorView', function () {
    return view('auth.passwords.init_done')->with('message','パスワードのリセットリンクが存在しません');
});
Route::post("login", 'AuthController@login')->name("post.login");
Route::any(config('app.saml_url_prefix') . '/{url_domain_id}/login', 'AuthController@getSSOLogin');
Route::any(config('app.saml_url_prefix') . '/{url_domain_id}', 'AuthController@getSSO');

Route::get("login", function () {
    if (config('app.enable_self_login')) {
        $agent = app('agent');
        if (!\Agent::isMobile()) {
            return view('pwd_login');
        }
        return view('mobile_pwd_login');
    } else {
        return redirect('/');
    }
})->name("login");
Route::get('login/{company}', 'Auth\LoginController@showLoginFormCompany'); //企業設定ログイン画面
Route::get('/reentry', function () {
    return view('auth.passwords.reentry'); // 5-553 単一システムログイン画面 パスワード設定画面
});
Route::get('/password-code', function () {
    return view('pwd-code'); //パスワード設定コード入力画面
});
Route::post('/password-code/getPasswordChangeUrl', 'PasswordController@getPasswordChangeUrl'); //パスワード設定コード入力
Route::post('/reentry', 'PasswordController@sendReentryMail'); // 5-553 単一システムログイン画面 パスワード変更メール
Route::get('password/init/{token}', 'PasswordController@passwordInit'); //メールから「パスワードを設定」ボタンを押下
Route::post('password/init/{token}', 'PasswordController@passwordPostInit'); //ログインパスワードの変更画面「更新」ボタンを押下

Route::get('password/init/code/{token}', 'PasswordController@loadPasswordChangePage'); //ログインパスワード変更画面の表示
Route::post('password/init/code/{token}', 'PasswordController@updatePassword'); //ログインパスワードの変更画面「更新」ボタンを押下

Route::get('passwords/init-outdate/{email}', 'PasswordController@initOutDate');
Route::post('passwords/init-outdate/{email}', 'PasswordController@postInit');
Route::get('/groupware/file_mail/download/{hash?}', 'ApplicationController@mailFileDownloadInit');
Route::post('/mailFileDownload', 'ApplicationController@mailFileDownload');
Route::view('/login-wrapper', 'login-wrapper');
Route::get('/site/showCardView/{token}', 'ApplicationController');
Route::get('/site/approval/{hash?}', 'ApplicationController');
Route::get('/site/destination/{hash?}', 'ApplicationController');
Route::get('/site/sendback/{hash?}', 'ApplicationController');
Route::get('/StampInfo/{info_id}', 'ApplicationController');
Route::group(['prefix' => 'public'], function () {
    Route::post('/upload', 'ApplicationController@upload');
    Route::post('/odsPreview', 'ApplicationController@odsPreview');
    Route::post('/odtPreview', 'ApplicationController@odtPreview');
    Route::post('/odtUpdate', 'ApplicationController@odtUpdate');
    Route::post('/odtReset', 'ApplicationController@odtReset');
    Route::post('/rejectPageBreaks', 'ApplicationController@rejectPageBreaks');
    Route::post('/acceptUpload', 'ApplicationController@acceptUpload');
    Route::post('/rejectUpload', 'ApplicationController@rejectUpload');
    Route::get('/loadCircularByHash', 'ApplicationController@loadCircularByHash');
    Route::get('/file/page', 'ApplicationController@getPage');
    Route::post('/deleteStoredFiles', 'ApplicationController@deleteStoredFiles');
    Route::post('/saveFile', 'ApplicationController@saveFile');
    Route::post('/downloadFile', 'ApplicationController@downloadFile');
    Route::post('/deleteCircularDocument', 'ApplicationController@deleteCircularDocument');
    Route::post('/renameCircularDocument', 'ApplicationController@renameCircularDocument');
    Route::post('/replacePdf', 'ApplicationController@replacePdf');
    Route::get('/userByHashing', 'ApplicationController@userByHashing');
    Route::post('/verifyMyInfo', 'ApplicationController@verifyMyInfo');
    Route::post('/downloadAttachment', 'ApplicationController@downloadAttachment');//選択した添付ファイルをダウンロードします。
    Route::post('/attachmentDelete', 'ApplicationController@deleteAttachment');//選択した添付ファイルを削除します。
    Route::post('/attachmentUpload', 'ApplicationController@attachmentUpload');//添付ファイルをアップロード
    // PAC_5-1488 クラウドストレージを追加する Start
    Route::middleware(['checkHash'])->group(function () {
        Route::get('/getCloudItems', 'ApplicationController@getCloudItems');
        Route::post('/uploadToCloud', 'ApplicationController@uploadToCloud');
        Route::get('/downloadCloudItem', 'ApplicationController@uploadFromCloud');
        Route::get('/downloadCloudAttachment', 'ApplicationController@uploadAttachmentFromCloud');
        // PAC_5-2242 Start
        Route::post('/uploadFilesForPageBreak', 'ApplicationController@uploadFilesForPageBreak');
        Route::post('/decidePageBreaksBeforeAcceptUpload', 'ApplicationController@decidePageBreaksBeforeAcceptUpload');
        Route::post('/decidePageBreaksAfterAcceptUpload', 'ApplicationController@decidePageBreaksAfterAcceptUpload');
        // PAC_5-2242 End
    });
    // PAC_5-1488 End
});

Route::middleware(['checkAuth'])->group(function () {
    Route::get("/logout", 'AuthController@logout');
    Route::get('/sso_home', 'AuthController@getSSOHome');

    Route::get('/extra-auth', 'Auth\MfaController@index'); // 利用者ログインする多要素認証
    Route::post('/extra-auth', 'Auth\MfaController@verify');
    Route::get('/extra-auth/poll', 'Auth\MfaController@pollQrCodeAuthStatus');
    Route::get('/extra-auth/resend', 'Auth\MfaController@resend')->name('mfa.resend');
    Route::middleware(['mfa'])->group(function () {
        Route::post('/upload', 'ApplicationController@upload');
        Route::post('/uploadFilesForPageBreak', 'ApplicationController@uploadFilesForPageBreak');
        Route::post('/odsPreview', 'ApplicationController@odsPreview');
        Route::post('/odtPreview', 'ApplicationController@odtPreview');
        Route::post('/odtUpdate', 'ApplicationController@odtUpdate');
        Route::post('/odtReset', 'ApplicationController@odtReset');
        Route::post('/rejectPageBreaks', 'ApplicationController@rejectPageBreaks');
        Route::post('/decidePageBreaksBeforeAcceptUpload', 'ApplicationController@decidePageBreaksBeforeAcceptUpload');
        Route::post('/decidePageBreaksAfterAcceptUpload', 'ApplicationController@decidePageBreaksAfterAcceptUpload');
        Route::post('/acceptUpload', 'ApplicationController@acceptUpload');
        Route::post('/attachmentUpload', 'ApplicationController@attachmentUpload');//添付ファイルをアップロード
        Route::post('/attachmentDelete', 'ApplicationController@deleteAttachment');//選択した添付ファイルを削除します。
        Route::post('/mailFileUpload', 'ApplicationController@mailFileUpload');//ファイルメール便をアップロード
        Route::post('/downloadAttachment', 'ApplicationController@downloadAttachment');//選択した添付ファイルをダウンロードします。
        Route::get('/downloadCloudAttachment', 'ApplicationController@uploadAttachmentFromCloud');//Cloudから添付ファイルをアップロードします
        Route::get('/downloadCloudMailFile', 'ApplicationController@downloadCloudMailFile');//メールファイル
        Route::post('/rejectUpload', 'ApplicationController@rejectUpload');
        Route::post('/saveFile', 'ApplicationController@saveFile');
        Route::post('/circular/{circular_id}/signatureCircular', 'ApplicationController@signatureCircular');
        Route::post('/downloadFile', 'ApplicationController@downloadFile');
        Route::post('/deleteCircularDocument', 'ApplicationController@deleteCircularDocument');
        Route::post('/renameCircularDocument', 'ApplicationController@renameCircularDocument');
        Route::post('/deleteStoredFiles', 'ApplicationController@deleteStoredFiles');
        Route::post('/replacePdf', 'ApplicationController@replacePdf');
        Route::get('/file/page', 'ApplicationController@getPage');
        Route::post('/extractPdfLine', 'ApplicationController@extractPdfLine');
        Route::get('/loadCircular', 'ApplicationController@loadCircular');
        Route::get('/uploadExternal', 'ApplicationController@uploadExternal');
        Route::get('/externalCallbackDone', 'ApplicationController@externalDriveCallback');

        Route::get('/getCloudItems', 'ApplicationController@getCloudItems');
        Route::post('/uploadToCloud', 'ApplicationController@uploadToCloud');
        Route::get('/downloadCloudItem', 'ApplicationController@uploadFromCloud');
        Route::get('/templates/convertExcelToImage/{templateId}', 'ApplicationController@convertTemplateExcelToImage');
        Route::get('/form-issuances/convertExcelToImage/{templateId}', 'ApplicationController@convertFormExcelToImage');
        Route::get('/form-issuances/page', 'ApplicationController@getFormIssuancePage');
        Route::get('/form-issuances/{templateId}', 'ApplicationController@loadFormIssuance');

        Route::post('/uploadUserImage', 'ApplicationController@uploadUserImage');
        Route::get('/{any?}', 'ApplicationController')
            ->where('any', '[\/\w\.-]*')
            ->name("home");
    });
});

