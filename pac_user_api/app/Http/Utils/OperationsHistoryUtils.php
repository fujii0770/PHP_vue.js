<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */
namespace App\Http\Utils;

use App\Jobs\TransferEnvLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class OperationsHistoryUtils
{
    const HISTORY_FLG_ADMIN = 0;
    const HISTORY_FLG_USER = 1;
    const HISTORY_FLG_API = 2;
    const HISTORY_FLG_AUDIT_USER = 3; //受信専用利用者
    const STATUS = ['成功', '失敗'];

    const DESTINATION_NULL = 0; // 出力なし
    const DESTINATION_DATABASE = 1; // 自環境: DB直接
    const DESTINATION_LOGSTASH = 2; // 自環境: logstash
    const DESTINATION_OTHER_SERVER = 3; // 他環境（同エディション）

    const COMPLETED_DOWNLOAD_RESERVE = 1; //完了一覧で予約
    const LONG_TERM_DOWNLOAD_RESERVE = 2; //長期保管で予約
    const FORM_ISSUANCE_DOWNLOAD_RESERVE = 3; //帳票一覧で予約
    const VIEWING_CIRCULAR_DOWNLOAD_RESERVE = 4;//閲覧一覧で予約
    const TEMPLATE_CSV_DOWNLOAD_RESERVE = 5; //回覧完了テンプレート一覧で予約
    const DOWNLOAD_FILE = 6;//ダウンロード状況でダウンロード

    /**
     * mst_display_id,  mst_operation_id, message_true, message_false, fields, fields multi
     */
    const  LOG_INFO = [
            'Auth' => 
            [
                'login' => 
                [
                    'withBox' => [87,500,'ログインに成功しました(with box)。','ログインに失敗しました(with box)。'],
                    'common' => [50,100,'ログインに成功しました。','ログインに失敗しました。'],
                ],
                'appLogin' =>
                [
                    'withBox' => [87,500,'ログインに成功しました(with box)。','ログインに失敗しました(with box)。'],
                    'common' => [50,100,'ログインに成功しました。','ログインに失敗しました。'],
                ],
                'recall' =>
                [
                    'withBox' => [87,500,'ログインに成功しました(with box)。','ログインに失敗しました(with box)。'],
                    'common' => [50,100,'ログインに成功しました。','ログインに失敗しました。'],
                ],
                'appRecall' => 
                [
                    'withBox' => [87,500,'ログインに成功しました(with box)。','ログインに失敗しました(with box)。'],
                    'common' => [50,100,'ログインに成功しました。','ログインに失敗しました。'],
                ],
                'logout' => 
                [
                    'withBox' => [87,501,'ログアウトに成功しました(with box)。','ログアウトに失敗しました(with box)。'],
                    'common' => [52,102,'ログアウトに成功しました。','ログアウトに失敗しました。'],
                ],
            ],
            'Password' => [
                'setPassword' => [54,104,'パスワードの変更に成功しました。','パスワードの変更に失敗しました。'],
                'sendReentryMail' => [54,106,'パスワード設定メール再送に成功しました。','パスワード設定メール再送に失敗しました。'],
            ],
            'CircularUserAPI' => [
                'indexCompleted' => [77,344,'完了一覧の検索結果の表示に成功しました。。','完了一覧の検索結果の表示に失敗しました。'],
                'pullback' =>  [58,122,'回覧を引戻しました。','回覧の引戻しに失敗しました。'],
                'sendNotifyFirst' =>  [62,143,'回覧を申請しました。回覧ID：:circular_id、件名：:title、ファイル名：:filename、宛先：:mail_to、宛先変更設定：:address_change_flg'
                                            ,'回覧の申請に失敗しました。回覧ID：:circular_id'
                                            ,['circular_id', 'title', 'filename', 'mail_to', 'enum:address_change_flg:不可,可']],
                'sendNotifyContinue' => [64,152,'「:mail_title」を承認しました。','「:mail_title」の承認に失敗しました。',
                                            ['sess:mail_title']],
                'sendBack' => [65,155,'「:mail_title」を差戻しました。','「:mail_title」の差戻しに失敗しました。',
                                ['sess:mail_title']],
            ],
            'CircularAPI' => [
                'getByHash' => [50,100,'ログインに成功しました。（承認依頼メールから）','ログインに失敗しました。（承認依頼メールから）'],
                'actionMultiple' =>
                [
                    'reNotification' => [58,123,'[:emails] へ再通知メールを送信しました。回覧ID：:cids',
                                        '[:emails] へ再通知メールを送信できませんでした。回覧ID：:cids',[], ['sess:cids','sess:emails']],
                    'deleteSent' => [58,124,':fileNamesを削除しました。',':fileNamesを削除できませんでした。',['sess:fileNames']],
                    'deleteCompleted' => [58,342,':fileNamesを削除しました。',':fileNamesを削除できませんでした。',['sess:fileNames']],
                    'deleteSaved' => [59,129,':fileNamesを削除しました。',':fileNamesを削除できませんでした。',['sess:fileNames']],
                    'storeMultipleCircular' => [72,232,':fileNamesを長期保管しました。',':fileNamesを長期保管できませんでした。',['sess:fileNames']],
                ],
                'exportWorkListToPdf' => [71, 191, '回覧用勤務表の作成に成功しました。', '回覧用勤務表の作成に失敗しました。'],
                'storeCircular' => [72, 232, ':fileNamesを長期保管しました。',':fileNamesを長期保管できませんでした。',['sess:fileNames']],
            ],
            'CircularDocumentAPI' => [
            ],
            'ContactsAPI' => [
                'store' => [60,133,'グループ名：:group_name、氏名：:name、メールアドレス：:email','アドレスの登録に失敗しました。',['group_name','name','email']],
                'update' => [60,134,'グループ名：:group_name、氏名：:name、メールアドレス：:email','アドレス帳の更新に失敗しました。',['group_name','name','email']],
                'destroy' => [60,135,'グループ名：:group_name、氏名：:name、メールアドレス：:email','アドレスの削除に失敗しました。', ['sess:group_name','sess:name','sess:email']],
            ],
            'UserAPI' => [
            ],
            'FavoriteService' => [
                'store' => [67, 161,'ポータル - サービスの登録に成功しました。サービス名：:service_name、URL :url','ポータル - サービスの登録に失敗しました。サービス名：:service_name、URL :url', ['service_name', 'url']],
                'destroy' => [67, 162,'ポータル - サービスの削除に成功しました。サービス名：:service_name、URL :url','ポータル - サービスの削除に失敗しました。サービス名：:service_name、URL :url', ['sess:service_name', 'sess:url']],
            ],
            'Mfa' => [
                'auth' => [66,156,'認証コードによる認証に成功しました。','認証コードによる認証に失敗しました。'],
                'resendAuthMail' => [66,157,'認証メール再送信に成功しました。','認証メール再送信に失敗しました。'],
            ],
            'HRWorkDetailAPI' => [
                'index' => [71, 196, 'タイムカード画面の表示に成功しました。', 'タイムカード画面の表示に失敗しました。'],
                'registerNewTimeCardDetail' => [71, 197, '出勤時刻の登録に成功しました。', '出勤時刻の登録に失敗しました。'],
                'leaveWork' => [71, 198, '退勤時刻の登録に成功しました。', '退勤時刻の登録に失敗しました。'],
                'onPaid' => [71, 178, '有給の登録に成功しました。', '有給の登録に失敗しました。'],
                'onHalfPaid' => [71, 179, '有給（半休）の登録に成功しました。', '有給（半休）の登録に失敗しました。'],
                'onSpecialHoliday' => [71, 180, '特休の登録に成功しました。', '特休の登録に失敗しました。'],
                'onHalfSpecialHoliday' => [71, 181, '特休（半休）の登録に成功しました。', '特休（半休）の登録に失敗しました。'],
                'onSubstituteHoliday' => [71, 182, '代休の登録に成功しました。', '代休の登録に失敗しました。'],
                'onHalfSubstituteHoliday' => [71, 183, '代休（半休）の登録に成功しました。', '代休（半休）の登録に失敗しました。'],
                'exportHrWorkListToCSV' => [71, 194, '勤務情報CSVの出力に成功しました。', '勤務情報CSVの出力に失敗しました。'],
                'register' => [71, 195, '勤務情報の更新に成功しました。', '勤務情報の更新に失敗しました。'],

            ],
            'MstHrDailyReportAPI' => [
                'index' => [71, 184, '日報画面の表示に成功しました。', '日報画面の表示に失敗しました。'],
                'search' => [71, 185, '検索結果の表示に成功しました。', '検索結果の表示に失敗しました。'],
                'register' => [71, 186, '日報の登録に成功しました。', '日報の登録に失敗しました。'],
            ],
            'WorkListAPI' => [
                'index' => [71, 187, '勤務一覧画面の表示に成功しました。', '勤務一覧画面の表示に失敗しました。'],
                'search' => [71, 188, '検索結果の表示に成功しました。', '検索結果の表示に失敗しました。'],
                'updateSubmissionState' => [71, 193, '勤務表の提出に成功しました。', '勤務表の提出に失敗しました。'],
            ],
            'UserWorkStatusListAPI' => [
                'index' => [71, 400, '勤務状況確認の表示に成功しました。', '勤務状況確認の表示に失敗しました。'],
            ],
            'LongTermDocumentApi' => [
                'delete' => [72, 233, ':fileNamesを削除しました。',':fileNamesを削除できませんでした。',['sess:fileNames']],
                'setIndex' => [72, 391, '長期保管インデックスを登録に成功しました。','長期保管インデックスを登録に失敗しました。'],
            ],
            'FormIssuanceAPI' => [
                'index' => [73, 300, '明細テンプレート一覧画面の表示に成功しました。', '明細テンプレート一覧画面の表示に失敗しました。'],
                'search' => [73, 301, '明細テンプレートの検索に成功しました。', '明細テンプレートの検索の検索に失敗しました。'],
                'uploadTemplate' => [73, 302, '明細テンプレートの登録に成功しました。', '明細テンプレートの登録に失敗しました。'],
                'settingTemplate' => [73, 304, '明細テンプレートの登録に成功しました。', '明細テンプレートの登録に失敗しました。'],
                'edit' => [73, 306, '明細の作成（アップロード）に成功しました。', '明細の作成（アップロード）に失敗しました。'],
                'enableFormTemplate' => [73, 315, '明細テンプレートの有効化に成功しました。', '明細テンプレートの有効化に失敗しました。'],
                'disableFormTemplate' => [73, 316, '明細テンプレートの無効化に成功しました。', '明細テンプレートの有効化に失敗しました。'],
                'delete' => [73, 317, '明細テンプレートの削除に成功しました。', '明細テンプレートの削除に失敗しました。'],
                'getFile' => [73, 318, '明細テンプレートのダウンロードに成功しました。', '明細テンプレートのダウンロードに失敗しました。'],
                'getFileCSVImport' => [73, 319, '明細テンプレートのインポートファイルダウンロードに成功しました。', '明細テンプレートのインポートファイルダウンロードに失敗しました。'],
                'formIssuanceDownloadLog' => [73, 320, '明細テンプレートのログファイルダウンロードに成功しました。', '明細テンプレートのログファイルダウンロードに失敗しました。'],

                // group action screen import
                'uploadCSVImport' => [73, 322, '明細インポートに成功しました。', '明細インポートに失敗しました。'],
                'formImportDownloadCSV' => [73, 323, 'ログファイルのダウンロードに成功しました。', 'ログファイルのダウンロードに失敗しました。'],

                // group action screen exportTemplateList
                'getExportTemplateList' => [73, 324, '明細Expテンプレート画面の表示に成功しました。', '明細Expテンプレート画面の表示に失敗しました。'],
                'searchExportTemplateList' => [73, 325, '明細Expテンプレートの検索に成功しました。', '明細Expテンプレートの検索に失敗しました。'],
                'uploadExpTemplate' => [73, 326, '明細Expテンプレートの登録に成功しました。', '明細Expテンプレートの登録に失敗しました。'],
                'getExpTemplate' => [73, 327, '明細Expテンプレートのダウンロードに成功しました。', '明細Expテンプレートのダウンロードに失敗しました。'],
                'deleteExpTemplate' => [73, 328, '明細Expテンプレートの削除に成功しました。', '明細Expテンプレートの削除に失敗しました。'],

            ],
            'DownloadDocument' => [
                'completedReserve' => [77, 357, '「:file_name」のダウンロード予約に成功しました。','「:file_name」のダウンロード予約に失敗しました。', ['file_name']],
                'longTermReserve'=>[72, 358,'「:file_name」のダウンロード予約に成功しました。','「:file_name」のダウンロード予約に失敗しました。', ['file_name']],
                'formIssuanceReserve'=>[73, 359,'「:file_name」のダウンロード予約に成功しました。','「:file_name」のダウンロード予約に失敗しました。', ['file_name']],
                'viewingReserve'=>[81, 360,'「:file_name」のダウンロード予約に成功しました。','「:file_name」のダウンロード予約に失敗しました。', ['file_name']],
                'templateCsvReserve'=>[80, 361,'「:file_name」のダウンロード予約に成功しました。','「:file_name」のダウンロード予約に失敗しました。', ['file_name']],
                'download' => [79, 362, '「:file_name」をダウンロードしました。', '「:file_name」をダウンロードできませんでした。', ['file_name']],
            ],
            'BbsAPI' => [
                'addCategory' => [82, 363, '掲示板カテゴリの追加に成功されました。', '掲示板カテゴリの追加に失敗しました。'],
                'updateCategory' => [82, 364, '掲示板カテゴリの更新に成功されました。', '掲示板カテゴリへの更新に失敗しました。'],
                'deleteCategory' => [82, 365, '掲示板カテゴリの削除に成功されました。', '掲示板カテゴリの削除に失敗しました。'],
                'addTopic' => [82, 367, '掲示板の投稿の作成に成功されました。', '掲示板の投稿の作成に失敗されました。'],
                'updateTopic' => [82, 368, '掲示板の投稿の更新に成功されました。', '掲示板の投稿の更新に失敗されました。'],
                'deleteTopic' => [82, 369, '掲示板の投稿の削除に成功されました。', '掲示板の投稿の削除に失敗されました。'],
                'addDraftTopic' => [82, 373, '掲示板の下書の保存に成功されました。', '掲示板の下書の保存に失敗されました。'],
                'updateDraftTopic' => [82, 373, '掲示板の下書の保存に成功されました。', '掲示板の下書の保存に失敗されました。'],
                'deleteDraftTopic' => [82, 374, '掲示板の下書の削除に成功されました。', '掲示板の下書の削除に失敗されました。'],
                'addComment' => [82, 370, '掲示板のコメントの追加に成功しました。', '掲示板のコメントの追加に失敗しました。'],
                'updateComment' => [82, 371, '掲示板のコメントの更新に成功しました。', '掲示板のコメントの更新に失敗しました。'],
                'deleteComment' => [82, 372, '掲示板のコメントの削除に成功しました。', '掲示板のコメントの削除に失敗しました。'],
            ],
        ];

    // LOG_INFO に対応する
    // SPECIAL_OPERATION には対応しない
    const ACCEPTABLE_LOG_INFO_WHEN_PUBLIC = [
        'CircularUserAPI@sendNotifyContinue',
        'CircularUserAPI@sendBack'
    ];

    const SPECIAL_OPERATION = [
        'AddStamp' => [
            'CreateNew' => [
                'Seal_Normal' => [69,176,'新規作成より文書に通常印を捺印しました。','新規作成より文書に通常印を捺印できませんでした。'],
                'Seal_Common' => [69,176,'新規作成より文書に共通印を捺印しました。','新規作成より文書に共通印を捺印できませんでした。'],
                'Text' => [70,177,'新規作成より文書に「:text」を追加しました。','新規作成より文書に「:text」を追加できませんでした。'],
            ],
            'CirculationDocument' => [
                'Seal_Normal' => [69,176,'回覧文書より文書に通常印を捺印しました。','回覧文書より文書に通常印を捺印できませんでした。'],
                'Seal_Common' => [69,176,'回覧文書より文書に共通印を捺印しました。','回覧文書より文書に共通印を捺印できませんでした。'],
                'Text' => [70,177,'回覧文書より文書に「:text」を追加しました。','回覧文書より文書に「:text」を追加できませんでした。'],
            ],
        ],
    ];

    // store-log から他環境へ転送する mst_operation_id のリスト
    const STORE_LOG_TRANSFER_ALLOWED_OPERATION_IDS = [108, 175];

    /**
     * ログ出力先を返す
     *
     * @param array $hashUserInfo CheckHashing により $request->attributes 内に格納された情報
     * @return int ログ出力先を示す数値
     */
    public static function getPublicLogDestination($hashUserInfo) {
        $is_same_edition = config('app.edition_flg') == $hashUserInfo['current_edition_flg'];
        $is_same_env = config('app.server_env') == $hashUserInfo['current_env_flg'];

        // 自環境か
        $is_same_server = $is_same_edition && $is_same_env
                            && config('app.server_flg') == $hashUserInfo['current_server_flg'];

        if ($is_same_server) {
            if ($hashUserInfo['is_external']) {
                // どの環境でもない
                return self::DESTINATION_NULL;
            } else {
                // 自環境のユーザー
                return self::DESTINATION_DATABASE;
            }
        } else if ($is_same_edition) {
            // 同じエディションの他環境のユーザー
            return self::DESTINATION_OTHER_SERVER;
        } else {
            // 他エディションのユーザー
            return self::DESTINATION_NULL;
        }
    }

    /**
     * 自環境へログを格納する
     *
     * @param array $records ログ情報配列の配列 $record['user_id'] は不要
     * @param int $userId ユーザーID
     * @param int $destination ログ出力先
     */
    public static function storeRecordsToCurrentEnv($records, $userId, $destination) {
        if (empty($records)) {
            return;
        }

        if (!isset($records[0])) {
            throw new \Exception('$records must be indexed array');
        }

        if ($userId == 0) {
            throw new \Exception('unexpected user_id');
        }

        foreach ($records as &$record) {
            $record['user_id'] = $userId;
        }
        unset($record);

        switch ($destination) {
            case self::DESTINATION_NULL:
                return;
            case self::DESTINATION_LOGSTASH:
                foreach ($records as $record) {
                    $line = self::recordArrayToString($record);
                    Log::channel('logstash')->info($line);
                }
                break;
            case self::DESTINATION_DATABASE:
                DB::table('operation_history')->insert($records);
                break;
            default:
                throw new \Exception('unexpected destination');
        }
    }

    /**
     * ダウンロード 操作履歴
     * @param $user_id int ユーザーID
     * @param $result boolean 処理の結果を格納。
     * @param $ip_address string 接続IPアドレス
     * @param $type int 1:完了一覧　｜ 2:長期保管 | 3:帳票一覧で予約 | 4:閲覧一覧で予約 | 5:回覧完了テンプレート一覧で予約 | 6:ダウンロード状況でダウンロード
     * @param $fileName string ファイルの名前
     */
    public static function storeOperationLog($user_id, $result, $ip_address, $type, $fileName,$isAuditUser = false){
        try {
            switch($type){
                case self::COMPLETED_DOWNLOAD_RESERVE:
                    $info = self::LOG_INFO['DownloadDocument']['completedReserve'];
                    break;
                case self::LONG_TERM_DOWNLOAD_RESERVE:
                    $info = self::LOG_INFO['DownloadDocument']['longTermReserve'];
                    break;
                case self::FORM_ISSUANCE_DOWNLOAD_RESERVE:
                    $info = self::LOG_INFO['DownloadDocument']['formIssuanceReserve'];
                    break;
                case self::VIEWING_CIRCULAR_DOWNLOAD_RESERVE:
                    $info = self::LOG_INFO['DownloadDocument']['viewingReserve'];
                    break;
                case self::TEMPLATE_CSV_DOWNLOAD_RESERVE:
                    $info = self::LOG_INFO['DownloadDocument']['templateCsvReserve'];
                    break;
                case self::DOWNLOAD_FILE:
                    $info = self::LOG_INFO['DownloadDocument']['download'];
                    break;
                default;
                    throw new \Exception('no match download type log');
            }

            if ($result === true){//処理の結果 成功
                $detail_info = __($info[2],['file_name' => $fileName]);
            }else{//成功
                $detail_info = __($info[3],['file_name' => $fileName]);
            }

            $record = [
                'auth_flg' => $isAuditUser?OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER:self::HISTORY_FLG_USER,
                'user_id' => $user_id,
                'mst_display_id' => $info[0],
                'mst_operation_id' => $info[1],
                'result' => $result ? 0 : 1,
                'detail_info' => $detail_info,
                'ip_address' => $ip_address,
                'create_at' => Carbon::now(),
            ];

            $line = self::recordArrayToString($record);
            Log::channel('logstash')->info($line);

        }catch (\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
        }

    }

    /**
     * 処理失敗したものをLaravelログに出力する
     *
     * @param array $records ログ情報配列の配列 $record['user_id'] は不要
     * @param string $title ログ行頭につける文字列
     */
    public static function storeRecordsAsFailedLog($records, $title) {
        if (empty($records)) {
            return;
        }

        if (!isset($records[0])) {
            throw new \Exception('$records must be indexed array');
        }

        foreach ($records as $record) {
            $record['user_id'] = 0;

            $line = self::recordArrayToString($record);
            Log::info("$title: $line");
        }
    }

    /**
     * 他環境へログを転送する
     *
     * @param array $records ログ情報配列の配列 $record['user_id'] は不要
     * @param string $email メールアドレス
     * @param int $env_flg
     * @param int $server_flg
     * @param boolean $isSync
     */
    public static function transferToOtherServer($records, $email, $env_flg, $server_flg, $isSync) {
        if (empty($records)) {
            return;
        }

        if (!isset($records[0])) {
            throw new \Exception('$records must be indexed array');
        }

        foreach ($records as &$record) {
            $record['create_at'] = Carbon::parse($record['create_at'])->toISOString();
        }
        unset($record);

        $job = new TransferEnvLog($email, $records, $env_flg, $server_flg);
        if ($isSync) {
            dispatch_now($job);
        } else {
            dispatch($job);
        }
    }

    /**
     * 自環境へログを格納する（メールアドレスから）
     *
     * @param array $records ログ情報配列の配列 $record['user_id'] は不要
     * @param string $email メールアドレス
     * @param int $destination ログ出力先
     */
    public static function storeRecordsToCurrentEnvEmail($records, $email, $destination) {
        $userId = self::getUserIdFromEmail($email);
        $isUserFound = $userId !== 0;

        if ($isUserFound) {
            self::storeRecordsToCurrentEnv($records, $userId, $destination);
        } else {
            self::storeRecordsAsFailedLog($records, "ID不明ユーザーの操作履歴: find user from email: $email");
        }
    }

    /**
     * ログを格納する (public)
     *
     * @param array $records ログ情報配列の配列 $record['user_id'] は不要
     * @param array $hashUserInfo CheckHashing により $request->attributes 内に格納された情報
     * @param boolean $transferAsync 他環境への転送が必要な場合、同期で行うかどうか
     */
    public static function storeRecordsPublic($records, $hashUserInfo, $transferSync) {
        $destination = self::getPublicLogDestination($hashUserInfo);

        if ($destination == self::DESTINATION_NULL) {
            return;
        }

        $email = $hashUserInfo['current_email'];

        $is_other_server = $destination == self::DESTINATION_OTHER_SERVER;
        if ($is_other_server) {
            self::transferToOtherServer($records, $email,
                                        $hashUserInfo['current_env_flg'],
                                        $hashUserInfo['current_server_flg'],
                                        $transferSync);
        } else {
            $userId = self::getUserIdFromHashUserInfo($hashUserInfo);
            $isUserFound = $userId !== 0;

            if ($isUserFound) {
                self::storeRecordsToCurrentEnv($records, $userId, $destination);
            } else {
                self::storeRecordsAsFailedLog($records, "ID不明ユーザーの操作履歴: find user from hashUserInfo: $email");
            }
        }
    }

    private static function getUserIdFromHashUserInfo($hashUserInfo) {
        $user = $hashUserInfo['user'];
        // ユーザーが見つからない場合、値は0
        return $user->mst_user_id ?? $user->id;
    }

    private static function getUserIdFromEmail($email) {
        $user = DB::table('mst_user')
            ->select('id')
            ->where('email', $email)
            ->where('state_flg', AppUtils::STATE_VALID)
            ->first();

        return $user->id ?? 0;
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
