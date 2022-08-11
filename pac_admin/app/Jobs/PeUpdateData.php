<?php

namespace App\Jobs;

use App\Http\Controllers\Csv\CsvController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\CsvUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\GwAppApiUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\StampUtils;
use App\Models\ApiUsers;
use App\Models\AssignStamp;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\RequestInfo;
use App\Models\Stamp;
use App\Models\User;
use App\Models\UserInfo;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Csv\Reader;

class PeUpdateData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request_id;
    protected $login_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request_id, $login_id)
    {
        $this->request_id = $request_id;
        $this->login_id = $login_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 「実行開始時間」を更新する
        $request_info = RequestInfo::where('id', $this->request_id)->first();
        $mst_company_id = $request_info->mst_company_id;
        $request_info->execution_flg = 0;
        $request_info->execution_start_datetime = Carbon::now();
        $request_info->save();
        $apiUser = ApiUsers::where('login_id',$this->login_id)->first();
        $addresses = array_filter(explode(';',$apiUser->email_addresses));// 結果通知先メールアドレス
        $date = Carbon::now();

        try {
            $gw_use = config('app.gw_use');
            $gw_domin = config('app.gw_domain');
            //sftp path
            $filepath =  $apiUser->sftp_username . '/' . CsvUtils::SFTP_UPDATE_PATH;

            //make local Directory
            if (Storage::disk('csv')->exists(CsvUtils::UPDATE_PATH . $mst_company_id . '/')) {
                Storage::disk('csv')->deleteDirectory(CsvUtils::UPDATE_PATH . $mst_company_id . '/');
            }
            Storage::disk('csv')->makeDirectory(CsvUtils::UPDATE_PATH . $mst_company_id . '/');

            //部署情報csv
            //copy to local from sftp
            Storage::disk('csv')->writeStream(CsvUtils::UPDATE_PATH . $mst_company_id . '/' . 'pe_department_update.csv', Storage::disk('csv_user')->readStream($filepath . 'pe_department_update.csv'));

            //read local csv
            $departments_csv = Reader::createFromPath(storage_path(CsvUtils::UPDATE_PATH) . $mst_company_id . '/' . 'pe_department_update.csv', 'r');

            //CSVファイルのバックアップ
            $exists = Storage::disk('csv_user')->has($filepath . $date->format('Ymd') . '/pe_department_update_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv');

            if (!$exists) {
                Storage::disk('csv_user')->copy($filepath . 'pe_department_update.csv', $filepath . $date->format('Ymd') . '/pe_department_update_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv');
            }

            //役職情報csv
            //copy to local from sftp
            Storage::disk('csv')->writeStream(CsvUtils::UPDATE_PATH . $mst_company_id  . '/' . 'pe_position_update.csv', Storage::disk('csv_user')->readStream($filepath . 'pe_position_update.csv'));

            //read local csv
            $position_csv = Reader::createFromPath(storage_path(CsvUtils::UPDATE_PATH) . $mst_company_id   . '/' . 'pe_position_update.csv', 'r');

            //CSVファイルのバックアップ
            $exists = Storage::disk('csv_user')->has($filepath . $date->format('Ymd') . '/pe_position_update_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv');
            if (!$exists) {
                Storage::disk('csv_user')->copy($filepath . 'pe_position_update.csv', $filepath . $date->format('Ymd') . '/pe_position_update_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv');
            }

            //利用者情報csv
            //copy to local from sftp
            Storage::disk('csv')->writeStream(CsvUtils::UPDATE_PATH . $mst_company_id  . '/' . 'pe_members_update.csv', Storage::disk('csv_user')->readStream($filepath . 'pe_members_update.csv'));

            //read local csv
            $members_csv = Reader::createFromPath(storage_path(CsvUtils::UPDATE_PATH) . $mst_company_id  . '/' . 'pe_members_update.csv', 'r');

            //CSVファイルのバックアップ
            $exists = Storage::disk('csv_user')->has($filepath . $date->format('Ymd') . '/pe_members_update_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv');
            if (!$exists) {
                Storage::disk('csv_user')->copy($filepath . 'pe_members_update.csv', $filepath . $date->format('Ymd') . '/pe_members_update_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv');
            }


            try {
                $company = Company::where('id',$mst_company_id)->first();
                // 部署情報
                $total = count($members_csv);
                $arrReason = [];

                if($total){

                    DB::beginTransaction();
                    try {

                        foreach($departments_csv as $i => $row) {
                            foreach ($row as $key => $value){
                                $row[$key] = iconv("CP932", "utf-8", $value);
                            }

                            $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

                            if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                                continue;
                            }elseif($i == 0){
                                // 先頭行
                                if(strpos($row[0], $bom) === 0){
                                    // 先頭行BOM削除
                                    $row[0] = ltrim($row[0], $bom);
                                }
                            }
                            if (count($row) != 3){
                                // 項目数が3ではない
                                $arrReason[] = ['row'=> $i+1, 'comment' =>'行が正しくありません'];
                                continue;
                            }

                            // 動作モード
                            $ope = $row[2];
                            if ($ope != '1' && $ope != '2'){
                                // 動作モードが1、2以外
                                $arrReason[] = ['row'=> $i+1, 'comment' =>'動作モードが正しくありません'];
                                continue;
                            }

                            // 部署ID
                            $opeid = $row[0];

                            if ($ope == '1' && $opeid != ''){
                                // 登録＋ID指定あり
                                $arrReason[] = ['row'=> $i+1, 'comment' =>'部署IDが正しくありません(動作モードが登録の場合、指定しないでください)'];
                                continue;

                            }elseif ($ope == '2' && $opeid == ''){
                                // 更新＋ID指定なし
                                $arrReason[] = ['row'=> $i+1, 'comment' =>'部署IDが正しくありません(動作モードが更新の場合、指定してください'];
                                continue;

                            }
                            $Departmentnames = explode(AppUtils::SPERATOR_SPLIT, $row[1]);

                            if ($ope == '1'){
                                // 登録
                                $parent_id = 0;
                                foreach($Departmentnames as $Departmentname){
                                    $record = Department::where('parent_id',$parent_id)
                                        ->where('department_name',$Departmentname)
                                        ->where('mst_company_id',$mst_company_id)
                                        ->where('state','1')
                                        ->first();
                                    if(!$record){
                                        $item = [
                                            'mst_company_id'=>$mst_company_id,
                                            'parent_id'=>$parent_id,
                                            'state'=>AppUtils::DEFAULT_DEPARTMENT_STATE,
                                            'department_name'=>$Departmentname,
                                            'create_user'=>'Shachihata',
                                            'create_at' => Carbon::now(),
                                        ];

                                        $validator = Validator::make($item, [
                                            'department_name' => 'required|max:256'
                                        ]);

                                        if ($validator->fails()){
                                            $message = $validator->messages();
                                            $arrReason[] = ['row'=> $i + 1, 'comment' => $message->all()];;

                                            if($message->has('department_name') == true){
                                                break 2;
                                            }
                                        }

                                        $parent_id = DB::table('mst_department')->insertGetId($item);
                                    }else{
                                        $parent_id = $record->id;
                                    }
                                }

                            }else{
                                // 更新
                                $bak_id = 99999;
                                $bak_parent_id = 99999;
                                for($j = count($Departmentnames) - 1; $j > -1; $j--){
                                    if($j == count($Departmentnames) - 1){
                                        // ID指定部署
                                        $record = DB::table('mst_department')
                                            ->where('id',$opeid)
                                            ->where('mst_company_id',$mst_company_id)
                                            ->where('state','1')
                                            ->first();
                                        if(!$record){
                                            // err 更新⇒レコードなし
                                            DB::rollBack();
                                            $arrReason[] = ['row'=> $i + 1, 'comment' =>'指定した部署IDが存在しない'];
                                            break 2;

                                        }else{

                                            if($Departmentnames[$j] != $record->department_name){
                                                // 部署名更新

                                                $item = [
                                                    'department_name' => $Departmentnames[$j],
                                                    'update_at' => Carbon::now(),
                                                    'update_user'=>'Shachihata',
                                                ];

                                                $validator = Validator::make($item, [
                                                    'department_name' => 'required|max:256'
                                                ]);

                                                if ($validator->fails()){
                                                    $message = $validator->messages();
                                                    $arrReason[] = ['row'=> $i + 1, 'comment' => $message->all()];;
                                                    if($message->has('department_name') == true){
                                                        break 2;
                                                    }
                                                }

                                                DB::table('mst_department')
                                                    ->where('id',$opeid)
                                                    ->update($item);

                                            }

                                            // backup
                                            $bak_id = $record->id;
                                            $bak_parent_id = $record->parent_id;
                                        }
                                    }else{
                                        // 移動判定
                                        $record = DB::table('mst_department')
                                            ->where('department_name',$Departmentnames[$j])
                                            ->where('mst_company_id',$mst_company_id)
                                            ->where('id',$bak_parent_id)
                                            ->where('state','1')
                                            ->first();

                                        if (!$record){
                                            // 実際に移動した部署 TODO 複数の部署の指定
                                            $record = DB::table('mst_department')
                                                ->where('department_name',$Departmentnames[$j])
                                                ->where('mst_company_id',$mst_company_id)
                                                ->where('state','1')
                                                ->orderBy('id','asc')
                                                ->first();
                                        }

                                        if(!$record){
                                            // err 移動先部署名存在しない
                                            DB::rollBack();
                                            $arrReason[] = ['row'=> $i + 1, 'comment' =>'指定した部署名が存在しない（'.$Departmentnames[$j].'）'];
                                            break 2;

                                        }else{

                                            if($bak_parent_id != $record->id){
                                                // 下レコード.親ID≠本レコード.ID

                                                // 本レコード配下に移動
                                                DB::table('mst_department')
                                                    ->where('id',$bak_id)
                                                    ->update([
                                                        'parent_id' => $record->id,
                                                        'update_at' => Carbon::now(),
                                                        'update_user'=>'Shachihata',
                                                    ]);

                                                break;
                                            }

                                            // backup
                                            $bak_id = $record->id;
                                            $bak_parent_id = $record->parent_id;
                                        }
                                    }
                                }
                            }
                        }
                        if(count($arrReason)){
                            DB::rollBack();
                            $request_info = RequestInfo::where('id', $this->request_id)->first();
                            $request_info->execution_flg = 1;
                            $request_info->execution_end_datetime = Carbon::now();
                            $request_info->result = 0;
                            $failed_rows = '部署情報CSV ';
                            foreach ($arrReason as $key => $value){
                                $failed_rows = $failed_rows . $value['comment'] . '（'. $value['row'].'行目：CSVの項目数）。';
                                if ($key > 4) {
                                    $failed_rows = $failed_rows . '...';
                                    break;
                                }
                            }
                            $request_info->message = $failed_rows;
                            $is_success = $request_info->save();
                            if ($is_success) {
                                CsvUtils::sendMail($this->request_id, $addresses, '');
                                return;
                            }
                        }else{
                            //更新会社の部署のtree
                            $trees = DepartmentUtils::updateCompanyDepartment($mst_company_id);
                            foreach ($trees as $id => $tree){
                                DB::table('mst_department')->where('id',$id)
                                    ->update(['tree' => $tree]);
                            }
                            DB::commit();
                        }
                    }catch(\Exception $e){
                        DB::rollBack();
                        self::failed($e);
                    }
                }

                //役職情報
                $total = count($position_csv);
                $arrReason = [];

                $positions = DB::table('mst_position')
                    ->select('position_name')
                    ->where('mst_company_id', $mst_company_id)
                    ->where('state',1)
                    ->pluck('position_name')
                    ->toArray();

                if($total){
                    $mapPosition = [];
                    $csv_position = [];
                    DB::beginTransaction();
                    try {

                        foreach($position_csv as $i => $row) {
                            foreach ($row as $key => $value){
                                $row[$key] = iconv("CP932", "utf-8", $value);
                            }
                            $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

                            if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                                continue;
                            }elseif($i == 0){
                                // 先頭行
                                if(strpos($row[0], $bom) === 0){
                                    // 先頭行BOM削除
                                    $row[0] = ltrim($row[0], $bom);
                                    if(!trim($row[0])){
                                        continue;
                                    }
                                }
                            }

                            if (count($row) > 1){
                                $arrReason[] = ['row' => $i + 1 ,'comment' => '行が正しくありません'];
                                continue;
                            }
                            $item = [
                                'mst_company_id' => $mst_company_id,
                                'position_name' =>  $row[0],
                                'state' => AppUtils::DEFAULT_POSITION_STATE,
                                'create_user' => 'Shachihata',
                                'update_user'=> 'Shachihata',
                                'create_at' => Carbon::now(),
                            ];

                            $validator = Validator::make($item, [
                                'position_name' => 'required|max:256'
                            ]);
                            if ($validator->fails()){
                                $message = $validator->messages();
                                $arrReason[] = ['row'=> $i + 1, 'comment' => $message->all()];;
                            }elseif (in_array($row[0], $positions) || in_array($row[0],$csv_position)){
                                //$arrReason[] = ['row'=> $i + 1, 'comment' => 'その役職はすでに使われています'];;
                                continue;
                            }else{
                                $csv_position[] = $row[0];
                                array_push($mapPosition,$item);
                            }
                        }

                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollBack();
                        self::failed($e);
                    }
                    if(!count($arrReason)){
                        if(count($mapPosition)){
                            DB::table('mst_position')->insert($mapPosition);
                        }
                    }else{
                        $request_info = RequestInfo::where('id', $this->request_id)->first();
                        $request_info->execution_flg = 1;
                        $request_info->execution_end_datetime = Carbon::now();
                        $request_info->result = 0;
                        $failed_rows = '役職情報CSV ';
                        foreach ($arrReason as $key => $value){
                            $failed_rows = $failed_rows .  $value['comment'] . '（'. $value['row'].'行目：CSVの項目数）。';
                            if ($key > 4) {
                                $failed_rows = $failed_rows . '...';
                                break;
                            }
                        }
                        $request_info->message = $failed_rows;
                        $is_success = $request_info->save();
                        if ($is_success) {
                            CsvUtils::sendMail($this->request_id, $addresses, '');
                            return;
                        }
                    }
                }


                //利用者情報
                $total = count($members_csv);
                $arrErrorMsg = []; //CSV取込失敗メッセージ
                if ($total) {
                    $csv_emails = [];// import CSV User email
                    $mapDataUser = []; //CSV User data
                    $mapDataUserInfo = []; //CSV UserInfo data
                    $mapStampId = []; //stamp id 行列
                    $importEmails = []; //CSV email 行列
                    $importEmailsWithRow = []; //CSV email with 行

                    // idm app client
                    $client = IdAppApiUtils::getAuthorizeClient();

                    //domainリスト出力
                    $domains = preg_split('/\r\n|\r|\n/', $company->domain);

                    //役職
                    $listPosition = Position::where('state', 1)->where('mst_company_id', $mst_company_id)->pluck('id', 'position_name')->toArray();

                    // 1行1行 check
                    foreach ($members_csv as $i => $row) {
                        foreach ($row as $key => $value){
                            $row[$key] = iconv("CP932", "utf-8", $value);
                        }

                        $email = strtolower(AppUtils::utf8_filter($row[0]));
                        $csv_emails[] = $email;
                        if (!$client) {
                            $arrErrorMsg[] = ['row' => $i + 1, 'comment' => '統合ID接続失敗'];
                            continue;
                        }
                        if ((!is_array($row) && !trim($row)) || (count(array_filter($row)) == 0)) {
                            continue;
                        }
                        // パラメータ数チェック
                        if (count($row) < 15) {
                            $arrErrorMsg[] = ['row' => $i + 1,  'comment' => '行が正しくありません'];
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
                        // (18)テンプレート機能(0:無効,1:有効)
                        // (19)おじぎ印(0:無効,1:有効)

                        $data_user = [
                            'email' => $email,
                            'family_name' => mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[1])),
                            'given_name' => mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $row[2])),
                            'state_flg' => $row[12],
                            'mst_company_id' => $mst_company_id
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
                            //'create_at' => Carbon::now(),
                            'template_flg' => in_array($row[18],[0,1]) ? $row[18] : 0,
                            'rotate_angle_flg' => in_array($row[19],[0,1]) ? $row[19] : 0,

                        ];

                        // 多要素認証の設定
                        if ($company->mfa_flg) {
                            if (isset($row[15])) {
                                $data_user_info['mfa_type'] = $row[15];
                            } else {
                                $data_user_info['mfa_type'] = 0;
                            }
                            if (isset($row[16])) {
                                $data_user_info['email_auth_dest_flg'] = $row[16];
                            } else {
                                $data_user_info['email_auth_dest_flg'] = 0;
                            }
                            if (isset($row[17])) {
                                $data_user_info['auth_email'] = $row[17];
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
                                $arrErrorMsg[] = ['row' => $i + 1, 'comment' => '印面文字に全角文字を入力してください'];
                                continue;
                            }
                            $division = $stamp_setting > 3 ? 1 : 0;
                            $font = ($stamp_setting - 1) % 3;

                            // 印面作成
                            $stamp = AppUtils::searchStamp($stamp_name, $division, $font);

                            // 失敗の場合
                            if (is_numeric($stamp)) {
                                $arrErrorMsg[] = ['row' => $i + 1, 'comment' => '利用者印面作成失敗しました。'];
                                continue;
                            }

                            // TODO insert batch ?
                            // ハンコ解像度調整
                            $stamp->contents = StampUtils::stampClarity(base64_decode($stamp->contents));

                            $stamp_id = Stamp::insertGetId([
                                'stamp_name' => $stamp_name, 'stamp_division' => $division, 'font' => $font,
                                'stamp_image' => $stamp->contents,
                                'width' => floatval($stamp->realWidth) * 100, 'height' => floatval($stamp->realHeight) * 100,
                                'date_x' => $stamp->datex, 'date_y' => $stamp->datey,
                                'date_width' => $stamp->datew, 'date_height' => $stamp->dateh,
                                'create_user' => 'Shachihata', 'serial' => ''
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
                            $arrErrorMsg[] = ['row' => $i + 1, 'comment' => $message_all];
                            continue;
                        }

                        if ($department) {
                            $obj_depart = new Department();
                            $departmentId = $obj_depart->detectFromName(explode(\App\Http\Utils\AppUtils::SPERATOR_SPLIT, $department), $mst_company_id);
                            if (!$departmentId) {
                                $arrErrorMsg[] = ['row' => $i + 1, 'comment' => __('message.not_detected', ['attribute' => $department])];
                                continue;
                            }
                        } else {
                            $departmentId = null;
                        }
                        if ($position) {
                            $positionId = isset($listPosition[$position]) ? $listPosition[$position] : false;
                            if (!$positionId) {
                                $arrErrorMsg[] = ['row' => $i + 1, 'comment' => __('message.not_detected', ['attribute' => $position])];
                                continue;
                            }
                        } else {
                            $positionId = null;
                        }
                        $data_user_info['mst_department_id'] = $departmentId;
                        $data_user_info['mst_position_id'] = $positionId;

                        if (in_array($data_user['email'], $importEmails, true)) {
                            $arrErrorMsg[] = ['row' => $i + 1,  'comment' => 'そのメールアドレスはすでに使われています'];
                            continue;
                        }

                        // 他の企業はemailが存在します。
                        $exist = User::where('state_flg', '!=', AppUtils::STATE_DELETE)->where('email', '=', $data_user['email'])->where('mst_company_id', '!=', $mst_company_id)->first();
                        if ($exist) {
                            $arrErrorMsg[] = ['row' => $i + 1, 'comment' => 'そのメールアドレスはすでに使われています'];
                            continue;
                        }

                        // PAC_5-1436 インポート時ドメインチェック
                        if( preg_match("/(@.*)/u", $email, $importDomain) ){
                            if(!in_array($importDomain[0],$domains)){
                                $arrErrorMsg[] = ['row' => $i + 1, 'comment' => 'ドメインの登録がありません'];
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
                        // check if exist
                        $itemOld = null;
                        $dataUserInfo = $mapDataUserInfo[$email];
                        if ($mapDbUser->has($email)) {
                            $itemOld = $mapDbUser[$email];
                        }

                        //有効ユーザーの数
                        $valid_user_count = User::where('mst_company_id',$mst_company_id)->where('option_flg',AppUtils::USER_NORMAL)
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
                        $intStampTotal = Company::getCompanyStampCount($company->id) + 1;
                        if($company->contract_edition != 3 && !empty($mapStampId[$email])){
                            if($intStampStatus == 1){
                                $dataUser['state_flg'] = AppUtils::STATE_INVALID_NOPASSWORD;
                            }
                            if($intStampTotal > $company->upper_limit){
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
                            "update_user_email" => 'master-pro@shachihata.co.jp',
                            "user_email" => strtolower($email),
                            "status" => AppUtils::convertState($dataUser['state_flg']),
                            "system_name" => $company->system_name
                        ];
                        if ($itemOld) {
                            // 更新の場合
                            $mst_user_id = $itemOld->id;

                            $arrAllStampData = (new User())->getStamps($mst_user_id);
                            Log::info("all total is ".$company->upper_limit." current user $email total is ".
                                ($intStampTotal+count($arrAllStampData['stampMaster']) + count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) + count($arrAllStampData['stampWaitDepartment']) + 1)
                            );

                            if(
                                $company->contract_edition != 3
                                && ($intStampTotal+count($arrAllStampData['stampMaster']) + count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) + count($arrAllStampData['stampWaitDepartment']) + 1) > $company->upper_limit
                            ){
                                $itemOld->state_flg = AppUtils::STATE_INVALID_NOPASSWORD;
                                $apiUser['status'] = AppUtils::convertState($itemOld->state_flg);
                                Log::info("change current user $email state to 0");
                            }

                            $itemOld->fill($dataUser);
                            $itemOld['update_user'] = 'shachihata';
                            $itemOld['update_at'] = Carbon::now();

                            if ($mapDbUserInfo && $mapDbUserInfo->has($mst_user_id)) {
                                $itemInfo = $mapDbUserInfo[$mst_user_id];
                                $itemInfo['update_user'] = 'shachihata';
                                $itemInfo['update_at'] = Carbon::now();
                            } else {
                                // user info 存在しません、追加
                                $itemInfo = new UserInfo();
                                $itemInfo['mst_user_id'] = $mst_user_id;
                                $itemInfo['create_user'] = 'shachihata';
                                $itemInfo['create_at'] = Carbon::now();
                            }
                            $itemInfo->fill($dataUserInfo);

                            $apiUser['update_user_email'] = 'master-pro@shachihata.co.jp';

                            // 統合更新
                            $result = $client->put("users", [
                                RequestOptions::JSON => $apiUser
                            ]);

                            if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                // $response = json_decode((string) $result->getBody());
                                DB::beginTransaction();
                                try {

                                    $arrAllStampData = (new User())->getStamps($mst_user_id);
                                    if(
                                        $company->contract_edition != 3
                                        && ($intStampTotal+count($arrAllStampData['stampMaster']) + count($arrAllStampData['stampCompany']) + count($arrAllStampData['stampDepartment']) + count($arrAllStampData['stampWaitDepartment']) + 1) > $company->upper_limit
                                    ){
                                        $itemOld->state_flg = AppUtils::STATE_INVALID_NOPASSWORD;
                                    }
                                    $itemOld->save(); //
                                    $itemInfo->save();

                                    if (key_exists($email, $mapStampId) && $mapStampId[$email]) {
                                        DB::table('mst_assign_stamp')->insert([
                                            'stamp_id' => $mapStampId[$email],
                                            'mst_user_id' => $mst_user_id,
                                            'display_no' => 0,
                                            'stamp_flg' => AppUtils::STAMP_FLG_NORMAL,
                                            'create_user' => 'shachihata',
                                            'state_flg' => AppUtils::STATE_VALID
                                        ]);
                                    }
                                    DB::commit();
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    Log::error( $e->getMessage() . $e->getTraceAsString());
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
                                        "update_user_email" => 'master-pro@shachihata.co.jp',
                                        "user_email" => strtolower($email),
                                        "status" => AppUtils::convertState($mapDbUser[$email]['state_flg']),
                                        "system_name" => $company->system_name
                                    ];
                                    // 統合削除
                                    $result = $client->put("users", [RequestOptions::JSON => $apiUser]);
                                    if ($result->getStatusCode() != 200) {
                                        Log::info("deleteInsertFail response: " . $result->getBody());
                                    }
                                    $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => $e->getMessage()];
                                }
                            } else if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_CONFLICT) {
                                $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています。'];
                            } else {
                                $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => '統合ID連携失敗'];
                            }
                        } else {
                            // 新規の場合
                            $dataUser['login_id'] = Str::uuid()->toString();
                            $dataUser['system_id'] = 0;
                            $dataUser['amount'] = 0;
                            $dataUser['password'] = "";

                            $apiUser['create_user_email'] = 'master-pro@shachihata.co.jp';

                            // idm api(store) 統合追加
                            $result = $client->post("users", [
                                RequestOptions::JSON => $apiUser
                            ]);
                            if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_OK) {
                                DB::beginTransaction();
                                try {
                                    //insert mst_user
                                    $dataUser['create_user'] = 'shachihata';
                                    $dataUser['create_at'] = Carbon::now();
                                    User::insert($dataUser);
                                    $insertUser = User::where('email', $email)->where('state_flg', '!=', AppUtils::STATE_DELETE)->first();

                                    // insert mst_user_info
                                    $dataUserInfo['mst_user_id'] = $insertUser->id;
                                    $dataUserInfo['create_user'] = 'shachihata';
                                    $dataUserInfo['create_at'] = Carbon::now();
                                    UserInfo::insert($dataUserInfo);
                                    if (key_exists($email, $mapStampId) && $mapStampId[$email]) {
                                        AssignStamp::insert([
                                            'stamp_id' => $mapStampId[$email],
                                            'mst_user_id' => $insertUser->id,
                                            'display_no' => 0,
                                            'stamp_flg' => AppUtils::STAMP_FLG_NORMAL,
                                            'create_user' => 'shachihata',
                                            'state_flg' => AppUtils::STATE_VALID
                                        ]);
                                    }

                                    DB::commit();
                                } catch (\Exception $e) {
                                    DB::rollBack();
                                    Log::error( $e->getMessage() . $e->getTraceAsString());

                                    $apiUser['update_user_email'] = 'master-pro@shachihata.co.jp';
                                    $apiUser['status'] = AppUtils::convertState(AppUtils::STATE_DELETE);
                                    // 統合削除
                                    $result = $client->put("users", [RequestOptions::JSON => $apiUser]);
                                    if ($result->getStatusCode() != 200) {
                                        Log::error("deleteInsertFail response: " . $result->getBody());
                                    }

                                    $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => $e->getMessage()];
                                }
                            } else if ($result->getStatusCode() == \Illuminate\Http\Response::HTTP_CONFLICT) {
                                $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => 'そのメールアドレスはすでに使われています。'];
                            } else {
                                Log::error("Call ID App Api to insert import user failed. Response Body " . $result->getBody());
                                $arrErrorMsg[] = ['row' => array_search($email, $importEmailsWithRow) + 1, 'email' => $email, 'comment' => '統合ID連携失敗'];
                            }
                        }
                    }

                    $dbUsers = User::where('mst_company_id',$mst_company_id)
                        ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                        ->where('option_flg',AppUtils::USER_NORMAL)
                        ->get();
                    foreach ($dbUsers as $dbUser){
                        if (!in_array($dbUser->email,$csv_emails)){
                            try {
                                DB::beginTransaction();

                                DB::table('mst_user')->where('id',$dbUser->id)
                                    ->update([
                                        'state_flg' => AppUtils::STATE_DELETE,
                                        'delete_at' => Carbon::now(),
                                        'email' => $dbUser->email . '.del',
                                        'update_user' => 'Shachihata',
                                        'update_at' => Carbon::now(),
                                    ]);
                                AssignStamp::where('mst_user_id', $dbUser->id)
                                    ->update([
                                        'state_flg' => AppUtils::STATE_INVALID,
                                        'delete_at' => Carbon::now()
                                    ]);
                                // ユーザ削除時、rememberToken削除
                                CommonUtils::rememberTokenClean($dbUser->id,'mst_user');

                                $apiUser = [
                                    "email" => $dbUser->email,
                                    "contract_app" => config('app.pac_contract_app'),
                                    "app_env" => config('app.pac_app_env'),
                                    "contract_server" => config('app.pac_contract_server'),
                                    "user_auth" => AppUtils::AUTH_FLG_USER,
                                    "user_first_name" => $dbUser->given_name,
                                    "user_last_name" => $dbUser->family_name,
                                    "company_name" => $company ? $company->company_name : '',
                                    "status" => 9,
                                    'update_user_email' => 'master-pro@shachihata.co.jp',
                                    'user_email' => $dbUser->email,
                                    "company_id" => $mst_company_id,
                                    "system_name" => $company ? $company->system_name : '',
                                ];
                                $client = IdAppApiUtils::getAuthorizeClient();
                                if (!$client){
                                    $arrErrorMsg[] = ['row' => $i + 1, 'comment' => '統合ID接続失敗'];
                                    DB::rollBack();
                                    continue;
                                }

                                // PAC_5-3112 GW・CalDAV側の利用者情報も削除する Start
                                if ($gw_use == 1 && $gw_domin) {
                                    Log::debug("Call Gw Api to delete company user $dbUser->email");
                                    $gw_result = GwAppApiUtils::userDelete($dbUser->id, $dbUser->email, $mst_company_id);
                                    if (!$gw_result){
                                        $arrErrorMsg[] = ['row' => $i + 1, 'comment' => 'GW・CalDAV側でユーザー情報の削除に失敗しました。'];
                                        DB::rollBack();
                                        continue;
                                    }
                                }
                                // PAC_5-3112 End

                                Log::debug("Call ID App Api to disable company user $dbUser->email");
                                $result = $client->put("users",[
                                    RequestOptions::JSON => $apiUser
                                ]);
                                if ($result->getStatusCode() != 200){
                                    $arrErrorMsg[] = ['row' => $i + 1, 'comment' => '統合ユーザ削除失敗'];
                                    DB::rollBack();
                                    Log::warning("Call ID App Api to disable company user failed. Response Body ".$result->getBody());
                                }
                                DB::commit();
                            }catch (Exception $e){
                                DB::rollBack();
                                $this->failed($e);
                            }
                        }
                    }

                    // 処理が正常終了した場合
                    $request_info = RequestInfo::where('id', $this->request_id)->first();
                    $request_info->execution_flg = 1;
                    $request_info->execution_end_datetime = Carbon::now();
                    if (count($arrErrorMsg) == 0){
                        $request_info->result = 1;
                    }else{
                        $request_info->result = 0;
                        $failed_rows = '利用者情報CSV ';
                        foreach ($arrErrorMsg as $key => $value){
                            $failed_rows = $failed_rows . $value['comment'] . '（'. $value['row'].'行目：CSVの項目数）。';
                            if ($key > 4) {
                                $failed_rows = $failed_rows . '...';
                                break;
                            }
                        }
                        $request_info->message = $failed_rows;
                    }
                    $success = $request_info->save();
                    // send email
                    if ($success) {
                        CsvUtils::sendMail($this->request_id, $addresses,$filepath . $date->format('Ymd').'/');
                    }

                }

            }catch (Exception $e){
                // 異常処理
                $this->failed($e);
            }


        }catch (Exception $e){
            // 異常処理
            $this->failed($e);
        }

    }

    public function failed(Exception $e)
    {
        $apiUser = ApiUsers::where('login_id',$this->login_id)->first();
        $addresses = array_filter(explode(';',$apiUser->email_addresses));// 結果通知先メールアドレス
        //error
        $request_info = RequestInfo::where('id', $this->request_id)->first();
        $request_info->execution_flg = 1;
        $request_info->execution_end_datetime = Carbon::now();
        $request_info->result = 0;
        if (strlen($e->getMessage()) > 200) {
            $message = substr($e->getMessage(), 0, 197) . '...';
        }else{
            $message = $e->getMessage();
        }
        $request_info->message = $message;
        $is_success = $request_info->save();

        Log::error($e->getMessage());
        Log::error($e->getTraceAsString());

        if ($is_success) {
            CsvUtils::sendMail($this->request_id, $addresses, '');
        }

    }
}
