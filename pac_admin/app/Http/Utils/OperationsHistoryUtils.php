<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */
namespace App\Http\Utils;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationsHistoryUtils
{
    const HISTORY_FLG_ADMIN = 0;
    const HISTORY_FLG_USER = 1;
    const HISTORY_FLG_API = 2;
    const HISTORY_FLG_AUDIT_USER = 3; //受信専用利用者
    const STATUS = ['成功', '失敗'];
    const CIRCULAR_IMPRINT_STATUS = 2; // 捺印 pac_user_apiにのみ存在するクラスのため、暫定対応としてこのクラスに記載

    /**
     * mst_display_id,  mst_operation_id, message_true, message_false, fields, fields multi
     */
    const  LOG_INFO = [
            'Login' => [
                'login' => [1,1,'ログインに成功しました。','ログインに失敗しました。'],
                'recall' => [1,1,'ログインに成功しました。','ログインに失敗しました。'],
                'logout' => [1,2,'ログアウトに成功しました。','ログアウトに失敗しました。'],
            ],
            'ReportsUsage' => [
                'show' => [3,3, '利用状況画面の表示に成功しました。','利用状況画面の表示に失敗しました。'],
//                'search' => [3,4,'検索結果の表示に成功しました。', '検索結果の表示に失敗しました。'],
            ],
            'OperationHistory' => [
                'admin' => [
                    'get' => [4,5,'管理者操作履歴画面の表示に成功しました。', '管理者操作履歴画面の表示に失敗しました。'],
//                    'post' => [4,6,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                    'post' => [4,5,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                ],
                'user' => [
                    'get' => [5,7,'利用者操作履歴画面の表示に成功しました。','利用者操作履歴画面の表示に失敗しました。'],
//                    'post' => [5,8,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                    'post' => [5,7,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                ],
//                'api' => [
//                    'get' => [6,9,'利用者API呼出履歴画面の表示に成功しました。','利用者API呼出履歴画面の表示に失敗しました。'],
//                    'post' => [6,10,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
//                ],
            ],
            'Branding' => [
                'show' => [7, 11, "ブランディング設定画面の表示に成功しました。", "ブランディング設定画面の表示に失敗しました。"],
                'store' => [7, 12, "ロゴ画像ファイル名：:file_logo、背景色：:background_color、文字色：:color", "ブランディング設定の更新に失敗しました。",['file:file_logo','background_color','color']],
            ],
            'Authority' => [
                'show' => [8,13,'管理者権限初期値設定画面の表示に成功しました。','管理者権限初期値設定画面の表示に失敗しました。'],
                'store' => [8,14,'管理者権限初期値設定の更新に成功しました。','管理者権限初期値設定の更新に失敗しました。'],
            ],
            'PasswordPolicy' => [
                'show' => [9,15,'パスワードポリシー設定画面の表示に成功しました。','パスワードポリシー設定画面の表示に失敗しました。'],
                'store' => [9,16,'パスワード最小文字数：:min_length、パスワード有効期限：:validity_period、前回と同じパスワード：:enable_password、パスワードメールの有効期間：:password_mail_validity_days','パスワードポリシー設定の更新に失敗しました。'
                ,['min_length','validity_period','enum:enable_password:使用不可,使用不可','password_mail_validity_days']],
            ],
            'DateStamp' => [
                'show' => [10,17,"日付印設定画面の表示に成功しました。","日付印設定画面の表示に失敗しました。"],
                'store' => [10,18,"日付形式：:dstamp_style","日付印設定の更新に失敗しました。",['dstamp_style']],
            ],
            'CompanyStamp' => [
                'index' => [11,19,"共通印設定画面の表示に成功しました。","共通印設定画面の表示に失敗しました。"],
//                'search' => [11,20,"検索結果の表示に成功しました。","検索結果の表示に失敗しました。"],
                'search' => [11,19,"検索結果の表示に成功しました。","検索結果の表示に失敗しました。"],
                'store' => [11,21,"共通印ID：:id 名称：:stamp_name","共通印名称の更新に失敗しました。",['id','stamp_name']],
                'destroy' => [11,22,"共通印ID：:id 名称：:stamp_name","共通印の削除に失敗しました。",['id','stamp_name']],
                'download' => [11,23,"共通印申請書ダウンロードに成功しました。","共通印申請書ダウンロードに失敗しました。"],
            ],
            'Limit' => [
                'show' => [12,24,'制限設定画面の表示に成功しました。','制限設定画面の表示に失敗しました。'],
                'store' => [12,25,'ローカル：:storage_local、BOX：:storage_box、Googleドライブ：:storage_google、Dropbox：:storage_dropbox、OneDrive：:storage_onedrive、送信先の制限：:enable_any_address、通知メールからの認証：:link_auth_flg、サムネイルの表示：:enable_email_thumbnail、受取人による文書の追加：可','制限設定の更新に失敗しました。'
                ,['enum:storage_local','enum:storage_box','enum:storage_google','enum:storage_dropbox', 'enum:storage_onedrive','enum:enable_any_address:制限しない,共通アドレス帳と管理者が登録した利用者のアドレスのみに制限する','enum:link_auth_flg:不要,必要','enum:enable_email_thumbnail:利用不可能,利用可能,','enum:receiver_permission:不可,可']],
            ],
            'SettingAdmin' => [
                'index' => [13,26,'管理者設定画面の表示に成功しました。','管理者設定画面の表示に失敗しました。'],
                'store' => [13,27,'メールアドレス：:email、氏名：:family_name:given_name、部署：:department_name、電話番号：:phone_number、状態：:sendEmail','管理者情報の登録に失敗しました。'
                        ,['email','family_name','given_name','department_name','phone_number','enum:sendEmail:不有効,有効']],
                'update' => [13,28,'メールアドレス：:email、氏名：:family_name:given_name、部署：:department_name、電話番号：:phone_number、状態：:state_flg','管理者情報の更新に失敗しました。'
                        ,['email','family_name','given_name','department_name','phone_number','enum:state_flg:-1|削除,0|登録,1|有効,9|無効']],
                'resetpass' => [13,29,'[:email]に初期パスワードの通知メールを送信しました。','[:email] に初期パスワードの通知メールを送信できませんでした。', ['sess:email']],
                'postPermission' => [13,30,'[:email]に管理者権限の更新に成功しました。','[:email]に管理者権限の更新に失敗しました。', ['email']],
            ],
            'User' => [
                'index' => [14,31,'利用者設定画面の表示に成功しました。','利用者設定画面の表示に失敗しました。'],
//                'search' => [14,32,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                'search' => [14,31,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                'store' => [14,33,'メールアドレス：:item.email、氏名：:item.family_name:item.given_name、部署：:item.info.mst_department_id、役職：:item.info.mst_position_id、電話番号：:item.info.phone_number、FAX番号：:item.info.fax_number、郵便番号：:item.info.postal_code、住所：:item.info.address、日付印の日付：:item.info.date_stamp_config、APIの使用：:item.info.api_apps','利用者情報の登録に失敗しました。'
                    ,['item.email','item.family_name','item.given_name','item.info.mst_department_id','item.info.mst_position_id','item.info.phone_number','item.info.fax_number', 'item.info.postal_code','item.info.address','enum:item.info.date_stamp_config:当日のみ,任意の日付', 'enum:item.info.api_apps:許可しない,許可する']],
                'update' => [14,34,'メールアドレス：:item.email、氏名：:item.family_name:item.given_name、部署：:item.info.mst_department_id、役職：:item.info.mst_position_id、電話番号：:item.info.phone_number、FAX番号：:item.info.fax_number、郵便番号：:item.info.postal_code、住所：:item.info.address、日付印の日付：:item.info.date_stamp_config、APIの使用：:item.info.api_apps、状態：:item.state_flg','利用者情報の更新に失敗しました。'
                    ,['item.email','item.family_name','item.given_name','item.info.mst_department_id','item.info.mst_position_id','item.info.phone_number','item.info.fax_number', 'item.info.postal_code','item.info.address','enum:item.info.date_stamp_config:当日のみ,任意の日付','enum:item.info.api_apps:許可しない,許可する','enum:item.state_flg:-1|削除,0|登録,1|有効,9|無効']],
                //
                'destroy' => [14,35,'利用者情報の削除に成功しました。','利用者情報の削除に失敗しました。'],
                //
                'deletes' => [14,35,'利用者情報の削除に成功しました。','利用者情報の削除に失敗しました。'],
                'resetpass' => [14,36,'[:emails] に初期パスワードの通知メールを送信しました。','[:emails] に初期パスワードの通知メールを送信できませんでした。',[], ['sess:emails']],
                'import' => [14,37,':fileの取り込みに成功しました。',':fileの取り込みに失敗しました。',['file:file']],
                'export' => [14,38,'CSVファイルのダウンロードに成功しました。','CSVファイルのダウンロードに失敗しました。'],
                'searchStamp' => [14,39,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
            ],
            'Assignstamps' => [ // message for true is custome in Middleware/LogOperation
                'store' => [ // stamp_flg: 0,1,2
                    [14,40,'','利用者への印面割当に失敗しました。'], // User Settings-Stamp Assignment
                    [15,45,'','共通印の割当に失敗しました。'], // Common seal assignment
                    [14,41,'','利用者への部署名入り日付印割当に失敗しました。'], // User setting-Date stamp assignment with department name
                ],
                'delete' => [ // stamp_flg: 0,1,2
                    [14,42,'','利用者へ割り当てた印面の削除に失敗しました。'],
                    [15,46,'','共通印の割当解除に失敗しました。'],
                    [14,42,'','利用者へ割り当てた印面の削除に失敗しました。'],
                ],
            ],
            'UserAssignStamp' => [
                'index' => [15,43,'共通印割当画面の表示に成功しました。','共通印割当画面の表示に失敗しました。'],
    //                'search' => [15,44,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                'search' => [15,43,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
            ],
            'CommonAddress' => [
                'index' => [16,47,'共通アドレス帳画面の表示に成功しました。','共通アドレス帳画面の表示に失敗しました。'],
//                'search' => [16,48,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                'search' => [16,47,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                'store' => [16,49,'アドレス帳ID：:id、メールアドレス：:item.email、氏名：:item.name 会社名：:item.company_name、役職：:item.position_name'
                                ,'共通アドレス帳の登録に失敗しました。', ['sess:id','item.email','item.name','item.company_name', 'item.position_name']],
                'update' => [16,50,'アドレス帳ID：:item.id、メールアドレス：:item.email、氏名：:item.name 会社名：:item.company_name、役職：:item.position_name',
                                '共通アドレス帳の更新に失敗しました。', ['item.id','item.email','item.name','item.company_name', 'item.position_name']],
                'deleteAddress' => [16,51,'アドレス帳ID：:id、メールアドレス：:email、氏名：:name 会社名：:company_name、役職：:position_name',
                                    '共通アドレス帳の削除に失敗しました。', [],
                                    ['sess:id','sess:email','sess:name','sess:company_name', 'sess:position_name']],
                'destroy' => [16,51,'共通アドレス帳の削除に成功しました。','共通アドレス帳の削除に失敗しました。'],
                'import' => [16,52,':fileの取り込みに成功しました。','CSVファイルのダウンロードに失敗しました。', ['file:file']],
//                'export' => [16,53,'CSVファイルのダウンロードに成功しました。','承認ルート画面の表示に失敗しました。'],
            ],
            'TemplateRoute' => [
                'index' => [17,54,'検索結果の表示に成功しました。','検索結果の表示に失敗しました。'],
                'store' => [17,55,'承認ルートの登録に成功しました。','承認ルートの登録に失敗しました。'],
                'update' => [17,56,'承認ルートの更新に成功しました。','承認ルートの更新に失敗しました。'],
                'deletes' => [17,57,'承認ルートの削除に成功しました。','承認ルートの削除に失敗しました。'],
            ],
            'DepartmentTitle' => [
                'index' => [18,58, '部署・役職画面の表示に成功しました。', '部署・役職画面の表示に失敗しました。'],
                'store' => [
                    'department' => [18,59,'親部署：:parent_name、部署名：:item.department_name','部署の登録に失敗しました。',['sess:parent_name','item.department_name']],
                    'position' => [18,62,'役職名：:item.position_name','役職の登録に失敗しました。',['item.position_name']],
                 ],
                 'update' => [
                    'department' => [18,60,'部署名：:item.department_name','部署の名称変更に失敗しました。',['item.department_name']],
                    'position' => [18,63,'役職名：:item.position_name','役職の名称変更に失敗しました。',['item.position_name']],
                 ],
                 'destroy' => [
                    'department' => [18,61,'部署名：:name','部署の削除に失敗しました。',['sess:name']],
                    'position' => [18,64,'役職名：:name','役職の削除に失敗しました。',['sess:name']],
                 ],
                 'addDepartmentDownload' => [18,65,'CSVの出力リクエストを受け付けました。','CSVの出力リクエストに失敗しました。'],
                 'departmentDownload' => [18,66,':file_name のダウンロードに成功しました。',':file_name のダウンロードに失敗しました。',['sess:file_name']],
                 'deleteDepartmentDownload' => [18,67,':file_name の削除に成功しました。',':file_name の削除に失敗しました。',['sess:file_name']],
            ],

            'Circulars' => [
                'index' => [19,68,'回覧一覧画面の表示に成功しました。','回覧一覧画面の表示に失敗しました。'],
                'deletes' => [19,70,'ファイル名：:file_names、件名：:subject、作成者メールアドレス：:creator_email、作成者氏名：:creator_name、状態：:circular_status'
                        ,':file_namesの削除に失敗しました。',null,['sess:file_names','sess:subject','sess:creator_email','sess:creator_name', 'sess:circular_status']],
                'deleteCirculating' => [19,70,'ファイル名：:file_names、件名：:subject、作成者メールアドレス：:creator_email、作成者氏名：:creator_name、状態：:circular_status'
                    ,':file_namesの削除に失敗しました。',null,['sess:file_names','sess:subject','sess:creator_email','sess:creator_name', 'sess:circular_status']],
//                'exports' => [19,72,'ファイル名：:file_names、件名：:subject、作成者メールアドレス：:creator_email、作成者氏名：:creator_name、状態：:circular_status',
//                        ':file_namesのダウンロードに失敗しました。', null, ['sess:file_names','sess:subject','sess:creator_email','sess:creator_name', 'sess:circular_status']],
                'reserve' => [19,72,'「:file_name」のダウンロード予約に成功しました。','「:file_name」のダウンロード予約に失敗しました。', ['sess:file_name']],
            ],
            'CircularsSaved' => [
                'index' => [20,74,'保存文書一覧画面の表示に成功しました。','保存文書一覧画面の表示に失敗しました。'],
                'deletes' => [20,76,'ファイル名：:file_names、件名：:subject、作成者メールアドレス：:creator_email、作成者氏名：:creator_name、状態：:circular_status'
                        ,':file_namesの削除に失敗しました。',null,['sess:file_names','sess:subject','sess:creator_email','sess:creator_name', 'sess:circular_status']],
//                'exports' => [20,78,'ファイル名：:file_names、件名：:subject、作成者メールアドレス：:creator_email、作成者氏名：:creator_name、状態：:circular_status',
//                        ':file_namesのダウンロードに失敗しました。', null, ['sess:file_names','sess:subject','sess:creator_email','sess:creator_name', 'sess:circular_status']],
                'reserve' => [20,78,'保存文書一覧ダウンロードの予約に成功しました。','保存文書一覧ダウンロードの予約に失敗しました。'],
            ],
            'RosterList' => [
                'index'  => [28,217,'勤務表一覧画面の表示に成功しました。','勤務表一覧画面の表示に失敗しました。'],
                'export' => [28,218,'勤務情報CSVの作成に成功しました。','勤務情報CSVの作成に失敗しました。'],
                'update' => [28,219,'勤務表一覧画面にて一括承認に成功しました。','勤務表一覧画面にて一括承認に失敗しました。'],
            ],
            'WorkDetail' => [
                'index'  => [29,220,'勤務詳細画面の表示に成功しました。','勤務詳細画面の表示に失敗しました。'],
                'update' => [29,221,'勤務詳細画面にて更新に成功しました。','勤務詳細画面にて更新に失敗しました。'],
                'bulkApproval' => [29,222,'勤務詳細画面にて一括承認に成功しました。','勤務詳細画面にて一括承認に失敗しました。'],
            ],
            'AttendanceConfirm' => [
                'index'  => [30,223,'勤務状況確認画面の表示に成功しました。','勤務状況確認画面の表示に失敗しました。'],
                'update' => [30,224,'勤務状況確認画面にて更新に成功しました。','勤務状況確認画面にて更新に失敗しました。'],
                'bulkApproval' => [30,225,'勤務状況確認画面にて一括承認に成功しました。','勤務状況確認画面にて一括承認に失敗しました。'],
            ],
            'HrUserRegistration' => [
                'index'  => [31,226,'利用ユーザ登録画面の表示に成功しました。','利用ユーザ登録画面の表示に失敗しました。'],
                'store'  => [31,227,'利用ユーザの登録に成功しました。','利用ユーザの登録に失敗しました。'],
                'update' => [31,228,'利用ユーザの更新に成功しました。','利用ユーザの更新に失敗しました。'],
                'updateHrUser' => [31,229,'一括利用登録に成功しました。','一括利用登録に失敗しました。'],
            ],
            'DailyReport' => [
                'index'  => [32,230,'日報確認画面の表示に成功しました。','日報確認画面の表示に失敗しました。'],
                'store'  => [32,231,'日報確認画面の更新に成功しました。','日報確認画面の更新に失敗しました。'],
            ],
            'IpRestriction' => [
                'index' => [21,80,'接続IP制限設定画面の表示に成功しました。','接続IP制限設定画面の表示に失敗しました。'],
                'bulkUpdate' => [21,81,'接続IP制限設定の更新に成功しました。','接続IP制限設定の更新に失敗しました。'],
            ],
            'Mfa' => [
    //          'index' => [22,82,'認証コード入力画面の表示に成功しました。','認証コード入力画面の表示に失敗しました。'],
                'verify' => [22,83,'認証コードによる認証に成功しました。','認証コードによる認証に失敗しました。'],
                'resend' => [22,84,'認証メール再送信に成功しました。','認証メール再送信に失敗しました。'],
            ],
            'FormIssuance' => [
                'index' => [74, 308, '利用ユーザ登録画面の表示に成功しました。', '利用ユーザ登録画面の表示に失敗しました。'],
                'search' => [74, 309, '検索結果の表示に成功しました。', '検索結果の表示に失敗しました。'],
                'register' => [74, 310, '利用ユーザの一括登録に成功しました。', '利用ユーザの一括登録に失敗しました。'],
                'cancel' => [74, 311, '利用ユーザの一括削除に成功しました。', '利用ユーザの一括削除に失敗しました。'],
                'show' => [74, 312, '利用ユーザ登録詳細画面の表示に成功しました。', '利用ユーザ登録詳細画面の表示に失敗しました。'],
                'create' => [74, 313, '利用ユーザ登録詳細情報の登録に成功しました。', '利用ユーザ登録詳細情報の登録に失敗しました。'],
                'update' => [74, 314, '利用ユーザ登録詳細情報の更新に成功しました。', '利用ユーザ登録詳細情報の更新に失敗しました。'],

            ],
            'LongTermStorage' => [
                'index' => [33, 85, '長期保管設定画面の表示に成功しました。', '長期保管設定画面の表示に失敗しました。'],
            ],
            'CircularsLongTerm' => [
                'index' => [34, 86, '長期保管一覧画面の表示に成功しました。', '長期保管一覧画面の表示に失敗しました。'],
                'download' => [34, 87, '長期保管一覧ダウンロードの予約に成功しました。', '長期保管一覧ダウンロードの予約に失敗しました。'],
                'delete' => [34, 88, ':file_names の削除に成功しました。', ':file_names の削除に失敗しました。', ['sess:file_names']],
            ],
            'AuditAccount' => [
                'index' => [35, 89, '監査用アカウント設定画面の表示に成功しました。', '監査用アカウント設定画面の表示に失敗しました。'],
                'store' => [35, 90, 'メールアドレス : :audit_email、名称 : :audit_name、有効期限 : :audit_expiration_dateの登録に成功しました。',
                    'メールアドレス : :audit_email、名称 : :audit_name、有効期限 : :deadlineの登録に失敗しました。', ['sess:audit_email', 'sess:audit_name', 'sess:audit_expiration_date']],
                'update' => [35, 91, 'メールアドレス : :audit_email、名称 : :audit_name、有効期限 : :audit_expiration_dateの更新に成功しました。',
                    'メールアドレス : :audit_email、名称 : :audit_name、有効期限 : :deadlineの更新に失敗しました。', ['sess:audit_email', 'sess:audit_name', 'sess:audit_expiration_date']],
                'deletes' => [35, 92, ':audit_email の削除に成功しました。', ':audit_email の削除に失敗しました。', ['sess:audit_email']],
            ],
            'CircularsDownloadList' => [
                'export' => [36, 93,'「:file_name」をダウンロードしました。', '「:file_name」をダウンロードできませんでした。', ['sess:file_name']],
            ],
            'Expense' => [
                'index' => [75, 330, '利用ユーザ登録画面の表示に成功しました。', '利用ユーザ登録画面の表示に失敗しました。'],
                'register' => [75, 332, '利用ユーザの一括登録に成功しました。', '利用ユーザの一括登録に失敗しました。'],
                'cancel' => [75, 333, '利用ユーザの一括削除に成功しました。', '利用ユーザの一括削除に失敗しました。'],
                'show' => [75, 334, '利用ユーザ登録詳細画面の表示に成功しました。', '利用ユーザ登録詳細画面の表示に失敗しました。'],
                'create' => [75, 335, '利用ユーザ登録詳細情報の登録に成功しました。', '利用ユーザ登録詳細情報の登録に失敗しました。'],
                'update' => [75, 336, '利用ユーザ登録詳細情報の更新に成功しました。', '利用ユーザ登録詳細情報の更新に失敗しました。'],
            ],
            'Exp_m_purpose' => [
                'index' => [92, 337, '目的管理画面の表示に成功しました。', '目的管理画面の表示に失敗しました。'],
                'bulkUsage' => [92, 339, '目的管理の削除に成功しました。', '目的管理の削除に失敗しました。'],
                'store' => [92, 340, '目的管理の登録に成功しました。', '目的管理の登録に失敗しました。'],
                'update' => [92, 341, '目的管理の更新に成功しました。', '目的管理の更新に失敗しました。'],
            ],
            'Exp_m_wtsm' => [
                'index' => [93, 412, '用途管理画面の表示に成功しました。', '用途管理画面の表示に失敗しました。'],
                'bulkUsage' => [93, 413, '用途管理の削除に成功しました。', '用途管理の削除に失敗しました。'],
                'store' => [93, 414, '用途管理の登録に成功しました。', '用途管理の登録に失敗しました。'],
                'update' => [93, 415, '用途管理の更新に成功しました。', '用途管理の更新に失敗しました。'],
            ],
            'Exp_m_account' => [
                'index' => [94, 416, '勘定科目管理画面の表示に成功しました。', '勘定科目管理画面の表示に失敗しました。'],
                'bulkUsage' => [94, 417, '勘定科目管理の削除に成功しました。', '勘定科目管理の削除に失敗しました。'],
                'store' => [94, 418, '勘定科目管理の登録に成功しました。', '勘定科目管理の登録に失敗しました。'],
                'update' => [94, 419, '勘定科目管理の更新に成功しました。', '勘定科目管理の更新に失敗しました。'],
            ],
            'Exp_m_journal' => [
                'index' => [96, 420, '仕訳設定画面の表示に成功しました。', '仕訳設定画面の表示に失敗しました。'],
                'bulkUsage' => [96, 421, '仕訳設定の削除に成功しました。', '仕訳設定の削除に失敗しました。'],
                'store' => [96, 422, '仕訳設定の登録に成功しました。', '仕訳設定の登録に失敗しました。'],
                'update' => [96, 423, '仕訳設定の更新に成功しました。', '仕訳設定の更新に失敗しました。'],
            ],
            'Exp_m_form_adv' => [
                'index' => [99, 424, '事前申請様式一覧画面の表示に成功しました。', '事前申請様式一覧画面の表示に失敗しました。'],
                'delete' => [99, 425, '事前申請様式一覧の削除に成功しました。', '事前申請様式一覧の削除に失敗しました。'],
                'post' => [99, 426, '事前申請様式一覧の登録に成功しました。', '事前申請様式一覧の登録に失敗しました。'],
                'update' => [99, 427, '事前申請様式一覧の更新に成功しました。', '事前申請様式一覧の更新に失敗しました。'],
            ],
            'Exp_m_form_exp' => [
                'index' => [89, 428, '経費申請様式一覧画面の表示に成功しました。', '経費申請様式一覧画面の表示に失敗しました。'],
                'delete' => [89, 429, '経費申請様式一覧の削除に成功しました。', '経費申請様式一覧の削除に失敗しました。'],
                'post' => [89, 430, '経費申請様式一覧の登録に成功しました。', '経費申請様式一覧の登録に失敗しました。'],
                'update' => [89, 431, '経費申請様式一覧の更新に成功しました。', '仕訳設定の更新に失敗しました。'],
            ],
            'Exp_t_app' => [
                'index' => [90, 432, '事前申請一覧画面の表示に成功しました。', '事前申請一覧画面の表示に失敗しました。'],
            ],
            'Exp_t_journal' => [
                'index' => [91, 437, '経費仕訳一覧画面の表示に成功しました。', '経費仕訳一覧画面の表示に失敗しました。'],
                'delete' => [91, 438, '経費仕訳一覧の削除に成功しました。', '経費仕訳一覧の削除に失敗しました。'],
                'post' => [91, 439, '経費仕訳一覧の登録に成功しました。', '経費仕訳一覧の登録に失敗しました。'],
                'update' => [91, 440, '経費仕訳一覧の更新に成功しました。', '経費仕訳一覧の更新に失敗しました。'],
            ],
            'SpecialUpload' => [
                'index' => [84, 383, '特設サイト文書登録画面の表示に成功しました。', '特設サイト文書登録画面の表示に失敗しました。'],
                'upload' => [84, 384, '特設サイト文書 :file_name の登録に成功しました。', '特設サイト文書 :file_name の登録に失敗しました。', ['sess:file_name']],
                'update' => [84, 385, '特設サイト文書 :file_names の更新に成功しました。', '特設サイト文書 :file_names の更新に失敗しました。', ['sess:file_names']],
                'destroy' => [84, 386, '特設サイト文書 :file_names の削除に成功しました。', '特設サイト文書 :file_names の削除に失敗しました。', ['sess:file_names']],
            ],
            'SpecialReceive' => [
                'index' => [85, 387, '特設サイト連携承認画面の表示に成功しました。', '特設サイト連携承認画面の表示に失敗しました。'],
                'update' => [85, 388, '特設サイト連携承認の更新に成功しました。', '特設サイト連携承認の更新に失敗しました。'],
            ],
            'SpecialSend' => [
                'index' => [86, 389, '特設サイト連携申請画面の表示に成功しました。', '特設サイト連携申請画面の表示に失敗しました。'],
                'update' => [86, 390, '特設サイト連携申請の更新に成功しました。', '特設サイト連携申請の更新に失敗しました。'],
            ],
            'Chat' => [
                'index' => [78, 345, 'ササッとTalk利用者設定画面の表示に成功しました。', 'ササッとTalk利用者設定画面の表示に失敗しました。'],
                'search' => [78, 346, '検索結果の表示に成功しました。', '検索結果の表示に失敗しました。'],
                'multipleRegister' => [78, 347, 'ササッとTalk利用者の一括登録に成功しました。', 'ササッとTalk利用者の一括登録に失敗しました。'],
                'multipleStop' => [78, 349, 'ササッとTalk利用者の一括停止に成功しました。。', 'ササッとTalk利用者の一括停止に失敗しました。'],
                'multipleDelete' => [78, 348, 'ササッとTalk利用者の一括削除に成功しました。', 'ササッとTalk利用者の一括削除に失敗しました。'],
                'show' => [78, 351, 'ササッとTalk利用者設定詳細の表示に成功しました。', 'ササッとTalk利用者設定詳細の表示に失敗しました。'],
                'singleRegister' => [78, 352, 'ササッとTalk利用者設定詳細の登録に成功しました。', 'ササッとTalk利用者設定詳細の登録に失敗しました。'],
                'update' => [78, 353, 'ササッとTalk利用者設定詳細の更新に成功しました。', 'ササッとTalk利用者設定詳細の更新に失敗しました。'],
                'singleStop' => [78, 354, 'ササッとTalk利用者設定詳細の停止に成功しました。。', 'ササッとTalk利用者設定詳細の停止に失敗しました。'],
                'singleUnstop' => [78, 355, 'ササッとTalk利用者設定詳細の停止解除に成功しました。。', 'ササッとTalk利用者設定詳細の停止解除に失敗しました。'],
                'singleDelete' => [78, 356, 'ササッとTalk利用者設定詳細の削除に成功しました。', 'ササッとTalk利用者設定詳細の削除に失敗しました。'],
            ],
            'BoxEnabledAutoStorage' => [
                'index' => [97, 505, 'BOX自動保管画面の表示に成功しました。', 'BOX自動保管画面の表示に失敗しました。'],
                'saveAutoStorageSetting' => [97, 506, 'BOX自動保管設定の更新に成功しました。', 'BOX自動保管設定の更新に失敗しました。'],
                'reSaveAutoStorage' => [97, 507, '「:file_names」の再保存に成功しました。', '「:file_names」の再保存に失敗しました。',['sess:file_names']],
            ],
        ];
    public static function storeRecordsToCurrentEnv($records, $userId) {
        if (empty($records)) {
            return;
        }
        if (!isset($records[0])) {
            throw new \Exception('$records must be indexed array');
        }
        if ($userId == 0) {
            throw new \Exception('unexpected user_id');
        }
        foreach ($records as $record) {
            $record['user_id'] = $userId;
           $line= self::recordArrayToString($record);
            Log::channel('logstash')->info($line);
        }

    }
    private static function recordArrayToString($record) {
        // logstash 出力用
        $keys = ['auth_flg', 'user_id', 'mst_display_id', 'mst_operation_id', 'result', 'detail_info', 'ip_address', 'create_at'];

        $line_items = array_map(function ($key) use ($record) {
            return $record[$key];
        }, $keys);

        return implode(' ', $line_items);
    }
}
