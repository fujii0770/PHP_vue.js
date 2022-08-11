<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Utils;

use App\Services\OfficeConverterService;
use App\Utils\OfficeConvertApiUtils;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AppUtils
{
    const ACCOUNT_TYPE_AUDIT = 'audit';
    const ACCOUNT_TYPE_USER = 'user';
    const ACCOUNT_TYPE_ADMIN = 'admin';
    const ACCOUNT_TYPE_OPTION = 'option';

    //ユーザー種類
    const AUTH_FLG_AUDIT = 3; // 監査用アカウント
    const AUTH_FLG_ADMIN = 2; //管理者
    const AUTH_FLG_USER = 1; //利用者
    const AUTH_FLG_OPTION = 4; //グループウェア専用利用者
    const AUTH_FLG_RECEIVE = 5; //受信専用利用者

    const DEFAULT_LIMIT_PAGE = 10;

    const CIRCULAR_STATUS = [1 => '回覧中','回覧完了','回覧完了','差戻し'];

    // ユーザー状態
    const STATE_DELETE = -1; // 削除
    const STATE_INVALID = 9; // 無効
    const STATE_VALID = 1; // 有効

    // mst_userのユーザー種類
    const USER_NORMAL = 0; //回覧利用者
    const USER_OPTION = 1; //グループウェア専用利用者
    const USER_RECEIVE = 2; //受信専用利用者

    // HR SHIFT WORK KBN
    const SHIFT_WORK_NORMAL           = 0; //通常勤務
    const SHIFT_WORK_SHIFT_TYPE_ONE   = 1; //シフト勤務１
    const SHIFT_WORK_SHIFT_TYPE_TWO   = 2; //シフト勤務２
    const SHIFT_WORK_SHIFT_TYPE_THREE = 3; //シフト勤務３
    const SHIFT_WORK_FLEX             = 4; //フレックス

    // HR WORK FROM KBN
    const WORK_FROM_NORMAL            = 0; //通常
    const WORK_FROM_SHIFT             = 1; //シフト
    const WORK_FROM_FLEX              = 2; //フレックス

    //印面設定
    const PX_TO_MICROMET = (25.4 / 600) * 1000; // pdi from current edition is 600
    const MICROMET_TO_PX = 600 / 25400;

    //部署状態
    const DEPARTMENT_STATE_VALID = 1;
    //役職状態
    const POSITION_STATE_VALID = 1;

    //部署名ソート
    const STR_KANJI = array("一", "二", "三", "四", "五", "六", "七", "八", "九");
    const STR_SUUJI = array("1", "2", "3", "4", "5", "6", "7", "8", "9");
    const LOGIN_TYPE_SSO = 1;
    const LOGIN_TYPE_NORMAL = 0;
    const FLG_ENABLE = 1;
    const FLG_DISABLE = 0;
    const FOLDER_INVALID_CHARS = ['\\', '/', ':', '*', '?', '"', '<', '>', '|'];
    const FILE_INVALID_CHARS = ['/'];

    //バッチ執行状態
    const BATCH_RUNNING = 0; //処理中
    const BATCH_SUCCESS = 1; //成功
    const BATCH_FAIL = 2; //失敗

    //メール送信状態
    const MAIL_STATE_WAIT = 0; //送信待ち
    const MAIL_STATE_RUNNING = 1; //送信中
    const MAIL_STATE_SUCCESS = 2; //送信成功
    const MAIL_STATE_FAILED = 3; //送信失敗
    const MAIL_STATE_DELAY = 4; //遅延(ファイルメール便)

    //メール送信回数
    const MAIL_SEND_DEFAULT_TIMES = 0;

    //メール送信対象種別
    const MAIL_TYPE_USER = 0;
    const MAIL_TYPE_ADMIN = 1;
    const MAIL_TYPE_AUDIT = 2;

    const BATCH_HISTORY_EMAIL = [0 => '実行中', 1 => '実行成功', 2 => '実行失敗'];

    // 承認権限
    const APPROVE_REQUEST_INVALID = 0;  // なし
    const APPROVE_REQUEST_VALID = 1;    // ある
    // テンプレート状態
    const TEMPLATE_VALID = 1; // 有効
    const TEMPLATE_INVALID = 0; // 無効

    // 会社状態
    const COMPANY_STATE_INVALID = 0;    //無効
    const COMPANY_STATE_VALID = 1;      //有効

    // 契約Edition
    const CONTRACT_EDITION_STANDARD = 0;            // Standard
    const CONTRACT_EDITION_BUSINESS = 1;            // Business
    const CONTRACT_EDITION_BUSINESS_PRO = 2;        // Business Pro
    const CONTRACT_EDITION_TRIAL = 3;               // トライアル

    const TIMESTAMP_AUTOMATIC_ON  = 1;

    //グループウェア設定GW_APPLICATION_ID_BOARD
    const GW_APPLICATION_ID_BOARD = 1;

    /*PAC_5-2246 S*/
    const GW_APPLICATION_ID_FAQ_BOARD = 9; //サポート掲示板
    /*PAC_5-2246 E*/
    const GW_APPLICATION_ID_FILE_MAIL_EXTEND = 11; //サポート掲示板
    const GW_APPLICATION_ID_TO_DO_LIST = 12; //ToDoリスト
    // Form issuance
    const FORM_TYPE_INVOICE = 1; // 請求書
    const FORM_TYPE_OTHER = 0; // その他
    const DOCUMENT_TYPE_WORD = 1; // 請求書
    const DOCUMENT_TYPE_EXCEL = 0; // その他
    const ADDITIONAL_FLG = 1;
    const ADDITIONAL_FLG_DEFAULT = 0;

    // imp status
    const FORM_IMPORT_WAITING = 0; // 待機中
    const FORM_IMPORT_RUNNING_VERIFICATION = 1; // 実行中(Step1)
    const FORM_IMPORT_RUNNING_CREATING = 2; // 実行中(Step2)
    const FORM_IMPORT_SUCCESS = 5; // 成功(正常終了)
    const FORM_IMPORT_CANCEL = -1; // 取消(実行の取消)
    const FORM_IMPORT_SUSPENDED_VERIFICATION = -11; // 中断(Step1)
    const FORM_IMPORT_SUSPENDED_CREATING = -12; // 中断(Step2)
    const FORM_IMPORT_DATA_ERROR = -21; // エラー(Step1でのデータエラー)
    const FORM_IMPORT_ERROR_CREATING = -22; // エラー(Step2でエラー)
    const FORM_IMPORT_ABNORMAL_TERMINATION = -99; // 異常終了

    // Request method
    const REQUEST_METHOD_WEB_SCREEN = 0;
    const REQUEST_METHOD_WEB_API = 1;

    //disk_mail
    const DISK_MAIL_TEMP_STATUS = 0; //送信前
    const DISK_MAIL_VALID_STATUS = 1; //有効
    const DISK_MAIL_FILE_DELETE_STATUS = 9; //ファイル削除

    // long term folder
    const LONG_TERM_FOLDER_AUTH_ALL = 0; //全体
    const LONG_TERM_FOLDER_AUTH_POSITION = 1; //役職
    const LONG_TERM_FOLDER_AUTH_DEPARTMENT = 2; //部署
    const LONG_TERM_FOLDER_AUTH_PERSON = 3; //個人

    /*PAC_5-2893 勤務時間の端数調整 S*/
    const WORK_TIME_ADJUST_NONE     = "0"; // 調整なし
    const WORK_TIME_ADJUST_ROUND_UP = "1"; // 切上げ
    const WORK_TIME_ADJUST_TRUNCATE = "2"; // 切捨て
    /*PAC_5-2893 勤務時間の端数調整 S*/

    /*PAC_5-1982 S*/
    const FAVORITE_FLG_VIEW = 1;   //'お気に入り登録: 1:閲覧ユーザー設定'
    const FAVORITE_FLG_DEFAULT = 0;//'お気に入り登録:0:宛先、回覧順
    /*PAC_5-1982 E*/

    const MAX_TITLE_LETTERS = 50;//回覧・文書複数によるzip生成時、フォルダ名の文字数制限用(フォルダ名重複時の連番、拡張子は除く)

    const BBS_STATE_SAVED = 0; //掲示板投稿一時保存

    //sticky_notes
    const STICKY_NOTE_INVALID_STATUS = 0; //無効
    const STICKY_NOTE_VALID_STATUS = 1; //有効

    /* feature/expense_settlement */
    const EPS_M_FORM_FORM_TYPE_UNKNOWN = 0;
    const EPS_M_FORM_FORM_TYPE_ADVANCE = 1;
    const EPS_M_FORM_FORM_TYPE_SETTLEMENT = 2;
    const EPS_M_WTSM_TAX_OPTION_DISPLAY = 1;

    const EPS_T_APP_STATUS_BEFORE_CIRCULAR = 0;
    const EPS_T_APP_STATUS_CIRCULATION = 1;
    const EPS_T_APP_STATUS_APPROVED = 2;
    const EPS_T_APP_STATUS_APPROVED_AFTER_DOWNLOAD = 3;
    const EPS_T_APP_STATUS_REMAND = 4;

    const EPS_T_APP_ITEMS_SUBMIT_METHOD_UNKNOWN = 0;

    public static function encrypt($data, $withoutSlash = false)
    {
        $password = trim(config('app.aes256_pass'));

        // CBC has an IV and thus needs randomness every time a message is encrypted
        $method = 'aes-256-cbc';

        // Must be exact 32 chars (256 bit)
        // You must store this secret random key in a safe place of your system.
        $key = substr(hash('sha256', $password, true), 0, 32);

        // Most secure key
        //$key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

        // Most secure iv
        // Never ever use iv=0 in real life. Better use this iv:
        // $ivlen = openssl_cipher_iv_length($method);
        // $iv = openssl_random_pseudo_bytes($ivlen);

        // av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
        $encrypted = base64_encode(openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv));
        if ($withoutSlash) {
            $encrypted = str_replace('/', '*', $encrypted);
        }
        return $encrypted;
    }

    public static function decrypt($encrypted, $withoutSlash = false)
    {
        $password = trim(config('app.aes256_pass'));

        // CBC has an IV and thus needs randomness every time a message is encrypted
        $method = 'aes-256-cbc';

        // Must be exact 32 chars (256 bit)
        // You must store this secret random key in a safe place of your system.
        $key = substr(hash('sha256', $password, true), 0, 32);

        // Most secure key
        //$key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

        // Most secure iv
        // Never ever use iv=0 in real life. Better use this iv:
        // $ivlen = openssl_cipher_iv_length($method);
        // $iv = openssl_random_pseudo_bytes($ivlen);

        // My secret message 1234
        if ($withoutSlash) {
            $encrypted = str_replace('*', '/', $encrypted);
        }
        return $decrypted = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

    }

    public static function getFileSize($strBase64)
    {
        return (int)(strlen($strBase64) * (3 / 4)) - 1;
    }

    public static function arrToTree($items)
    {
        if (!count($items)) return $items;
        $childs = [];
        $rootItems = [];
        foreach ($items as $item) {
            if ($item->parent_id == null) $item->parent_id = 0;
            $childs[$item->parent_id][$item->id] = $item;

            if (!$item->parent_id) {
                $rootItems[] = $item;
            }
        }

        foreach ($items as $item) {
            if (isset($childs[$item->id]))
                $item->data_child = $childs[$item->id];
        }
        if (count($childs)) {
            $items = $rootItems;
        } else {
            $items = [];
        }
        return $items;
    }

    public static function treeToArr($items, $level = 1, $fieldText = 'name')
    {
        $arr_out = [];
        if (count($items)) {
            foreach ($items as $item) {
                $arr_out[] = ['id' => $item->id, 'level' => $level, 'text' => $item->$fieldText, 'parent_id' => $item->parent_id];
                if (isset($item->data_child) and count($item->data_child)) {
                    $arr_out = array_merge($arr_out, self::treeToArr($item->data_child, $level + 1, $fieldText));
                }
            }
        }
        return $arr_out;
    }

    public static function normalizeOrderDir($orderDir)
    {
        if (strtoupper($orderDir) == 'ASC') {
            return 'ASC';
        } else {
            return 'DESC';
        }
    }

    public static function normalizeLimit($limit, $default)
    {
        $value = intval($limit);
        if ($value <= 0) {
            return $default;
        } else {
            return $value;
        }
    }

    /**
     * 回覧メールにサムネイル画像URL作成
     */
    public static function getPreviewPagePath($edition_flg, $env_flg, $server_flg, $company, $userid)
    {
        $today = new \DateTime();
        $uniquePath = $today->format('Y/m/d/');
        // fix PAC_5-1012 【セキュリティ強化】他社情報漏洩防止プログラム改善: サムネイル画像格納
        if ($company){
            $uniquePath .= $edition_flg.$env_flg.$server_flg."/$company";
        }else{
            $uniquePath .= $edition_flg.$env_flg.$server_flg."/guest";
        }
        if (!File::exists(storage_path("app/preview/$uniquePath"))) {
            File::makeDirectory(storage_path("app/preview/$uniquePath"), 0777, true);
        }

        return storage_path("app/preview/$uniquePath") . "/page-" . "$userid-" . strtoupper(md5(uniqid(session_create_id(), true))) . ".png";
    }

    /**
     * 回覧文書のzip名作成
     */
    public static function getUniqueName($edition_flg, $env_flg, $server_flg, $company, $userid)
    {
        return "$edition_flg-$env_flg-$server_flg-$company-$userid-" . strtoupper(md5(uniqid(session_create_id(), true)));
    }

    /**
     * Unique作成
     * @return string
     */
    public static function getUnique()
    {
        return strtoupper(md5(uniqid(session_create_id(), true)));
    }

    /**
     * フォルダ名変換
     * @param $folder_name
     * @return mixed
     */
    public static function folderNameReplace($folder_name)
    {
        return str_replace(AppUtils::FOLDER_INVALID_CHARS, '_', $folder_name);
    }

    public static function getMailLoginUrlLabel($env_app_url){
        $samlURL = '/'.rtrim(config('app.saml_url_prefix'), "/");
        if (strpos($env_app_url, $samlURL) !== false){
            return 'SAML機能でログイン';
        }else{
            return 'ログイン画面に移動';
        }
    }
    /**
     * ファイル名変換
     * @param $file_name
     * @return mixed
     */
     public static function fileNameReplace($file_name)
     {
         return str_replace(AppUtils::FILE_INVALID_CHARS, '-', $file_name);
     }
    /**
     * 色を分割してRGBの配列に変更する
     */
    public static function changeColorToRgbArray ($color)
    {
        if ($color != '' && mb_ereg('^([0-9]|[A-F]){6}$',$color)) {
            return [hexdec(substr($color,0, 2)), hexdec(substr($color,2, 2)), hexdec(substr($color,4, 2))];
        } else {
            //初期値は赤色で返す
            return ['255', '00', '00'];
        }
    }

    /**
     * Office文書からPDFへの変換を試みる
     * 成功した場合 null を、そうでなければクライアントへ返すためのエラーレスポンスを返す
     *
     * @param string $officeFilePath 入力ファイルパス (Word, Excel)
     * @param string $outputFilePath 出力ファイルパス (PDF)
     */
    public static function tryConvertOfficeToPdf(string $officeFilePath, string $outputFilePath): ?\Illuminate\Http\JsonResponse {
        // アップロード対応しない拡張子は弾く
        $extension = pathinfo($officeFilePath, PATHINFO_EXTENSION);

        $supportedOfficeExtensions = ["doc", "docx", "xls", "xlsx"];
        $isSupportedOfficeExtension = in_array($extension, $supportedOfficeExtensions, true);
        if (!$isSupportedOfficeExtension) {
            Log::debug("file extension not supported. ($extension)");

            return Response::json([
                'status' => false,
                'message' => "対応していない拡張子のファイルです。",
                'data' => null
            ], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        Log::debug("OfficeConverter start: $officeFilePath");

        try {
            OfficeConvertApiUtils::convertInstantly($officeFilePath, $outputFilePath);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        Log::debug("OfficeConverter success: $officeFilePath");
        return null;
    }
}
