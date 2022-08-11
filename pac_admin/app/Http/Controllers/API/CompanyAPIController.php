<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppSettingConstraint;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\MailUtils;
use App\Models\Authority;
use App\Models\Company;
use App\Models\Constraint;
use App\Models\Permission;
use App\Models\Stamp;
use App\Models\UsageSituationDetail;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Response;
use Session;
use App\Http\Utils\StampUtils;
use Illuminate\Support\Facades\Validator;

class CompanyAPIController extends Controller
{
    /**
     * homepage連携
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDomain(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("createDomain：" . $request);
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }
            // 企業名
            if (!$request->has('nickname') || empty($request->nickname)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{nickname}', 0, '');
            }
            // 企業名(カナ)
            if (!$request->has('kananame') || empty($request->kananame)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{kananame}', 0, '');
            }
            // ドメイン
            if (!$request->has('domains') || empty($request->domains)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{domains}', 0, '');
            }
            // 印鑑数
            if (!$request->has('maxstamps') || empty($request->maxstamps)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{maxstamps}', 0, '');
            }
            // メールアドレス
            if (!$request->has('email') || empty($request->email)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{email}', 0, '');
            }
            // 責任者氏名(姓)
            if (!$request->has('familyname') || empty($request->familyname)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{familyname}', 0, '');
            }
            // 責任者氏名(名)
            if (!$request->has('givenname') || empty($request->givenname)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{givenname}', 0, '');
            }
            // 責任者氏名フリガナ(姓)
            if (!$request->has('familynameKana') || empty($request->familynameKana)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{familynameKana}', 0, '');
            }
            // 責任者氏名フリガナ(名)
            if (!$request->has('givennameKana') || empty($request->givennameKana)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{givennameKana}', 0, '');
            }
            // 企業名maxlength
            $nickname = $request->nickname;
            if (mb_strlen($nickname) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{nickname}', 0, '');
            }
            // 企業名(カナ)maxlength
            $kananame = $request->kananame;
            if (mb_strlen($kananame) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{kananame}', 0, '');
            }
            // ドメイン形式のチェック
            $domains = $request->domains;
            // 印鑑数int maxlength　minlength
            $maxstamps = $request->maxstamps;
            if (!preg_match("/^\+?[1-9][0-9]*$/", $maxstamps) || strlen($maxstamps) < 1 || strlen($maxstamps) > 9) {
                return $this->ApiResponse('1~9桁整数を入力してください。{maxstamps}', 0, '');
            }
            // メールアドレス
            $email = $request->email;
            if (strlen($email) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->ApiResponse('メールアドレス形式不正。{email}', 0, '');
            }
            // 責任者氏名(姓)
            $familyname = $request->familyname;
            if (mb_strlen($familyname) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{familyname}', 0, '');
            }
            // 責任者氏名(名)
            $givenname = $request->givenname;
            if (mb_strlen($givenname) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{givenname}', 0, '');
            }

            if ($request->has('agcode') && !empty($request->agcode)) {
                $agcodetext = '代理店コード : ';
                $agcodetext .= $request->agcode;
                $agcodetext .= ' \r\n';
            } else {
                $agcodetext = '';
            }
            if ($request->has('agency_coupon_code') && !empty($request->agency_coupon_code)) {
                $agcouponcodetext = '代理店クーポンコード : ';
                $agcouponcodetext .= $request->agency_coupon_code;
                $agcouponcodetext .= ' \r\n';
            } else {
                $agcouponcodetext = '';
            }

            // メール送信用データ作成
            $data = [];
            $data['nickname'] = $request->nickname;
            $data['kananame'] = $request->kananame;
            $data['telno'] = $request->telno;
            $data['group'] = $request->group;
            $data['post'] = $request->post;
            $data['city'] = $request->city;
            $data['familyname'] = $request->familyname;
            $data['givenname'] = $request->givenname;
            $data['familynameKana'] = $request->familynameKana;
            $data['givennameKana'] = $request->givennameKana;
            $data['email'] = $request->email;
            $data['task'] = $request->task;
            $data['maxstamps'] = $request->maxstamps;
            $data['domains'] = $request->domains;
            $data['mailMagazine'] = $request->mailMagazine;
            $data['question'] = $request->question;
            $data['nowdete'] = Carbon::now()->format('Y/m/d H:i:s');
            $data['industry'] = $request->industry;
            $data['position'] = $request->position;
            $data['telno2'] = $request->telno2;
            $data['is_inuse'] = $request->is_inuse;
            $data['pretiming'] = $request->pretiming;
            $data['agcode'] = $request->agcode;
            $data['agcodetext'] = $agcodetext;
            $data['agcouponcode'] = $request->agency_coupon_code;
            $data['agcouponcodetext'] = $agcouponcodetext;
            //統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                return $this->ApiResponse('Cannot connect to ID App', 0, '');
            }

            //簡易利用者登録状態
            $simple_user_flag = $request->simple_user_flag;
            $data['failed_users'] = '';
            $data['simple_user_flag'] = 0;
            $parameters = $data;
            $success_users = [];
            $failed_users = [];
            $users = [];
            $parameters['email_format'] = true;
            if ($simple_user_flag == 'する') {
                $data['simple_user_flag'] = 1;
                $parameters['simple_user_flag'] = 1;
                //メールアドレス
                $simple_user_emails = $request->simple_user_email;
                //お名前姓
                $simple_user_name1s = $request->simple_user_name1;
                //お名前名
                $simple_user_name2s = $request->simple_user_name2;
                //印面文字
                $inscriptions = $request->inscription;
                //印面設定
                $inscription_settings = $request->inscription_setting;

                foreach ($simple_user_emails as $key => $simple_user_email) {
                    if (isset($simple_user_email) && strrpos($simple_user_email, '@')) {
                        $users[$key]['simple_user_email'] = $simple_user_email;
                        $users[$key]['simple_user_status_repeat'] = '';
                        $users[$key]['simple_user_status_domain'] = '';
                        $users[$key]['simple_user_status'] = '';
                    }
                }

                foreach ($simple_user_name1s as $key => $simple_user_name1) {
                    if (isset($simple_user_name1)) {
                        $users[$key]['simple_user_name1'] = $simple_user_name1;
                    }
                }

                foreach ($simple_user_name2s as $key => $simple_user_name2) {
                    if (isset($simple_user_name2)) {
                        $users[$key]['simple_user_name2'] = $simple_user_name2;
                    }
                }

                foreach ($inscriptions as $key => $inscription) {
                    if (isset($inscription)) {
                        $users[$key]['inscription'] = $inscription;
                    }
                }

                foreach ($inscription_settings as $key => $inscription_setting) {
                    if (isset($inscription_setting)) {
                        $users[$key]['inscription_setting'] = substr($inscription_setting, 0, strpos($inscription_setting, ':'));
                    }
                }

                foreach ($users as $key => $user) {
                    $results = $client->get('users', [
                        RequestOptions::JSON => [
                            'email' => $user['simple_user_email'],
                            'contract_app' => 1
                        ]
                    ]);
                    unset($simple_user_emails[$key]);
                    $datas = json_decode((string)$results->getBody())->data->data;

                    if ($user['simple_user_email'] == $email || in_array($user['simple_user_email'], $simple_user_emails)) {
                        $user['simple_user_status_repeat'] = 'メールアドレス重複';
                        $failed_users[$key] = $user;
                        $users[$key] = $user;
                        $parameters['email_format'] = false;
                    } else {
                        foreach ($datas as $val) {
                            //メールアドレスが既に存在, 失敗したユーザの取得
                            if (($val->status != 9 && $val->email == $user['simple_user_email'])) {
                                $user['simple_user_status_repeat'] = 'メールアドレス重複';
                                $failed_users[$key] = $user;
                                $users[$key] = $user;
                                $parameters['email_format'] = false;
                            }
                        }
                    }
                }

                //利用ドメインチェック
                $domain_array = preg_split('/\r\n|\r|\n/', $domains);
                foreach ($users as $key => $user) {
                    $simple_domain = explode('@', $user['simple_user_email']);
                    if (!in_array('@' . $simple_domain[1], $domain_array)) {
                        $user['simple_user_status_domain'] = 'ドメインが正しくありません';
                        $failed_users[$key] = $user;
                        $users[$key] = $user;
                        $parameters['email_format'] = false;
                    }
                }

                //成功したユーザを取得します。
                foreach ($users as $key => $user) {
                    if (!in_array($key, array_keys($failed_users))) {
                        if ($request->has('isValidEmail') && $request->isValidEmail == 1) {
                            $user['simple_user_status'] = '登録可';
                        } else {
                            $user['simple_user_status'] = '登録成功';
                        }
                        $success_users[$key] = $user;
                        $users[$key] = $user;
                    }
                }
                //送信の内容
                $success_users_string = '簡易利用者情報\r\n';
                $failed_users_string = '';
                foreach ($failed_users as $failed_user) {
                    $failed_users_string = $failed_users_string . 'お名前 : ' . $failed_user['simple_user_name1'] . ' ' . $failed_user['simple_user_name2'] . '\r\n';
                    $failed_users_string = $failed_users_string . 'メールアドレス : ' . $failed_user['simple_user_email'] . '\r\n';
                    $failed_users_string = $failed_users_string . '状態 : ' . $failed_user['simple_user_status_repeat'] . ($failed_user['simple_user_status_repeat'] ? ';' : '') .
                        $failed_user['simple_user_status_domain'] . ($failed_user['simple_user_status_domain'] ? ';' : '') . '\r\n';
                }
                $failed_users_string = $failed_users_string . '\r\n';
                foreach ($success_users as $success_user) {
                    $success_users_string = $success_users_string . 'お名前 : ' . $success_user['simple_user_name1'] . ' ' . $success_user['simple_user_name2'] . '\r\n';
                    $success_users_string = $success_users_string . 'メールアドレス : ' . $success_user['simple_user_email'] . '\r\n';
                    $success_users_string = $success_users_string . '状態 : ' . $success_user['simple_user_status'] . '\r\n';
                }
                $success_users_string = $success_users_string . '\r\n';
                $data['failed_users'] = $failed_users_string;
                $data['success_users'] = $success_users_string;
                $parameters['failed_users'] = $failed_users;
                $parameters['success_users'] = $success_users;
            }

            $results = $client->get('users', [
                RequestOptions::JSON => [
                    'email' => $request->email,
                    'contract_app' => 1
                ]
            ]);
            $datas = json_decode((string)$results->getBody())->data->data;
            $result = [];
            foreach ($datas as $key => $val) {
                if ($val->status != 9 && $val->email == $request->email) $result[] = $val;
            }
            if ($result || ($request->has('isValidEmail') && $request->isValidEmail == 1 && $parameters['failed_users'])) {
                //トライアル:重複登録エラー
                MailUtils::InsertMailSendResume(
                // 送信先メールアドレス
                    $email,
                    // メールテンプレート
                    MailUtils::MAIL_DICTIONARY['TRIAL_DUPLICATE_FAILED']['CODE'],
                    // パラメータ
                    json_encode($parameters, JSON_UNESCAPED_UNICODE),
                    // タイプ
                    AppUtils::MAIL_TYPE_ADMIN,
                    // 件名
                    config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.trialDuplicateErrorMail.subject'),
                    // メールボディ
                    trans('mail.trialDuplicateErrorMail.body', $data)
                );
                if (!$parameters['email_format']) {
                    $message_repeated = '';
                    $message_format = '';
                    foreach ($failed_users as $key => $failed_user) {
                        if ($failed_user['simple_user_status_repeat']) {
                            $message_repeated = $message_repeated . $key . ',';
                        }
                        if ($failed_user['simple_user_status_domain']) {
                            $message_format = $message_format . $key . ',';
                        }
                    }
                    if ($message_repeated) {
                        $message_repeated = rtrim($message_repeated, ',');
                    }
                    if ($message_format) {
                        $message_format = rtrim($message_format, ',');
                    }
                    $message = $message_repeated . ($message_repeated != '' ? ':メールアドレス重複; ' : '') .
                        ($message_format ?? '') . ($message_format != '' ? ':ドメインが正しくありません;' : '') . ($result ? 'メールアドレスが既に存在しています。' : '');
                    Log::channel('trial-daily')->info('簡易利用者登録: ' . $message);
                    return $this->ApiResponse($message, 0, '');
                } else {
                    return $this->ApiResponse('メールアドレスが既に存在しています。', 0, '');
                }
            }

            if ($request->has('isValidEmail') && $request->isValidEmail == 1) {
                return $this->ApiResponse('メールアドレスは一意です', 1, '');
            }

            // 統合IDに該当企業利用責任者登録処理 mst_user 追加
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                Log::channel('trial-daily')->error("統合ID側アクセスできない");
                return $this->ApiResponse('統合ID側アクセスできない', 0, '');
            }

            DB::beginTransaction();
            // 企業新規作成 mst_company追加 (NULLのものには値を設定しない？)
            if(config('app.gw_use') == 1 && config('app.gw_domain')){
                $gw_flg = 1;
            }else{
                $gw_flg = 0;
            }

            //企業の契約Edition項目作成
            $mst_company_default = DB::table('mst_contract_edition as me')
                ->select('info.department_stamp_flg','info.template_route_flg','info.rotate_angle_flg','info.phone_app_flg','info.attachment_flg','info.portal_flg','info.contract_edition'
                    ,'info.convenient_flg','info.usage_flg','info.convenient_upper_limit','info.default_stamp_flg','info.confidential_flg','info.esigned_flg','info.ip_restriction_flg','info.signature_flg','info.permit_unregistered_ip_flg'
                    ,'info.stamp_flg','info.repage_preview_flg','info.timestamps_count','info.box_enabled','info.time_stamp_issuing_count','info.mfa_flg','info.long_term_storage_flg','info.template_flg','info.long_term_storage_option_flg','info.template_search_flg'
                    ,'info.long_term_folder_flg','info.max_usable_capacity','info.template_csv_flg','info.hr_flg','info.template_edit_flg','info.multiple_department_position_flg','info.option_user_flg','info.user_plan_flg','info.receive_user_flg','info.template_approval_route_flg'
                    ,'info.skip_flg','info.form_user_flg','info.frm_srv_flg','info.bizcard_flg','info.local_stamp_flg','info.with_box_flg','info.dispatch_flg','info.attendance_system_flg','info.circular_list_csv','info.is_together_send','info.enable_any_address_flg'
                    ,'info.sanitizing_flg','info.enable_email','info.email_format','info.received_only_flg','info.pdf_annotation_flg','info.addressbook_only_flag','info.view_notification_email_flg','info.updated_notification_email_flg','info.enable_email_thumbnail'
                    ,'info.is_show_current_company_stamp')
                ->join('mst_company as info', 'info.edition_id', 'me.id')
                ->where('me.state_flg', AppUtils::EDITION_T_STATE)
                ->where('info.contract_edition_sample_flg', AppUtils::EDITION_SAMPLE_T)
                ->where('info.contract_edition', AppUtils::CONTRACT_EDITION_TRIAL)
                ->first();
            $mst_company_arr = (array)$mst_company_default;
            $mst_company_arr['certificate_flg'] = 0;
            $mst_company_arr['company_name'] = $nickname;
            $mst_company_arr['company_name_kana'] = $kananame;
            $mst_company_arr['contract_edition'] = AppUtils::CONTRACT_EDITION_TRIAL;
            $mst_company_arr['domain'] = $domains;
            $mst_company_arr['dstamp_style'] = '\'y.m.d';
            $mst_company_arr['guest_company_flg'] = 0;
            $mst_company_arr['guest_document_application'] = 0;
            $mst_company_arr['gw_flg'] = $gw_flg;
            $mst_company_arr['login_type'] = 0;
            $mst_company_arr['state'] = 1;
            $mst_company_arr['system_name'] = 'Shachihata Cloud';
            $mst_company_arr['trial_flg'] = 1;
            $mst_company_arr['upper_limit'] = $maxstamps;
            $mst_company_arr['use_api_flg'] = 1;
            $mst_company_arr['edition_id'] = 0;
            $mst_company_arr['contract_edition_sample_flg'] = 0;
            $mst_company_arr['create_at'] = Carbon::now();
            $mst_company_arr['create_user'] = 'Shachihata';

            $company_id = DB::table('mst_company')->insertGetId($mst_company_arr);

            //掲示板のフラグがデフォルトON
            DB::table('mst_application_companies')->insert([
                "mst_company_id" => $company_id,
                "mst_application_id" => AppUtils::GW_APPLICATION_ID_BOARD,
                'created_at' => Carbon::now()
            ]);

            // 企業の制約テーブル情報追加
            $appSetting = AppSettingConstraint::getAppSettingConstraint();

            DB::table('mst_constraints')->insert([
                'mst_company_id' => $company_id,
                'max_requests' => $appSetting->getSettingRequestsMax(),
                'max_document_size' => $appSetting->getSettingFileSize(),
                'use_storage_percent' => $appSetting->getSettingStoragePercent(),
                'max_keep_days' => $appSetting->getSettingRetentionDay(),
                'delete_informed_days_ago' => $appSetting->getSettingDeleteDay(),
                'long_term_storage_percent' => $appSetting->getSettingLongTermStoragePercent(),
                'max_ip_address_count' => $appSetting->getSettingMaxIpAddressCount(),
                'max_viwer_count' => $appSetting->getSettingMaxViwerCount(),
                'max_attachment_size' => $appSetting->getSettingMaxAttachmentSize(),
                'max_total_attachment_size' => $appSetting->getSettingMaxTotalAttachmentSize(),
                'max_attachment_count' => $appSetting->getSettingMaxAttachmentCount(),
                'max_frm_document' => $appSetting->getSettingMaxFrmDocument(),
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            //企業に関する設定テーブルの情報追加
            //password_policy,mst_limit,mst_time_stamp,mst_protection 追加
            DB::table('password_policy')->insert([
                "mst_company_id" => $company_id,
                "min_length" => 4,
                "validity_period" => 0,
                "enable_password" => 1,
                "password_mail_validity_days" => 7,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            //特設サイト
            DB::table('special_site_receive_send_available_state')->insert([
                'company_id' => $company_id,
                'is_special_site_receive_available' => 0,
                'is_special_site_send_available' => 0,
                'created_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            DB::table('mst_limit')->insert([
                "mst_company_id" => $company_id,
                'storage_local' => 1,
                'storage_box' => 0,
                'storage_google' => 0,
                'storage_dropbox' => 0,
                'storage_onedrive' => 0,
                'enable_any_address' => 0,
                'link_auth_flg' => 0,
                'enable_email_thumbnail' => 1,
                'receiver_permission' => 1,
                'environmental_selection_dialog' => 0,
                "time_stamp_permission" => 0,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            DB::table('mst_protection')->insert([
                'mst_company_id' => $company_id,
                'protection_setting_change_flg' => 0,
                'destination_change_flg' => 0,
                'enable_email_thumbnail' => 0,
                'access_code_protection' => 1,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            // 企業権限データ追加
            //admin_authorities_default 追加
            (new Authority())->initDefaultValue($company_id, 'Shachihata');
            (new Authority())->initDefaultValuePortal($company_id, 'Shachihata');

            // トライアルユーザーに初期パスワード付与
            $pass = $this->getPassword();

            // 企業利用責任者登録 mst_admin 追加
            $admin_id = DB::table('mst_admin')->insertGetId([
                'mst_company_id' => $company_id,
                'login_id' => Str::uuid()->toString(),
                'given_name' => $givenname,
                'family_name' => $familyname,
                'email' => strtolower($email),
                'password' => $pass[1],
                'role_flg' => AppUtils::ADMIN_MANAGER_ROLE_FLG,
                'department_name' => $request->has('group') ? $request->group : '',
                'phone_number' => $request->has('telno') ? $request->telno : '',
                'state_flg' => AppUtils::STATE_VALID,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
                'password_change_date' => Carbon::now(),
                'email_auth_flg' => 0,
                'email_auth_dest_flg' => 0
            ]);

            $arrApiUser2Insert = [];
            $apiAdmin = [
                "email" => $email,
                "contract_app" => config('app.pac_contract_app'),
                "app_env" => config('app.pac_app_env'),
                "user_auth" => AppUtils::AUTH_FLG_ADMIN,
                "user_first_name" => $givenname,
                "user_last_name" => $familyname,
                "company_name" => $nickname,
                "company_id" => $company_id,
                "status" => 0,
                "create_user_email" => 'master-pro@shachihata.co.jp',
                "system_name" => 'Shachihata Cloud',
                "contract_server" => config('app.pac_contract_server'),
            ];
            $arrApiUser2Insert[] = $apiAdmin;

            // 企業利用責任者権限データ model_has_permissions 追加
            $arrPermission = (new Permission())->getListMaster();
            $arrAuthority = Authority::where('mst_company_id', $company_id)->get()->keyBy('code');

            $insert = [];
            foreach ($arrPermission as $groups) {
                foreach ($groups as $code => $permissions) {
                    if (isset($arrAuthority[$code])) {
                        $authority = $arrAuthority[$code];
                        if ($authority->read_authority == 1 && isset($permissions['view'])) {
                            $insert[] = ['permission_id' => $permissions['view'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                        if ($authority->create_authority == 1 && isset($permissions['create'])) {
                            $insert[] = ['permission_id' => $permissions['create'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                        if ($authority->update_authority == 1 && isset($permissions['update'])) {
                            $insert[] = ['permission_id' => $permissions['update'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                        if ($authority->delete_authority == 1 && isset($permissions['delete'])) {
                            $insert[] = ['permission_id' => $permissions['delete'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                    }
                }
            }
            DB::table('model_has_permissions')->insert($insert);


            //利用者登録
            $user_id = DB::table('mst_user')->insertGetId([
                'mst_company_id' => $company_id,
                'login_id' => Str::uuid()->toString(),
                'system_id' => 0,
                'given_name' => $givenname,
                'family_name' => $familyname,
                'email' => $email,
                'password' => $pass[1],
                'amount' => 0,
                'state_flg' => AppUtils::STATE_VALID,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
                'password_change_date' => Carbon::now(),
            ]);

            //簡易利用者登録
            $simple_user_ids = [];
            if ($success_users) {
                foreach ($success_users as $key => $success_user) {
                    $simple_user_id = DB::table('mst_user')->insertGetId([
                        'mst_company_id' => $company_id,
                        'login_id' => Str::uuid()->toString(),
                        'system_id' => 0,
                        'given_name' => $success_user['simple_user_name2'],
                        'family_name' => $success_user['simple_user_name1'],
                        'email' => $success_user['simple_user_email'],
                        'password' => '',
                        'amount' => 0,
                        'state_flg' => AppUtils::STATE_VALID,
                        'create_at' => Carbon::now(),
                        'create_user' => 'Shachihata',
                        'password_change_date' => Carbon::now(),
                    ]);
                    $simple_user_ids[$key] = $simple_user_id;
                }
            }

            //署登録
            if ($request->group) {
                $departmentId = DB::table('mst_department')->insertGetId([
                    'mst_company_id' => $company_id,
                    'parent_id' => 0,
                    'department_name' => $request->group,
                    'state' => 1,
                    'create_at' => Carbon::now(),
                    'create_user' => 'Shachihata',
                    'update_at' => Carbon::now(),
                    'update_user' => 'Shachihata'
                ]);
                DB::table('mst_department')
                    ->where('id', $departmentId)
                    ->update(['tree' => $departmentId.',']);
            }

            //役職登録
            if ($request->post) {
                $postId = DB::table('mst_position')->insertGetId([
                    'mst_company_id' => $company_id,
                    'position_name' => $request->group,
                    'state' => 1,
                    'create_at' => Carbon::now(),
                    'create_user' => 'Shachihata',
                    'update_at' => Carbon::now(),
                    'update_user' => 'Shachihata'
                ]);
            }

            DB::table('mst_user_info')->insert([
                'mst_user_id' => $user_id,
                'mst_department_id' => $request->group ? $departmentId : null,
                'mst_position_id' => $request->post ? $postId : null,
                'phone_number' => $request->telno,
                'address' => $request->city,
                'date_stamp_config' => 1,
                'api_apps' => 0,
                'approval_request_flg' => 1,
                'browsed_notice_flg' => 0,
                'update_notice_flg' => 0,
                'mfa_type' => 0,
                'email_auth_dest_flg' => 0,
                'completion_notice_flg' => 1,
                'comment1' => '承認をお願いします。',
                'comment2' => '至急確認をお願いします。',
                'comment3' => '了解。',
                'comment4' => '了解しました。',
                'comment5' => '承認しました。',
                'comment6' => '差戻します。',
                'comment7' => 'いつもお世話になっております。',
                'page_display_first' => 'ポータル',
                'circular_info_first' => '印鑑',
                'time_stamp_permission' => 0,
                'operation_notice_flg' => 1,
                'default_rotate_angle' => 0,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            if ($simple_user_ids) {
                foreach ($simple_user_ids as $simple_user_id) {
                    DB::table('mst_user_info')->insert([
                        'mst_user_id' => $simple_user_id,
                        'mst_department_id' => null,
                        'mst_position_id' => null,
                        'phone_number' => null,
                        'address' => null,
                        'date_stamp_config' => 1,
                        'api_apps' => 0,
                        'approval_request_flg' => 1,
                        'browsed_notice_flg' => 0,
                        'update_notice_flg' => 0,
                        'mfa_type' => 0,
                        'email_auth_dest_flg' => 0,
                        'completion_notice_flg' => 1,
                        'comment1' => '承認をお願いします。',
                        'comment2' => '至急確認をお願いします。',
                        'comment3' => '了解。',
                        'comment4' => '了解しました。',
                        'comment5' => '承認しました。',
                        'comment6' => '差戻します。',
                        'comment7' => 'いつもお世話になっております。',
                        'page_display_first' => 'ポータル',
                        'circular_info_first' => '印鑑',
                        'time_stamp_permission' => 0,
                        'operation_notice_flg' => 1,
                        'default_rotate_angle' => 0,
                        'create_at' => Carbon::now(),
                        'create_user' => 'Shachihata',
                    ]);
                }
            }

            //API パラメーター作成
            $apiUser = [
                "email" => strtolower($email),
                "contract_app" => config('app.pac_contract_app'),
                "app_env" => config('app.pac_app_env'),
                "contract_server" => config('app.pac_contract_server'),
                "user_auth" => AppUtils::AUTH_FLG_USER,
                "user_first_name" => $givenname,
                "user_last_name" => $familyname,
                "company_name" => $nickname,
                "company_id" => $company_id,
                "status" => AppUtils::convertState(AppUtils::STATE_VALID),
                "system_name" => 'Shachihata Cloud',
                'create_user_email' => 'master-pro@shachihata.co.jp',
            ];
            $arrApiUser2Insert[] = $apiUser;

            //API 利用者のパラメーター作成
            if ($success_users) {
                foreach ($success_users as $success_user) {
                    $apiSimpleUser = [
                        "email" => strtolower($success_user['simple_user_email']),
                        "contract_app" => config('app.pac_contract_app'),
                        "app_env" => config('app.pac_app_env'),
                        "contract_server" => config('app.pac_contract_server'),
                        "user_auth" => AppUtils::AUTH_FLG_USER,
                        "user_first_name" => $success_user['simple_user_name2'],
                        "user_last_name" => $success_user['simple_user_name1'],
                        "company_name" => $nickname,
                        "company_id" => $company_id,
                        "status" => AppUtils::convertState(AppUtils::STATE_VALID),
                        "system_name" => 'Shachihata Cloud',
                        'create_user_email' => 'master-pro@shachihata.co.jp',
                    ];
                    $arrApiUser2Insert[] = $apiSimpleUser;
                }
            }

            //印面作成
            $name = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $familyname);
            $name = mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $name);
            $stamp = AppUtils::searchStamp($name);
            if (is_numeric($stamp)) {
                Log::channel('trial-daily')->info("利用者印面作成失敗しました。 デフォルト印面再作成します。" . $name . ' ' . $stamp);
                $name = '起案';
                $stamp = AppUtils::searchStamp($name);
                if (is_numeric($stamp)) {
                    DB::rollBack();
                    Log::channel('trial-daily')->error("利用者印面作成失敗しました。" . $name . ' ' . $stamp);
                    return $this->ApiResponse('利用者印面作成失敗しました。', 0, '');
                }
            }

            // ハンコ解像度調整
            $stamp->contents = StampUtils::stampClarity(base64_decode($stamp->contents));

            $stampId = DB::table('mst_stamp')->insertGetId([
                'stamp_name' => $name,
                'stamp_division' => 0,
                'font' => 0,
                'stamp_image' => $stamp->contents,
                'width' => floatval($stamp->realWidth) * 100,
                'height' => floatval($stamp->realHeight) * 100,
                'date_x' => $stamp->datex,
                'date_y' => $stamp->datey,
                'date_width' => $stamp->datew,
                'date_height' => $stamp->dateh,
                'create_user' => 'shachihata',
            ]);
            $mst_stamp = Stamp::find($stampId);
            $mst_stamp->serial = AppUtils::generateStampSerial(AppUtils::STAMP_FLG_NORMAL, $stampId);
            $mst_stamp->save();

            DB::table('mst_assign_stamp')->insert([
                'mst_user_id' => $user_id,
                'stamp_id' => $stampId,
                'display_no' => 0,
                'stamp_flg' => AppUtils::STAMP_FLG_NORMAL,
                'state_flg' => AppUtils::STATE_VALID,
                'time_stamp_permission' => 0,
                'create_user' => 'shachihata',
            ]);

            //簡易利用者の印面作成
            if ($success_users) {
                //印面設定
                $missingStamps = [
                    1 => ['stamp_division' => 0, 'font' => 0],
                    2 => ['stamp_division' => 0, 'font' => 1],
                    3 => ['stamp_division' => 0, 'font' => 2],
                    4 => ['stamp_division' => 1, 'font' => 0],
                    5 => ['stamp_division' => 1, 'font' => 1],
                    6 => ['stamp_division' => 1, 'font' => 2]
                ];
                foreach ($success_users as $key => $success_user) {
                    //印面作成
                    $name = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $success_user['inscription']);
                    $name = mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $name);
                    $stamp = AppUtils::searchStamp($name, $missingStamps[$success_user['inscription_setting']]['stamp_division'], $missingStamps[$success_user['inscription_setting']]['font']);
                    if (is_numeric($stamp)) {
                        Log::channel('trial-daily')->info("利用者印面作成失敗しました。 デフォルト印面再作成します。" . $name . ' ' . $stamp);
                        $name = '起案';
                        $stamp = AppUtils::searchStamp($name, $missingStamps[$success_user['inscription_setting']]['stamp_division'], $missingStamps[$success_user['inscription_setting']]['font']);
                        if (is_numeric($stamp)) {
                            DB::rollBack();
                            Log::channel('trial-daily')->error("利用者印面作成失敗しました。" . $name . ' ' . $stamp);
                            return $this->ApiResponse('利用者印面作成失敗しました。', 0, '');
                        }
                    }

                    // ハンコ解像度調整
                    $stamp->contents = StampUtils::stampClarity(base64_decode($stamp->contents));

                    $stampId = DB::table('mst_stamp')->insertGetId([
                        'stamp_name' => $name,
                        'stamp_division' => $missingStamps[$success_user['inscription_setting']]['stamp_division'],
                        'font' => $missingStamps[$success_user['inscription_setting']]['font'],
                        'stamp_image' => $stamp->contents,
                        'width' => floatval($stamp->realWidth) * 100,
                        'height' => floatval($stamp->realHeight) * 100,
                        'date_x' => $stamp->datex,
                        'date_y' => $stamp->datey,
                        'date_width' => $stamp->datew,
                        'date_height' => $stamp->dateh,
                        'create_user' => 'shachihata',
                    ]);
                    $mst_stamp = Stamp::find($stampId);
                    $mst_stamp->serial = AppUtils::generateStampSerial(AppUtils::STAMP_FLG_NORMAL, $stampId);
                    $mst_stamp->save();

                    DB::table('mst_assign_stamp')->insert([
                        'mst_user_id' => $simple_user_ids[$key],
                        'stamp_id' => $stampId,
                        'display_no' => 0,
                        'stamp_flg' => AppUtils::STAMP_FLG_NORMAL,
                        'state_flg' => AppUtils::STATE_VALID,
                        'time_stamp_permission' => 0,
                        'create_user' => 'shachihata',
                    ]);
                }
            }

            // トライアルユーザーに初期パスワード メール通知
            $data['password'] = $pass[0];
            $parameters['password'] = $pass[0];

            //簡易利用者:パスワードを設定通知メール
            if ($success_users) {
                foreach ($success_users as $success_user) {
                    $this->sendMailResetPassword(AppUtils::ACCOUNT_TYPE_SIMPLE_USER, $success_user['simple_user_email'], config('app.url_app_user'), null, null, $company_id);
                }
            }

            //利用者初期パスワードのお知らせ 成功通知
            MailUtils::InsertMailSendResume(
            // 送信先メールアドレス
                $email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['TRIAL_SUCCESS']['CODE'],
                // パラメータ
                json_encode($parameters, JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_ADMIN,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.user') . trans('mail.trialSuccessMail.subject'),
                // メールボディ
                trans('mail.trialSuccessMail.body', $data)
            );


            $result = $client->post("users/importInsert", [
                RequestOptions::JSON => $arrApiUser2Insert
            ]);

            if ($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::channel('trial-daily')->error("統合ID側API呼出失敗しました。 " . $result->getBody());
                return $this->ApiResponse('利用者登録統合ID側API呼出失敗しました。', 0, '');
            } else {
                $response = json_decode((string)$result->getBody());
                if ($response->message) {
                    DB::rollBack();
                    Log::channel('trial-daily')->error("他の環境にメールアドレス存在して、登録失敗しました。" . $result->getBody());
                    return $this->ApiResponse('他の環境にメールアドレス存在して、登録失敗しました。', 0, '');
                }
            }

            // リクエストに設定ある場合、更新
            if(config('app.gw_use') == 1 && config('app.gw_domain')) {
                //登録GW会社
                $store_company_result = GwAppApiUtils::storeCompany($company_id, $nickname, 1);
                if (!$store_company_result){
                    Log::channel('trial-daily')->error('Api storeGwCompany failed, please refer to laravel.log for detailed information');
                    return $this->ApiResponse(__('message.false.api.gw_company_store'),0,'');
                }
                //アプリ利用制限登録API呼び出し
                $store_company_limit_result = GwAppApiUtils::storeCompanyLimit($company_id);
                if (!$store_company_limit_result){
                    Log::channel('trial-daily')->error('Api storeCompanyLimit failed, please refer to laravel.log for detailed information');
                    return $this->ApiResponse(__('message.false.api.gw_company_limit_Store'),0,'');
                }
                //スケジューラーのフラグがデフォルトON
                $store_scheduler_flg_result = GwAppApiUtils::storeCompanySetting($company_id,AppUtils::GW_APPLICATION_ID_SCHEDULE,AppUtils::GW_APPLICATION_SCHEDULE_LIMIT_FLG,AppUtils::GW_APPLICATION_SCHEDULE_BUY_COUNT);
                if (!$store_scheduler_flg_result){
                    DB::rollBack();
                    Log::channel('trial-daily')->error("スケジューラフラグ設定失敗しました ");
                    return $this->ApiResponse('スケジューラフラグ設定失敗しました。', 0, '');
                }
                //CalDAVフラグがデフォルトON
                $store_caldav_flg_result = GwAppApiUtils::storeCompanySetting($company_id, AppUtils::GW_APPLICATION_ID_CALDAV, AppUtils::GW_APPLICATION_SCHEDULE_LIMIT_FLG, AppUtils::GW_APPLICATION_SCHEDULE_BUY_COUNT);
                if (!$store_caldav_flg_result){
                    DB::rollBack();
                    Log::channel('trial-daily')->error("CalDAVフラグ設定失敗しました, please refer to laravel.log for detailed information");
                    return $this->ApiResponse(__('message.false.api.update_gw_caldav_flg'), 0, '');
                }
            }
            // サポート掲示板 登録
            ApplicationAuthUtils::storeCompanySetting($company_id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD, 1, 0);
            DB::commit();
            return $this->ApiResponse('トライアル企業登録成功', 1, '');
        } catch (\Exception $e) {
            //error
            DB::rollBack();
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->alert(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * トライアル情報取得
     * @param Request $request
     * @return array
     */
    public function getTrialInfo(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("getTrialInfo： " . $request);
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // パラメーター必須チェック
            // メールアドレス
            if (!$request->has('id') || empty($request->id)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。', 0, '');
            }

            // 申込みされる方の情報(管理者)情報取得
            $admin_apply = DB::table('mst_admin')->where('email', $request->id)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
            if (!$admin_apply) {
                return $this->ApiResponse('申込みされる方の情報(管理者)取得に失敗しました。', 0, '');
            }

            //会社情報取得
            $company = DB::table('mst_company')->where('id', $admin_apply->mst_company_id)->first();
            if (!$company) {
                return $this->ApiResponse('会社情報取得に失敗しました。', 0, '');
            }

            //運用責任者情報取得
            $administrator = DB::table('mst_admin')->where('mst_company_id', $admin_apply->mst_company_id)
                ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                ->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                ->first();
            if (!$administrator) {
                return $this->ApiResponse('責任者情報取得に失敗しました。', 0, '');
            }

            $domain_info = [
                "nick_name" => rtrim($company->company_name),
                "kana_name" => $company->company_name_kana,
                "domains" => str_replace("\n", ",", $company->domain),
                "max_stamps" => $company->upper_limit,
                "old_contract_flg" => $company->old_contract_flg //旧契約形態
            ];
            $member_info = [
                "group" => $admin_apply->department_name ? $admin_apply->department_name : '',
                "family_name" => $admin_apply->family_name,
                "given_name" => $admin_apply->given_name,
                "email" => $admin_apply->email
            ];
            $admin_info = [
                "group" => $administrator->department_name ? $administrator->department_name : '',
                "family_name" => $administrator->family_name,
                "given_name" => $administrator->given_name,
                "email" => $administrator->email
            ];
            $result = [
                "domain_info" => $domain_info,
                "member_info" => $member_info,
                "admin_info" => $admin_info
            ];

            return $this->ApiResponse('', 1, $result);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * トライアル状態を取得するAPI
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDomainsTrialState(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("getDomainsTrialState： " . $request);
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // メールアドレス
            if (!$request->has('email') || empty($request->email)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。', 0, '');
            }

            $company = DB::table('mst_company as c')->join('mst_admin as a', 'a.mst_company_id', 'c.id')
                ->select('c.id', 'c.trial_flg', 'c.contract_edition')
                ->where('a.role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                ->where('a.state_flg', '!=', AppUtils::STATE_DELETE)
                ->where('a.email', $request->email)
                ->first();

            if (!$company) {
                return $this->ApiResponse('トライアル状態取得失敗しました。', 0, '');
            }

            return $this->ApiResponse('', 1, ['trial' => $company->contract_edition == 3 ? 1 : 0, 'domainId' => $company->id]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * 企業のトライアル状態を切り替えるAPI
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDomainsTrialState(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("updateDomainsTrialState： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // メールアドレス
            if (!$request->has('domainid') || empty($request->domainid)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{domainid}', 0, '');
            }

            if (!$request->has('contract_edition') ||
                !in_array($request->contract_edition, [AppUtils::CONTRACT_EDITION_STANDARD, AppUtils::CONTRACT_EDITION_BUSINESS, AppUtils::CONTRACT_EDITION_GW])) {
                return $this->ApiResponse('無効パラメーター。{contract_edition}', 0, '');
            }

            if (!$request->has('number') || !preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->number)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{number}', 0, '');
            }

            $company = DB::table('mst_company')->where('id', $request->domainid)->first();

            if (!$company || $company->contract_edition != AppUtils::CONTRACT_EDITION_TRIAL) {
                if ($company->contract_edition != AppUtils::CONTRACT_EDITION_GW || $request->contract_edition != AppUtils::CONTRACT_EDITION_STANDARD){
                return $this->ApiResponse('トライアル企業ではありません。', 0, '');
            }
            }

            if ($request->contract_edition != AppUtils::CONTRACT_EDITION_GW && empty($request->number)){
                return $this->ApiResponse('リクエストパラメータが不足しています。{number}', 0, '');
            }

            //グループウェア会社　maxstamps = 0
            if ($request->contract_edition == AppUtils::CONTRACT_EDITION_GW && $request->number != 0){
                return $this->ApiResponse('0を入力してください。{number}', 0, '');
            }

            $situation = StampUtils::getUsageSituation($request->domainid);

            if ($situation && intval($situation['total_name_stamp'] + $situation['total_date_stamp'] + $situation['total_common_stamp']) > intval($request->number) && $request->contract_edition != AppUtils::CONTRACT_EDITION_GW) {
                return $this->ApiResponse('企業の登録印面数が超えています。', 0, '');
            }

            DB::beginTransaction();

            $userInfoIds = DB::table('mst_user_info')
                ->join('mst_user', 'mst_user_info.mst_user_id', '=', 'mst_user.id')
                ->where('mst_user.mst_company_id', '=', $request->domainid)
                ->where('mst_user.state_flg', '!=',AppUtils::STATE_DELETE)
                ->select('mst_user_info.mst_user_id')
                ->pluck('mst_user_info.mst_user_id')
                ->toArray();

            if ($request->contract_edition == 1) {
                DB::table('mst_company')->where('id', $request->domainid)
                    ->update([
                        'contract_edition' => $request->contract_edition,
                        'system_name' => 'Shachihata Cloud',
                        'trial_flg' => 0,
                        'state' => 1,
                        'upper_limit' => $request->number,
                        'template_search_flg' => 0,
                        'template_csv_flg' => 0,
                        'convenient_flg' => 0,
                    ]);
            } else {
                DB::table('mst_company')->where('id', $request->domainid)
                    ->update(['contract_edition' => $request->contract_edition,
                        'system_name' => 'Shachihata Cloud',
                        'trial_flg' => 0,
                        'state' => 1,
                        'esigned_flg' => 0,
                        'box_enabled' => 0,
                        'template_flg' => 0,
                        'template_search_flg' => 0,
                        'template_csv_flg' => 0,
                        'convenient_flg' => 0,
                        'ip_restriction_flg' => 0,
                        'mfa_flg' => 0,
                        'repage_preview_flg' => 0,
                        'upper_limit' => $request->number]);
                DB::table('mst_user_info')->whereIn('mst_user_id', $userInfoIds)
                    ->update(['mfa_type' => 0]);
                }
            //デフォルト（Business）
            $updateList = [
                'contract_edition' => $request->contract_edition,
                'system_name' => 'Shachihata Cloud',
                'trial_flg' => 0,
                'state' => 1,
                'upper_limit' => $request->number,
                'template_search_flg' => 0,
                'template_csv_flg' => 0,
                'default_stamp_flg' => 1,//デフォルト印
                'convenient_flg' => 0,//便利印
                'long_term_storage_flg' => 0, //長期保管
                'long_term_storage_option_flg' => 0, //長期保管オプション
                'max_usable_capacity' => 0, //長期保管使用容量(GB)
                'regular_at'=> Carbon::now() //本契約切替日
            ];

            // 契約Edition：Standard
            if ($request->contract_edition == AppUtils::CONTRACT_EDITION_STANDARD){
                $updateList['esigned_flg'] = 0; //PDFへの電子署名付加
                $updateList['default_stamp_flg'] = 0; //デフォルト印
                $updateList['confidential_flg'] = 0; //社外秘
                $updateList['ip_restriction_flg'] = 0; //接続IP制限
                $updateList['repage_preview_flg'] = 0; //改ページ調整プレビュー
                $updateList['box_enabled'] = 0; //外部連携
                $updateList['mfa_flg'] = 0; //多要素認証
                $updateList['template_flg'] = 0; //テンプレート機能

                if ($company->contract_edition == AppUtils::CONTRACT_EDITION_GW){//機能：無効　⇒　有効
                    $updateList['department_stamp_flg'] = 1; //部署名入り日付印
                    $updateList['template_route_flg'] = 1; //承認ルート
                    $updateList['rotate_angle_flg'] = 1; //おじぎ印
                    $updateList['phone_app_flg'] = 1; //携帯アプリ
                    $updateList['attachment_flg'] = 1; //添付ファイル機能
                }
                //多要素認証  無効
                DB::table('mst_user_info')
                    ->whereIn('mst_user_id', $userInfoIds)
                    ->update(['mfa_type' => 0,'template_flg' => 0]);
            }
            // 契約Edition：グループウェア
            if ($request->contract_edition == AppUtils::CONTRACT_EDITION_GW){
                $updateList['department_stamp_flg'] = 0; //部署名入り日付印
                $updateList['template_route_flg'] = 0; //承認ルート
                $updateList['rotate_angle_flg'] = 0; //おじぎ印
                $updateList['phone_app_flg'] = 0; //携帯アプリ
                $updateList['attachment_flg'] = 0; //添付ファイル機能
                $updateList['confidential_flg'] = 0; //社外秘
                $updateList['esigned_flg'] = 0; //PDFへの電子署名付加
                $updateList['ip_restriction_flg'] = 0; //接続IP制限
                $updateList['repage_preview_flg'] = 0; //改ページ調整プレビュー
                $updateList['box_enabled'] = 0; //外部連携
                $updateList['mfa_flg'] = 0; //多要素認証
                $updateList['template_flg'] = 0; //テンプレート機能
                $updateList['default_stamp_flg'] = 0; //デフォルト印
                $updateList['option_user_flg'] = 1; //グループウェア専用利用者

                //回覧利用者 無効
                DB::table('mst_user')->where('mst_company_id', $request->domainid)
                    ->where('state_flg', AppUtils::STATE_VALID)
                    ->where('option_flg', AppUtils::USER_NORMAL)
                    ->update(['state_flg' => AppUtils::STATE_INVALID]);

                //多要素認証 無効
                DB::table('mst_user_info')
                    ->whereIn('mst_user_id', $userInfoIds)
                    ->update(['mfa_type' => 0,'template_flg' => 0]);
            }

            DB::table('mst_company')->where('id', $request->domainid)->update($updateList);

            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                DB::rollBack();
                Log::channel('trial-daily')->error("統合ID側アクセスできない");
                return $this->ApiResponse('統合ID側アクセスできない', 0, '');
            }
            $params = [
                'company_id' => $request->domainid,
                'system_name' => 'Shachihata Cloud',
                'app_env' => config('app.pac_app_env'),
                "contract_server" => config('app.pac_contract_server'),
            ];

            $result = $client->post('company/update', [
                RequestOptions::JSON => $params
            ]);
            $response = json_decode((string)$result->getBody());
            if ($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::channel('trial-daily')->error("統合ID側API呼出失敗 " . $response->message);
                return $this->ApiResponse('統合ID側API呼出失敗', 0, '');
            }
            $gw_use=config('app.gw_use');
            $gw_domin=config('app.gw_domain');
            if($gw_use==1 && $gw_domin) {
                $setting  = GwAppApiUtils::getCompanySetting($company->id);
                $setting_pac = ApplicationAuthUtils::getCompanySetting($company->id);
                $scheduler_flg = $setting['scheduler_flg'];
                $caldav_flg = $setting['caldav_flg'];
                $attendance_flg = $setting_pac['attendance_flg'];
                $file_mail_flg = $setting_pac['file_mail_flg'];
                if ($scheduler_flg){
                    $settingCompanyIds = GwAppApiUtils::getCompanySettingId($company->id, $company->company_name, $company->state);
                    if ($settingCompanyIds){
                        $gw_app_schedule_id = $settingCompanyIds['schedule_id'];
                        $gw_app_caldav_id = $settingCompanyIds['caldav_id'];
                        if($gw_app_schedule_id){
                            $del_scheduler_result = GwAppApiUtils::deleteCompanySetting($gw_app_schedule_id);
                            if (!$del_scheduler_result){
                                DB::rollBack();
                                Log::channel('trial-daily')->error("スケジューラー削除失敗しました。" . $response->message);
                                return $this->ApiResponse('スケジューラー削除失敗しました。', 0, '');
                            }
                            if($caldav_flg){
                                if($gw_app_caldav_id){
                                    $del_caldav_result = GwAppApiUtils::deleteCompanySetting($gw_app_caldav_id);
                                    if (!$del_caldav_result){
                                        DB::rollBack();
                                        Log::channel('trial-daily')->error("CalDAV削除失敗しました。" . $response->message);
                                        return $this->ApiResponse('CalDAV削除失敗しました。', 0, '');
                                    }
                                }
                            }
                        }
                    }else{
                        DB::rollBack();
                        Log::channel('trial-daily')->error("GW設定取得失敗しました。" . $response->message);
                        return $this->ApiResponse('GW設定取得失敗しました。', 0, '');
                    }
                }

                if($attendance_flg || $file_mail_flg){
                    DB::table('mst_company')->where('id', $company->id)->update(['gw_flg' => 1]);
                }else{
                    DB::table('mst_company')->where('id', $company->id)->update(['gw_flg' => 0]);
                }

            }
            ApplicationAuthUtils::deleteCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD);
            $applicationUsers = DB::table('mst_user')
                ->where('mst_company_id', $company->id)
                ->pluck('id')
                ->toArray();
            if (count($applicationUsers) > 0) {
                DB::table('mst_application_users')
                    ->where('mst_application_id', AppUtils::GW_APPLICATION_ID_FAQ_BOARD)
                    ->whereIn('mst_user_id', $applicationUsers)
                    ->delete();
            }
            DB::commit();
            return $this->ApiResponse('', 1, '');
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * 登録可能印面数の更新API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDomainsMaxUserStamps(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("updateDomainsMaxUserStamps： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // メールアドレス
            if (!$request->has('domainid') || empty($request->domainid)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{domainid}', 0, '');
            }

            if (!$request->has('number') || empty($request->number) || !preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->number)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{number}', 0, '');
            }

            $company = DB::table('mst_company')->where('id', $request->domainid)->first();
            if (!$company) {
                return $this->ApiResponse('会社情報取得に失敗しました。', 0, '');
            }
            if($company->contract_edition == AppUtils::CONTRACT_EDITION_BUSINESS){
                //Businessエディション企業＋old_contractフラグがON→現状通り、利用ユーザがnumberを下回っていなかったら購入できるようにする
                if ($company->old_contract_flg){
                    //PAC_5-2801 Business会社 有効ユーザ数 <= number
                    $mst_user_count = DB::table('mst_user')
                        ->where('mst_company_id', $request->domainid)
                        ->where('option_flg',AppUtils::USER_NORMAL)
                        ->where('state_flg', AppUtils::STATE_VALID)
                        ->count();
                    if($mst_user_count && intval($mst_user_count) > intval($request->number)){
                        return $this->ApiResponse('企業の登録印面数が超えています。', 0, '');
                    }
                }else{//Businessエディション＋old_contractフラグがOFF→Standardのように割当印面数 <= numberであれば購入できるようにする
                    $situation = StampUtils::getUsageSituation($request->domainid);

                    if ($situation && intval($situation['total_name_stamp'] + $situation['total_date_stamp'] + $situation['total_common_stamp']) > intval($request->number)) {
                        return $this->ApiResponse('企業の登録印面数が超えています。', 0, '');
                    }
                }

            }else{
                $situation = StampUtils::getUsageSituation($request->domainid);

                if ($situation && intval($situation['total_name_stamp'] + $situation['total_date_stamp'] + $situation['total_common_stamp']) > intval($request->number)) {
                    return $this->ApiResponse('企業の登録印面数が超えています。', 0, '');
                }
            }

            DB::table('mst_company')->where('id', $request->domainid)->update(['upper_limit' => $request->number]);

            return $this->ApiResponse('', 1, '');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * 追加ファイル容量のAPI（10GB単位）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAppendFileCapacity(Request $request){
        try {
            Log::channel('trial-daily')->info("updateAppendFileCapacity： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // メールアドレス
            if (!$request->has('domainid') || empty($request->domainid)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{domainid}', 0, '');
            }
            //追加ファイル容量
            if (!$request->has('append_file_capacity') || !preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->append_file_capacity)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{append_file_capacity}', 0, '');
            }
            $company = DB::table('mst_company')->where('id',$request->domainid)->get();
            if (!$company){
                return $this->ApiResponse('この企業IDは存在しません', 0, '');
            }
            try {

                DB::table('mst_company')
                    ->where('id',$request->domainid)
                    ->update(['add_file_limit' => $request->append_file_capacity]);

                return $this->ApiResponse('', 1, '');
            }catch (\Exception $e){
                $errorMessage = $e->getMessage() . $e->getTraceAsString();
                Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
                return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * トライアル踏まずに本契約登録するAPI「Standard」or「Business」
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createContractDomain(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("createContractDomain：" . $request);
            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }
            // 企業名
            if (!$request->has('nickname') || empty($request->nickname)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{nickname}', 0, '');
            }
            // 企業名(カナ)
            if (!$request->has('kananame') || empty($request->kananame)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{kananame}', 0, '');
            }
            // ドメイン
            if (!$request->has('domains') || empty($request->domains)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{domains}', 0, '');
            }
            // 印鑑数
            if (!$request->has('maxstamps')) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{maxstamps}', 0, '');
            }
            // メールアドレス
            if (!$request->has('email') || empty($request->email)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{email}', 0, '');
            }
            // 責任者氏名(姓)
            if (!$request->has('familyname') || empty($request->familyname)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{familyname}', 0, '');
            }
            // 責任者氏名(名)
            if (!$request->has('givenname') || empty($request->givenname)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{givenname}', 0, '');
            }
            // 責任者氏名フリガナ(姓)
            if (!$request->has('familynameKana') || empty($request->familynameKana)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{familynameKana}', 0, '');
            }
            // 責任者氏名フリガナ(名)
            if (!$request->has('givennameKana') || empty($request->givennameKana)) {
                return $this->ApiResponse('リクエストパラメータが不足しています。{givennameKana}', 0, '');
            }
            // 企業名maxlength
            $nickname = $request->nickname;
            if (mb_strlen($nickname) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{nickname}', 0, '');
            }
            // 企業名(カナ)maxlength
            $kananame = $request->kananame;
            if (mb_strlen($kananame) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{kananame}', 0, '');
            }
            // ドメイン形式のチェック
            $domains = $request->domains;
            // 印鑑数int maxlength　minlength
            $maxstamps = $request->maxstamps;
            if (!preg_match("/^([1-9]\d*|[0]{1,1})$/", $maxstamps) || strlen($maxstamps) < 1 || strlen($maxstamps) > 9) {
                return $this->ApiResponse('0~9桁整数を入力してください。{maxstamps}', 0, '');
            }
            // メールアドレス
            $email = $request->email;
            if (strlen($email) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->ApiResponse('メールアドレス形式不正。{email}', 0, '');
            }
            // 責任者氏名(姓)
            $familyname = $request->familyname;
            if (mb_strlen($familyname) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{familyname}', 0, '');
            }
            // 責任者氏名(名)
            $givenname = $request->givenname;
            if (mb_strlen($givenname) > 30) {
                return $this->ApiResponse('入力文字の長さが超えています。{givenname}', 0, '');
            }
            //契約Edition:Standard | Business | グループウェア
            if (!$request->has('contract_edition') ||
                !in_array($request->contract_edition, [AppUtils::CONTRACT_EDITION_STANDARD, AppUtils::CONTRACT_EDITION_BUSINESS, AppUtils::CONTRACT_EDITION_GW])) {
                return $this->ApiResponse('無効パラメーター。{contract_edition}', 0, '');
            }
            //グループウェア会社　maxstamps = 0
            if ($request->contract_edition == AppUtils::CONTRACT_EDITION_GW && $maxstamps != 0){
                return $this->ApiResponse('0を入力してください。{maxstamps}', 0, '');
            }

            //統合ID側からユーザー情報取得
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                return $this->ApiResponse('Cannot connect to ID App', 0, '');
            }

            $results = $client->get('users', [
                RequestOptions::JSON => [
                    'email' => $request->email,
                    'contract_app' => 1
                ]
            ]);
            $datas = json_decode((string)$results->getBody())->data->data;
            $result = [];
            foreach ($datas as $key => $val) {
                if ($val->status != 9 && $val->email == $request->email) $result[] = $val;
            }

            if ($result) {
                return $this->ApiResponse('メールアドレスが既に存在しています。', 0, '');
            }

            if ($request->has('isValidEmail') && $request->isValidEmail == 1) {
                return $this->ApiResponse('メールアドレスは一意です', 1, '');
            }
            DB::beginTransaction();

            // 企業新規作成 mst_company追加 (NULLのものには値を設定しない？)
            if (in_array($request->contract_edition, [AppUtils::CONTRACT_EDITION_STANDARD, AppUtils::CONTRACT_EDITION_BUSINESS])){//Standard || Business
                $company_id = DB::table('mst_company')->insertGetId([
                    'company_name' => $nickname,
                    'company_name_kana' => $kananame,
                    'contract_edition' => $request->contract_edition,
                    'department_stamp_flg' => 1,
                    'domain' => $domains,
                    'esigned_flg' => $request->contract_edition == 1 ? 1 : 0,
                    'guest_company_flg' => 0,
                    'guest_document_application' => 0,
                    'ip_restriction_flg' => $request->contract_edition == 1 ? 1 : 0,
                    'login_type' => 0,
                    'long_term_storage_flg' => 0,
                    'max_usable_capacity' => 0,
                    'mfa_flg' => $request->contract_edition == 1 ? 1 : 0,
                    'stamp_flg' => 0,
                    'state' => 1,
                    'system_name' => 'Shachihata Cloud',
                    'updated_notification_email_flg' => 0,
                    'upper_limit' => $maxstamps,
                    'use_api_flg' => 1,
                    'view_notification_email_flg' => 0,
                    'dstamp_style' => '\'y.m.d',
                    'create_at' => Carbon::now(),
                    'create_user' => 'Shachihata',
                    'trial_flg' => 0,
                    'permit_unregistered_ip_flg' => 0,
                    'certificate_flg' => 0,
                    'phone_app_flg' => 1,
                    'box_enabled' => $request->contract_edition == 1 ? 1 : 0,
                    'template_flg' => $request->contract_edition == 1 ? 1 : 0,
                    'portal_flg' => 1,
                    'rotate_angle_flg' => 1,
                    'template_route_flg' => 1,
                    'bizcard_flg' => 0,
                    'attachment_flg' => 1,
                    'gw_flg' => 0,
                    'regular_at' => Carbon::now()
                ]);
            }else{//グループウェア
                $company_id = DB::table('mst_company')->insertGetId([
                    'company_name' => $nickname,
                    'company_name_kana' => $kananame,
                    'contract_edition' => $request->contract_edition,
                    'department_stamp_flg' => 0,
                    'domain' => $domains,
                    'esigned_flg' => 0,
                    'guest_company_flg' => 0,
                    'guest_document_application' => 0,
                    'ip_restriction_flg' => 0,
                    'login_type' => 0,
                    'long_term_storage_flg' => 0,
                    'max_usable_capacity' => 0,
                    'mfa_flg' => 0,
                    'stamp_flg' => 0,
                    'state' => 1,
                    'system_name' => 'Shachihata Cloud',
                    'updated_notification_email_flg' => 0,
                    'upper_limit' => $maxstamps,
                    'use_api_flg' => 0,
                    'view_notification_email_flg' => 0,
                    'dstamp_style' => '\'y.m.d',
                    'create_at' => Carbon::now(),
                    'create_user' => 'Shachihata',
                    'trial_flg' => 0,
                    'permit_unregistered_ip_flg' => 0,
                    'certificate_flg' => 0,
                    'phone_app_flg' => 0,
                    'box_enabled' => 0,
                    'template_flg' => 0,
                    'rotate_angle_flg' => 0,
                    'template_route_flg' => 0,
                    'bizcard_flg' => 0,
                    'attachment_flg' => 0,
                    'gw_flg' => 0,
                    'portal_flg' => 1,//ポータル機能
                    'option_user_flg' => 1,//グループウェア専用利用者
                    'regular_at' => Carbon::now()
                ]);
            }


            //掲示板のフラグがデフォルトON
            DB::table('mst_application_companies')->insert([
                "mst_company_id" => $company_id,
                "mst_application_id" => AppUtils::GW_APPLICATION_ID_BOARD,
                'created_at' => Carbon::now()
            ]);

            //企業の制約テーブル情報追加
            //mst_constraints 追加
            $appSetting = AppSettingConstraint::getAppSettingConstraint();

            DB::table('mst_constraints')->insert([
                'mst_company_id' => $company_id,
                'max_requests' => $appSetting->getSettingRequestsMax(),
                'max_document_size' => $appSetting->getSettingFileSize(),
//                'user_storage_size' => $appSetting->getSettingDiskCapacity(),
                'use_storage_percent' => $appSetting->getSettingStoragePercent(),
                'max_keep_days' => $appSetting->getSettingRetentionDay(),
                'delete_informed_days_ago' => $appSetting->getSettingDeleteDay(),
                'long_term_storage_percent' => $appSetting->getSettingLongTermStoragePercent(),
                'max_ip_address_count' => $appSetting->getSettingMaxIpAddressCount(),
                'max_viwer_count' => $appSetting->getSettingMaxViwerCount(),
                'max_attachment_size' => $appSetting->getSettingMaxAttachmentSize(),
                'max_total_attachment_size' => $appSetting->getSettingMaxTotalAttachmentSize(),
                'max_attachment_count' => $appSetting->getSettingMaxAttachmentCount(),
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
                'dl_max_keep_days' => $appSetting->getSettingDlMaxKeepDays(),
                'dl_after_proc' => $appSetting->getSettingDlAfterProc(),
                'dl_after_keep_days' => $appSetting->getSettingDlAfterKeepDays(),
                'dl_request_limit' => $appSetting->getSettingDlRequestLimit(),
                'dl_request_limit_per_one_hour' => $appSetting->getSettingDlRequestLimitPerOneHour(),
                'dl_file_total_size_limit' => $appSetting->getSettingDlFileTotalSizeLimit(),
                'max_frm_document' => $appSetting->getSettingMaxFrmDocument(),
            ]);

            //企業に関する設定テーブルの情報追加
            //password_policy,mst_limit,mst_time_stamp,mst_protection 追加
            DB::table('password_policy')->insert([
                "mst_company_id" => $company_id,
                "min_length" => 4,
                "validity_period" => 0,
                "enable_password" => 1,
                // PAC_5-1970 パスワードメールの有効期限を変更する Start
                "password_mail_validity_days" => 7,
                // PAC_5-1970 End
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            //特設サイト
            DB::table('special_site_receive_send_available_state')->insert([
                'company_id' => $company_id,
                'is_special_site_receive_available' => 0,
                'is_special_site_send_available' => 0,
                'created_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            DB::table('mst_limit')->insert([
                "mst_company_id" => $company_id,
                'storage_local' => 1,
                'storage_box' => 0,
                'storage_google' => 0,
                'storage_dropbox' => 0,
                'storage_onedrive' => 0,
                'enable_any_address' => 0,
                'link_auth_flg' => 0,
                'enable_email_thumbnail' => 1,
                'receiver_permission' => 1,
                'environmental_selection_dialog' => 0,
                "time_stamp_permission" => 0,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            DB::table('mst_protection')->insert([
                'mst_company_id' => $company_id,
                'protection_setting_change_flg' => 0,
                'destination_change_flg' => 0,
                'enable_email_thumbnail' => 0,
                'access_code_protection' => 1,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
            ]);

            // 企業権限データ追加
            //admin_authorities_default 追加
            (new Authority())->initDefaultValue($company_id, 'Shachihata');
            (new Authority())->initDefaultValuePortal($company_id, 'Shachihata');
            //初期パスワード付与
            $pass = $this->getPassword();

            // 企業利用責任者登録 mst_admin 追加
            $admin_id = DB::table('mst_admin')->insertGetId([
                'mst_company_id' => $company_id,
                'login_id' => Str::uuid()->toString(),
                'given_name' => $givenname,
                'family_name' => $familyname,
                'email' => $email,
                'password' => $pass[1],
                'role_flg' => AppUtils::ADMIN_MANAGER_ROLE_FLG,
                'department_name' => $request->has('group') ? $request->group : '',
                'phone_number' => $request->has('telno') ? $request->telno : '',
                'state_flg' => AppUtils::STATE_VALID,
                'create_at' => Carbon::now(),
                'create_user' => 'Shachihata',
                'email_auth_flg' => 0,
                'email_auth_dest_flg' => 0
            ]);

            // 企業利用責任者権限データ model_has_permissions 追加
            $arrPermission = (new Permission())->getListMaster();
            $arrAuthority = Authority::where('mst_company_id', $company_id)->get()->keyBy('code');

            $insert = [];
            foreach ($arrPermission as $groups) {
                foreach ($groups as $code => $permissions) {
                    if (isset($arrAuthority[$code])) {
                        $authority = $arrAuthority[$code];
                        if ($authority->read_authority == 1 && isset($permissions['view'])) {
                            $insert[] = ['permission_id' => $permissions['view'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                        if ($authority->create_authority == 1 && isset($permissions['create'])) {
                            $insert[] = ['permission_id' => $permissions['create'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                        if ($authority->update_authority == 1 && isset($permissions['update'])) {
                            $insert[] = ['permission_id' => $permissions['update'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                        if ($authority->delete_authority == 1 && isset($permissions['delete'])) {
                            $insert[] = ['permission_id' => $permissions['delete'], 'model_type' => 'App\CompanyAdmin', 'model_id' => $admin_id];
                        }
                    }
                }
            }
            DB::table('model_has_permissions')->insert($insert);

            //初期パスワード メール通知
            $param['password'] = $pass[0];
            $param['account_type'] = 'admin';

            // 管理者:初期パスワード発行の通知
            MailUtils::InsertMailSendResume(
            // 送信先メールアドレス
                $email,
                // メールテンプレート
                MailUtils::MAIL_DICTIONARY['ADMIN_INIT_PASSWORD_ALERT']['CODE'],
                // パラメータ
                json_encode($param, JSON_UNESCAPED_UNICODE),
                // タイプ
                AppUtils::MAIL_TYPE_ADMIN,
                // 件名
                config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendAdminPasswordMail.subject'),
                // メールボディ
                trans('mail.SendAdminPasswordMail.body', $param)
            );

            // 統合IDに該当企業利用責任者登録処理 mst_user 追加
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                DB::rollBack();
                Log::channel('trial-daily')->error("統合ID側アクセスできない");
                return $this->ApiResponse('統合ID側アクセスできない', 0, '');
            }

            $apiUser = [
                "email" => $email,
                "contract_app" => config('app.pac_contract_app'),
                "app_env" => config('app.pac_app_env'),
                "user_auth" => AppUtils::AUTH_FLG_ADMIN,
                "user_first_name" => $givenname,
                "user_last_name" => $familyname,
                "company_name" => $nickname,
                "company_id" => $company_id,
                "status" => 0,
                "create_user_email" => 'master-pro@shachihata.co.jp',
                "system_name" => 'Shachihata Cloud',
                "contract_server" => config('app.pac_contract_server'),
            ];

            $result = $client->post("users", [
                RequestOptions::JSON => $apiUser
            ]);

            if ($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::channel('trial-daily')->error("統合ID側API呼出失敗 " . $result->getBody());
                return $this->ApiResponse('統合ID側API呼出失敗', 0, '');
            }

            // リクエストに設定ある場合、更新
            if(config('app.gw_use') == 1 && config('app.gw_domain')) {
                //登録GW会社
                $store_company_result = GwAppApiUtils::storeCompany($company_id, $nickname, 1);
                if (!$store_company_result){
                    Log::channel('trial-daily')->error('Api storeGwCompany failed, please refer to laravel.log for detailed information');
                    return $this->ApiResponse(__('message.false.api.gw_company_store'),0,'');
                }
                //アプリ利用制限登録API呼び出し
                $store_company_limit_result = GwAppApiUtils::storeCompanyLimit($company_id);
                if (!$store_company_limit_result){
                    Log::channel('trial-daily')->error('Api storeCompanyLimit failed, please refer to laravel.log for detailed information');
                    return $this->ApiResponse(__('message.false.api.gw_company_limit_Store'),0,'');
                }
            }

            DB::commit();
            return $this->ApiResponse('トライアル企業登録成功', 1, '');
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * 企業機能設定一括更新API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDomainSetting(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("updateDomainSetting： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')->where('api_name', 'HomePage')->first();
            if (!$request->has('accessId') || !$request->has('accessCode') || !$api_authority || $api_authority->access_id != $request->accessId || $api_authority->access_code != $request->accessCode) {
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            $validator = Validator::make($request->all(), [
                'mst_company_id' => 'required',
                'esigned_flg' => 'nullable|integer|in:0,1',
                'signature_flg' => 'nullable|integer|in:0,1',
                'department_stamp_flg' => 'nullable|integer|in:0,1',
                'view_notification_email_flg' => 'nullable|integer|in:0,1',
                'updated_notification_email_flg' => 'nullable|integer|in:0,1',
                'enable_email_thumbnail' => 'nullable|integer|in:0,1',
                'ip_restriction_flg' => 'nullable|integer|in:0,1',
                'portal_flg' => 'nullable|integer|in:0,1',
                'mfa_flg' => 'nullable|integer|in:0,1',
                'template_flg' => 'nullable|integer|in:0,1',
                'template_search_flg' => 'nullable|integer|in:0,1',
                'phone_app_flg' => 'nullable|integer|in:0,1',
                'stamp_flg' => 'nullable|integer|in:0,1',
                'time_stamp_issuing_count' => 'nullable|integer|in:0,1',
                'long_term_storage_flg' => 'nullable|integer|in:0,1',
                'box_enabled' => 'nullable|integer|in:0,1',
                'received_only_flg' => 'nullable|integer|in:0,1',
                'rotate_angle_flg' => 'nullable|integer|in:0,1',
                'long_term_storage_option_flg' => 'nullable|integer|in:0,1',
                'scheduler_flg' => 'nullable|integer|in:0,1',
                'default_stamp_flg' => 'nullable|integer|in:0,1',
                'confidential_flg' => 'nullable|integer|in:0,1',
                'local_stamp_flg' => 'nullable|integer|in:0,1',
                'template_csv_flg' => 'nullable|integer|in:0,1',
                'template_edit_flg' => 'nullable|integer|in:0,1',  
                'attendance_flg' => 'nullable|integer|in:0,1',
                'convenient_flg' => 'nullable|integer|in:0,1',
                'multiple_department_position_flg' => 'nullable|integer|in:0,1',
                'user_plan_flg' => 'nullable|integer|in:0,1',
                'skip_flg' => 'nullable|integer|in:0,1',
                'circular_list_csv' => 'nullable|integer|in:0,1',
                'is_together_send' => 'nullable|integer|in:0,1',
                'enable_any_address_flg' => 'nullable|integer|in:0,1',
                'form_user_flg' => 'nullable|integer|in:0,1',
                'frm_srv_flg' => 'nullable|integer|in:0,1',
                'file_mail_flg' => 'nullable|integer|in:0,1',
                'faq_board_flg' => 'nullable|integer|in:0,1',
                'faq_board_limit_flg' => 'nullable|integer|in:0,1',
                'receive_plan_flg' => 'nullable|integer|in:0,1',
                'with_box_flg' => 'nullable|integer|in:0,1',
            ]);
            if ($validator->fails()) {
                $message = $validator->messages();
                $message_all = implode("", $message->all());
                return $this->ApiResponse($message_all, 0, '');
            }

            // 企業コード 存在チェック
            $company = DB::table('mst_company')->where('id', $request->mst_company_id)->first();

            if (!$company) {
                return $this->ApiResponse(__('message.false.api.mst_company_id_exist'), 0, '');
            }

            // 更新項目
            $updateList = array(); // mst_company
            $updateUserList = array(); // mst_user_info
            $updateProtectionList = array(); // mst_protection
            $updateAssignStampList = array(); // mst_assign_stamp
            $updateLimitList = array(); // mst_limit
            $updateConstraints = array();// mst_constraints
            // 制約マスタ
            $constraints = DB::table('mst_constraints')->where('mst_company_id', $request->mst_company_id)->first();

            //　PDFへの電子署名付加（esigned_flg）
            if ($request->has('esigned_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->esigned_flg != $request->esigned_flg) {
                    $updateList['esigned_flg'] = $request->esigned_flg;
                }
            }

            //　電子証明書設定（signature_flg）
            if ($request->has('signature_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->signature_flg != $request->signature_flg) {
                    $updateList['signature_flg'] = $request->signature_flg;
                }
            }

            //　部署名入り日付印（department_stamp_flg）
            if ($request->has('department_stamp_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->department_stamp_flg != $request->department_stamp_flg) {
                    $updateList['department_stamp_flg'] = $request->department_stamp_flg;
                }
            }

            //　閲覧通知メール設定（view_notification_email_flg）
            if ($request->has('view_notification_email_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->view_notification_email_flg != $request->view_notification_email_flg) {

                    $updateList['view_notification_email_flg'] = $request->view_notification_email_flg;

                    // mst_user_info
                    if ($request->view_notification_email_flg == 0) {
                        $updateUserList['browsed_notice_flg'] = $request->view_notification_email_flg;
                    }
                }
            }

            //　更新通知メール設定（updated_notification_email_flg）
            if ($request->has('updated_notification_email_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->updated_notification_email_flg != $request->updated_notification_email_flg) {

                    $updateList['updated_notification_email_flg'] = $request->updated_notification_email_flg;

                    // mst_user_info
                    if ($request->updated_notification_email_flg == 0) {
                        $updateUserList['update_notice_flg'] = $request->updated_notification_email_flg;
                    }
                }
            }

            //　メール内の文書のサムネイル表示（enable_email_thumbnail）
            if ($request->has('enable_email_thumbnail')) {
                // リクエストに設定ある場合、更新
                if ($company->enable_email_thumbnail != $request->enable_email_thumbnail) {
                    $updateList['enable_email_thumbnail'] = $request->enable_email_thumbnail;

                    // mst_protection
                    if ($request->enable_email_thumbnail == 0) {
                        $updateProtectionList['enable_email_thumbnail'] = $request->enable_email_thumbnail;
                    }
                }
            }

            //　接続IP制限（ip_restriction_flg）
            if ($request->has('ip_restriction_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->ip_restriction_flg != $request->ip_restriction_flg) {
                    $updateList['ip_restriction_flg'] = $request->ip_restriction_flg;
                }
            }

            //　ポータル機能（portal_flg）
            if ($request->has('portal_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->portal_flg != $request->portal_flg) {
                    $updateList['portal_flg'] = $request->portal_flg;
                }
            }

            //　多要素認証（mfa_flg）
            if ($request->has('mfa_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->mfa_flg != $request->mfa_flg) {
                    $updateList['mfa_flg'] = $request->mfa_flg;

                    // mst_user_info
                    if ($request->mfa_flg == 0) {
                        $updateUserList['mfa_type'] = $request->mfa_flg;
                    }
                }
            }

            //　テンプレート機能（template_flg）
            if ($request->has('template_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->template_flg != $request->template_flg) {
                    $updateList['template_flg'] = $request->template_flg;
                }
                if ($request->get('template_flg') == 0){
                    $updateList['template_search_flg'] = 0;
                    $updateList['template_csv_flg'] = 0;
                    $updateList['template_edit_flg'] = 0;
                    $updateUserList['template_flg'] = 0;
                }
            }

            //　テンプレート検索機能（template_search_flg）
            if ($request->has('template_search_flg')) {
                // リクエストに設定ある場合、更新
                if ($request->get('template_flg')){
                    if ($company->template_search_flg != $request->template_search_flg){
                        $updateList['template_search_flg'] = $request->template_search_flg;
                    }
                }else if ($request->has('template_flg') && $request->get('template_flg') == 0){
                    $updateList['template_search_flg'] = 0;
                }else if(!$request->has('template_flg')){
                    if ($company->template_flg && $company->template_search_flg != $request->template_search_flg){
                        $updateList['template_search_flg'] = $request->template_search_flg;
                    }
                }
            }

            //テンプレートcsv出力機能
            if ($request->has('template_csv_flg')){
                // テンプレート機能ON　＋　リクエストに設定ある場合、更新
                if ($request->get('template_flg')){
                    if ($company->template_csv_flg != $request->template_csv_flg){
                        $updateList['template_csv_flg'] = $request->template_csv_flg;
                    }
                }else if ($request->has('template_flg') && !$request->get('template_flg')){
                    $updateList['template_csv_flg'] = 0;
                }else if(!$request->has('template_flg')){
                    if ($company->template_flg && $company->template_csv_flg != $request->template_csv_flg){
                        $updateList['template_csv_flg'] = $request->template_csv_flg;
                    }
                }
            }

            //テンプレート編集機能
            if ($request->has('template_edit_flg')){
                // テンプレート機能ON　＋　リクエストに設定ある場合、更新
                if ($request->get('template_flg')){
                    if ($company->template_edit_flg != $request->template_edit_flg){
                        $updateList['template_edit_flg'] = $request->template_edit_flg;
                    }
                }else if ($request->has('template_flg') && !$request->get('template_flg')){
                    $updateList['template_edit_flg'] = 0;
                }else if(!$request->has('template_flg')){
                    if ($company->template_flg && $company->template_edit_flg != $request->template_edit_flg){
                        $updateList['template_edit_flg'] = $request->template_edit_flg;
                    }
                }
            }

            //　携帯アプリ
            if ($request->has('phone_app_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->phone_app_flg != $request->phone_app_flg) {
                    $updateList['phone_app_flg'] = $request->phone_app_flg;
                }
            }

            //　タイムスタンプ付署名
            if ($request->has('stamp_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->stamp_flg != $request->stamp_flg) {

                    $updateList['stamp_flg'] = $request->stamp_flg;

                    if ($request->stamp_flg == 0) {
                        // mst_user_info 配下利用者.タイムスタンプ発行権限無効に更新
                        $updateUserList['time_stamp_permission'] = $request->stamp_flg;
                        // mst_assign_stamp 配下利用者に割当済み印鑑.タイムスタンプ発行権限無効に更新
                        $updateAssignStampList['time_stamp_permission'] = $request->stamp_flg;
                        // mst_limit 制限設定.タイムスタンプ発行権限(全ユーザー)を無効に更新
                        $updateLimitList['time_stamp_permission'] = $request->stamp_flg;
                        //利用者側タイムスタンプ再付与機能
                        $updateList['time_stamp_assign_flg'] = 0;
                    }
                }
            }

            //　タイムスタンプ発行を自社でカウント（time_stamp_issuing_count）
            if ($request->has('time_stamp_issuing_count')) {
                // リクエストに設定ある場合、更新
                if ($company->time_stamp_issuing_count != $request->time_stamp_issuing_count) {
                    $updateList['time_stamp_issuing_count'] = $request->time_stamp_issuing_count;
                }
            }

            //　長期保管（long_term_storage_flg）
            if ($request->has('long_term_storage_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->long_term_storage_flg != $request->long_term_storage_flg
                    || ($request->has('max_usable_capacity') && $company->max_usable_capacity != $request->max_usable_capacity)) {
                    // 長期保管使用容量(GB)
                    if ($request->long_term_storage_flg == 0) {
                        // 長期保管利用なし場合、長期保管使用容量指定不可
                        if ($request->has('max_usable_capacity') && $request->max_usable_capacity != 0) {
                            return $this->ApiResponse(__('message.false.api.max_usable_capacity_value'), 0, '');
                        }
                        $updateList['long_term_storage_flg'] = $request->long_term_storage_flg;
                        $updateList['max_usable_capacity'] = 0;
                    } else {
                        // 長期保管利用あり場合、長期保管使用容量指定必須
                        if (!$request->has('max_usable_capacity')) {
                            // 未指定
                            return $this->ApiResponse(__('message.false.api.max_usable_capacity_value'), 0, '');
                        } else {
                            // 0／11桁整数字以外
                            $max_usable_capacity = $request->max_usable_capacity;
                            if (!preg_match("/^[1-9][0-9]*$/", $max_usable_capacity)
                                || strlen($max_usable_capacity) < 1
                                || strlen($max_usable_capacity) > 9
                            ) {
                                return $this->ApiResponse(__('message.false.api.max_usable_capacity_numeric'), 0, '');
                            }
                        }
                        $updateList['long_term_storage_flg'] = $request->long_term_storage_flg;
                        $updateList['max_usable_capacity'] = $request->max_usable_capacity;
                    }
                }

                // 会社のの長期保存 <> リクエストの長期保存　の場合
                if ($company->long_term_storage_flg != $request->long_term_storage_flg) {
                    // リクエストの長期保存 = 0　の場合
                    if ($request->long_term_storage_flg == 0) {
                        // 長期保存オプションフラグ設定値：0
                        $updateList['long_term_storage_option_flg'] = 0;
                        //利用者側タイムスタンプ再付与機能
                        $updateList['time_stamp_assign_flg'] = 0;
                    } else {
                        // リクエストに長期保存オプションフラグ設定ある場合（long_term_storage_option_flg）
                        if ($request->has('long_term_storage_option_flg')) {
                            // リクエストに設定ある場合、更新
                            if ($company->long_term_storage_option_flg != $request->long_term_storage_option_flg) {
                                $updateList['long_term_storage_option_flg'] = $request->long_term_storage_option_flg;
                                if ($request->long_term_storage_option_flg == 0){
                                    //利用者側タイムスタンプ再付与機能
                                    $updateList['time_stamp_assign_flg'] = 0;
                                }
                            }
                        }
                    }
                } else {
                    if ($company->long_term_storage_flg == 1) {
                        // リクエストに長期保存オプションフラグ設定ある場合（long_term_storage_option_flg）
                        if ($request->has('long_term_storage_option_flg')) {
                            // リクエストに設定ある場合、更新
                            if ($company->long_term_storage_option_flg != $request->long_term_storage_option_flg) {
                                $updateList['long_term_storage_option_flg'] = $request->long_term_storage_option_flg;
                                if ($request->long_term_storage_option_flg == 0){
                                    //利用者側タイムスタンプ再付与機能
                                    $updateList['time_stamp_assign_flg'] = 0;
                                }
                            }
                        }
                    }
                }
            } else {
                if ($company->long_term_storage_flg == 1) {
                    // リクエストに長期保存オプションフラグ設定ある場合（long_term_storage_option_flg）
                    if ($request->has('long_term_storage_option_flg')) {
                        // リクエストに設定ある場合、更新
                        if ($company->long_term_storage_option_flg != $request->long_term_storage_option_flg) {
                            $updateList['long_term_storage_option_flg'] = $request->long_term_storage_option_flg;
                            if ($request->long_term_storage_option_flg == 0){
                                //利用者側タイムスタンプ再付与機能
                                $updateList['time_stamp_assign_flg'] = 0;
                            }
                        }
                    }
                }
            }

            //　外部連携（box_enabled）
            if ($request->has('box_enabled')) {
                // リクエストに設定ある場合、更新
                if ($company->box_enabled != $request->box_enabled) {
                    $updateList['box_enabled'] = $request->box_enabled;
                }
            }

            //　受信のみ（received_only_flg）
            if ($request->has('received_only_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->received_only_flg != $request->received_only_flg) {
                    $updateList['received_only_flg'] = $request->received_only_flg;
                }
            }

            //　おじぎ印（rotate_angle_flg）
            if ($request->has('rotate_angle_flg')) {
                // リクエストに設定ある場合、更新
                if ($company->rotate_angle_flg != $request->rotate_angle_flg) {
                    $updateList['rotate_angle_flg'] = $request->rotate_angle_flg;
                    if ($request->rotate_angle_flg == 0){
                        $updateUserList['rotate_angle_flg'] = 0;
                        $updateUserList['default_rotate_angle'] = 0;
                }
            }
            }

            //　旧契約形態
            if (!CommonUtils::isNullOrEmpty($request->old_contract_flg)) {
                $updateList['old_contract_flg'] = $request->old_contract_flg;
            }

            //デフォルト印
            if ($request->has('default_stamp_flg')){
                // リクエストに設定ある場合、更新
                if ($company->default_stamp_flg != $request->default_stamp_flg){
                    $updateList['default_stamp_flg'] = $request->default_stamp_flg;
                }
            }

            //社外秘
            if ($request->has('confidential_flg')){
                // リクエストに設定ある場合、更新
                if ($company->confidential_flg != $request->confidential_flg){
                    $updateList['confidential_flg'] = $request->confidential_flg;
                }
            }

            //ローカル捺印
            if ($request->has('local_stamp_flg')){
                // リクエストに設定ある場合、更新
                if ($company->local_stamp_flg != $request->local_stamp_flg){
                    $updateList['local_stamp_flg'] = $request->local_stamp_flg;
                }
            }

            // 便利印
            $assign_convenient_off = false;
            if ($request->has('convenient_flg')){
                if ($company->convenient_flg != $request->convenient_flg){
                    $updateList['convenient_flg'] = $request->convenient_flg;
                    if ($request->convenient_flg == 0) {
                        $assign_convenient_off = true;
                        $updateList['convenient_upper_limit'] = 0;
                    }
                }
            }
    
            // 回覧一覧CSV出力
            if ($request->has('circular_list_csv')){
                if ($company->circular_list_csv != $request->circular_list_csv){
                    $updateList['circular_list_csv'] = $request->circular_list_csv;
                }
            }

            // 帳票発行文書数上限
            if ($request->has('max_frm_document')) {
                // リクエストに設定ある場合、更新
                if ($constraints->max_frm_document != $request->max_frm_document) {
                    $updateConstraints['max_frm_document'] = $request->max_frm_document;
                }
            }

            // 部署・役職複数登録
            if ($request->has('multiple_department_position_flg')){
                if ($company->multiple_department_position_flg != $request->multiple_department_position_flg){
                    $updateList['multiple_department_position_flg'] = $request->multiple_department_position_flg;
                    if ($request->multiple_department_position_flg == 0){
                        $updateUserList['mst_department_id_1'] = null;
                        $updateUserList['mst_department_id_2'] = null;
                        $updateUserList['mst_position_id_1'] = null;
                        $updateUserList['mst_position_id_2'] = null;
                    }
                }
            }

            // 合議機能
            if ($request->has('user_plan_flg')){
                if ($company->user_plan_flg != $request->user_plan_flg){
                    $updateList['user_plan_flg'] = $request->user_plan_flg;
                }
            }

            // スキップ機能
            if ($request->has('skip_flg')){
                if ($company->skip_flg != $request->skip_flg){
                    $updateList['skip_flg'] = $request->skip_flg;
                }
            }

            // 一斉送信
            if ($request->has('is_together_send')){
                if ($company->is_together_send != $request->is_together_send){
                    $updateList['is_together_send'] = $request->is_together_send;
                }
            }

            // 承認ルートのみに制限
            if ($request->has('enable_any_address_flg')){
                if ($company->enable_any_address_flg != $request->enable_any_address_flg){
                    $updateList['enable_any_address_flg'] = $request->enable_any_address_flg;
                }
            }
            // PAC_5-2924 S
            // 携帯アプリ
            if ($request->has('phone_app_flg')){
                if ($company->phone_app_flg != $request->phone_app_flg){
                    $updateList['phone_app_flg'] = $request->phone_app_flg;
                }
            }
            // 帳票専用利用企業
            if ($request->has('form_user_flg')){
                if ($company->form_user_flg != $request->form_user_flg){
                    $updateList['form_user_flg'] = $request->form_user_flg;
                }
            }
            // 帳票発行サービスの使用許可
            if ($request->has('frm_srv_flg')){
                if ($company->frm_srv_flg != $request->frm_srv_flg){
                    $updateList['frm_srv_flg'] = $request->frm_srv_flg;
                }
            }
            // PAC_5-2924 E

            // 受信専用プラン
            if ($request->has('receive_plan_flg')){
                if ($company->receive_plan_flg != $request->receive_plan_flg){
                    $updateList['receive_plan_flg'] = $request->receive_plan_flg;
                    if ($request->receive_plan_flg == 1){
                        $updateList['receive_plan_url'] = CommonUtils::getReceivePlanUrl($company->id);
                    }else{
                        $updateList['receive_plan_url'] = "";
                    }
                }
            }
            if (config("app.pac_app_env")){
                $updateList['receive_plan_flg'] = 0;
                $updateList['receive_plan_url'] = "";
            }

            //Box捺印
            if ($request->has('with_box_flg')){
                // リクエストに設定ある場合、更新
                if ($company->with_box_flg != $request->with_box_flg){
                    $updateList['with_box_flg'] = $request->with_box_flg;
                    if ($request->with_box_flg == 0) {
                        $updateLimitList['with_box_login_flg'] = 0;
                        $updateLimitList['shachihata_login_flg'] = 0;
                    }
                }
            }


            DB::beginTransaction();
            // mst_company
            if (count($updateList)) {
                $updateList['update_user'] = 'Shachihata';
                DB::table('mst_company')
                    ->where('id', $company->id)
                    ->update($updateList);
            }

            // mst_user_info
            $userInfoIds = DB::table('mst_user_info')
                ->join('mst_user', 'mst_user_info.mst_user_id', '=', 'mst_user.id')
                ->where('mst_user.mst_company_id', '=', $company->id)
                ->select('mst_user_info.mst_user_id')
                ->pluck('mst_user_info.mst_user_id')
                ->toArray();

            if (count($userInfoIds)) {
                if (count($updateUserList)) {
                    $updateUserList['update_user'] = 'Shachihata';
                    DB::table('mst_user_info')
                        ->whereIn('mst_user_id', $userInfoIds)
                        ->update($updateUserList);
                }
            }

            // mst_protection
            if (count($updateProtectionList)) {
                $updateProtectionList['update_user'] = 'Shachihata';
                DB::table('mst_protection')
                    ->where('mst_company_id', $company->id)
                    ->update($updateProtectionList);
            }

            // mst_assign_stamp
            if (count($userInfoIds)) {
                if (count($updateAssignStampList)) {
                    $updateAssignStampList['update_user'] = 'Shachihata';
                    DB::table('mst_assign_stamp')
                        ->whereIn('mst_user_id', $userInfoIds)
                        ->update($updateAssignStampList);
                }
                // 便利印OFFに更新する場合、割当済み便利印削除
                if ($assign_convenient_off){
                    DB::table('mst_assign_stamp')
                        ->whereIn('mst_user_id', $userInfoIds)
                        ->where('stamp_flg','=',StampUtils::CONVENIENT_STAMP)
                        ->where('state_flg','=',AppUtils::STATE_VALID)
                        ->update(['state_flg' => AppUtils::STATE_INVALID,
                            'delete_at' => Carbon::now(),
                            'update_at' =>  Carbon::now(),
                            'update_user' =>  'Shachihata',
                        ]);
                }
            }

            // mst_limit
            if (count($updateLimitList)) {
                $updateLimitList['update_user'] = 'Shachihata';
                DB::table('mst_limit')
                    ->where('mst_company_id', $company->id)
                    ->update($updateLimitList);
            }
            if ($request->has('enable_any_address_flg')){
                if ($company->enable_any_address_flg != $request->enable_any_address_flg && !$request->enable_any_address_flg){
                    DB::table('mst_limit')
                        ->where('mst_company_id', $company->id)
                        ->where('enable_any_address', AppUtils::STORAGE_ANY_ADDRESS_ROUTES)
                        ->update(['enable_any_address' => AppUtils::STORAGE_ANY_ADDRESS_DEFAULT,
                            'update_user' =>  'Shachihata',
                        ]);
                }
            }

            // mst_constraints
            if(count($updateConstraints)) {
                $updateConstraints['update_user'] = 'Shachihata';
                DB::table('mst_constraints')
                    ->where('mst_company_id', $company->id)
                    ->update($updateConstraints);
            }

            //スケジューラ(scheduler_flg)
            if ($request->has('scheduler_flg')) {
                // リクエストに設定ある場合、更新
                if(config('app.gw_use') == 1 && config('app.gw_domain')) {
                    $gw_app_schedule_id = '';
                    $gw_app_caldav_id = '';
                    $setting  = GwAppApiUtils::getCompanySetting($company->id);
                    $schedule_flg = $setting['scheduler_flg'];
                    $caldav_flg = $setting['caldav_flg'];
                    if ($schedule_flg){
                        $company_setting_ids = GwAppApiUtils::getCompanySettingId($company->id, $company->company_name, $company->state);
                        if (!$company_setting_ids){
                            Log::channel('trial-daily')->error('get appCompany portalId failed, please refer to laravel.log for detailed information');
                            return $this->ApiResponse(__('message.false.api.gw_company_setting'),0,'');
                        }
                        $gw_app_schedule_id = $company_setting_ids['schedule_id'];
                        $gw_app_caldav_id = $company_setting_ids['caldav_id'];
                    }

                    //スケジュールフラグ更新
                    if ($request->scheduler_flg){
                        // スケジューラー（有効にする）場合、無制限指定必須
                        if(!$request->has('scheduler_limit_flg')){
                            return $this->ApiResponse(__('message.false.api.scheduler_limit_flg_value'), 0, '');
                        }else{
                            // 無制限OFF場合、購入数指定必須
                            if(!$request->scheduler_limit_flg){
                                if(!$request->has('scheduler_buy_count')){
                                    return $this->ApiResponse(__('message.false.api.scheduler_buy_count_value'), 0, '');
                                }else{
                                    // 0／11桁整数字以外
                                    $scheduler_buy_count = $request->scheduler_buy_count;
                                    if (!preg_match("/^[1-9][0-9]*$/", $scheduler_buy_count)
                                        || strlen($scheduler_buy_count) < 1
                                        || strlen($scheduler_buy_count) > 9
                                    ) {
                                        return $this->ApiResponse(__('message.false.api.scheduler_buy_count_numeric'), 0, '');
                                    }elseif($scheduler_buy_count < 1){
                                        return $this->ApiResponse(__('message.false.api.scheduler_buy_count_value'), 0, '');
                                    }
                                }
                                $scheduler_limit_flg = $request->scheduler_limit_flg;
                                $scheduler_buy_count = $request->scheduler_buy_count;
                            }else{
                                // 無制限ON場合、購入数指定不可
                                if($request->has('scheduler_buy_count') && $request->scheduler_buy_count != 0){
                                    return $this->ApiResponse(__('message.false.api.scheduler_buy_count_value'), 0, '');
                                }
                                $scheduler_limit_flg = $request->scheduler_limit_flg;
                                $scheduler_buy_count = 0;
                            }
                        }
                        $upd_schedule_result = GwAppApiUtils::updateCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_SCHEDULE, $scheduler_limit_flg, $scheduler_buy_count);
                        if (!$upd_schedule_result){
                            Log::channel('trial-daily')->error('storeSchedule companyId failed, please refer to laravel.log for detailed information');
                            return $this->ApiResponse(__('message.false.api.upd_gw_company_setting_schedule'),0,'');
                        }
                    }elseif(!$request->scheduler_flg && $schedule_flg){
                        if ($gw_app_schedule_id){
                            $del_schedule_result = GwAppApiUtils::deleteCompanySetting($gw_app_schedule_id);
                            if (!$del_schedule_result){
                                Log::channel('trial-daily')->error('delSchedule gw_app_schedule_id failed, please refer to laravel.log for detailed information');
                                return $this->ApiResponse(__('message.false.api.del_gw_company_setting_schedule'),0,'');
                            }
                        }
                        if($caldav_flg){
                            if ($gw_app_caldav_id){
                                $del_caldav_result = GwAppApiUtils::deleteCompanySetting($gw_app_caldav_id);
                                if (!$del_caldav_result){
                                    Log::channel('trial-daily')->error('delcaldav gw_app_caldav_id failed, please refer to laravel.log for detailed information');
                                    return $this->ApiResponse(__('message.false.api.del_gw_company_setting_caldav'),0,'');
                                }
                            }
                        }
                    }
                }
            }
            /*PAC_5-2246 S*/
            if ($request->has('attendance_flg')){
                $setting = ApplicationAuthUtils::getCompanySetting($company->id);
                $attendance_flg = $setting['attendance_flg'];
                if ($request->attendance_flg) {
                    // スケジューラー（有効にする）場合、無制限指定必須
                    if (!$request->has('attendance_limit_flg')) {
                        return $this->ApiResponse(__('message.false.api.time_card_limit_flg_value'), 0, '');
                    } else {
                        // 無制限OFF場合、購入数指定必須
                        if (!$request->attendance_limit_flg) {
                            if (!$request->has('attendance_buy_count')) {
                                return $this->ApiResponse(__('message.false.api.time_card_buy_count_value'), 0, '');
                            } else {
                                // 0／11桁整数字以外
                                $attendance_buy_count = $request->attendance_buy_count;
                                if (!preg_match("/^[1-9][0-9]*$/", $attendance_buy_count)
                                    || strlen($attendance_buy_count) < 1
                                    || strlen($attendance_buy_count) > 9
                                ) {
                                    return $this->ApiResponse(__('message.false.api.time_card_buy_count_numeric'), 0, '');
                                } elseif ($attendance_buy_count < 1) {
                                    return $this->ApiResponse(__('message.false.api.time_card_buy_count_value'), 0, '');
                                }
                            }
                            $attendance_limit_flg = $request->attendance_limit_flg;
                            $attendance_buy_count = $request->attendance_buy_count;
                        } else {
                            // 無制限ON場合、購入数指定不可
                            if ($request->has('attendance_buy_count') && $request->attendance_buy_count != 0) {
                                return $this->ApiResponse(__('message.false.api.time_card_buy_count_value'), 0, '');
                            }
                            $attendance_limit_flg = $request->attendance_limit_flg;
                            $attendance_buy_count = 0;
                        }
                    }
                    $upd_attendance_result = ApplicationAuthUtils::storeCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_TIME_CARD, $attendance_limit_flg, $attendance_buy_count);
                    if (!$upd_attendance_result) {
                        Log::channel('trial-daily')->error('storeTimeCard companyId failed, please refer to laravel.log for detailed information');
                        return $this->ApiResponse(__('message.false.api.upd_gw_company_setting_time_card'), 0, '');
                    }
                } elseif (!$request->attendance_flg && $attendance_flg) {
                    ApplicationAuthUtils::deleteCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_TIME_CARD);
                }
            }
            /*PAC_5-2246 E*/
            //CalDAVフラグ(caldav_flg)
            if ($request->has('caldav_flg')){
                // リクエストに設定ある場合、更新
                if(config('app.gw_use') == 1 && config('app.gw_domain')) {
                    $setting  = GwAppApiUtils::getCompanySetting($company->id);
                    $schedule_flg = $setting['scheduler_flg'];
                    $caldav_flg = $setting['caldav_flg'];
                    //スケジューラー（有効にする）場合
                    if ($schedule_flg){
                        //グループウェア側の会社設定
                        $company_setting_ids = GwAppApiUtils::getCompanySettingId($company->id, $company->company_name, $company->state);
                        if (!$company_setting_ids){
                            Log::channel('trial-daily')->error('get appCompany portalId failed, please refer to laravel.log for detailed information');
                            return $this->ApiResponse(__('message.false.api.gw_company_setting'),0,'');
                        }
                        //グループウェア側のcaldav_id
                        $gw_app_caldav_id = $company_setting_ids['caldav_id'];
                        //API caldav_flg（有効にする） 場合
                        if ($request->get('caldav_flg')){
                            // CalDAV（有効にする）場合、無制限指定必須
                            if(!$request->has('caldav_limit_flg')){
                                return $this->ApiResponse(__('message.false.api.caldav_limit_flg_value'), 0, '');
                            }else {
                                // 無制限OFF場合、購入数指定必須
                                if (!$request->caldav_limit_flg) {
                                    // 0／11桁整数字以外
                                    $caldav_buy_count = $request->caldav_buy_count;
                                    if (!preg_match("/^[1-9][0-9]*$/", $caldav_buy_count) || strlen($caldav_buy_count) < 1 || strlen($caldav_buy_count) > 9) {
                                        return $this->ApiResponse(__('message.false.api.caldav_buy_count_numeric'), 0, '');
                                    }elseif($caldav_buy_count < 1){
                                        return $this->ApiResponse(__('message.false.api.caldav_buy_count_value'), 0, '');
                                    }
                                    $caldav_limit_flg = $request->caldav_limit_flg;
                                    $caldav_buy_count = $request->caldav_buy_count;
                                }else {
                                    // 無制限ON場合、購入数指定不可
                                    if($request->has('caldav_buy_count') && $request->caldav_buy_count != 0){
                                        return $this->ApiResponse(__('message.false.api.caldav_buy_count_value'), 0, '');
                                    }
                                    $caldav_limit_flg = $request->caldav_limit_flg;
                                    $caldav_buy_count = AppUtils::GW_APPLICATION_SCHEDULE_BUY_COUNT;
                                }
                                $upd_caldav_flg_result = GwAppApiUtils::storeCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_CALDAV, $caldav_limit_flg, $caldav_buy_count);
                                if (!$upd_caldav_flg_result){
                                    DB::rollBack();
                                    Log::channel('trial-daily')->error("CalDAVフラグ設定失敗しました, please refer to laravel.log for detailed information");
                                    return $this->ApiResponse(__('message.false.api.update_gw_caldav_flg'), 0, '');
                                }
                            }
                        }else{
                            //CalDAV（無効にする）場合
                            if ($caldav_flg && $gw_app_caldav_id){
                                $del_caldav_result = GwAppApiUtils::deleteCompanySetting($gw_app_caldav_id);
                                if (!$del_caldav_result){
                                    Log::channel('trial-daily')->error('delCalDAV gw_app_caldav_id failed, please refer to laravel.log for detailed information');
                                    return $this->ApiResponse(__('message.false.api.del_gw_company_setting_caldav'),0,'');
                                }
                            }
                        }
                    }
                }
            }

            // PAC_5-3100 ファイルメール便のAPI追加
            if ($request->has('file_mail_flg')){
                $setting = ApplicationAuthUtils::getCompanySetting($company->id);
                $file_mail_flg = $setting['file_mail_flg'];
                if ($request->file_mail_flg) {
                    // ファイルメール便（有効にする）場合、無制限指定必須
                    if (!$request->has('file_mail_limit_flg')) {
                        return $this->ApiResponse(__('message.false.api.file_mail_limit_flg_value'), 0, '');
                    } else {
                        // 無制限OFF場合、購入数指定必須
                        if (!$request->file_mail_limit_flg) {
                            if (!$request->has('file_mail_buy_count')) {
                                return $this->ApiResponse(__('message.false.api.file_mail_buy_count_value'), 0, '');
                            } else {
                                // 0／11桁整数字以外
                                $file_mail_buy_count = $request->file_mail_buy_count;
                                if (!preg_match("/^[1-9][0-9]*$/", $file_mail_buy_count)
                                    || strlen($file_mail_buy_count) < 1
                                    || strlen($file_mail_buy_count) > 9
                                ) {
                                    return $this->ApiResponse(__('message.false.api.file_mail_buy_count_numeric'), 0, '');
                                } elseif ($file_mail_buy_count < 1) {
                                    return $this->ApiResponse(__('message.false.api.file_mail_buy_count_value'), 0, '');
                                }
                            }
                            $file_mail_limit_flg = $request->file_mail_limit_flg;
                            $file_mail_buy_count = $request->file_mail_buy_count;
                        } else {
                            // 無制限ON場合、購入数指定不可
                            if ($request->has('file_mail_buy_count') && $request->file_mail_buy_count != 0) {
                                return $this->ApiResponse(__('message.false.api.file_mail_buy_count_value'), 0, '');
                            }
                            $file_mail_limit_flg = $request->file_mail_limit_flg;
                            $file_mail_buy_count = 0;
                        }
                    }
                    $upd_file_mail_result = ApplicationAuthUtils::storeCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL, $file_mail_limit_flg, $file_mail_buy_count);
                    if (!$upd_file_mail_result) {
                        Log::channel('trial-daily')->error('storeFileMail companyId failed, please refer to laravel.log for detailed information');
                        return $this->ApiResponse(__('message.false.api.upd_gw_company_setting_file_mail'), 0, '');
                    }
                } elseif (!$request->file_mail_flg && $file_mail_flg) {
                    ApplicationAuthUtils::deleteCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL);
                    ApplicationAuthUtils::deleteCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_FILE_MAIL_EXTEND);
                }
            }
            if ($request->has('faq_board_flg')){
                $setting = ApplicationAuthUtils::getCompanySetting($company->id);
                $faq_board_flg = $setting['faq_board_flg'];
                if ($request->faq_board_flg) {
                    // サポート掲示板（有効にする）場合、無制限指定必須
                    if (!$request->has('faq_board_limit_flg')) {
                        return $this->ApiResponse(__('message.false.api.faq_board_limit_flg_value'), 0, '');
                    } else {
                        // 無制限OFF場合、購入数指定必須
                        if (!$request->faq_board_limit_flg) {
                            if (!$request->has('faq_board_buy_count')) {
                                return $this->ApiResponse(__('message.false.api.faq_board_buy_count_value'), 0, '');
                            } else {
                                // 0／11桁整数字以外
                                $faq_board_buy_count = $request->faq_board_buy_count;
                                if (!preg_match("/^[1-9][0-9]*$/", $faq_board_buy_count)
                                    || strlen($faq_board_buy_count) < 1
                                    || strlen($faq_board_buy_count) > 9
                                ) {
                                    return $this->ApiResponse(__('message.false.api.faq_board_buy_count_numeric'), 0, '');
                                } elseif ($faq_board_buy_count < 1) {
                                    return $this->ApiResponse(__('message.false.api.faq_board_buy_count_value'), 0, '');
                                }
                            }
                            $faq_board_limit_flg = $request->faq_board_limit_flg;
                            $faq_board_buy_count = $request->faq_board_buy_count;
                        } else {
                            // 無制限ON場合、購入数指定不可
                            if ($request->has('faq_board_buy_count') && $request->faq_board_buy_count != 0) {
                                return $this->ApiResponse(__('message.false.api.faq_board_buy_count_value'), 0, '');
                            }
                            $faq_board_limit_flg = $request->faq_board_limit_flg;
                            $faq_board_buy_count = 0;
                        }
                    }
                    $upd_faq_board_result = ApplicationAuthUtils::storeCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD, $faq_board_limit_flg, $faq_board_buy_count);
                    if (!$upd_faq_board_result) {
                        Log::channel('trial-daily')->error('store サポート掲示板 companyId failed, please refer to laravel.log for detailed information');
                        return $this->ApiResponse(__('message.false.api.upd_gw_company_setting_faq_board'), 0, '');
                    }
                } elseif (!$request->faq_board_flg && $faq_board_flg) {
                    ApplicationAuthUtils::deleteCompanySetting($company->id, AppUtils::GW_APPLICATION_ID_FAQ_BOARD);
                }
            }
            // gw_flg
            if(config('app.gw_use') == 1 && config('app.gw_domain')) {
                $gw_settings = GwAppApiUtils::getCompanySetting($company->id);
                $setting_pac = ApplicationAuthUtils::getCompanySetting($company->id);
                $scheduler_flg = $gw_settings['scheduler_flg'];
                $caldav_flg = $gw_settings['caldav_flg'];
                $attendance_flg = $setting_pac['attendance_flg'];
                $file_mail_flg = $setting_pac['file_mail_flg'];
                $faq_board_flg = $setting_pac['faq_board_flg'];
                if($scheduler_flg || $caldav_flg || $attendance_flg || $file_mail_flg || $faq_board_flg){
                    DB::table('mst_company')->where('id', $company->id)->update(['gw_flg' => 1,'portal_flg' => 1]);
                }else{
                    DB::table('mst_company')->where('id', $company->id)->update(['gw_flg' => 0]);
                }
            }

            DB::commit();
            return $this->ApiResponse('', 1, '');
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * レスポンス
     * @param $result_message
     * @param $result_code
     * @param $result_data
     * @return \Illuminate\Http\JsonResponse
     */
    private function ApiResponse($result_message, $result_code, $result_data)
    {
        return response()->json(['result_code' => $result_code, 'result_message' => $result_message, 'result_data' => $result_data]);
    }

    /**
     * 初期パスワード取得
     * @return mixed
     */
    private function getPassword()
    {
        // トライアルユーザーに初期パスワード付与
        $randStr = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz123456789');
        $pass[0] = substr($randStr, 0, 8);
        $pass[1] = Hash::make($pass[0]);
        return $pass;
    }

    /**
     * 企業機能設定更新API(オプションの契約数、タイムスタンプ数)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDomainSettingSecond(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("updateDomainSettingSecond： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')
                ->where('api_name', 'HomePage')
                ->where('access_id', $request->accessId)
                ->where('access_code', $request->accessCode)
                ->first();
            if (!$api_authority) {
                Log::channel('trial-daily')->error(__('message.false.api.api_authentication'));
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // メールアドレス
            if (CommonUtils::isNullOrEmpty($request->domainid)) {
                Log::channel('trial-daily')->error('リクエストパラメータが不足しています。{domainid}');
                return $this->ApiResponse('リクエストパラメータが不足しています。{domainid}', 0, '');
            }

            $mst_company = Company::find($request->domainid);
            // PAC_5-2792 S
            $updateConstraints = array();// mst_constraints
            // 制約マスタ
            $constraints = DB::table('mst_constraints')->where('mst_company_id', $mst_company->id)->first();
            // PAC_5-2792 E
            $updateUserList = array(); // mst_user_info

            // オプションの契約数
            if (!CommonUtils::isNullOrEmpty($request->option_contract_count)) {
                if (!preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->option_contract_count)) {
                    Log::channel('trial-daily')->error('リクエストパラメータが不正です。{option_contract_count}');
                    return $this->ApiResponse('リクエストパラメータが不正です。{option_contract_count}', 0, '');
                }
                if ($request->option_contract_count == 0) {
                    $mst_company->option_contract_flg = 0;
                    $mst_company->option_contract_count = 0;
                } else {
                    $mst_company->option_contract_flg = 1;
                    $mst_company->option_contract_count = $request->option_contract_count;
                }
            }

            // タイムスタンプ数
            if (!CommonUtils::isNullOrEmpty($request->timestamps_count)) {
                if (!preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->timestamps_count)) {
                    Log::channel('trial-daily')->error('リクエストパラメータが不正です。{timestamps_count}');
                    return $this->ApiResponse('リクエストパラメータが不正です。{timestamps_count}', 0, '');
                }
                if ($request->timestamps_count == 0) {
                    $mst_company->stamp_flg = 0;
                    $mst_company->timestamps_count = 0;
                    $updateUserList['time_stamp_permission'] = 0;
                } else {
                    $mst_company->stamp_flg = 1;
                    $mst_company->timestamps_count = $request->timestamps_count;
                }
            }

            // 便利印契約数
            $assign_convenient_off = false;
            if (!CommonUtils::isNullOrEmpty($request->convenient_upper_limit)) {
                if (!preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->convenient_upper_limit)) {
                    Log::channel('trial-daily')->error('リクエストパラメータが不正です。{convenient_upper_limit}');
                    return $this->ApiResponse('リクエストパラメータが不正です。{convenient_upper_limit}', 0, '');
                }
                if ($request->convenient_upper_limit == 0) {
                    $assign_convenient_off = true;
                    $mst_company->convenient_flg = 0;
                    $mst_company->convenient_upper_limit = 0;
                } else {
                    $mst_company->convenient_flg = 1;
                    $mst_company->convenient_upper_limit = $request->convenient_upper_limit;
                }
            }

            if($assign_convenient_off){
                $userInfoIds = DB::table('mst_user_info')
                    ->join('mst_user', 'mst_user_info.mst_user_id', '=', 'mst_user.id')
                    ->where('mst_user.mst_company_id', '=', $mst_company->id)
                    ->select('mst_user_info.mst_user_id')
                    ->pluck('mst_user_info.mst_user_id')
                    ->toArray();

                if (count($userInfoIds)) {
                    // 便利印OFFに更新する場合、割当済み便利印削除
                    if ($assign_convenient_off){
                        DB::table('mst_assign_stamp')
                            ->whereIn('mst_user_id', $userInfoIds)
                            ->where('stamp_flg','=',StampUtils::CONVENIENT_STAMP)
                            ->where('state_flg','=',AppUtils::STATE_VALID)
                            ->update(['state_flg' => AppUtils::STATE_INVALID,
                                'delete_at' => Carbon::now(),
                                'update_at' =>  Carbon::now(),
                                'update_user' =>  'Shachihata',
                            ]);
                    }
                    if (count($updateUserList)) {
                        $updateUserList['update_user'] = 'Shachihata';
                        DB::table('mst_user_info')
                            ->whereIn('mst_user_id', $userInfoIds)
                            ->update($updateUserList);
                }
            }
            }

            // 名刺機能
            if (!CommonUtils::isNullOrEmpty($request->bizcard_flg)) {
                if (!preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->bizcard_flg)) {
                    Log::channel('trial-daily')->error('リクエストパラメータが不正です。{bizcard_flg}');
                    return $this->ApiResponse('リクエストパラメータが不正です。{bizcard_flg}', 0, '');
                }
                $mst_company->bizcard_flg = $request->bizcard_flg;
            }

            // 改ページプレビュー
            if (!CommonUtils::isNullOrEmpty($request->repage_preview_flg)) {
                if (!preg_match('/^([1-9]\d*|[0]{1,1})$/', $request->repage_preview_flg)) {
                    Log::channel('trial-daily')->error('リクエストパラメータが不正です。{repage_preview_flg}');
                    return $this->ApiResponse('リクエストパラメータが不正です。{repage_preview_flg}', 0, '');
                }
                $mst_company->repage_preview_flg = $request->repage_preview_flg;
            }

            // PAC_5-2792 S
            // 帳票発行文書数上限
            if ($request->has('max_frm_document')) {
                // リクエストに設定ある場合、更新
                if ($constraints->max_frm_document != $request->max_frm_document) {
                    $updateConstraints['max_frm_document'] = $request->max_frm_document;
                }
            }
            // PAC_5-2792 E
            // PAC_5-2924 S
            // 携帯アプリ
            if (!CommonUtils::isNullOrEmpty($request->phone_app_flg)) {
                $mst_company->phone_app_flg = $request->phone_app_flg;
            }
            // 帳票専用利用企業
            if (!CommonUtils::isNullOrEmpty($request->form_user_flg)) {
                $mst_company->form_user_flg = $request->form_user_flg;
            }
            // 帳票発行サービスの使用許可
            if (!CommonUtils::isNullOrEmpty($request->frm_srv_flg)) {
                $mst_company->frm_srv_flg = $request->frm_srv_flg;
            }
            // PAC_5-2924 E

            $mst_company->save();
            // mst_constraints
            if(count($updateConstraints)) {
                $updateConstraints['update_user'] = 'Shachihata';
                DB::table('mst_constraints')
                    ->where('mst_company_id', $mst_company->id)
                    ->update($updateConstraints);
            }

            return $this->ApiResponse('', 1, '');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * 有効ユーザー数と登録印面数を取得するAPI
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDomainInfo(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("getDomainInfo： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')
                ->where('api_name', 'HomePage')
                ->where('access_id', $request->accessId)
                ->where('access_code', $request->accessCode)
                ->first();
            if (!$api_authority) {
                Log::channel('trial-daily')->error(__('message.false.api.api_authentication'));
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // メールアドレス
            if (CommonUtils::isNullOrEmpty($request->domainid)) {
                Log::channel('trial-daily')->error('リクエストパラメータが不足しています。{domainid}');
                return $this->ApiResponse('リクエストパラメータが不足しています。{domainid}', 0, '');
            }

            $result = [];
            //有効ユーザー数
            $result['mst_user_count'] = DB::table('mst_user')->where('mst_company_id', $request->domainid)->where('option_flg', AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
            // 利用責任者
            $result['role_admin'] = DB::table('mst_admin')
                ->where('mst_company_id', $request->domainid)
                ->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                ->select('email','department_name','given_name','family_name')
                ->first();

            // 登録印面数
            $result['stamps_count'] = Company::getCompanyStampCount($request->domainid);
            //有効利用者の共通印割当
            $result['company_stamps_count'] = Company::getValidCommonStampsCount($request->domainid);
            //有効利用者の部署名入日付印割当
            $result['department_stamps_count'] = Company::getValidDepartmentStampCount($request->domainid);
            //有効利用者の氏名印割当
            $result['name_stamps_count'] = Company::getNameStampCount($request->domainid);
            //有効利用者の日付印割当
            $result['date_stamps_count'] = Company::getDateStampCount($request->domainid);
            //登録できるドメイン
            $company = Company::find($request->domainid);
            $result['domains'] = str_replace(PHP_EOL,',', $company ? $company->domain : '');

            return $this->ApiResponse('', 1, $result);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }

    /**
     * 共通印申込書codeリストを取得するAPI
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPdfNumber(Request $request)
    {
        try {
            Log::channel('trial-daily')->info("getPdfNumber： " . $request);

            //アクセス認証
            $api_authority = DB::table('api_authentication')
                ->where('api_name', 'HomePage')
                ->where('access_id', $request->accessId)
                ->where('access_code', $request->accessCode)
                ->first();
            if (!$api_authority) {
                Log::channel('trial-daily')->error(__('message.false.api.api_authentication'));
                return $this->ApiResponse(__('message.false.api.api_authentication'), 0, '');
            }

            // 企業ID
            if (CommonUtils::isNullOrEmpty($request->domainid)) {
                Log::channel('trial-daily')->error('リクエストパラメータが不足しています。{domainid}');
                return $this->ApiResponse('リクエストパラメータが不足しています。{domainid}', 0, '');
            }

            // 企業利用責任者情報取得
            $administrator = DB::table('mst_admin')->where('mst_company_id', $request->domainid)
                ->where('state_flg', AppUtils::STATE_VALID)
                ->where('role_flg', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                ->first();
            if (!$administrator) {
                return $this->ApiResponse('企業利用責任者情報取得に失敗しました。', 0, '');
            }

            // 共通印申込書code作成
            $pdfNumber =CommonUtils::getPdfNumberFirst();
            $pdfNumber .= strtoupper(substr(str_replace('-', '', Str::uuid()->toString()), 0, 12));
            $item = DB::table('mst_company_stamp_order_history')->where('create_at', '>=', Carbon::today())->orderBy('create_at', 'desc')->first();
            if ($item) {
                $number = (int)(substr($item->pdf_number, -4)) + 1;
                $pdfNumber .= sprintf("%04d", $number);
            } else {
                $pdfNumber .= '0001';
            }

            // 共通印申込書番号保存
            DB::table('mst_company_stamp_order_history')->insert([
                'pdf_number' => $pdfNumber,
                'mst_admin_id' => $administrator->id,
                'create_at' => Carbon::now(),
            ]);

            $result = [];
            // 共通印申込書番号
            $result['pdf_number'] = $pdfNumber;

            return $this->ApiResponse('', 1, $result);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() . $e->getTraceAsString();
            Log::channel('trial-daily')->error(__('message.false.api.system_error') . $errorMessage);
            return $this->ApiResponse(__('message.false.api.system_error'), 0, '');
        }
    }
}
