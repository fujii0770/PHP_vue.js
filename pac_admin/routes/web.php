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
Route::get('password/init/{token}', 'PasswordController@passwordInit'); //メールから「パスワードを設定」ボタンを押下
Route::post('password/init/{token}', 'PasswordController@passwordPostInit'); //ログインパスワードの変更画面「更新」ボタンを押下

Route::post('password/getPasswordCodeUser', 'PasswordController@getPasswordCodeUser'); //パスワード設定コード生成と格納(利用者)
Route::post('password/getPasswordCodeAdmin', 'PasswordController@getPasswordCodeAdmin'); //パスワード設定コード生成と格納(会社管理者)

Route::get('/reentry', function () {
    return view('auth.passwords.reentry'); // 5-553 単一システムログイン画面 パスワード設定画面
});

Route::get('login/{company}', 'Auth\LoginController@showLoginFormCompany'); //企業設定ログイン画面
Route::get('/password-code', function () {
    return view('pwd-code'); //パスワード設定コード入力画面
});
Route::post('/password-code/getPasswordChangeUrl', 'PasswordController@getPasswordChangeUrl'); //パスワード設定コード入力
Route::get('password/init/code/{token}', 'PasswordController@loadPasswordChangePage'); //コード入力後からの遷移
Route::post('password/init/code/{token}', 'PasswordController@updatePassword'); //ログインパスワードの変更画面「更新」ボタンを押下

Route::post('/reentry', 'PasswordController@sendReentryMail'); // 5-553 単一システムログイン画面 パスワード変更メール
Route::get(config('app.saml_url_prefix').'/{url_domain_id}', 'Auth\LoginController@getSSO');
Route::get('/change-nav-menu-active','SettingController@changeNavMenuActive');
Route::middleware(['LogOperation'])->group(function () {
    Auth::routes();
    Route::middleware(['auth','checkIpRestriction','checkMfa'])->group(function () {
        Route::get('/logout', 'Auth\LoginController@logout'); //ログアウト

        Route::get('/extra-auth', 'Auth\MfaController@index');
        Route::post('/extra-auth', 'Auth\MfaController@verify');
        Route::get('/extra-auth/resend', 'Auth\MfaController@resend')->name('mfa.resend');
        Route::middleware(['mfa'])->group(function () {
            Route::middleware(['CheckneedResetPass'])->group(function () {

                // PAC_5-2546 START
                Route::get('/hr-admin-reg', 'HrAdminRegistrationController@index')->name('HrAdminRegistration.Index');//HR管理ユーザ登録
                Route::post('/hr-admin-reg', 'HrAdminRegistrationController@index')->name('HrAdminRegistration.Index');
                Route::post('/hr-admin-reg-getusers', 'HrAdminRegistrationController@getUsers')->name('HrAdminRegistration.getUsers');
                Route::post('/hr-admin-reg-updateflg', 'HrAdminRegistrationController@updateHrAdmin')->name('HrAdminUpdate');
                Route::post('/hr-admin-reg-updatehrusers', 'HrAdminRegistrationController@updateHrUsers')->name('HrUsersUpdate');
                // PAC_5-2546 END

                //PAC_5_3190-3191_START
                Route::get('/hr-working-hours', 'HrWorkingHourController@index')->name('HrWorkingHour.index');//就労時間管理
                Route::post('/hr-working-hours', 'HrWorkingHourController@index')->name('HrWorkingHour.index');
                Route::get('/hr-working-hours-show/{id}', 'HrWorkingHourController@show')->name('HrWorkingHour.show');
                Route::post('/hr-working-hours-insert', 'HrWorkingHourController@insert')->name('HrWorkingHour.insert');
                Route::put('/hr-working-hours-update/{id}', 'HrWorkingHourController@update')->name('HrWorkingHour.update');
                //PAC_5_3190-3191_END

                Route::get('/hr-user-reg', 'HrUserRegistrationController@index')->name('HrUserRegistration.Index');//HRユーザ登録
                Route::post('/hr-user-reg', 'HrUserRegistrationController@index')->name('HrUserRegistration.Index');
                Route::get('/hr-user-reg/{id}', 'HrUserRegistrationController@show');
                Route::post('/hr-user-reg-store', 'HrUserRegistrationController@store')->name('HrUserRegistration.store');
                Route::put('/hr-user-reg/{id}', 'HrUserRegistrationController@update')->name('HrUserRegistration.update');
                Route::post('/hr-user-reg-updateflg', 'HrUserRegistrationController@updateHrUser')->name('HrUserStore');
                Route::get('/work-detail', 'WorkDetailController@index')->name('WorkDetail.Index');//勤務詳細
                Route::post('/work-detail', 'WorkDetailController@index')->name('WorkDetail.Index');
                Route::get('/work-detail/{id}', 'WorkDetailController@show')->name('WorkDetail.Index');
                Route::post('/work-detail/{id}', 'WorkDetailController@store')->name('WorkDetail.store');
                Route::put('/work-detail/{id}', 'WorkDetailController@update')->name('WorkDetail.update');
                Route::post('/work-detail_approval', 'WorkDetailController@bulkApproval')->name('WorkDetail.BulkApproval');
                //20210311 江澤 e

                Route::get('/about', function () {
                    return view('about');
                })->name('get.about');
                Route::get('setting-admin/resetpass/{id?}', 'Admin\SettingAdminController@resetpass')->name('settingadmin.resetpass');
                Route::get('setting-admin/permission/{id?}', 'Admin\SettingAdminController@getPermission')->name('settingadmin.getPermission');
                Route::post('setting-admin/permission/{id?}', 'Admin\SettingAdminController@postPermission')->name('settingadmin.postPermission');
                Route::get('setting-admin/resetpermission/{id?}', 'Admin\SettingAdminController@resetPermission')->name('settingadmin.resetpermission');
                Route::get('setting-admin/change-password', 'Admin\SettingAdminController@viewChangePassword')->name('settingadmin.viewChangePassword');
                Route::post('setting-admin/change-password', 'Admin\SettingAdminController@changePassword')->name('settingadmin.changePassword');
                Route::post('global-setting/company-stamp/search', 'GlobalSetting\CompanyStampController@search')->name('GlobalSetting.CompanyStamp.Search'); //共通印情報を取得
                Route::post('global-setting/company-convenient-stamp/search', 'GlobalSetting\CompanyStampController@searchConvenientStamps')->name('GlobalSetting.CompanyConvenientStamp.Search'); //便利印情報を取得
                Route::post('global-setting/company-stamp/download', 'GlobalSetting\CompanyStampController@download')->name('GlobalSetting.CompanyStamp.download'); //共通印申請書ダウンロード
                Route::post('setting-admin/update-menu-state', 'Admin\SettingAdminController@updateMenuStateFlg');
                Route::get('master/list-permission', 'Admin\PermissionsController@getListMaster')->name('master.permission');
                // PAC_5-2018 START
                Route::post('global-setting/company-stamp/getUsersAssign', 'GlobalSetting\CompanyStampController@getUsersGlobalCompanyStamp');
                // PAC_5-2018 END

                Route::post('ajax/assign-stamps/store', 'Admin\AssignstampsController@store')->name('assignstamps.store');
                Route::delete('ajax/assign-stamps/{id?}', 'Admin\AssignstampsController@delete')->name('assignstamps.delete');
                Route::post("ajax/assign-stamps/checkStoreConvenientStamp",'Admin\AssignstampsController@checkStoreConvenientStamp')->name('assignstamps.checkStoreConvenientStamp');
                Route::get('ajax/time_stamp_permission/{id?}', 'Admin\AssignstampsController@getTimeStampPermission')->name('assignstamps.getTimeStampPermission');
                Route::post('ajax/time_stamp_permission/{id?}', 'Admin\AssignstampsController@updateTimeStampPermission')->name('assignstamps.updateTimeStampPermission');
                Route::get('setting-user/get-address', 'Admin\UserController@getAddress'); //zipcode Cover address
                Route::middleware(['CheckSettingAdmin'])->group(function () {
                    Route::get('reports/usage/csv/admin', 'Reports\ReportsUsageController@getCsvDataAdmin');
                    Route::get('reports/usage/csv/user', 'Reports\ReportsUsageController@getCsvDataUser');
                    Route::get('reports/usage/csv/stamp', 'Reports\ReportsUsageController@getCsvStampRegister');
                    Route::get('reports/usage/previewStamp/{serial}', 'Reports\ReportsUsageController@previewStamp');
                    Route::get('reports/usage', 'Reports\ReportsUsageController@show')->name('Reports.Usage.Show');
                    Route::get('reports/usage/search/{year}/{month}/{statistics_range}/{company_id?}', 'Reports\ReportsUsageController@search');
                    Route::get('reports/usage/hostCompany/{year}/{month}/{statistics_range}/{company_id?}/{is_guest?}', 'Reports\ReportsUsageController@searchGuestCompanyInfo');
                    Route::get('reports/usage/download/{company_id?}', 'Reports\ReportsUsageController@download');
                    Route::get('reports/usage/downloadGuestCompanyInfo/{company_id?}/{is_guest?}', 'Reports\ReportsUsageController@downloadGuestCompanyInfo');
                    Route::get('reports/usage/downloadFileInfo/{company_id?}', 'Reports\ReportsUsageController@downloadFileInfo');
                    Route::get('reports/usage/downloadGuestFileInfo/{company_id?}/{is_guest?}', 'Reports\ReportsUsageController@downloadGuestFileInfo');
                    Route::get('reports/usage/downloadSummaryInfo/{data_range}/{company_id?}', 'Reports\ReportsUsageController@downloadSummaryInfo');
                    Route::get('reports/usage/downloadGuestSummaryInfo/{data_range}/{company_id?}/{is_guest?}', 'Reports\ReportsUsageController@downloadGuestSummaryInfo');
                    Route::post('reports/usage/downloadRequest', 'Reports\ReportsUsageController@downloadRequest');
                    Route::get('reports/usage/showChart/{range?}/{company_id?}', 'Reports\ReportsUsageController@showChart');
                    Route::get('reports/usage/showGuestCompanyChart/{range?}/{company_id?}/{is_guest?}', 'Reports\ReportsUsageController@showGuestCompanyChart');
                    Route::get('reports/usage/reSituation/{company_id}', 'Reports\ReportsUsageController@reSituation');

                    Route::resource('setting-admin/setting-stamp-group', 'Admin\SettingAdminStampGroupController');
                    Route::post('setting-admin/setting-stamp-group', 'Admin\SettingAdminStampGroupController@updates')->name('adminStampGroup.updates');
                    Route::resource('setting-admin', 'Admin\SettingAdminController');

                    Route::resource('setting-user/setting-audit-account', 'Admin\AuditAccountController');
                    Route::post('ajax/setting-user/setting-audit-account/deletes', 'Admin\AuditAccountController@deletes')->name('AuditDelete');
                    Route::post('ajax/setting-user/setting-audit-account/resetpass', 'Admin\AuditAccountController@resetpass')->name('audit.resetpass');
                    Route::post('ajax/setting-user/setting-audit-account/send-login-url', 'Admin\AuditAccountController@sendLoginUrl')->name('audit.sendLoginUrl');

                    Route::resource('setting-user/option-user', 'Admin\OptionUserController'); // オプション利用者
                    Route::post('ajax/setting-user/option-user/deletes', 'Admin\OptionUserController@deletes')->name('option_user.deletes');
                    Route::post('setting-user/option-user/csv_import', 'Admin\OptionUserController@import')->name('option_user.import'); // グループウェア専用利用者 csv取込
                    Route::post("setting-user/option-user/showPasswordList",'Admin\OptionUserController@showPasswordList')->name('option_user.showPasswordList');// グループウェア専用利用者 パスワード設定コード一括


                    Route::resource('setting-user/receive-user', 'Admin\ReceiveUserController');//受信専用利用者
                    Route::post('setting-user/receive-user/deletes', 'Admin\ReceiveUserController@deletes')->name('receive_user.deletes');//受信専用利用者一括削除
                    Route::post('setting-user/search_stamps', 'Admin\ReceiveUserController@getNameStamps')->name('receive_user.search_stamps');//受信専用利用者氏名印検索
                    Route::post('ajax/setting-user/receive-user/sendLoginUrl', 'Admin\ReceiveUserController@sendLoginUrl')->name('receive_user.sendLoginUrl');//受信専用利用者ログインURL送信
                    Route::post('ajax/setting-user/receive-user/resetPass', 'Admin\ReceiveUserController@resetPass')->name('receive_user.resetPass');//受信専用利用者パスワード設定依頼
                    Route::post('setting-user/receive-user/storeNameStamps', 'Admin\ReceiveUserController@storeNameStamps')->name('receive_user.store_stamp');//受信専用利用者印面登録
                    Route::post('setting-user/receive-user/csv_import', 'Admin\ReceiveUserController@import')->name('receive_user.import'); // 受信専用利用者 csv取込
                    Route::post("setting-user/receive-user/showPasswordList",'Admin\ReceiveUserController@showPasswordList')->name('receiver_user.showPasswordList');// 受信専用利用者 パスワード設定コード一括

                    Route::resource('setting-user/assign-stamp', 'Admin\UserAssignStampController'); // 利用者情報を取得する
                    Route::resource('setting-user', 'Admin\UserController'); // 利用者情報を取得する

                    Route::post('setting-user/csv', 'Admin\UserController@import')->name('user.import'); // csv取込
                    Route::post('ajax/setting-user/search-stamp', 'Admin\UserController@searchStamp')->name('user.searchstamp');
                    Route::post('ajax/setting-user/get-department-stamp', 'Admin\UserController@getDepartmentStamp')->name('user.getDepartmentStamp');
                    Route::post('ajax/setting-user/resetpass', 'Admin\UserController@resetpass')->name('user.resetpass');
                    Route::post('ajax/setting-user/send-login-url', 'Admin\UserController@sendLoginUrl')->name('user.sendLoginUrl');
                    Route::post('ajax/setting-user/deletes', 'Admin\UserController@deletes')->name('user.deletes');
                    Route::post('ajax/setting-user/getDepartmentStampInfo', 'Admin\UserController@getDepartmentStampInfo')->name('user.getDepartmentStampInfo');  // 捺印が生成されたときに情報を取得します

                    Route::post("ajax/setting-user/checkUserEmailOrStampOrStatus",'Admin\SettingAdminController@checkUserEmailOrStampOrStatus')->name('user.checkUserEmailOrStampOrStatus');
                    Route::post("ajax/setting-user/setUsersEmailOrStampOrStatus",'Admin\SettingAdminController@setUsersEmailOrStampOrStatus')->name('user.setUsersEmailOrStampOrStatus');

                    Route::post("setting-user/showPasswordList",'Admin\UserController@showPasswordList')->name('user.showPasswordList');

                    Route::resource('setting-user/info', 'Admin\UserInfoController');

                    Route::resource('import-history', 'Admin\UserImportCsvController'); // csv取込履歴一覧

                    Route::resource('global-setting/company-stamp', 'GlobalSetting\CompanyStampController');

                    Route::get('global-setting/date-stamp', 'GlobalSetting\DateStampController@show');
                    Route::post('global-setting/date-stamp', 'GlobalSetting\DateStampController@store')->name('GlobalSetting.Date.Store');
                    Route::get('global-setting/limit', 'GlobalSetting\LimitController@show');
                    Route::post('global-setting/limit', 'GlobalSetting\LimitController@store')->name('GlobalSetting.Limit.Store');
                    Route::get('global-setting/branding', 'GlobalSetting\BrandingController@show')->name('GlobalSetting.Branding');
                    Route::post('global-setting/branding', 'GlobalSetting\BrandingController@store')->name('GlobalSetting.Branding.Store');
                    Route::get('global-setting/authority', 'GlobalSetting\AuthorityController@show')->name('GlobalSetting.Authority');
                    Route::post('global-setting/authority', 'GlobalSetting\AuthorityController@store')->name('GlobalSetting.Authority.Store');
                    Route::get('global-setting/password-policy', 'GlobalSetting\PasswordPolicyController@show')->name('GlobalSetting.PasswordPolicy');
                    Route::post('global-setting/password-policy', 'GlobalSetting\PasswordPolicyController@store')->name('GlobalSetting.PasswordPolicy.Store');
                    Route::get('global-setting/protection', 'GlobalSetting\ProtectionController@index');
                    Route::post('global-setting/protection', 'GlobalSetting\ProtectionController@store')->name('GlobalSetting.Protection.Store');

                    Route::resource('global-setting/ip-restriction', 'GlobalSetting\IpRestrictionController');
                    Route::post('global-setting/ip-restriction/bulk-update', 'GlobalSetting\IpRestrictionController@bulkUpdate');
                    Route::post('ajax/global-setting/ip-restriction/delete', 'GlobalSetting\IpRestrictionController@multiDelete')->name('IpRestrictionDelete');

                    Route::get('global-setting/signature', 'GlobalSetting\SignatureController@index');
                    Route::get('global-setting/signature/show/{id?}', 'GlobalSetting\SignatureController@show')->name('signature.show');
                    Route::post('global-setting/signature/update', 'GlobalSetting\SignatureController@update')->name('signature.update');
                    Route::post('global-setting/signature/delete', 'GlobalSetting\SignatureController@delete')->name('signature.delete');
                    Route::get('long-term/long-term-index', 'LongTerm\LongTermIndexController@show')->name('LongTerm.LongTermIndex.Show');;
                    Route::get('long-term/long-term-index/{id}', 'LongTerm\LongTermIndexController@showOne');
                    Route::post('long-term/long-term-index/setting', 'LongTerm\LongTermIndexController@store')->name('LongTerm.LongTermIndex.Store');
                    Route::post('long-term/long-term-index/setting/update/{id}', 'LongTerm\LongTermIndexController@update')->name('LongTerm.LongTermIndex.Update');
                    Route::post('long-term/long-term-index/setting/delete/{id}', 'LongTerm\LongTermIndexController@delete')->name('LongTerm.LongTermIndex.Delete');
                    Route::post('long-term/long-term-index/setting/templateRelease/{id}', 'LongTerm\LongTermIndexController@templateRelease')->name('LongTerm.LongTermIndex.templateRelease');
                    Route::post('long-term/long-term-index/setting/formIssuanceRelease/{id}', 'LongTerm\LongTermIndexController@formIssuanceRelease')->name('LongTerm.LongTermIndex.formIssuanceRelease');
                    Route::post('long-term/long-term-index/setting/templateValid/{id}', 'LongTerm\LongTermIndexController@templateValid')->name('LongTerm.LongTermIndex.templateValid');
                    Route::post('long-term/long-term-index/setting/formIssuanceValid/{id}', 'LongTerm\LongTermIndexController@formIssuanceValid')->name('LongTerm.LongTermIndex.formIssuanceValid');
                    Route::get('long-term/long-term-save','LongTerm\LongTermStorageController@index');//長期保存機能の自動保存フラグを取得
                    Route::post('long-term/long-term-save','LongTerm\LongTermStorageController@store')->name('LongTerm.LongTermStorage.Store');//長期保存機能の自動保存 設定保存
                    Route::resource('long-term/long-term-folder', 'LongTerm\LongTermFolderController');
                    Route::get('long-term/long-term-folder/getParentPermissions/{id}', 'LongTerm\LongTermFolderController@getParentPermissions');
                    Route::post('long-term/long-term-folder/saveFolderPermissions', 'LongTerm\LongTermFolderController@saveFolderPermissions');
                    Route::post('long-term/long-term-folder/addUsersToFolderPermissions', 'LongTerm\LongTermFolderController@addUsersToFolderPermission');
                    Route::post('long-term/long-term-folder/deleteUsersFromFolderPermissions', 'LongTerm\LongTermFolderController@deleteUsersFromFolderPermission');

                    Route::get('global-setting/app-use', 'GlobalSetting\AppUseController@show');
                    Route::get('global-setting/app-use/{id}', 'GlobalSetting\AppUseController@show')->name('AppUse.Index');
                    Route::post('global-setting/app-use/{id}', 'GlobalSetting\AppUseController@store')->name('AppUse.store');
                    Route::put('global-setting/app-use/{id}', 'GlobalSetting\AppUseController@update')->name('AppUse.update');
                    Route::get('global-setting/app-role', 'GlobalSetting\AppRoleController@index')->name('AppRole.Index');//アプリロール設定
                    Route::post('global-setting/app-role', 'GlobalSetting\AppRoleController@index');
                    Route::get('global-setting/app-role/{id}', 'GlobalSetting\AppRoleController@show');
                    Route::post('global-setting/app-role/{id}', 'GlobalSetting\AppRoleController@store')->name('AppRole.store');
                    Route::put('global-setting/app-role/{id}', 'GlobalSetting\AppRoleController@update')->name('AppRole.update');
                    Route::post('global-setting/app-role-updateflg', 'HrUserRegistrationController@AppRoleStore')->name('AppRoleUpdateFlg');
                    /*PAC_5-2246 START*/
                    Route::get('attendance/users', 'Admin\AttendanceController@users')->name('attendance.users');
                    Route::get('attendance/book', 'Admin\AttendanceController@book')->name('attendance.book');
                    Route::post('attendance/update/{date}', 'Admin\AttendanceController@update')->name('attendance.update');
                    // CSV出力非同期ダウンロード処理 ///////////////////////////////////////////////
                    // 各出力処理が表示処理と分離しての記述が困難だったため、1つのコントローラに集約させることとした
                    // 既存の出力処理は未削除
                    Route::post('csv-download/stamp-register-state',    'DownloadController@stampRegisterState')->name('CsvStampRegisterState');   // 利用者・印面登録状況
                    Route::post('csv-download/stamp-ledger',            'DownloadController@stampLedger')->name('CsvStampLedger');                 // 捺印台帳CSV出力
                    Route::post('csv-download/history',                 'DownloadController@history')->name('CsvHistory');                         // 管理者・利用者操作履歴情報
                    Route::post('csv-download/user-setting',            'DownloadController@userSetting')->name('CsvUserSetting');                 // 利用者設定情報
                    Route::post('csv-download/address-common',          'DownloadController@addressCommon')->name('CsvAddressCommon');             // 共通アドレス帳
                    Route::post('csv-download/department',              'DownloadController@department')->name('CsvDepartment');                   // 部署情報
                    Route::post('csv-download/position',                'DownloadController@position')->name('CsvPosition');                       // 役職情報
                    Route::post('csv-download/time-card',               'DownloadController@timeCard')->name('CsvTimeCard');                       // 打刻情報
                    Route::post('csv-download/receive-user',            'DownloadController@receiveUserSetting')->name('receiver_user.download_csv');     // 受信専用利用者情報
                    Route::post('csv-download/option-user',             'DownloadController@optionUserSetting')->name('option_user.download_csv');        // グループウェア専用利用者情報
                    Route::post('csv-download/circulars',               'DownloadController@circulars')->name('CsvCirculars');                     // 回覧一覧情報
                    Route::post('csv-download/registration-Status',     'DownloadController@UserRegistrationStatus')->name('CsvUserRegistrationStatus'); //登録情報
                    Route::post('csv-download/disk-usages',             'DownloadController@diskUsages')->name('CsvUserDiskUsages');//PAC_5-897 利用者ファイル容量CSV出力
                    Route::post('csv-download/disk-host-usages',        'DownloadController@diskHostUsages')->name('CsvUserDiskHostUsages');
                    Route::post('csv-download/template-route',          'DownloadController@templateRoute')->name('CsvTemplateRoute'); //承認ルートCSV出力
                    Route::post('csv-download/expense-m-form-adv',      'DownloadController@expenseMFormAdv')->name('CsvExpenseMFormAdv');          // 事前申請様式一覧
                    Route::post('csv-download/expense-m-form-exp',      'DownloadController@expenseMFormExp')->name('CsvExpenseMFormExp');          // 精算申請様式一覧
                    Route::post('csv-download/expense-app-list',        'DownloadController@expenseAppList')->name('CsvExpenseAppList');           // 申請一覧
                    Route::post('csv-download/journal-list',            'DownloadController@journalList')->name('CsvJournalList');                 // 仕訳一覧
                    //////////////////////////////////////////////////////////////////////////////


                    Route::get('operation-history/{type}', 'OperationHistoryController@index'); //利用者操作履歴情報
                    Route::post('operation-history/{type}', 'OperationHistoryController@index'); //利用者操作履歴情報
                    Route::get('global-setting/box-enabled-auto-storage', 'GlobalSetting\BoxEnabledAutoStorageController@index'); // 外部連携
                    Route::get('/selectFolersExternal', 'GlobalSetting\BoxEnabledAutoStorageController@selectFolersExternal')->name('SelectFolersExternal'); // Boxフォルダを選択
                    Route::get('/externalCallbackDone', 'GlobalSetting\BoxEnabledAutoStorageController@externalDriveCallback'); // Boxフォルダを選択のコールバック関数
                    Route::get('/getCloudItems', 'GlobalSetting\BoxEnabledAutoStorageController@getCloudItems')->name('GetCloudItems'); // Cloudのファイルリストを取得
                    Route::post('/saveAutoStorageSetting', 'GlobalSetting\BoxEnabledAutoStorageController@saveAutoStorageSetting')->name('SaveAutoStorageSetting'); // 外部連携Box 設定保存
                    Route::post('/createFolder', 'GlobalSetting\BoxEnabledAutoStorageController@createFolder')->name('CreateFolder'); // Boxフォルダを作成
                    Route::post('/reSaveAutoStorage', 'GlobalSetting\BoxEnabledAutoStorageController@reSaveAutoStorage')->name('ReSaveAutoStorage'); // Box自動保管失敗後再保存

                    Route::get('operation-history/{type}/{id}', 'OperationHistoryController@show');

                    Route::any('circulars', 'CircularsController@index')->name('Circulars.Index'); //回覧一覧情報を取得
                    Route::post('circulars/exports', 'CircularsController@exports'); //ダウンロードファイル
                    Route::post('circulars/deletes', 'CircularsController@deletes');
                    Route::post('circulars/reserve', 'CircularsController@reserve'); // ダウンロード予約
                    Route::post('circulars/longterm', 'CircularsController@storeMultipleCircular');// 長期保管
                    Route::get('circulars/getLongTermIndexValue', 'CircularsController@getLongTermIndexValue');
                    Route::any('attachments','CircularAttachmentsController@index')->name('Attachments.Index');//添付ファイル一覧情報を取得
                    Route::post('attachments/download','CircularAttachmentsController@download');//添付ファイルダウンロード

                    Route::any('bizcards','BizcardController@index')->name('Bizcards.Index');//名刺一覧情報を取得
                    Route::post('bizcards/deletes', 'BizcardController@deletes')->name('bizcards.deletes');
                    Route::get('bizcards/{id}', 'BizcardController@show')->name('bizcards.show');
                    Route::post('bizcards/{id}', 'BizcardController@update')->name('bizcards.update');

                    Route::post('circulars/reserve', 'CircularsController@reserve');
                    Route::any('circulars-saved', 'CircularsSavedController@index')->name('Circulars.Saved'); //保存文書一覧情報を取得
                    Route::post('circulars-saved/exports', 'CircularsSavedController@exports'); //ダウンロードファイル

                    Route::post('circulars-saved/deletes', 'CircularsSavedController@deletes');

                    Route::post('circulars-saved/reserve', 'CircularsSavedController@reserve'); // ダウンロード予約

                    // PAC_5-1595 追加　▼
                    Route::any('circulars-long-term', 'LongTerm\CircularsLongTermController@index')->name('Circulars.LongTerm');// 長期文書一覧情報を取得
                    Route::post('circulars-long-term/download', 'LongTerm\CircularsLongTermController@download');// 長期文書一覧情報をダウンロード
                    Route::post('circulars-long-term/getDetail', 'LongTerm\CircularsLongTermController@getDetail');
                    Route::post('circulars-long-term/getPreview', 'LongTerm\CircularsLongTermController@getPreview');
                    Route::post('circulars-long-term/updateDocument', 'LongTerm\CircularsLongTermController@updateDocument');
                    Route::post('circulars-long-term/deleteDetail', 'LongTerm\CircularsLongTermController@delete');
                    Route::post('circulars-long-term/downloadAttachment', 'LongTerm\CircularsLongTermController@downloadAttachment');
                    Route::post('circulars-long-term/removeFolder', 'LongTerm\CircularsLongTermController@removeFolder');
                    // PAC_5-1595 追加　▲
                    Route::post('circulars-long-term/saveLongTermDocument', 'LongTerm\CircularsLongTermController@saveLongTermDocument');// 2395
                    Route::post('circulars-long-term/long-term-upload', 'LongTerm\CircularsLongTermController@LongTermUpload');// 2395
                    //PAC_5-2034 テンプレート保存一覧
                    Route::any('template-index', 'TemplateRegisterController@index')->name('Template.index');
                    Route::post('template-index/upload', 'TemplateRegisterController@upload')->name('Template.update');
                    Route::post('template-index/delete', 'TemplateRegisterController@delete')->name('delete');

                    Route::any('circulars-downloadlist', 'CircularsDownloadListController@index')->name('Circulars.DownloadList');
                    Route::post('circulars-downloadlist/export', 'CircularsDownloadListController@export');
                    Route::post('circulars-downloadlist/delete', 'CircularsDownloadListController@delete');
                    Route::post('circulars-downloadlist/rerequest', 'CircularsDownloadListController@rerequest');
                    Route::post('circulars-downloadlist/sanitizingUpdate', 'CircularsDownloadListController@sanitizingUpdate');//PAC_5-2874

                    Route::post('ajax/setting-address-common/delete', 'Admin\CommonAddressController@deleteAddress')->name('AddressDelete');
                    Route::post('setting-address-common/csv', 'Admin\CommonAddressController@import')->name('AddressImport');
                    Route::resource('setting-address-common', 'Admin\CommonAddressController');

                    // 承認ルート
//                    Route::post('ajax/setting-address-common/delete', 'Admin\TemplateRouteController@deleteAddress')->name('AddressDelete');
                    Route::resource('template-route', 'Admin\TemplateRouteController');
                    Route::post('template-route/csv', 'Admin\TemplateRouteController@import')->name('templateRoute.import'); // PAC_5-2133 csv取込
                    Route::post('ajax/template-route/deletes', 'Admin\TemplateRouteController@deletes')->name('templateRoute.deletes');
                    Route::get('template-route/getRouteInfo/{routeId}/{templateId}', 'Admin\TemplateRouteController@getRouteInfo');

                    //回覧完了テンプレート一覧画面
                    Route::get('Template-csv', 'TemplateAdminController@index')->name('templateCsv.index');
                    Route::post('Template-csv/download', 'TemplateAdminController@download')->name('templateCsv.download');

                    Route::get('department-title/add-download', 'DepartmentTitleController@addDepartmentDownload');
                    Route::get('department-title/download/{id}', 'DepartmentTitleController@departmentDownload')->name('Department.download');
                    Route::delete('department-title/download/{id}', 'DepartmentTitleController@deleteDepartmentDownload');
                    Route::resources([
                        'department-title' => 'DepartmentTitleController',
                    ]);
                    Route::post('department-title/importDep', 'DepartmentTitleController@importDep')->name('Department.import');
                    Route::post('department-title/importPos', 'DepartmentTitleController@importPos')->name('Position.import');
                    Route::post('department-title/department/sort', 'DepartmentTitleController@updateDepartmentSort');
                    Route::post('department-title/position/sort', 'DepartmentTitleController@updatePositionSort');
                    Route::get('department-title/ajax/department', 'DepartmentTitleController@getDepartment');
                    Route::any('form-issuance/user-register', 'FormIssuance\FormIssuanceController@index');
                    Route::post('form-issuance/user-register/bulk-usage', 'FormIssuance\FormIssuanceController@bulkUsage');
                    Route::get('/form-issuance/user-register/{id}', 'FormIssuance\FormIssuanceController@show');
                    Route::post('/form-issuance/user-register/{id}', 'FormIssuance\FormIssuanceController@update');
                    Route::resource('form-issuance/frm-index', 'FormIssuance\FormIssuanceIndexController');

                    // PAC_5-2663
                    Route::group(['middleware' => ['CheckCompanyUseChat']], function () {
                        Route::get('chat/management-user', 'Chat\ChatController@index');

                        Route::group(['middleware' => ['CheckCompanyContractChat']], function () {
                            Route::post('chat/management-user/bulk-usage', 'Chat\ChatController@bulkUsage');
                            Route::get('chat/management-user/{id}', 'Chat\ChatController@show');
                            Route::post('chat/management-user/{id}', 'Chat\ChatController@update');
                        });
                    });

                    Route::any('dispatcharea', 'DispatchAreaController@index')->name('dispatcharea.index');
                    Route::any('contract', 'ContractController@index')->name('contract.index');
                    Route::any('dispatchhr', 'DispatchHRController@index')->name('dispatchhr.index');
                    Route::post('dispatcharea/agencydeletes', 'DispatchAreaController@agencydeletes')->name('dispatcharea.agencydeletes');
                    Route::post('dispatcharea/dispatchareadeletes', 'DispatchAreaController@dispatchareadeletes')->name('dispatcharea.dispatchareadeletes');
                    Route::post('dispatcharea/agencysave', 'DispatchAreaController@agencysave')->name('dispatcharea.agencysave');
                    Route::post('dispatcharea/dispatchareasave', 'DispatchAreaController@dispatchareasave')->name('dispatcharea.dispatchareasave');
                    Route::post('dispatcharea/getagency', 'DispatchAreaController@getagency')->name('dispatcharea.getagency');
                    Route::post('dispatcharea/geteditdata', 'DispatchAreaController@geteditdata')->name('dispatcharea.geteditdata');
                    Route::post('contract/geteditdata', 'ContractController@geteditdata')->name('contract.geteditdata');
                    Route::post('contract/deletes', 'ContractController@deletes')->name('contract.deletes');
                    Route::post('contract/save', 'ContractController@save')->name('contract.save');
                    Route::post('contract/getdispatcharea', 'ContractController@getdispatcharea')->name('contract.getdispatcharea');
                    Route::post('contract/getuser', 'ContractController@getuser')->name('contract.getuser');
                    Route::post('dispatchhr/geteditdata', 'DispatchHRController@geteditdata')->name('dispatchhr.geteditdata');
                    Route::post('dispatchhr/deletes', 'DispatchHRController@deletes')->name('dispatchhr.deletes');
                    Route::post('dispatchhr/save', 'DispatchHRController@save')->name('dispatchhr.save');
                    Route::post('dispatchhr/getuser', 'DispatchHRController@getuser')->name('dispatchhr.getuser');
                    Route::post('dispatchhr/savesetting', 'DispatchHRController@savesetting')->name('dispatchhr.savesetting');
                    Route::post('dispatchhr/savejobcareer', 'DispatchHRController@savejobcareer')->name('dispatchhr.savejobcareer');
                    Route::post('dispatchhr/geteditjobcareer', 'DispatchHRController@geteditjobcareer')->name('dispatchhr.geteditjobcareer');
                    Route::post('dispatchhr/deletejobcareer', 'DispatchHRController@deletejobcareer')->name('dispatchhr.deletejobcareer');

                    // PAC_14-45 Start
                    Route::get('setting-groupware/holiday', 'SettingGroupware\HolidayController@index')->name('Holiday.Index');
                    Route::post('setting-groupware/add-holiday', 'SettingGroupware\HolidayController@store')->name('Holiday.Store');
                    Route::put('setting-groupware/holiday/{id}', 'SettingGroupware\HolidayController@update')->name('Holiday.Update');
                    Route::delete('setting-groupware/holiday', 'SettingGroupware\HolidayController@destroy')->name('Holiday.Destroy');
                    Route::post('setting-groupware/holiday-reset', 'SettingGroupware\HolidayController@reset')->name('Holiday.Reset');
                    // PAC_14-45 End

                    //経費精算 様式一覧
                    Route::any('expense/user-register', 'Expense\ExpenseController@index');
                    Route::post('expense/user-register/bulk-usage', 'Expense\ExpenseController@bulkUsage');
                    Route::get('expense/user-register/{id}', 'Expense\ExpenseController@show');
                    Route::post('expense/user-register/{id}', 'Expense\ExpenseController@update');
                    Route::get('expense/m_purpose', 'Expense\Exp_m_purposeController@index');  //目的管理
                    Route::post('expense/m_purpose/bulk-usage', 'Expense\Exp_m_purposeController@bulkUsage');
                    Route::get('expense/m_purpose/{id}', 'Expense\Exp_m_purposeController@show');
                    Route::post('expense/m_purpose/{id}', 'Expense\Exp_m_purposeController@update');
                    Route::put('expense/m_purpose', 'Expense\Exp_m_purposeController@store');
                    Route::any('expense/m_wtsm', 'Expense\Exp_m_wtsmController@index');  //用途管理
                    Route::post('expense/m_wtsm/bulk-usage', 'Expense\Exp_m_wtsmController@bulkUsage');
                    Route::get('expense/m_wtsm/{id}', 'Expense\Exp_m_wtsmController@show');
                    Route::post('expense/m_wtsm/{id}', 'Expense\Exp_m_wtsmController@update');
                    Route::put('expense/m_wtsm', 'Expense\Exp_m_wtsmController@store');
                    Route::any('expense/m_account', 'Expense\Exp_m_accountController@index');  //勘定科目管理
                    Route::post('expense/m_account/bulk-usage', 'Expense\Exp_m_accountController@bulkUsage');
                    Route::get('expense/m_account/{id}', 'Expense\Exp_m_accountController@show');
                    Route::post('expense/m_account/{id}', 'Expense\Exp_m_accountController@update');
                    Route::put('expense/m_account', 'Expense\Exp_m_accountController@store');
                    Route::any('expense/m_journal_config', 'Expense\Exp_m_journalController@index'); //仕分設定
                    Route::post('expense/m_journal_config/bulk-usage', 'Expense\Exp_m_journalController@bulkUsage');
                    Route::get('expense/m_journal_config/{id}', 'Expense\Exp_m_journalController@show');
                    Route::post('expense/m_journal_config/{id}', 'Expense\Exp_m_journalController@update');
                    Route::put('expense/m_journal_config/{id}', 'Expense\Exp_m_journalController@store');
                    Route::get('expense/m_form_adv', 'Expense\Exp_m_form_advController@index');//事前申請様式一覧
                    Route::get('expense/m_form_adv/{form_code}', 'Expense\Exp_m_form_advController@show');
                    Route::post('expense/m_form_adv', 'Expense\Exp_m_form_advController@post');
                    Route::post('expense/m_form_adv/update', 'Expense\Exp_m_form_advController@update');
                    Route::post('expense/m_form_adv/delete', 'Expense\Exp_m_form_advController@delete');
                    Route::get('expense/m_form_exp', 'Expense\Exp_m_form_expController@index');//精算申請様式一覧
                    Route::get('expense/m_form_exp/{form_code}', 'Expense\Exp_m_form_expController@show');
                    Route::post('expense/m_form_exp', 'Expense\Exp_m_form_expController@post');
                    Route::post('expense/m_form_exp/update', 'Expense\Exp_m_form_expController@update');
                    Route::post('expense/m_form_exp/delete', 'Expense\Exp_m_form_expController@delete');
                    Route::post('expense/m_form_exp/check', 'Expense\Exp_m_form_expController@check');
                    Route::any('expense/t_app', 'Expense\Exp_t_appController@index'); //経費申請一覧
                    Route::post('expense/t_app/detail1/{id}', 'Expense\Exp_t_appController@show');
                    Route::post('expense/t_app/indexdetail/{id}', 'Expense\Exp_t_appController@indexDetail');
                    Route::post('expense/t_app/detail2/{id}', 'Expense\Exp_t_appController@show2');
                    Route::post('expense/t_app/reserve', 'Expense\Exp_t_appController@reserve'); 
                    Route::any('expense/t_journal', 'Expense\Exp_t_journalController@index'); //経費仕訳一覧
                    Route::post('expense/t_journal/show/{id}', 'Expense\Exp_t_journalController@show');
                    Route::put('expense/t_journal/{id}', 'Expense\Exp_t_journalController@update');
                    Route::post('expense/t_journal/store', 'Expense\Exp_t_journalController@store');
                    Route::post('expense/t_journal/delete/{id}', 'Expense\Exp_t_journalController@delete'); 
                    Route::post('expense/t_journal/reserve', 'Expense\Exp_t_appController@reserve');
                    //Route::post('expense/t_journal/delete/{id}', 'Expense\Exp_t_journalController@delete');
                    //Route::post('expense/t_journal/reserve', 'Expense\Exp_t_journalController@reserve'); // ダウンロード予約
                });

                Route::group(['middleware' => ['CheckShachihataAdmin']], function () {
                    Route::get('setting/constraint', 'Setting\SettingConstraintController@index')->name('SettingConstraint');
                    Route::post('setting/constraint', 'Setting\SettingConstraintController@postUpdateSettingCorporate')->name('postUpdateSettingCorporate');
                    Route::resources([
                        'companies' => 'Shachihata\CompaniesController',
                    ]);
                    Route::post('setting/convenient/stamps/upload','Setting\SettingConvenientController@uploadStamps')->name('StampConvenient.Upload');;
                    Route::post('setting/convenient/stamps/search', 'Setting\SettingConvenientController@search')->name('StampConvenient.Search');

                    Route::resources([
                        'setting/convenient' => 'Setting\SettingConvenientController',
                    ]);
                    Route::post('companies/upload-saml-metadata', 'Shachihata\CompaniesController@uploadSamlMetadata')->name('uploadSamlMetadata');
                    Route::post('companies-stamp/list-company', 'Shachihata\CompaniesController@getListCompany')->name('Companies.getListCompany');
                    Route::post('companies/depstamps/{company_id?}', 'Shachihata\CompaniesController@importDepStamps')->name('Companies.depstamps');
                    Route::post('companies-admin/{company_id?}', 'Shachihata\CompaniesController@indexAdmin')->name('Companies.admin');
                    Route::get('companies-stamp/{company_id?}', 'Shachihata\CompaniesController@indexStamp')->name('Companies.indexStamp');
                    Route::post('companies-stamp/{company_id}', 'Shachihata\CompaniesController@addStamps')->name('Companies.addStamps');
                    Route::put('companies-stamp/{company_id}/{stamp_id}', 'Shachihata\CompaniesController@updateStamp')->name('Companies.updateStamp');
                    Route::delete('companies-stamp/{company_id}/{stamp_id}', 'Shachihata\CompaniesController@deleteStamp')->name('Companies.deleteStamp');
                    Route::get('companies-stamp/resetpass/{company_id?}', 'Shachihata\CompaniesController@resetpass')->name('Companies.resetpass');

                    Route::resources(['edition' => 'EditionController']); //契約Edition

                    Route::get('/mail-send-resume', 'MailSendResumeController@index');
                    Route::get('/mail-send-resume/{mail_id}', 'MailSendResumeController@show');
                    Route::get('/mail-send-resume/send-mail/{mail_id}', 'MailSendResumeController@mailResend');

                    Route::get('/login-layout-setting', 'loginSettingController@index');
                    Route::post('/login-layout-setting/write', 'loginSettingController@write');
                    Route::post('/login-layout-setting/writeurl', 'loginSettingController@writeurl');
                    Route::post('/login-layout-setting/imageChange', 'loginSettingController@imageChange');

                    // PAC_5-2912 S
                    Route::resources(['setting/sanitizing' => 'Setting\SettingSanitizingController']); //無害化回線設定
                    // PAC_5-2912 E
                });

                Route::get('setting-groupware/app-use', 'AppUseController@index')->name('AppUse.Index');//アプリ利用設定
                Route::post('setting-groupware/app-use', 'AppUseController@index');
                Route::get('setting-groupware/app-use/{id}', 'AppUseController@show');
                Route::post('setting-groupware/app-use/{id}', 'AppUseController@store')->name('AppUse.store');
                Route::put('setting-groupware/app-use', 'AppUseController@update')->name('AppUse.update');
                Route::get('setting-groupware/app-role', 'AppRoleController@index')->name('AppRole.Index');//アプリロール設定
                Route::post('setting-groupware/app-role', 'AppRoleController@index');
                Route::get('setting-groupware/app-role/{id}/{app_id}', 'AppRoleController@show');
                Route::post('setting-groupware/app-role/store', 'AppRoleController@store')->name('AppRole.store');
                Route::put('setting-groupware/app-role', 'AppRoleController@update')->name('AppRole.update');
                Route::put('setting-groupware/app-role-detailupdate/{id}', 'AppRoleController@detailupdate')->name('AppRole.detailupdate');
                Route::post('setting-groupware/app-role-detailstore/', 'AppRoleController@detailstore')->name('AppRole.detailstore');
                Route::delete('setting-groupware/app-role-detaildelete/{id}/{app_id}', 'AppRoleController@delete')->name('AppRole.delete');
                Route::post('setting-groupware/app-role-updateflg', 'AppRoleController@AppRoleStore')->name('AppRoleUpdateFlg');
                Route::get('setting-groupware/show-schedule', 'AppUseController@showSchedule');
                Route::post('setting-groupware/update-schedule', 'AppUseController@updateSchedule');

                Route::post('setting-groupware/facility', 'FacilityController@show');
                Route::get('setting-groupware/facility', 'FacilityController@show');
                Route::delete('setting-groupware/facility/{id}', 'FacilityController@delete');
                // PAC_14-61  カテゴリ設定
                Route::get('setting-groupware/colorCategoryList', 'ColorCategoryController@index')->name("colorCategory.index");
                Route::post('setting-groupware/colorCategory', 'ColorCategoryController@store')->name("colorCategory.create");
                Route::post('setting-groupware/colorCategory/show/', 'ColorCategoryController@show')->name("colorCategory.show");
                Route::put('setting-groupware/colorCategory/', 'ColorCategoryController@update')->name("colorCategory.update");
                Route::post('setting-groupware/colorCategory/delete', 'ColorCategoryController@delete')->name("colorCategory.delete");

                Route::get('setting-groupware/master-sync', 'MasterSyncController@show');

                Route::get('/home', 'AdminController@home')->name('home');

                Route::get('/', 'AdminController@home');

                Route::group(['prefix' => 'error'], function (){
                    Route::get('/permission-denied', function () {return view('errors.permission_denied');})->name('errors.PermissionDenied');
                });

                Route::get('get-stamp-over-status','Admin\UserController@getStampOverStatus');
                Route::get('find-user-stamp-ok-status','Admin\UserController@findCurrentUserStampIsOk');
                // PAC_5-1902 追加　▼
                Route::get('special-upload','Special\SpecialUploadController@index')->name('SpecialUpload.Index');
                Route::put('special-upload','Special\SpecialUploadController@update')->name('SpecialUpload.Update');
                Route::post('special-upload','Special\SpecialUploadController@upload')->name('SpecialUpload.Upload');
                Route::post('special-upload/destroy','Special\SpecialUploadController@destroy')->name('SpecialUpload.Destroy');
                Route::any('special-receive', 'Special\SpecialReceiveController@index')->name('SpecialReceive.Index'); //連携承認情報を取得
                Route::put('special-receive','Special\SpecialReceiveController@update')->name('SpecialReceive.Update');
                Route::any('special-send','Special\SpecialSendController@index')->name('SpecialSend.Index'); //連携申請情報を取得
                Route::put('special-send','Special\SpecialSendController@update')->name('SpecialSend.Update');
                // PAC_5-1902 追加　▲

            });
        });
    });

});
Route::view('/login-wrapper', 'login-wrapper');

