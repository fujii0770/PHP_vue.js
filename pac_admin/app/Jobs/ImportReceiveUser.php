<?php

namespace App\Jobs;

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\OptionUserUtils;
use App\Http\Utils\StampUtils;
use App\Models\Admin;
use App\Models\AssignStamp;
use App\Models\Company;
use App\Models\CsvImportDetail;
use App\Models\CsvImportList;
use App\Models\Department;
use App\Models\Position;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserInfo;
use Carbon\Carbon;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportReceiveUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    /**
     * Create a new job instance.
     *
     * @return void
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

            $listPosition = Position::where('state', 1)->where('mst_company_id', $user->mst_company_id)->pluck('id', 'position_name')->toArray();//役職

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
                if($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_RECEIVE_USER){
                    foreach ($domains as $domain){
                        $domains[] = "$domain.wf.scs";
                    }
                }

                // 1行1行 check
                foreach ($csv_data as $i => $row) {
                    $email = strtolower(AppUtils::utf8_filter($row[0]));
                    $notification_email = strtolower(AppUtils::utf8_filter($row[1]));
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

                    if( preg_match("/(@.*)/u", $email, $importDomain) ){
                        if(!in_array($importDomain[0],$domains)){
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => 'ドメインの登録がありません'];
                            continue;
                        }
                        $email = OptionUserUtils::replaceEmail($email, 'wf');
                    }
                    if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_RECEIVE_USER) {
                        $email = substr($email, -4) == '.scs' ? $email : "$email.scs";
                    }

                    // 受信専用利用者 (0)ユーザID,(1)通知先メールアドレス,(2)姓,(3)名,(4)部署,(5)役職,(6)郵便番号,(7)住所,
                    // (8)電話番号,(9)FAX番号,
                    // (10)印面設定,(11)印面文字,(12)有効化(1:有効にする),
                    // (13)多要素認証(0:無効,1:メール,2:QRコード),(14)備考,

                    $data_user = [
                        'email' => $email,
                        'family_name' => mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[2])),
                        'given_name' => mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[3])),
                        'state_flg' => $row[12],
                        'mst_company_id' => $user->mst_company_id,
                        'reference' => $row[14],
                        'option_flg' => AppUtils::USER_RECEIVE,
                    ];
                    if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_RECEIVE_USER) {
                        $data_user['notification_email'] = $notification_email;
                    }

                    $data_user_info = [
                        'postal_code' => $row[6],
                        'address' => $row[7],
                        'phone_number' => $row[8],
                        'fax_number' => $row[9],
                        'date_stamp_config' => 1,
                        'api_apps' => 0,
                        'approval_request_flg' => 1,
                        // 企業設定と同じように設定する
                        'browsed_notice_flg' => $company ? $company->view_notification_email_flg : 0,
                        'update_notice_flg' => $company ? $company->updated_notification_email_flg : 0,
                        'template_flg' => 0,
                        'rotate_angle_flg' => 0,
                    ];

                    // 多要素認証の設定
                    if ($company->mfa_flg) {
                        $data_user_info['mfa_type'] = $row[13];
                        $data_user_info['email_auth_dest_flg'] = 0;
                    }else{
                        $data_user_info['mfa_type'] = 0;
                        $data_user_info['email_auth_dest_flg'] = 0;
                    }

                    // 印面作成
                    $stamp_id = null;
                    $stamp_setting = intval(trim($row[10]));
                    $stamp_name = mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[11]);
                    $stamp_name = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $stamp_name);

                    if ($stamp_setting && $stamp_name){
                        // 印面文字半角チェック
                        if (!AppUtils::jpn_zenkaku_only($stamp_name)) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => '印面文字に全角文字を入力してください'];
                            continue;
                        }
                        $division = 0;
                        $font = $stamp_setting - 1;

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

                    //部署＆役職
                    $department = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[4]));
                    $position = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[5]));

                    //ユーザのパラメータ　チェック
                    $m_user = new User();
                    if ($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_RECEIVE_USER){
                        $validator = Validator::make($data_user, $m_user->rules(0, false, true));
                    }else{
                        $validator = Validator::make($data_user, $m_user->rules(0, false, true, true));
                    }
                    $infoRules = [
                        'phone_number' => 'max:15|regex:/^[0-9-]{0,15}$/',
                        'fax_number' => 'max:15|regex:/^[0-9-]{0,15}$/',
                        'postal_code' => 'max:10|regex:/^[0-9-]{0,10}$/',
                        'mfa_type' => 'required|numeric|min:0|max:2',
                    ];
                    $infoValidator = Validator::make($data_user_info, $infoRules);
                    if ($validator->fails() || $infoValidator->fails()){
                        $message = $validator->messages()->merge($infoValidator->messages());
                        $message_all = $message->all();
                        $num_error++;
                        $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => $message_all[0]];
                        continue;
                    }

                    //部署　チェック
                    if ($department){
                        $obj_depart = new Department();
                        $departmentId = $obj_depart->detectFromName(explode(AppUtils::SPERATOR_SPLIT, $department), $user->mst_company_id);
                        if (!$departmentId) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => __('message.not_detected', ['attribute' => $department])];
                            continue;
                        }
                    } else {
                        $departmentId = null;
                    }

                    //役職　チェック
                    if ($position) {
                        $positionId = isset($listPosition[$position]) ? $listPosition[$position] : false;
                        if (!$positionId) {
                            $num_error++;
                            $arrErrorMsg[] = ['row' => $i + 1, 'email' => $email, 'comment' => __('message.not_detected', ['attribute' => $position])];
                            continue;
                        }
                    }else {
                        $positionId = null;
                    }

                    $data_user_info['mst_department_id'] = $departmentId;
                    $data_user_info['mst_position_id'] = $positionId;

                    //csvはemailが存在します。
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
                    if($csv_import_list->import_type == AppUtils::STATE_IMPORT_CSV_WITHOUT_EMAIL_RECEIVE_USER){
                        $dataUser['without_email_flg'] = AppUtils::WITHOUT_EMAIL_T;
                    }
                    // check if exist
                    $itemOld = null;
                    $dataUserInfo = $mapDataUserInfo[$email];
                    if ($mapDbUser->has($email)) {
                        $itemOld = $mapDbUser[$email];
                    }

                    // status再判定
                    if ($itemOld){
                        // 更新
                        // 状態有効
                        if ($dataUser['state_flg']) {
                            // 印面指定なし場合、
                            if (!key_exists($email, $mapStampId)) {
                                $item = User::where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();
                                $itemStamps = $item->getStamps($item->id);
                                if (count($itemStamps['stampMaster']) == 0) {
                                    $dataUser['state_flg'] = 0;
                                }
                            }
                        }
                    }else{
                        // 登録
                        // 状態有効＋印面指定なし場合、無効で登録
                        if ($dataUser['state_flg']) {
                            if (!key_exists($email, $mapStampId)) {
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

                    $apiUser = [
                        "email" => strtolower($email),
                        "contract_app" => config('app.pac_contract_app'),
                        "app_env" => config('app.pac_app_env'),
                        "contract_server" => config('app.pac_contract_server'),
                        "user_auth" => AppUtils::AUTH_FLG_RECEIVE,
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
                        unset($dataUserInfo['approval_request_flg']);
                        unset($dataUserInfo['browsed_notice_flg']);
                        unset($dataUserInfo['update_notice_flg']);
                        $itemInfo->fill($dataUserInfo);

                        $apiUser['update_user_email'] = $user->email;

                        // 統合更新
                        $result = $client->put("users", [
                            RequestOptions::JSON => $apiUser
                        ]);

                        if ($result->getStatusCode() == Response::HTTP_OK) {
                            DB::beginTransaction();
                            try {
                                $itemOld->save();
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
                                    "user_auth" => AppUtils::AUTH_FLG_RECEIVE,
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
                        } else if ($result->getStatusCode() == Response::HTTP_CONFLICT) {
                            $num_error += 1;
                            $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています。'];
                        } else {
                            Log::channel('import-csv-daily')->info("Call ID App Api to update import user failed(list id:" . $csv_import_list->id . "). Response Body " . $result->getBody());
                            $num_error += 1;
                            $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => '統合ID連携失敗'];
                        }
                    }else{
                        // 新規の場合
                        $dataUser['login_id'] = Str::uuid()->toString();
                        $dataUser['system_id'] = 0;
                        $dataUser['amount'] = 0;
                        $dataUser['password'] = "";
                        $apiUser['create_user_email'] = $user->email;

                        // idm api(store) 統合追加
                        $result = $client->post("users", [
                            RequestOptions::JSON => $apiUser
                        ]);

                        if ($result->getStatusCode() == Response::HTTP_OK){
                            DB::beginTransaction();
                            try {
                                //ユーザ　入力
                                $dataUser['create_user'] = $create_user_name;
                                $dataUser['create_at'] = Carbon::now();
                                User::insert($dataUser);
                                $insertUser = User::where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();

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
                            }catch (\Exception $e) {
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
                        } else if ($result->getStatusCode() == Response::HTTP_CONFLICT) {
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
        }catch (\Exception $e) {
            Log::channel('import-csv-daily')->error("(list id:" . $csv_import_list->id . ")" . $e->getMessage() . $e->getTraceAsString());
            $csv_import_list->result = 0; // 0:failed;1:success
            $csv_import_list->update_at = Carbon::now();
            $csv_import_list->save();
        }
    }
}
