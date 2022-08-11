<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Utils;

use App\Models\Company;
use App\Models\CompanyStampGroups;
use App\Models\SpecialSiteReceiveSendAvailableState;
use Config;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Request;
use DB;
use App\Http\Utils\MailUtils;
use Carbon\Carbon;

class AppUtils
{
    //   出退勤管理追加
    const SUBMISSION_STATE = [0 => '未提出', 1 => '提出済'];
    const APPROVAL_STATE = [0 => '未承認', 1 => '承認済', 2 => '修正依頼'];
    const LATE_FLG = [0 => '通常', 1 => '遅刻'];
    const EARLYLEAVE_FLG = [0 => '通常', 1 => '早退'];
    const PAID_VACATION_FLG = [0 => '通常', 1 => '有給', 2 => '有給（半休）'];
    const SP_VACATION_FLG = [0 => '通常', 1 => '特休', 2 => '特休（半休）'];
    const DAY_OFF_FLG = [0 => '通常', 1 => '代休', 2 => '代休（半休）'];
    const COMMUTING_STATUS = [0 => '未出勤'];
    //
    //   HRユーザー登録追加
    const HR_USER_FLG = [0 => '未利用', 1 => '利用中'];
    const HR_NON_USE  = 0; //未利用
    const HR_USE      = 1; //利用中

    //   HR管理ユーザー登録追加
    const HR_ADMIN_FLG = [0 => '一般ユーザ', 1 => '管理者'];
    const HR_USER  = 0; //一般ユーザ
    const HR_ADMIN = 1; //管理者

    // 就労時間管理
    const HR_WORKING_HOURS= [0=>'通常',1=>'シフト',2=>'フレックス'];

    //   HR管理区分
    const HR_ADMIN_UN_MNG = 0; //管理対象外
    const HR_ADMIN_MNG    = 1; //管理対象

    // mst_userのユーザー種類名
    const USER_NORMAL_NAME = "一般利用者"; //回覧利用者
    const USER_RECEIVE_NAME = "受信専用利用者"; //受信専用利用者

    //   経費精算
    const INEQUALITY_SIGN = ['1' => '=', '2' => '≦', '3' => '<', '4' => '≧', '5' => '>'];
    const DETAIL_COND = ['1' => 'を全て含む', '2' => 'のいずれかを含む', '3' => 'に一致する'];
    const TAX_DIV = ['0' => '内税', '1' => '外税'];
    const TAX_DIV_LIST = ['0' => '内税', '1' => '外税', 'DUMMY' => ''];

    const SESSION_ADMIN_LOGIN_TYPE = 'admin.login_type';
    const SESSION_ADMIN_LOGIN_TYPE_SHACHIHATA = 'admin.login_type.shachihata';
    const SESSION_ADMIN_LOGIN_TYPE_COMPANY = 'admin.login_type.company';
    const SESSION_ADMIN_HAS_USER_ACCOUNT = 'admin.has_user_account';

    //管理者ロール
    const ADMIN_DEFAULT_ROLE_FLG = 0; //通常管理者
    const ADMIN_MANAGER_ROLE_FLG = 1; //企業管理者

    //
    const SCOPES_TYPE_USER = '["user"]'; //利用者
    const SCOPES_TYPE_AUDIT = '["audit"]'; //監査用アカウント

    //ユーザー種類
    const AUTH_FLG_RECEIVE = 5; //受信専用利用者
    const AUTH_FLG_OPTION = 4; //グループウェア専用利用者
    const AUTH_FLG_AUDIT = 3; //監査アカウント
    const AUTH_FLG_ADMIN = 2; //管理者
    const AUTH_FLG_USER = 1; //利用者
    const ACCOUNT_TYPE_ADMIN = 'admin'; //管理者
    const ACCOUNT_TYPE_USER = 'user'; //利用者
    const ACCOUNT_TYPE_AUDIT = 'audit';//監査アカウント
    const ACCOUNT_TYPE_OPTION = 'option';//オプション
    const ACCOUNT_TYPE_SIMPLE_USER = 'simple_user';//簡易利用者

    // ユーザー状態
    const STATE_INVALID_NOPASSWORD = 0; //登録（パスワード設定前、無効）
    const STATE_VALID = 1; //有効
    const STATE_WAIT_ACTIVE = 2;
    const STATE_INVALID = 9; //無効
    const STATE_DELETE = -1; //削除

    // mst_userのユーザー種類
    const USER_NORMAL = 0; //回覧利用者
    const USER_OPTION = 1; //オプション利用者
    const USER_RECEIVE = 2; //受信専用利用者

    // stamp_infoの集計状態
    const STAMP_NOT_COLLECT = 0;//未集計
    const STAMP_COLLECTING = 1;//集計中
    const STAMP_COLLECTED  = 2;//集計完了

    //バッチ執行状態
    const BATCH_RUNNING = 0;
    const BATCH_SUCCESS = 1;
    const BATCH_FAIL = 2;

    //印面種類
    const STAMP_FLG_NORMAL = 0; //通常印
    const STAMP_FLG_COMPANY = 1; //共通印
    const STAMP_FLG_DEPARTMENT = 2; //部署名入り日付印
    const STAMP_FLG_CONVENIENT = 3; //便利印

    const STAMP_DIVISION_NAME = 0;//氏名印
    const STAMP_DIVISION_DATE = 1;//日付印

    const SPERATOR_SPLIT = "＞";
    //経費精算
    const EXPENSE_FLG = [0 => '未利用',1 => '利用中'];

    //form issuance user ussage
    const FRM_SRV_USER_FLG = [0 => '未利用',1 => '利用中'];

    // PAC_5-2663 talk

    const MST_CHAT_STATUS_INVALID = 0;
    const MST_CHAT_STATUS_VALID = 1;
    const MST_CHAT_STATUS_DELETED = 9;

    const CHAT_SERVER_USER_CHAT_ROLE_USER = 1;
    const CHAT_SERVER_USER_CHAT_ROLE_ADMIN = 0;

    const CHAT_SERVER_USER_CHAT_ROLE_FLG = [0 => '管理者', 1 => '利用者'];

    const CHAT_SERVER_USER_STATUS_SELECT_SEARCH = [1 => '利用中', 2 => '停止中', 0 => '未利用',
        90 => '未利用(登録失敗)', 91 => '利用中(削除失敗)', 92 => '利用中(停止失敗)', 93 => '停止中(停止解除失敗)'];

    const CHAT_SERVER_USER_STATUS_SELECT_DETAIL_REGISTER = [1 => '利用中', 0 => '未利用'];
    const CHAT_SERVER_USER_STATUS_SELECT_DETAIL_UPDATE = [1 => '利用中', 2 => '停止中', 0 => '未利用'];
    const CHAT_SERVER_USER_STATUS_MAP_DATA = [
        1 => '利用中', 2 => '停止中', 0 => '未利用', 9 => '未利用',
        10 => '登録待ち', 11 => '削除待ち', 12 => '停止待ち',
        13 => '停止解除待ち', 90 => '未利用(登録失敗)',
        91 => '利用中(削除失敗)', 92 => '利用中(停止失敗)',
        93 => '利用中(停止失敗)',
    ];
    const CHAT_SERVER_USER_STATUS_INVALID = 0;
    const CHAT_SERVER_USER_STATUS_VALID = 1;
    const CHAT_SERVER_USER_STATUS_STOPPED = 2;
    const CHAT_SERVER_USER_STATUS_DELETED = 9;
    const CHAT_SERVER_USER_STATUS_WAITING_FOR_REGISTRATION = 10;
    const CHAT_SERVER_USER_STATUS_WAITING_FOR_DELETION = 11;
    const CHAT_SERVER_USER_STATUS_WAITING_FOR_STOP = 12;
    const CHAT_SERVER_USER_STATUS_WAITING_FOR_UNSTOP = 13;
    const CHAT_SERVER_USER_STATUS_REGISTRATION_ERROR = 90;
    const CHAT_SERVER_USER_STATUS_DELETION_ERROR = 91;
    const CHAT_SERVER_USER_STATUS_STOP_ERROR = 92;
    const CHAT_SERVER_USER_STATUS_UNSTOP_ERROR = 93;

    const MST_COMPANY_CHAT_FLG_INVALID = 0;
    const MST_COMPANY_CHAT_FLG_USING = 1;

    const MST_COMPANY_CHAT_TRIAL_FLG_USE = 1;
    const MST_COMPANY_CHAT_TRIAL_FLG_NOT_USE = 0;

    const WITHOUT_EMAIL_F = 0; //メール
    const WITHOUT_EMAIL_T = 1;//メールアドレス無し

    //メール送信状態
    const MAIL_STATE_WAIT = 0; //送信待ち
    const MAIL_STATE_RUNNING = 1; //送信中
    const MAIL_STATE_SUCCESS = 2; //送信成功
    const MAIL_STATE_FAILED = 3; //送信失敗
    const MAIL_STATE_DELAY = 4; //遅延(ファイルメール便)

    //メール送信回数
    const MAIL_SEND_DEFAULT_TIMES = 0;
    const MAX_MAIL_SEND_DEFAULT_TIMES = 4;

    //メール送信対象種別
    const MAIL_TYPE_USER = 0;
    const MAIL_TYPE_ADMIN = 1;
    const MAIL_TYPE_AUDIT = 2;

    //部署状態
    const DEFAULT_DEPARTMENT_STATE = 1;
    //役職状態
    const DEFAULT_POSITION_STATE = 1;
    //契約Edition
    const EDITION_T_STATE = 1; //有効
    const EDITION_F_STATE = 0; //無効
    const EDITION_D_STATE = 9; //削除

    //企業設定
    const EDITION_SAMPLE_T = 1;//サンプルフラグ契約Edition
    const EDITION_SAMPLE_F = 0;//サンプルフラグ企業
    const EDITION_ID_COMPANY = 0;//企業表示

    //画面マッピング用
    const MAIL_STATE_CODE = ['0' => '送信待ち', '1' => '送信中', '2' => '送信成功', '3' => '送信失敗',];
    const STATE_COMPANY = [1 => '有効', 0 => '無効'];
    const STATE_USER = [1 => '有効', 0 => '無効', 9 => '無効'];
    const STATE_AUDIT = [1 => '有効', 0 => '登録', 9 => '無効'];
//    const CONTRACT_EDITION_LABEL = [0 => 'Standard', 1 => 'Business', 2 => 'Business Pro', 3 => 'trial'];
    const CONTRACT_EDITION_LABEL = [0 => 'Standaradパック', 1 => 'Bizパック', 2 => 'Business Pro', 3 => 'Trialパック', 4 => 'グループウェア'];
    const STATE_USER_LABEL = [1 => '有効', 0 => '無効',];
    const EDITION_STATE_FLG = [9 => '削除', 0 => '無効', 1 => '有効'];
    //const CONTRACT_TYPE_LABEL
    const CONTRACT_TYPE_LABEL = [0 => 'standard', 1 => 'business', 2 => 'pro'];
    //特設サイト用
    const SEND_SEND = 1;//申請
    const SEND_CLN = 0;//申請取消
    const RECEIVE_APP = 2;//承認
    const RECEIVE_CLN = 0;//承認解除
    const RECEIVE_UPD = 1;//承認変更
    const RECEIVE_CODE = [1 => '未承認', 2 => '承認済み', -1 => '期限切れ'];
    const TEMPLATE_CODE = [0 => '無効', 1 => '有効', -1 => '期限切れ'];
    const SEND_CODE = [0 => '未申請', 1 => '申請中', 2 => '承認済', -1 => '承認切れ'];

    // csv取込状態
    const STATE_IMPORT_CSV = [0 => '失敗', 1 => '成功', 2 => '待機中'];
    // PAC_5-2133 CSV取込
    const STATE_IMPORT_CSV_TYPE = [1 => '利用者', 2 => '承認ルート', 3 => 'グループウェア専用利用者', 4 => '受信専用利用者'];
    //PAC_5-2334 CSV取込履歴一覧で前の画面種類
    const STATE_IMPORT_CSV_BACK_URL = [1 => 'setting-user', 2 => 'template-route', 3 => 'setting-user/option-user', 4 => 'setting-user/receive-user'];

    //csv取込種類 PAC_5-2133
    const STATE_IMPORT_CSV_USER = 1; // 利用者
    const STATE_IMPORT_CSV_TEMPLATE_ROTE = 2; // 承認ルート
    const STATE_IMPORT_CSV_OPTION_USER = 3; // グループウェア専用利用者
    const STATE_IMPORT_CSV_RECEIVE_USER = 4; // 受信専用利用者
    const STATE_IMPORT_CSV_WITHOUT_EMAIL_USER = 5; // 利用者メールなし
    const STATE_IMPORT_CSV_WITHOUT_EMAIL_RECEIVE_USER = 6; // 受信専用利用者メールなし
    const STATE_IMPORT_CSV_WITHOUT_EMAIL_OPTION_USER = 7; // グループウェア専用利用者メールなし

    const MAX_FORM_USER_COUNT = 5;// 帳票発行機能専用ユーザの最大数

    // 会社状態
    const COMPANY_STATE_INVALID = 0; //無効
    const COMPANY_STATE_VALID = 1; //有効

    // 名刺公開範囲
    const BIZCARD_DISPLAY_TYPE = [0 => '会社', 1 => '部署', 2 => '個人', 3 => 'グループ'];

    // 名刺の公開状態(削除フラグOn/Off)
    const BIZCARD_DEL_FLG = [0 => '公開', 1 => '非公開'];

    // 契約Edition
    const CONTRACT_EDITION_STANDARD = 0;            // Standard
    const CONTRACT_EDITION_BUSINESS = 1;            // Business
    const CONTRACT_EDITION_BUSINESS_PRO = 2;        // Business Pro
    const CONTRACT_EDITION_TRIAL = 3;               // トライアル
    const CONTRACT_EDITION_GW = 4;                  // グループウェア

    // 多要素認証
    const MULTI_FACTOR_AUTH_VALID = 1;    // 有効
    const MULTI_FACTOR_AUTH_INVALID = 0;  // 無効

    const DATE_STAMP = [0 => '任意の日付', 1 => '当日のみ'];
    const STAMP_TYPE_CSV = ['XGL-15', 'XGFD-21'];
    const STAMP_TYPE = ['XGL-15' => '15.5ミリ日付印', 'XGFD-21' => '21ミリ日付印'];
    const STAMP_LAYOUT = [
        'XGL-15' => [
            ['value' => 'E101', 'text' => '上下１行'],
            ['value' => 'E0{0}1', 'text' => '上下１行（子付き）']],
        'XGFD-21' => [
            ['value' => 'E101', 'text' => '上下１行'],
            ['value' => 'E102', 'text' => '下２行'],
            ['value' => 'E201', 'text' => '上２行'],
            ['value' => 'E202', 'text' => '上下２行']],
    ];
    const STAMP_LAYOUT_CSV = ['E101' => 1, 'E0{0}1' => 2, 'E102' => 3, 'E201' => 4, 'E202' => 5];
    const STAMP_WAKU = ['XGL-15' => 'XGL15-E.drw', 'XGFD-21' => 'XG21-E.drw'];
    const STAMP_BIZID = 'dstamp2'; // dstamp2
    const STAMP_GARBLED = 1;
//    const STAMP_SIZE    = 208;
    const STAMP_SIZE = ['XGL-15' => 208, 'XGFD-21' => 281];

    const PX_TO_MICROMET = (25.4 / 96) * 1000;
//    const STAMP_DATE_X      = 26;
//    const STAMP_DATE_Y      = 77;
//    const STAMP_DATE_WIDTH  = 156;
//    const STAMP_DATE_HEIGHT = 51;
    const STAMP_DATE_X = ['XGL-15' => 26, 'XGFD-21' => 28];
    const STAMP_DATE_Y = ['XGL-15' => 77, 'XGFD-21' => 100];
    const STAMP_DATE_WIDTH = ['XGL-15' => 156, 'XGFD-21' => 225];
    const STAMP_DATE_HEIGHT = ['XGL-15' => 51, 'XGFD-21' => 81];

    const STAMP_FONT_LABEL = ['楷書', '古印', '行書'];
    const STAMP_DEFAULT_LABEL = -1;
    const STAMP_FONT_VALUE = ['鯱旗楷書体W5', '鯱旗古印体W5', '鯱旗行書体W5'];
    const STAMP_COLOR = ['02' => '赤色', '04' => '黒色', '03' => '藍色', '06' => '緑色', '05' => '朱色', '01' => '紫色'];

    const ADMIN_STATE_FLG = [-1 => '削除', 0 => '無効', 1 => '有効', 9 => '無効'];
    const DATE_STAMP_FORMAT = ['\'y.m.d' => '\'yy.mm.dd', '\'y/m/d' => '\'yy/mm/dd', 'Y.m.d' => 'yyyy.mm.dd', 'Y/m/d' => 'yyyy/mm/dd', 'gy.m.d' => '和暦yy.mm.dd', 'gy/m/d' => '和暦yy/mm/dd'];

    const DSTAMP_STYLE_DEFAULT = '\'y.m.d';

    const MIN_LENGTH_DEFAULT = 4;
    const VALIDITY_PERIOD_DEFAULT = 0;
    const ENABLE_PASSWORD_DEFAULT = 1;
    // PAC_5-1970 パスワードメールの有効期限を変更する Start
    const PASSWORD_MAIL_VALIDITY_DAYS_DEFAULT = 7;
    // PAC_5-1970 End
    const STORAGE_LOCAL_DEFAULT = 1;
    const STORAGE_BOX_DEFAULT = 0;
    const STORAGE_GOOGLE_DEFAULT = 0;
    const STORAGE_DROPBOX_DEFAULT = 0;
    const STORAGE_ONEDRIVE_DEFAULT = 0;
    const STORAGE_ANY_ADDRESS_DEFAULT = 0;
    const LINK_AUTH_FLG_DEFAULT = 0;
    const ENABLE_EMAIL_THUMBNAIL_DEFAULT = 1;
    const RECEIVER_PERMISSION_DEFAULT = 1;
    const ENVIRONMENTAL_SELECTION_DIALOG_DEFAULT = 0;
    /*PAC_5-2616 S*/
    const STORAGE_ANY_ADDRESS_ROUTES = 2; //送信先の制限:承認ルートのみに制限する
    /*PAC_5-2616 E*/
    /*PAC_5-2821 S*/
    const SKIP_FLG_DEFAULT = 1; //スキップ機能:有効
    const LIMIT_SKIP_FLG_DEFAULT = 1; //LIMIT スキップ機能:有効
    const LIMIT_SKIP_FLG_STATUS = 0; //LIMIT スキップ機能:有効
    /*PAC_5-2821 E*/
    const CIRCULAR_STATUS = [1 => '回覧中', '回覧完了', '回覧完了', '差戻し'];
    const CIRCULAR_LABEL_STATUS = [1 => '回覧中', 2 => '回覧完了', 4 => '差戻し'];
    const CIRCULAR_SAVED_STATUS = ['保存中', 5 => '引戻', 9 => '削除'];
    //経費精算表示用
    const CIRCULAR_DISP_STATUS = [0 => '回覧前',1 => '回覧中', 2 => '回覧完了', 3 => '回覧完了', 4 => '差戻し',5 => '回覧前'];

    const DEPARTMENT_CSV_BEFORE = 0;
    const DEPARTMENT_CSV_CREATED = 1;
    const TIME_STAMP_PERMISSION_DEFAULT = 0;
    const TIME_STAMP_ISSUING_COUNT = 0;

    const PROTECTION_SETTING_CHANGE_FLG_DEFAULT = 0;
    const DESTINATION_CHANGE_FLG_DEFAULT = 0;
    const ENABLE_EMAIL_THUMBNAIL_PROTECTION = 0;
    const ACCESS_CODE_PROTECTION_DEFAULT = 1;
    const TEXT_APPEND_FLG_DEFAULT = 1;
    const REQUIRE_PRINT_DEFAULT = 0;
    const AUTO_SAVE_DEFAULT = 0;
    const AUTO_SAVE_DAYS_DEFAULT = 0;
    const REQUIRE_APPROVE_DEFAULT = 0;
    const DEFAULT_STAMP_HISTORY_FLG_DEFAULT = 0;

    //グループウェア設定GW_APPLICATION_ID_BOARD
    const GW_APPLICATION_ID_BOARD = 1;
    const GW_APPLICATION_NAME_BOARD = '掲示板';
    const GW_APPLICATION_ID_SCHEDULE = 2;
    const GW_APPLICATION_ID_CALDAV = 3;
    const GW_APPLICATION_ID_GOOGLE = 4;
    const GW_APPLICATION_ID_OUTLOOK = 5;
    const GW_APPLICATION_ID_APPLE = 6;
    const GW_APPLICATION_ID_TIME_CARD = 7;
    const GW_APPLICATION_ID_FILE_MAIL = 8;
    const GW_APPLICATION_ID_FAQ_BOARD = 9; //サポート掲示板
    const GW_APPLICATION_ID_SHARED_SCHEDULE = 10; //共有スケジューラ
    const GW_APPLICATION_ID_FILE_MAIL_EXTEND = 11;
    const GW_APPLICATION_ID_TO_DO_LIST = 12; //ToDoリスト
    const GW_APPLICATION_ID_ADDRESS_LIST = 13;

    const APPLICATION_IDS_GW = [
        AppUtils::GW_APPLICATION_ID_SCHEDULE,
        AppUtils::GW_APPLICATION_ID_CALDAV,
        AppUtils::GW_APPLICATION_ID_GOOGLE,
        AppUtils::GW_APPLICATION_ID_OUTLOOK,
        AppUtils::GW_APPLICATION_ID_APPLE,
        AppUtils::GW_APPLICATION_ID_SHARED_SCHEDULE,
    ];

    const APPLICATION_IDS_PAC = [
        AppUtils::GW_APPLICATION_ID_BOARD,
        AppUtils::GW_APPLICATION_ID_TIME_CARD,
        AppUtils::GW_APPLICATION_ID_FILE_MAIL,
        AppUtils::GW_APPLICATION_ID_FAQ_BOARD,
        AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND,
        AppUtils::GW_APPLICATION_ID_TO_DO_LIST,
        AppUtils::GW_APPLICATION_ID_ADDRESS_LIST,
    ];

    const MAX_BBS_COUNT = 10000;
    const MAX_SCHEDULE_COUNT = 720000;
    const GW_APPLICATION_SCHEDULE_LIMIT_FLG = 1;
    const GW_APPLICATION_SCHEDULE_BUY_COUNT = 0;



    const FIND_CURRENT_COMPANY_STAMP_USE_USERS_LIMIT = 10;
    //sticky_notes
    const STICKY_NOTE_INVALID_STATUS = 0; //無効
    const STICKY_NOTE_VALID_STATUS = 1; //有効

    //部署名ソート
    const STR_KANJI = array("一", "二", "三", "四", "五", "六", "七", "八", "九");
    const STR_SUUJI = array("1", "2", "3", "4", "5", "6", "7", "8", "9");

    const LOGIN_TYPE_SSO = 1;
    const LOGIN_TYPE_NORMAL = 0;
    const FLG_ENABLE = 1;
    const FLG_DISABLE = 0;
    const BATCH_HISTORY_EMAIL = [0 => '実行中', 1 => '実行成功', 2 => '実行失敗'];

    // PAC_5-2018 START
    const FIND_COMPANY_STAMP_USE_USERS_LIMIT = 10;
    // PAC_5-2018 END

    const MAX_TITLE_LETTERS = 50;//回覧・文書複数によるzip生成時、フォルダ名の文字数制限用(フォルダ名重複時の連番、拡張子は除く)

    const USAGE_KIKAN = [0 => '過去30日間', 1 => '過去60日間', 2 => '過去90日間',3=>'過去半年',4=>'過去1年'];
    const SHUUKEI_KOUMOKU=[
        0=>[
            'name'=>'使用状况',
            'child'=>[
                0 => [
                    'name'=>'契約数',
                    'checked'=>true
                ],
                1 =>[
                    'name'=>'印面登録数',
                    'checked'=>true
                ],
                2=>[
                    'name'=>'有効ユーザ数',
                    'checked'=>true
                ],
                3=>[
                    'name'=>'アクティビティユーザ数',
                    'checked'=>true
                ]
            ]
        ],
        1=>[
            'name'=>'稼働率',
            'child'=>[
                0=>[
                    'name'=>'回覧完了率',
                    'checked'=>true
                ],
                1=>[
                    'name'=>'利用率（アクティビティユーザ数／有効ユーザ数）',
                    'checked'=>true
                ]
            ]
        ],
        2=>[
            'name'=>'申請状況',
            'child'=>[
                0 => [
                    'name'=>'申請数',
                    'checked'=>true
                ],
                1 =>[
                    'name'=>'回覧完了数',
                    'checked'=>true
                ]
            ]
        ],
        3=>[
            'name'=>'平均時間（回覧開始～終了）（h）'
        ],
        4=>[
            'name'=>'社外経由数（送信）'
        ],
        5=>[
            'name'=>'社外経由数（受信）'
        ],
        6=>[
            'name'=>'超過登録印面数'
        ]
    ];
    const USAGE_ITEMS = [];
    const LONGTERM_INDEX_DATA_TYPE = [0 => '数字型', 1 => '文字型', 2 => '日付型'];
    const FRM_INDEX_DATA_TYPE = [0 => '数字型', 1 => '文字型', 2 => '日付型'];
    const CIRCULAR_COMPLETED_TIME = [
        0 => '当月',
        1 => '1ヶ月前',
        2 => '2ヶ月前',
        3 => '3ヶ月前',
        4 => '4ヶ月前',
        5 => '5ヶ月前',
        6 => '6ヶ月前',
        7 => '7ヶ月前',
        8 => '8ヶ月前',
        9 => '9ヶ月前',
        10 => '10ヶ月前',
        11 => '11ヶ月前',
        12 => '12ヶ月前',
    ];

    //日付色の設定
    const COMMON_STAMP_DATE_COLOR = ['FF0000' => '赤', '0000FF' => '青', '008000' => '緑', '8000FF' => '紫', 'ED6C00' => '朱', 'other' => 'その他'];
    // 操作ログ容量
    const OPERATION_HISTORY_AUTH_FLG_ADMIN = 0; // 管理者
    const OPERATION_HISTORY_AUTH_FLG_USER = 1; // 利用者
    const ADMIN_DEFAULT_MENU_STATE_FLG = 0; //未定義
    const ADMIN_SIMPLE_MENU_STATE_FLG = 1; // 簡易表示
    const ADMIN_USUALLY_MENU_STATE_FLG = 2; //通常表示

    // long term folder
    const LONG_TERM_FOLDER_AUTH_ALL = 0; //全体
    const LONG_TERM_FOLDER_AUTH_POSITION = 1; //役職
    const LONG_TERM_FOLDER_AUTH_DEPARTMENT = 2; //部署
    const LONG_TERM_FOLDER_AUTH_PERSON = 3; //個人

    const CIRCULAR_USER_COUNT = 1;//ユーザー数（アクティビティ＋アクティビティ率）
    const CIRCULAR_DOCUMENT_DATA_SIZE = 2;//ドキュメントデータ容量
    const CIRCULAR_ATTACHMENT_DATA_SIZE = 3;//添付ファイルデータ容量
    const CIRCULAR_OUTSIDE_SEND_COUNT = 4;//社外経由数（送信）
    const CIRCULAR_OUTSIDE_RECEIVE_COUNT = 5;//社外経由数（受信）

    public static function searchStamp($name, $stamp_division = 0, $font = 0)
    {
        // （土）吉対応
        // 吉田、吉川、吉村（mst_stamp_special）
        $stamp_synonym = DB::table('mst_stamp_special')
            ->where('face', $name)
            ->where('stamp_division', $stamp_division)
            ->where('font', $font)
            ->first();

        if ($stamp_synonym) {
            // 吉田、吉川、吉村の場合、mst_stamp_special
            return $stamp_synonym;
        } else {
            // 吉田、吉川、吉村以外の場合、stamp-api
            $client = new Client(['base_uri' => config('app.stamp_api_base_url'), 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout')]);

            $result = $client->get("/ananke/shaadvservice/api/v1/rqst/$stamp_division/$font/" . rawurlencode("$name") . "/0/");

            if ($result->getStatusCode() == 200) {
                $stamp = json_decode((string)$result->getBody());
                if ($stamp->contents) {
                    $stamp->stamp_division = $stamp_division;
                    $stamp->font = $font;
                    return $stamp;
                } else {
                    Log::warning("Search stamp response body: " . $result->getBody());
                    return $stamp->code;
                }
            } else {
                Log::warning("Search stamp response body: " . $result->getBody());
            }
            return -1;
        }
    }

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

    public static function getDefaultConstraint()
    {
        return [
            'max_requests' => Config::get('constraints_max_requests', 0),
            'max_document_size' => Config::get('constraints_max_document_size', 8),
            //'user_storage_size' => Config::get('constraints_user_storage_size', 1024),
            'use_storage_percent' => Config::get('constraints_use_storage_percent', 95),
            'max_keep_days' => Config::get('constraints_max_keep_days', 365),
            'delete_informed_days_ago' => Config::get('constraints_delete_informed_days_ago', 365),
        ];
    }

    public static function jpn_zenkaku_only($str)
    {

        $encoding = "UTF-8";

        // Get length of string
        $len = mb_strlen($str, $encoding);

        // Check each character
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($str, $i, 1, $encoding);

            // Check for non-printable characters
            if (ctype_print($char)) {
                return false;
            }

            // Convert to WINDOWS-31J to include kana characters
            $char = mb_convert_encoding($char, 'MS932', $encoding);

            // Check if string lengths match
            if (strlen($char) === mb_strlen($char, 'MS932')) {
                return false;
            }
        }
        return true;
    }

    public static function forceSecureUrl($url)
    {
        if (!config('app.debug')) {
            return str_replace('http://', 'https://', $url);
        } else {
            return $url;
        }
    }

    public static function utf8_filter($value)
    {
        return preg_replace('/[[:^print:]]/', '', $value);
    }

    public static function convertState($state = 0)
    {
        $arrState = [-1 => 9, 0 => 1, 1 => 0, 9 => 1];
        if (isset($arrState[$state])) return $arrState[$state];
        else return false;
    }

    /**
     * 会社の情報取得
     * @param int $gw_option 0: スケジューラー取得しない | 1:スケジューラー取得する
     * @return mixed
     */
    public static function getLoggedCompany(int $gw_option = 0)
    {
        $user = Request::user();
        $company = Company::find($user->mst_company_id);
        $applicationSetting = ApplicationAuthUtils::getCompanySetting($user->mst_company_id);
        $company->attendance_flg = $applicationSetting['attendance_flg'];
        $company->board_flg = $applicationSetting['board_flg'];
        if($gw_option && config('app.gw_use') == 1 && config('app.gw_domain')){
            $gw_settings = GwAppApiUtils::getCompanySetting($user->mst_company_id);
            $company->scheduler_flg = $gw_settings['scheduler_flg'];
        }else{
            $company->scheduler_flg = 0;
        }
        $special = SpecialSiteReceiveSendAvailableState::where('company_id', $user->mst_company_id)->first();
        $company->special_receive_flg = $special && $special->is_special_site_receive_available ? 1 : 0;
        $company->special_send_flg = $special && $special->is_special_site_send_available ? 1 : 0;
        return $company;
    }

    //PAC_5-1902
    public static function getSpecial()
    {
        $specialState = SpecialSiteReceiveSendAvailableState::where('company_id', '=', Request::user()->mst_company_id)->get();
        if(count($specialState)){
            return $specialState[0];
        }else{
            return false;
        }
    }

    public static function getStampGroup()
    {
        $listGroup = CompanyStampGroups::where('mst_company_id', '=', Request::user()->mst_company_id)->get();

        if (count($listGroup)) {
            return true;
        } else {
            return false;
        }
    }

    public static function generateStampSerial($stampFlg, $stampId)
    {
        $uuid = sprintf("%012s", $stampId);

        if (config('app.pac_contract_app')) {
            $mapCharacter = [['B', 'D', 'F', 'H', 'J', 'L'], ['@', '$'], ['q', 'e', 't', 'u'], ['1', '3'], ['N', 'P', 'R', 'T'], ['4', '6'], '9', ['V', 'Y'], ['a', 'd', 'g', 'h'], ['!', '^']];
        } else {
            $mapCharacter = [['C', 'E', 'G', 'I', 'K', 'Z'], ['*', '='], ['r', 'y', 'i', 'o'], ['2', '4'], ['M', 'O', 'Q', 'S'], ['5', '7'], '8', ['U', 'W'], ['s', 'f', 'j', 'k'], ['&', '+']];
        }

        $chars = array_reverse(str_split($uuid));
        $uuid = '';
        foreach ($chars as $char) {
            $mapChar = $mapCharacter[intval($char)];
            if (is_array($mapChar)) {
                $rand_keys = array_rand($mapChar);
                $mapChar = $mapChar[$rand_keys];
            }
            $uuid .= $mapChar;
        }

        $uuid = $stampFlg . config('app.pac_contract_app') . config('app.pac_app_env') . config('app.pac_contract_server') . $uuid;
        return $uuid;
    }

    public static function getCompanyContractStatus($contract_edition, $trial_flg, $state)
    {
        if ($contract_edition == self::CONTRACT_EDITION_TRIAL) {//トライアル
            if ($state != 1) {
                return '無効(トライアル終了)';
            }
            if ($trial_flg == 1) {
                return '有効(トライアル)';
            }
            return 'トライアル終了';
        } else {
            if ($state == 1) {
                if ($contract_edition == self::CONTRACT_EDITION_GW){//グループウェア
                    return '有効(グループウェア)';
                }else{
                    return '有効(本契約)';
                }
            } else {
                return '無効';
            }
        }
    }

    /**
     * 回覧文書のzip名作成
     */
    public static function getUniqueName($edition_flg, $env_flg, $server_flg, $company, $userid)
    {
        return "$edition_flg-$env_flg-$server_flg-$company-$userid-" . strtoupper(md5(uniqid(session_create_id(), true)));
    }

    /**
     * PDF処理API　token取得
     * @return Client
     */
    public static function getStampApiClient()
    {
        $api_host = rtrim(config('app.stamp_api_host'), "/");
        $api_base = ltrim(config('app.stamp_api_base'), "/");
        $client = new Client(['base_uri' => $api_host . "/" . $api_base, 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout'), 'http_errors' => false, 'verify' => false,
            'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest']
        ]);
        return $client;
    }

    /*
     *
     */
    public static function getMailDictionary()
    {
        $list = [];
        $origin_list = MailUtils::MAIL_DICTIONARY;
        foreach ($origin_list as $item) {
            $list[$item['CODE']] = $item['FUNCTION'];
        }
        return $list;
    }

    /*
     *
     */
    public static function getMailDictionaryByCode($code)
    {
        $list = AppUtils::getMailDictionary();
        $function = '-';
        if ($list && $list[$code]) {
            $function = $list[$code];
        }
        return $function;
    }

    /**
     * date to ms timestemp
     */
    public static function calcDiffInMilliseconds($st, $ed)
    {
        $st_carbon = Carbon::parse($st);
        $ed_carbon = Carbon::parse($ed);
        $diff = $st_carbon->diffInMilliseconds($ed_carbon, true);
        return $diff;
    }

    /**
     * get env
     */
    public static function getEnvStr()
    {
        if (env('APP_ENV') == 'production') {
            $env = '本番環境';
        } else {
            $env = env('APP_ENV') . "環境";
        }

        if (config('app.pac_app_env') == 0) {
            $env .= 'app' . (config('app.pac_contract_server') + 1);
        } else if (config('app.pac_app_env') == 1) {
            $env .= 'K5';
        }
        return $env;
    }

    /**
     * 色を分割してRGBの配列に変更する
     */
    public static function changeDateColorLists ($itemStamp)
    {
        if ($itemStamp['date_color'] != '') {
            if (isset(self::COMMON_STAMP_DATE_COLOR[$itemStamp['date_color']])) {
                return $itemStamp['date_color'];
            } else {
                return 'other';
            }
        } else {
            return '';
        }
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

    public static function getFileSize($strBase64)
    {
        return (int)(strlen($strBase64) * (3 / 4)) - 1;
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

    public static function normalizeOrderDir($orderDir)
    {
        if (strtoupper($orderDir) == 'ASC') {
            return 'ASC';
        } else {
            return 'DESC';
        }
    }
    /**
     * Unique作成
     * @return string
     */
    public static function getUnique()
    {
        return strtoupper(md5(uniqid(session_create_id(), true)));
    }

    public static function stringHasJapaneseCharacter($string)
    {
        $regex = '/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u';
        return preg_match($regex, $string);
    }
}
