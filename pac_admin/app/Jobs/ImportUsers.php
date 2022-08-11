<?php

namespace App\Jobs;

use App\Http\Utils\CommonUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;
use Mockery\Exception;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\Validator;
use App\Models\Department;
use App\Models\CsvImportDetail;
use App\Models\CsvImportList;
use App\Models\User;
use GuzzleHttp\RequestOptions;
use App\Models\Company;
use App\Models\Position;
use App\Models\Stamp;
use App\Models\UserInfo;
use App\Models\AssignStamp;
use App\Models\Admin;
use App\Http\Utils\IdAppApiUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Utils\StampUtils;

class ImportUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    /**
     * ImportUsers constructor.
     * @param $id
     */
    public function __construct($id)
    {
        //
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $csv_import_list = CsvImportList::where('id', $this->id)->first();
        try {
            $csv_data = json_decode($csv_import_list->file_data);
            $user = Admin::where('id', $csv_import_list->user_id)->first();
            $create_user_name = $user->family_name . ' ' . $user->given_name;
            $company = Company::where('id', $csv_import_list->company_id)->first();
            // PAC_5-2308
            // 旧契約形態ONでBusiness選択時にCSV取込した場合ライセンス契約数までしか有効ユーザーにならない
            // 旧契約のBusinessはライセンス契約数に上限がないため、全ユーザー有効で登録される
            $boolCurrentCompanyFlg = false;
            if ($company && $company->old_contract_flg && $company->contract_edition == 1){
                $boolCurrentCompanyFlg = true;
            }
            $listPosition = Position::where('state', 1)->where('mst_company_id', $user->mst_company_id)->pluck('id', 'position_name')->toArray();

            $total = count($csv_data);
            $num_error = 0;// 失敗件数
            $num_normal = 0;// 成功件数
            $arrErrorMsg = []; //CSV取込失敗メッセージ
            if ($total) {
                $mapDataUser = []; //CSV User data
                $mapDataUserInfo = []; //CSV UserInfo data
                $mapStampId = []; //stamp id 行列
                $importEmails = []; //CSV email 行列
                $importEmailsWithRow = []; //CSV email with 行

                // idm app client
                $client = IdAppApiUtils::getAuthorizeClient();

                //domainリスト出力
                $domains = preg_split('/\r\n|\r|\n/', $company->domain);
                if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER){
                    foreach ($domains as $domain){
                        $domains[] = "$domain.scs";
                    }
                }

                // 1行1行 check
                foreach ($csv_data as $i => $row) {
                    $email = strtolower(AppUtils::utf8_filter($row[0]));
                    if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER) {
                        $email = substr($email, -4) == '.scs' ? $email : "$email.scs";
                    }
                    if (!$client) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => '統合ID接続失敗'];
                        continue;
                    }
                    if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                        continue;
                    }
                    // パラメータ数チェック
                    if (count($row) < 15) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => '行が正しくありません'];
                        continue;
                    }

                    // (0)メールアドレス(xxx@domain.co.jp形式),(1)姓,(2)名,(3)部署,(4)役職,(5)郵便番号,(6)住所,
                    // (7)電話番号,(8)FAX番号,
                    // (9)ホームページ追加
                    // (10)印面設定(※),(11)印面文字,(12)有効化(1:有効にする),
                    // (13)日付印の日付変更(0:変更不可),(14)APIの使用(0:許可しない),
                    // (15)多要素認証(0:無効,1:メール,2:QRコード),
                    // (16)認証コード送信先(0:登録メールアドレス,1:その他),
                    // (17)認証コード送信先メールアドレス(xxx@domain.co.jp形式)

                    $data_user = [
                        'email' => $email,
                        'family_name' => mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[1])),
                        'given_name' => mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[2])),
                        'state_flg' => $row[12],
                        'mst_company_id' => $user->mst_company_id
                    ];

                    $data_user_info = [
                        'postal_code' => $row[5],
                        'address' => $row[6],
                        'phone_number' => $row[7],
                        'fax_number' => $row[8],
                        'date_stamp_config' => $row[13],
                        'api_apps' => $row[14],
                        'approval_request_flg' => 1,
                        // 企業設定と同じように設定する
                        'browsed_notice_flg' => $company ? $company->view_notification_email_flg : 0,
                        'update_notice_flg' => $company ? $company->updated_notification_email_flg : 0,
                        'template_flg' => !empty($row[18]) && in_array($row[18],[0,1]) ? $row[18] : 0,
                        'rotate_angle_flg' => !empty($row[19]) && in_array($row[19],[0,1]) ? $row[19] : 0,

                    ];

                    // 多要素認証の設定
                    if ($company->mfa_flg) {
                        if (isset($row[15])) {
                            // メールアドレス無しユーザーの場合、多要素認証「1:メール」使用不可
                            if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER && $row[15] == 1) {
                                $num_error++;
                                $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => '多要素認証が「1」以外文字を入力してください'];
                                continue;
                            }
                            $data_user_info['mfa_type'] = $row[15];
                        } else {
                            $data_user_info['mfa_type'] = 0;
                        }
                        if (isset($row[16]) && $csv_import_list->import_type != AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER) {
                            $data_user_info['email_auth_dest_flg'] = $row[16];
                        } else {
                            $data_user_info['email_auth_dest_flg'] = 0;
                        }
                        if (isset($row[17])) {
                            $data_user_info['auth_email'] = $csv_import_list->import_type != AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER ? $row[17] : '';
                        }
                    } else {
                        $data_user_info['mfa_type'] = 0;
                        $data_user_info['email_auth_dest_flg'] = 0;
                    }

                    $stamp_id = null;
                    $stamp_setting = intval(trim($row[10]));
                    $stamp_name = mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[11]);
                    $stamp_name = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $stamp_name);

                    if ($stamp_setting and $stamp_name) {

                        // 印面文字半角チェック
                        if (!AppUtils::jpn_zenkaku_only($stamp_name)) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => '印面文字に全角文字を入力してください'];
                            continue;
                        }
                        $division = $stamp_setting > 3 ? 1 : 0;
                        $font = ($stamp_setting - 1) % 3;

                        // 印面上限チェック：
                        if ($company->old_contract_flg) {
                            //旧契約形態ON　&& Standarad ：上限がイセンス契約数
                            //旧契約形態ON　&& Business、Business Pro、trial ：上限なし
                            if ($company->contract_edition == 0 && Company::getGreaterThanByCompanyLimitAndUserCount($user->mst_company_id)) {
                                $num_error++;
                                $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => __('message.stamp_limit_csv')];
                                continue;
                            }
                        } else {
                            //旧契約形態OFF　&& Standarad、Business、Business Pro ：上限がイセンス契約数
                            //旧契約形態OFF　&& trial ：上限なし
                            if (in_array($company->contract_edition, [0, 1, 2]) && Company::getGreaterThanByCompanyLimitAndUserCount($user->mst_company_id)) {
                                $num_error++;
                                $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => __('message.stamp_limit_csv')];
                                continue;
                            }
                        }

                        // 印面作成
                        $stamp = AppUtils::searchStamp($stamp_name, $division, $font);

                        // 失敗の場合
                        if (is_numeric($stamp)) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => '利用者印面作成失敗しました。'];
                            continue;
                        }

                        // TODO insert batch ?
                        // ハンコ解像度調整
                        $stamp->contents = StampUtils::stampClarity(base64_decode($stamp->contents));

                        $stamp_id = Stamp::insertGetId(['stamp_name' => $stamp_name, 'stamp_division' => $division, 'font' => $font,
                            'stamp_image' => $stamp->contents,
                            'width' => floatval($stamp->realWidth) * 100, 'height' => floatval($stamp->realHeight) * 100,
                            'date_x' => $stamp->datex, 'date_y' => $stamp->datey,
                            'date_width' => $stamp->datew, 'date_height' => $stamp->dateh,
                            'create_user' => $create_user_name, 'serial' => ''
                        ]);

                        Stamp::where('id', $stamp_id)->update(['serial' => AppUtils::generateStampSerial(AppUtils::STAMP_FLG_NORMAL, $stamp_id)]);
                    }

                    $department = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[3]));
                    $position = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[4]));

                    $m_user = new User();
                    $validator = Validator::make($data_user, $m_user->rules(0, false));
                    $infoRules = [
                        'api_apps' => 'required|boolean',
                        'phone_number' => 'max:15|regex:/^[0-9-]{0,15}$/',
                        'fax_number' => 'max:15|regex:/^[0-9-]{0,15}$/',
                        'postal_code' => 'max:10|regex:/^[0-9-]{0,10}$/',
                    ];
                    if ($company->mfa_flg) {
                        $infoRules = array_merge($infoRules, [
                            'mfa_type' => 'required|numeric|min:0|max:2',
                            'email_auth_dest_flg' => 'required|boolean',
                            'auth_email' => ($data_user_info['email_auth_dest_flg'] ? 'required' : 'nullable') . '|email|max:256',
                        ]);
                    }
                    $infoValidator = Validator::make($data_user_info, $infoRules);
                    if ($validator->fails() || $infoValidator->fails()) {
                        $message = $validator->messages()->merge($infoValidator->messages());
                        $message_all = $message->all();
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => $message_all[0]];
                        continue;
                    }

                    if ($department) {
                        $obj_depart = new Department();
                        $departmentId = $obj_depart->detectFromName(explode(\App\Http\Utils\AppUtils::SPERATOR_SPLIT, $department), $user->mst_company_id);
                        if (!$departmentId) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => __('message.not_detected', ['attribute' => $department])];
                            continue;
                        }
                    } else {
                        $departmentId = null;
                    }
                    if ($position) {
                        $positionId = isset($listPosition[$position]) ? $listPosition[$position] : false;
                        if (!$positionId) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => __('message.not_detected', ['attribute' => $position])];
                            continue;
                        }
                    } else {
                        $positionId = null;
                    }
                    $data_user_info['mst_department_id'] = $departmentId;
                    $data_user_info['mst_position_id'] = $positionId;

                    if (in_array($data_user['email'], $importEmails, true)) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています'];
                        continue;
                    }

                    // 他の企業はemailが存在します。
                    $exist = User::where('state_flg', '!=', AppUtils::STATE_DELETE)->where('email', '=', $data_user['email'])->where('mst_company_id', '!=', $user->mst_company_id)->first();
                    if ($exist) {
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています'];
                        continue;
                    }

                    // PAC_5-1436 インポート時ドメインチェック
                    if( preg_match("/(@.*)/u", $email, $importDomain) ){
                        if(!in_array($importDomain[0],$domains)){
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => 'ドメインの登録がありません'];
                            continue;
                        }
                    }

                    $importEmails[] = $data_user['email'];
                    $importEmailsWithRow[$i] = $data_user['email'];
                    $mapDataUser[$data_user['email']] = $data_user;
                    $mapDataUserInfo[$data_user['email']] = $data_user_info;
                    if ($stamp_id) {
                        // 挿入のstamp id
                        $mapStampId[$data_user['email']] = $stamp_id;
                    }
                }
                // csv email はDBに存在します => 行列(key:email,value:user model)  update
                $mapDbUser = User::where('state_flg', '!=', AppUtils::STATE_DELETE)->whereIn('email', $importEmails)->get()->keyBy('email');
                // csv email はDBに存在します 行列(id)  insert
                $dbUserIds = [];
                foreach ($mapDbUser as $dbUser) {
                    $dbUserIds[] = $dbUser->id;
                }

                $mapDbUserInfo = UserInfo::whereIn('mst_user_id', $dbUserIds)->get()->keyBy('mst_user_id');

                // ループ CSV data
                foreach ($mapDataUser as $email => $dataUser) {
                    if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_USER){
                        $dataUser['without_email_flg'] = AppUtils::WITHOUT_EMAIL_T;
                    }
                    // check if exist
                    $itemOld = null;
                    $dataUserInfo = $mapDataUserInfo[$email];
                    if ($mapDbUser->has($email)) {
                        $itemOld = $mapDbUser[$email];
                    }
                    //有効ユーザーの数
                    $valid_user_count = User::where('mst_company_id',$user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)
                        ->where('state_flg',AppUtils::STATE_VALID)->count();

                    // status再判定
                    if ($itemOld) {
                        // 更新
                        // 状態有効
                        if ($dataUser['state_flg']) {
                            //帳票発行機能専用ユーザ 最大数が5人
                            if ($company->form_user_flg && in_array($itemOld->state_flg,[AppUtils::STATE_INVALID_NOPASSWORD,AppUtils::STATE_INVALID])
                                && $valid_user_count >= AppUtils::MAX_FORM_USER_COUNT){
                                $dataUser['state_flg'] = 0;
                            }else{
                                // 印面指定なし場合、
                                if (!key_exists($email, $mapStampId)) {
                                    $item = User::where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
                                    $itemStamps = $item->getStamps($item->id);
                                    // 既存印面場合、無効で更新
                                    if ($company->default_stamp_flg) {
                                        // PAC_5-2544 START
                                        if (count($itemStamps['stampMaster']) + count($itemStamps['stampDepartment'])  == 0 ) {
                                            // get stamp master
                                            $intCountStampMaster = AssignStamp::where([
                                                    'stamp_flg'=> AppUtils::STAMP_FLG_COMPANY,
                                                    'mst_user_id'=>$item->id,
                                                    'state_flg'=>AppUtils::STATE_VALID]
                                            )
                                                ->select('id as assign_id','stamp_id','stamp_flg')
                                                ->with('stampMaster')->count();

                                            if(empty($intCountStampMaster)){
                                                $dataUser['state_flg'] = 0;
                                            }
                                        }
                                        // PAC_5-2544 END
                                    } else {
                                        if ((count($itemStamps['stampMaster']) + count($itemStamps['stampCompany']) + count($itemStamps['stampDepartment'])) == 0) {
                                            $dataUser['state_flg'] = 0;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // 登録
                        // 状態有効＋印面指定なし場合、無効で登録
                        if ($dataUser['state_flg']) {
                            if (!key_exists($email, $mapStampId) || ($company->form_user_flg && $valid_user_count >= AppUtils::MAX_FORM_USER_COUNT)) {
                                $dataUser['state_flg'] = 0;
                            }
                        }
                    }
                    // 有効 -> 無効更新時、invalid_atを設定
                    if ($itemOld) {
                        // 更新
                        if ($dataUser['state_flg'] == AppUtils::STATE_INVALID || $dataUser['state_flg'] == AppUtils::STATE_INVALID_NOPASSWORD) {
                            if ($itemOld->state_flg == AppUtils::STATE_VALID) {
                                $dataUser['invalid_at'] = Carbon::now();
                            }
                        } elseif ($dataUser['state_flg'] == AppUtils::STATE_VALID) {
                            $dataUser['invalid_at'] = null;
                        }
                    }

                    // PAC_5-1577 CSV取込で印面数チェックの追加
                    $intStampStatus = Company::getGEByCompanyLimitAndUserCount($company->id);
                    $intStampTotal = Company::getCompanyStampCount($company->id);
                    if((($company->old_contract_flg && $company->contract_edition == 0) || (!$company->old_contract_flg && $company->contract_edition != 3)) && !empty($mapStampId[$email])){
                        if($intStampStatus == 1 && !$boolCurrentCompanyFlg){
                            $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                        }
                        $intStampTotal = $intStampTotal + 1;
                        if($intStampTotal > $company->upper_limit && !$boolCurrentCompanyFlg){
                            $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                        }
                    }

                    $apiUser = [
                        "email" => strtolower($email),
                        "contract_app" => config('app.pac_contract_app'),
                        "app_env" => config('app.pac_app_env'),
                        "contract_server" => config('app.pac_contract_server'),
                        "user_auth" => AppUtils::AUTH_FLG_USER,
                        "user_first_name" => $dataUser['given_name'],
                        "user_last_name" => $dataUser['family_name'],
                        "company_name" => $company->company_name,
                        "company_id" => $company->id,
                        "update_user_email" => $user->email,
                        "user_email" => strtolower($email),
                        "status" => AppUtils::convertState($dataUser['state_flg']),
                        "system_name" => $company->system_name
                    ];
                    if ($itemOld) {
                        // 更新の場合
                        $mst_user_id = $itemOld->id;

                        // 有効ユーザー数印面チェック：旧契約形態OFF && オプションフラグがON （上限：有効ユーザー上限がオプション契約数）
                        if ($company && !$company->old_contract_flg && $company->option_contract_flg && $dataUser['state_flg'] == AppUtils::STATE_VALID &&$itemOld->state_flg !=AppUtils::STATE_VALID ) {
                            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_F)->where('state_flg', AppUtils::STATE_VALID)->count();
                            if ($mst_user_count + 1 > $company->option_contract_count) {
                                // # PAC_5-2309 CSV取込したとき登録ユーザーがオプション契約数を超えた場合、超過したユーザーを無効状態で登録したい
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($dataUser['state_flg']);
                            }
                        }

                        //PAC_5-2476
                        //旧契約形態ON　&& Bussiness : CSV取込したとき登録ユーザーがライセンス契約数を超えた場合、超過したユーザーを無効状態で登録したい
                        if ($company->old_contract_flg){
                            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('state_flg', AppUtils::STATE_VALID)->count();
                            if ($dataUser['state_flg'] == AppUtils::STATE_VALID && $company->contract_edition == 1 && $mst_user_count + 1 > $company->upper_limit) {
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($dataUser['state_flg']);
                            }
                        }

                        if($company && $dataUser['state_flg'] == AppUtils::STATE_VALID && $itemOld->state_flg !=AppUtils::STATE_VALID && !$boolCurrentCompanyFlg &&
                            (($company->old_contract_flg && $company->contract_edition == 0) || (!$company->old_contract_flg && $company->contract_edition != 3))){
                            $arrAllStampData = (new User())->getStamps($mst_user_id);
                            if(($intStampTotal+count($arrAllStampData['stampMaster']) + count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) + count($arrAllStampData['stampWaitDepartment'])) > $company->upper_limit){
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($dataUser['state_flg']);
                            }
                        }

                        // 便利印
                        if ($company && $company->contract_edition != 3 && $company->convenient_flg == 1 && $dataUser['state_flg'] == AppUtils::STATE_VALID && $itemOld->state_flg !=AppUtils::STATE_VALID) {
                            $companyConvenientStampCount = Company::getCompanyConvenientStampCount($company->id);
                            $arrAllStampData = (new User())->getStamps($mst_user_id);
                            if (($companyConvenientStampCount + count($arrAllStampData['convenientStamp'])) > $company->convenient_upper_limit) {
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($dataUser['state_flg']);
                            }
                        }

                        $itemOld->fill($dataUser);
                        $itemOld['update_user'] = $create_user_name;
                        $itemOld['update_at'] = Carbon::now();

                        if ($mapDbUserInfo && $mapDbUserInfo->has($mst_user_id)) {
                            $itemInfo = $mapDbUserInfo[$mst_user_id];
                            $itemInfo['update_user'] = $create_user_name;
                            $itemInfo['update_at'] = Carbon::now();
                        } else {
                            // user info 存在しません、追加
                            $itemInfo = new UserInfo();
                            $itemInfo['mst_user_id'] = $mst_user_id;
                            $itemInfo['create_user'] = $create_user_name;
                            $itemInfo['create_at'] = Carbon::now();
                        }
                        // PAC_5-1907 利用者CSV取込で「メール受信設定」のチェックが入る
                        unset($dataUserInfo['approval_request_flg']);
                        unset($dataUserInfo['browsed_notice_flg']);
                        unset($dataUserInfo['update_notice_flg']);
                        $itemInfo->fill($dataUserInfo);

                        $apiUser['update_user_email'] = $user->email;

                        // 統合更新
                        $result = $client->put("users", [
                            RequestOptions::JSON => $apiUser
                        ]);

                        if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            // $response = json_decode((string) $result->getBody());
                            DB::beginTransaction();
                            try {

                                $itemOld->save(); //
                                $itemInfo->save();

                                if (key_exists($email, $mapStampId) && $mapStampId[$email]) {
                                    DB::table('mst_assign_stamp')->insert([
                                        'stamp_id' => $mapStampId[$email],
                                        'mst_user_id' => $mst_user_id,
                                        'display_no' => 0,
                                        'stamp_flg' => AppUtils::STAMP_FLG_NORMAL,
                                        'create_user' => $create_user_name,
                                        'state_flg' => AppUtils::STATE_VALID
                                    ]);
                                }
                                $num_normal += 1;
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Log::channel('import-csv-daily')->error("(list id:" . $csv_import_list->id . ")" . $e->getMessage() . $e->getTraceAsString());
                                $apiUser = [
                                    "email" => strtolower($email),
                                    "contract_app" => config('app.pac_contract_app'),
                                    "app_env" => config('app.pac_app_env'),
                                    "contract_server" => config('app.pac_contract_server'),
                                    "user_auth" => AppUtils::AUTH_FLG_USER,
                                    "user_first_name" => $mapDbUser[$email]['given_name'],
                                    "user_last_name" => $mapDbUser[$email]['family_name'],
                                    "company_name" => $company->company_name,
                                    "company_id" => $company->id,
                                    "update_user_email" => $user->email,
                                    "user_email" => strtolower($email),
                                    "status" => AppUtils::convertState($mapDbUser[$email]['state_flg']),
                                    "system_name" => $company->system_name
                                ];
                                // 統合削除
                                $result = $client->put("users", [RequestOptions::JSON => $apiUser]);
                                if ($result->getStatusCode() != 200) {
                                    Log::channel('import-csv-daily')->info("deleteInsertFail response: " . $result->getBody());
                                }
                                $num_error++;
                                $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => $e->getMessage()];
                            }
                        } else if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_CONFLICT) {
                            $num_error += 1;
                            $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています。'];
                        } else {
                            Log::channel('import-csv-daily')->info("Call ID App Api to update import user failed(list id:" . $csv_import_list->id . "). Response Body " . $result->getBody());
                            $num_error += 1;
                            $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => '統合ID連携失敗'];
                        }
                    } else {
                        // 新規の場合
                        $dataUser['login_id'] = Str::uuid()->toString();
                        $dataUser['system_id'] = 0;
                        $dataUser['amount'] = 0;
                        $dataUser['password'] = "";

                        $apiUser['create_user_email'] = $user->email;

                        // 有効ユーザー数印面チェック：旧契約形態OFF && オプションフラグがON （上限：有効ユーザー上限がオプション契約数）
                        if ($company && !$company->old_contract_flg && $company->option_contract_flg && $dataUser['state_flg'] == AppUtils::STATE_VALID && !$boolCurrentCompanyFlg) {
                            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg', AppUtils::STATE_VALID)->count();
                            if ($mst_user_count + 1 > $company->option_contract_count) {
                                // # PAC_5-2309 CSV取込したとき登録ユーザーがオプション契約数を超えた場合、超過したユーザーを無効状態で登録したい
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($dataUser['state_flg']);
                            }
                        }

                        //PAC_5-2476
                        //旧契約形態ON　&& Bussiness : CSV取込したとき登録ユーザーがライセンス契約数を超えた場合、超過したユーザーを無効状態で登録したい
                        if ($company->old_contract_flg){
                            $mst_user_count = DB::table('mst_user')->where('mst_company_id', $user->mst_company_id)->where('state_flg', AppUtils::STATE_VALID)->count();
                            if ($dataUser['state_flg'] == AppUtils::STATE_VALID && $company->contract_edition == 1 && $mst_user_count + 1 > $company->upper_limit) {
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($dataUser['state_flg']);
                            }
                        }

                        // idm api(store) 統合追加
                        $result = $client->post("users", [
                            RequestOptions::JSON => $apiUser
                        ]);
                        if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                            //$response = json_decode((string) $result->getBody());
                            DB::beginTransaction();
                            try {
                                //insert mst_user
                                $dataUser['create_user'] = $create_user_name;
                                $dataUser['create_at'] = Carbon::now();
                                User::insert($dataUser);
                                $insertUser = User::where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();

                                // insert mst_user_info
                                $dataUserInfo['mst_user_id'] = $insertUser->id;
                                $dataUserInfo['create_user'] = $create_user_name;
                                $dataUserInfo['create_at'] = Carbon::now();
                                UserInfo::insert($dataUserInfo);
                                if (key_exists($email, $mapStampId) && $mapStampId[$email]) {
                                    AssignStamp::insert([
                                        'stamp_id' => $mapStampId[$email],
                                        'mst_user_id' => $insertUser->id,
                                        'display_no' => 0,
                                        'stamp_flg' => AppUtils::STAMP_FLG_NORMAL,
                                        'create_user' => $create_user_name,
                                        'state_flg' => AppUtils::STATE_VALID
                                    ]);
                                }

                                DB::commit();
                                $num_normal += 1;
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Log::channel('import-csv-daily')->error("(list id:" . $csv_import_list->id . ")" . $e->getMessage() . $e->getTraceAsString());

                                $apiUser['update_user_email'] = $user->email;
                                $apiUser['status'] = AppUtils::convertState(AppUtils::STATE_DELETE);
                                // 統合削除
                                $result = $client->put("users", [RequestOptions::JSON => $apiUser]);
                                if ($result->getStatusCode() != 200) {
                                    Log::channel('import-csv-daily')->error("deleteInsertFail response: " . $result->getBody());
                                }

                                $num_error += 1;
                                $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => $e->getMessage()];
                            }
                        } else if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_CONFLICT) {
                            $num_error += 1;
                            $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています。'];
                        } else {
                            Log::channel('import-csv-daily')->error("Call ID App Api to insert import user failed(list id:" . $csv_import_list->id . "). Response Body " . $result->getBody());
                            $num_error += 1;
                            $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => '統合ID連携失敗'];
                        }
                    }
                }

                // csv取込履歴追加
                $csv_import_list->success_num = $num_normal;
                $csv_import_list->failed_num = $num_error;
                $csv_import_list->total_num = $num_normal + $num_error;
                $csv_import_list->result = count($arrErrorMsg) == 0 ? 1 : 0; // 0:failed;1:success
                $csv_import_list->update_at = Carbon::now();
                $csv_import_list->save();

                $failed_rows = ""; // 失敗した行目
                // csv取込履歴詳細
                foreach ($arrErrorMsg as $error) {
                    if ($failed_rows == "") {
                        $failed_rows .= $error['row'];
                    } else {
                        $failed_rows = $failed_rows . ',' . $error['row'];
                    }
                    $csv_import_detail = new CsvImportDetail();
                    $csv_import_detail->list_id = $csv_import_list->id; // CSVリストのID
                    $csv_import_detail->row_id = $error['row']; // CSV行目
                    $csv_import_detail->email = $error['email']; // メールアドレス
                    $csv_import_detail->comment = $error['comment']; // コメント
                    $csv_import_detail->create_at = date("Y-m-d H:i:s");
                    $csv_import_detail->save();
                }
            }
        } catch (\Exception $e) {
            Log::channel('import-csv-daily')->error("(list id:" . $csv_import_list->id . ")" . $e->getMessage() . $e->getTraceAsString());
            $csv_import_list->result = 0; // 0:failed;1:success
            $csv_import_list->update_at = Carbon::now();
            $csv_import_list->save();
        }
    }
}
