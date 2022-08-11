<?php

namespace App\Http\Utils;

use App\Models\NewTimeCard;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Log;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\CommonUtils;
use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use App\Models\Department;
use App\Models\UsageSituation;
use App\Models\DownloadRequest;
use App\Models\DownloadWaitData;
use Illuminate\Support\Facades\Schema;

/**
 * CSVダウンロード処理
 * Class DownloadControllerUtils
 * @package App\Http\Utils
 */
class DownloadControllerUtils
{

    // ダウンロード種別
    const TYPE_STAMP_REGISTER_STATE     = 0; // 利用者・印面登録状況
    const TYPE_STAMP_LEDGER             = 1; // 捺印台帳CSV出力
    const TYPE_ADMIN_HISTORY            = 2; // 管理者操作履歴
    const TYPE_USER_HISTORY             = 3; // 利用者操作履歴
    const TYPE_USER_SETTING             = 4; // 利用者設定
    const TYPE_ADDRESS_COMMON           = 5; // 共通アドレス帳
    const TYPE_DEPARTMENT               = 6; // 部署
    const TYPE_POSITION                 = 7; // 役職
    const TYPE_TIME_CARD                = 8; // 打刻履歴CSV出力
    const TYPE_TIME_CARD_MANAGE         = 9; // 数人の打刻履歴CSV出力
    const TYPE_OPTION_USER              = 10; // グループウェア専用利用者設定
    const TYPE_RECEIVE_USER             = 11; // 受信専用利用者設定
    const TYPE_CIRCULARS                = 12; // 回覧一覧
    const TYPE_USER_REGISTRATION        = 13; //登録状況
    const TYPE_USER_DISKUSAGE           = 14; //容量利用者
    const TYPE_TEMPLATE_ROUTE           = 15; //承認ルート
    const TYPE_EXPENSE_M_FORM_ADV       = 14; // 事前申請様式一覧
    const TYPE_EXPENSE_M_FORM_EXP       = 15; // 精算申請様式一覧
    const TYPE_EXPENSE_APP_LIST         = 16; // 経費申請一覧
    const TYPE_JOURNAL_LIST             = 17; // 経費仕訳一覧
    // ファイル名
    const FILE_NAME = array(
        DownloadControllerUtils::TYPE_STAMP_REGISTER_STATE     => '利用者・印面登録状況_',
        DownloadControllerUtils::TYPE_STAMP_LEDGER             => 'stamp', // stamp選択月_yyyyMMddhhmm.csv
        DownloadControllerUtils::TYPE_ADMIN_HISTORY            => 'adminlog_',
        DownloadControllerUtils::TYPE_USER_HISTORY             => 'userlog_',
        DownloadControllerUtils::TYPE_USER_SETTING             => 'users_',
        DownloadControllerUtils::TYPE_ADDRESS_COMMON           => 'address_',
        DownloadControllerUtils::TYPE_DEPARTMENT               => '部署_',
        DownloadControllerUtils::TYPE_POSITION                 => 'position_',
        DownloadControllerUtils::TYPE_TIME_CARD                => 'timecard_',
        DownloadControllerUtils::TYPE_TIME_CARD_MANAGE         => 'timecard_',
        DownloadControllerUtils::TYPE_OPTION_USER              => 'option_user_',
        DownloadControllerUtils::TYPE_RECEIVE_USER             => 'receive_user_',
        DownloadControllerUtils::TYPE_CIRCULARS                => 'circulars_',
        DownloadControllerUtils::TYPE_USER_REGISTRATION        => '登録状況_',
        DownloadControllerUtils::TYPE_USER_DISKUSAGE           => '各利用者のファイル容量_',
        DownloadControllerUtils::TYPE_TEMPLATE_ROUTE           => 'template_route_',
        DownloadControllerUtils::TYPE_EXPENSE_M_FORM_ADV       => '事前申請様式一覧_',
        DownloadControllerUtils::TYPE_EXPENSE_M_FORM_EXP       => '精算申請様式一覧_',
        DownloadControllerUtils::TYPE_EXPENSE_APP_LIST         => '経費申請一覧_',
        DownloadControllerUtils::TYPE_JOURNAL_LIST             => '経費仕訳一覧_',
    );

    // ********************************************************** //
    // public methods

    /**
     * 利用者・印面登録状況ファイルデータ取得
     *
     * @param $user
     * @param $params
     */
    public static function getStampRegisterState($user, $params)
    {
        $company_id             = $user->mst_company_id;
        $mst_company_id         = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN) ? $company_id : $user->mst_company_id;
        $show_longterm_storage  = $params == null ? null : $params["show_longterm_storage"];
        $is_host                = $params == null ? null : $params["is_host"];

        if($is_host){
            $infos = DownloadControllerUtils::_getCsvHostDataReport($mst_company_id, $company_id, $is_host);
        }else{
            $infos = DownloadControllerUtils::getCsvDataReport($mst_company_id);
        }

        // title
        $title = ['対象月','当月利用者総数','割当中の氏名印','割当中の日付印','割当中の共通印','割当中の印面の合計','タイムスタンプ発行数'];
        //20210804文字化回避追加
        mb_convert_variables('SJIS-win', 'UTF-8',$title);
        if($show_longterm_storage){
            $title = ['対象月','当月利用者総数','割当中の氏名印','割当中の日付印','割当中の共通印','割当中の印面の合計','タイムスタンプ発行数','長期保管ディスク使用容量'];
            //20210804文字化回避追加
            mb_convert_variables('SJIS-win', 'UTF-8',$title);
        }
        $outText = '';
        for ($i = 0; $i < count($title); $i++) {
            if($i == 0){
                $outText = $outText . $title[$i];
            }
            else {
                $outText = $outText . ',' . $title[$i];
            }
        }
        for ($_i = 0, $items = $infos; $_i < count($infos); $_i++) {
            $outText = $outText . "\r\n";
            $outText = $outText . $items[$_i]->target
                . ',' . $items[$_i]->user_total_count
                . ',' . $items[$_i]->total_name_stamp
                . ',' . $items[$_i]->total_date_stamp
                . ',' . $items[$_i]->total_common_stamp
                . ',' . ($items[$_i]->total_name_stamp + $items[$_i]->total_date_stamp + $items[$_i]->total_common_stamp)
                . ',' . $items[$_i]->total_time_stamp;
            if($show_longterm_storage){
                $outText = $outText . ',' . $items[$_i]->storage_use_capacity;
            }
        }
        $outText = $outText . "\r\n";
        $path = sys_get_temp_dir() .
                "/download-csv-usage-" .
                AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $mst_company_id, $user->id) .
                'csv';
        // ファイルを書き込みモードで開く
        $file_handle = fopen( $path, "w" );
        // ファイルへデータを書き込み
        fwrite( $file_handle, $outText );
        // ファイルを閉じる
        fclose( $file_handle );

        return \file_get_contents($path);
    }

    /**
     * 捺印台帳CSVファイルデータ取得
     *
     * @param $user
     * @param $params
     */
    public static function getStampLedger($user, $params)
    {
        $company_id     = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN) ? $params->get("company_id", null) : $user->mst_company_id;
        $select_month   = $params['select_month'];
        $serial         = $params['serial'];
        $finishedDate = str_replace('-','',$select_month);
        $query_sub_company = DB::table('mst_user as su')
            ->Join('mst_user_info as sui','sui.mst_user_id','su.id')
            ->leftJoin('mst_department as sd','sd.id','sui.mst_department_id')
            ->select('sd.id as department_id','sd.department_name as department_name', 'su.email as email', 'su.mst_company_id')
            ->where('su.state_flg',AppUtils::STATE_VALID)
            ->where('su.option_flg',AppUtils::USER_NORMAL);

        $objQueryTitle =  DB::table("circular_user$finishedDate")
            ->select("title as circular_name",'circular_id')
            ->whereRaw("id in(SELECT MAX(cu.id) FROM circular_user$finishedDate as cu WHERE mst_company_id = ? GROUP BY cu.circular_id)",[$company_id]);

        $StampHistory = DB::table('circular_operation_history as coh')
            ->orderBy('coh.create_at','asc')
            ->Join('stamp_info as si', 'si.circular_operation_id','coh.id')
            ->Join("circular$finishedDate as c", 'c.id','coh.circular_id')
            ->joinSub($query_sub_company, 'com', function ($join) {
                $join->on('com.email', '=', 'si.email');
            })
            ->joinSub($objQueryTitle, 'title', function ($join) {
                $join->on('title.circular_id', '=', 'coh.circular_id');
            })
            ->select('si.create_at','coh.operation_name','coh.operation_email','com.department_name','com.department_id','si.serial','si.file_name','title.circular_name')
            ->where('coh.circular_status',OperationsHistoryUtils::CIRCULAR_IMPRINT_STATUS)
            ->where('com.mst_company_id',$company_id)
            ->whereIn('c.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS,CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
        if($serial && DownloadControllerUtils::checkSerial($serial)){
            $StampHistory = $StampHistory->where('serial',$serial);
        }

        $StampHistory = $StampHistory->get();
        //他環境の捺印履歴
        $otherStampHistory = DB::table('assign_stamp_info as asi')
            ->select('asi.create_at','asi.name as operation_name','asi.email as operation_email','com.department_name','com.department_id','asi.serial','asi.file_name','asi.circular_title as circular_name')
            ->join("circular$finishedDate as c",'c.id','asi.circular_id')
            ->joinSub($query_sub_company,'com',function ($join){
                $join->on('com.email','=','asi.email');
            })
            ->where('com.mst_company_id',$company_id)
            ->whereIn('c.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS,CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);

        if($serial && DownloadControllerUtils::checkSerial($serial)){
            $otherStampHistory = $otherStampHistory->where('serial',$serial);
        }

        $otherStampHistory = $otherStampHistory->get();

        $StampHistory = $StampHistory->merge($otherStampHistory)->sortBy('create_at');
        
        /*本月のデータを取得する */

        if ($finishedDate == date('Ym')) {
            $finishedDate = '';
            $query_sub_company = DB::table('mst_user as su')
                ->Join('mst_user_info as sui', 'sui.mst_user_id', 'su.id')
                ->leftJoin('mst_department as sd', 'sd.id', 'sui.mst_department_id')
                ->select('sd.id as department_id', 'sd.department_name as department_name', 'su.email as email', 'su.mst_company_id')
                ->where('su.state_flg', AppUtils::STATE_VALID)
                ->where('su.option_flg', AppUtils::USER_NORMAL);

            $objQueryTitle = DB::table("circular_user$finishedDate")
                ->select("title as circular_name", 'circular_id')
                ->whereRaw("id in(SELECT MAX(cu.id) FROM circular_user$finishedDate as cu WHERE mst_company_id = ? GROUP BY cu.circular_id)", [$company_id]);

            $StampHistoryNow = DB::table('circular_operation_history as coh')
                ->orderBy('coh.create_at', 'asc')
                ->Join('stamp_info as si', 'si.circular_operation_id', 'coh.id')
                ->Join("circular$finishedDate as c", 'c.id', 'coh.circular_id')
                ->joinSub($query_sub_company, 'com', function ($join) {
                    $join->on('com.email', '=', 'si.email');
                })
                ->joinSub($objQueryTitle, 'title', function ($join) {
                    $join->on('title.circular_id', '=', 'coh.circular_id');
                })
                ->select('si.create_at', 'coh.operation_name', 'coh.operation_email', 'com.department_name', 'com.department_id', 'si.serial', 'si.file_name', 'title.circular_name')
                ->where('coh.circular_status', OperationsHistoryUtils::CIRCULAR_IMPRINT_STATUS)
                ->where('com.mst_company_id', $company_id)
                ->whereIn('c.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS])
                ->where('c.completed_copy_flg', CircularUtils::CIRCULAR_COMPLETED_COPY_FLG_FALSE)
                ->whereRaw('DATE_FORMAT(c.completed_date,"%Y-%m")=' . '\'' . $select_month . '\'');
            if ($serial && DownloadControllerUtils::checkSerial($serial)) {
                $StampHistoryNow = $StampHistoryNow->where('serial', $serial);
            }

            $StampHistoryNow = $StampHistoryNow->get();
            $StampHistory = $StampHistory->merge($StampHistoryNow)->sortBy('create_at');
        }

        /* PAC_5-3023 S */
        $listDepartmentTree = DepartmentUtils::getDepartmentTree($company_id);
        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
        /* PAC_5-3023 E */

        $path = sys_get_temp_dir() .
            "/download-csv-usage-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen($path, 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

        $header = [
            '捺印時刻',
            'ユーザー名',
            'メールアドレス',
            '所属部署',
            '印鑑シリアル',
            'ファイル名',
            '文書名'
        ];

        fputcsv($output, $header);

        foreach ($StampHistory as $item){
            /* PAC_5-3023 S */
            $department = isset($listDepartmentDetail[$item->department_id]) ? $listDepartmentDetail[$item->department_id]['text'] : '';
            /* PAC_5-3023 E */

            if(!trim($item->circular_name)){
                $circular_name = $item->file_name;
            }else{
                $circular_name = $item->circular_name;
            }
            $row = [
                $item->create_at,
                $item->operation_name ,
                $item->operation_email,
//                $item->department_name,  PAC_5-3023
                $department,
                $item->serial,
                $item->file_name,
                $circular_name,
            ];
            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);

	}

    /**
     * 利用者登録状況ファイルデータ取得
     *
     * @param $user
     */

    public static function getUserRegistrationStatus($user, $params)
    {
        $users = [];
        $users = User::where('mst_company_id', $user->mst_company_id)->where('option_flg','!=',AppUtils::USER_OPTION)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        $company = Company::where('id', $user->mst_company_id)->first();
        $company->domain = explode("\r\n", $company->domain);
        if (count($company->domain) == 1){
            $company->domain = explode("\n", $company->domain[0]);
        }
        $email_domain_company = [];
        foreach($company->domain as $domain){
            $email_domain_company[$domain] = ltrim($domain,"@");
        }

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        $listPosition = Position::
            select('id' , 'position_name as text' , 'position_name as sort_name')
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);

                return $sort_name;
            })
            ->keyBy('id')
            ->sortBy('sort_name')
            ->toArray();

        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        $path = sys_get_temp_dir() .
            "/download-csv-usage-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

        $header = [
            'メールアドレス',
            '氏名',
            '部署',
            '役職',
            '氏名印',
            '日付印',
            '共通印',
            '便利印',
            '状態',
            'パスワード',
            '作成日時'
        ];
        fputcsv($output, $header);
        foreach ($users as $user) {
            $count_mst_stamp_name = DB::table('mst_assign_stamp')
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
            ->where('mst_user.id',$user->id)
            ->where('mst_assign_stamp.state_flg', 1)
            ->where('mst_assign_stamp.stamp_flg', 0)
            ->where('mst_stamp.stamp_division', 0)
            ->count();

            $count_mst_stamp_date = DB::table('mst_assign_stamp')
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
            ->where('mst_user.id',$user->id)
            ->where('mst_assign_stamp.state_flg', 1)
            ->where('mst_assign_stamp.stamp_flg', 0)
            ->where('mst_stamp.stamp_division', 1)
            ->count();

            $count_mst_department_date = DB::table('mst_assign_stamp')
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
            ->where('mst_user.id',$user->id)
            ->where('mst_assign_stamp.state_flg', 1)
            ->where('mst_assign_stamp.stamp_flg', 2)
            ->count();

            $count_common_stamp = DB::table('mst_assign_stamp')
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
            ->where('mst_user.id',$user->id)
            ->where('mst_assign_stamp.state_flg', 1)
            ->where('mst_assign_stamp.stamp_flg', 1)
            ->count();

            $userHasConvenientStamp = DB::table("mst_assign_stamp")
            ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
            ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
            ->where('mst_user.id',$user->id)
            ->where('mst_assign_stamp.stamp_flg', 3)
            ->where('mst_assign_stamp.state_flg', 1)
            ->where('mst_user.option_flg', 0)
            ->count();

            $department = isset($listDepartmentDetail[$user->info->mst_department_id]) ? $listDepartmentDetail[$user->info->mst_department_id]['text'] : '';
            $position_id = $user->info->mst_position_id;
            $total_date_stamp = $count_mst_stamp_date + $count_mst_department_date;
            if($user->state_flg == 1){
                $state_text = '有効';
            }else{
                $state_text = '無効';
            }
            $row = [$user->email, $user->family_name . " ".$user->given_name, $department,
                isset($listPosition[$position_id]) ? $listPosition[$position_id]['text'] : "",
                $count_mst_stamp_name,
                $total_date_stamp,
                $count_common_stamp,
                $userHasConvenientStamp,
                $state_text,
                $user->password == "" ? "未設定" : "設定済",
                $user->create_at
            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * 管理者・利用者操作履歴ファイルデータ取得
     *
     * @param $user
     * @param $params
     */
    public static function getHistory($user, $params)
    {
        // get list user
        $limit      = $params['limit'] ? $params['limit'] : config('app.page_limit');
        $orderBy    = $params['orderBy'] ? $params['orderBy'] : 'time';
        $orderDir   = $params['orderDir'] ? $params['orderDir'] : 'desc';
        $arrOrder   = ['user' => 'user_name','time' => 'H.create_at', 'status' => 'H.result',
            'type' =>'H.mst_operation_id','screen' => 'H.mst_display_id','ipAddress' => 'H.ip_address',
            'email' => 'email','adminDepartment' => 'U.department_name',
            'userDepartment' => 'D.department_name','position' => 'P.position_name'];

        $select_month   = $params['select_month'];
        $filter_user    = $params['user'];
        $filter_screen  = $params['screen'];
        $filter_type    = $params['type'];
        $filter_status  = $params['status'];
        $filter_audit_user  = $params['audit_user']??"";
        

        $time=Carbon::now()->addMonthsNoOverflow(-3)->format('Y-m-d');
        $where      = ['1 = 1'];
        $where_arg  = [];

        if($select_month){
            $where[]        = 'Date(H.create_at) like ?';
            $where_arg[]    = "%".$select_month."%";

        }else{
            $where[]        = 'Date(H.create_at) like ?';
            $where_arg[]    ="%".date("Y-m")."%";
        }

        if($filter_user){
            $where[]        = 'H.user_id = ?';
            $where_arg[]    = $filter_user;
        }

        if($filter_screen){
            $where[]        = 'H.mst_operation_id  = ?';
            $where_arg[]    = "$filter_screen";
        }

        if($filter_status != ''){
            $where[]        = 'H.result  = ?';
            $where_arg[]    = "$filter_status";
        }
        
        if($filter_audit_user){
            $where[]        = 'H.user_id = ?';
            $where_arg[]    = $filter_audit_user;
        }
        $id_end=substr($user->mst_company_id,-1);

        $arrHistory = DownloadControllerUtils::getOperationHistory($filter_type, $user->mst_company_id, $select_month, $orderBy, $orderDir, $where, $where_arg,$arrOrder);

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        $role = $filter_type == 'user' ? OperationsHistoryUtils::HISTORY_FLG_USER : OperationsHistoryUtils::HISTORY_FLG_ADMIN;
        $arrOperation_info = DB::table('mst_operation_info')->where('role', $role)->select('info','id')->pluck('info','id');

        $path = sys_get_temp_dir() .
                "/download-csv-history-" .
                AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
                'csv';
        // ファイルを書き込みモードで開く
        $output = fopen( $path, "w" );
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
        if ($filter_type == 'user'){
            foreach ($arrHistory as $item){
                $departmentText = isset($listDepartmentDetail[$item->department_id]) ? $listDepartmentDetail[$item->department_id]['text'] : '';
                $row = [
                    $item->create_at,
                    $item->email ,
                    $item->user_name,
                    $departmentText,
                    $item->position_name,
                    $arrOperation_info[$item->mst_operation_id],
                    $item->ip_address,
                    $item->detail_info,
                ];
                fputcsv($output, $row);
            }
        }else{
            foreach ($arrHistory as $item){
                $row = [
                    $item->create_at,
                    $item->email ,
                    $item->user_name,
                    $item->department_name,
                    $arrOperation_info[$item->mst_operation_id],
                    $item->detail_info,
                ];
                fputcsv($output, $row);
            }
        }

        fclose($output);

        return \file_get_contents($path);
    }

    /**
     * 利用者設定ファイルデータ取得
     *
     * @param $user
     * @param $params
     */
    public static function getUserSetting($user, $params)
    {
        if (isset($params['without_email_flg']) && $params['without_email_flg']) {
            $users = User::where('mst_company_id', $user->mst_company_id)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_T)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        }else{
            $users = User::where('mst_company_id', $user->mst_company_id)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_F)->where('option_flg',AppUtils::USER_NORMAL)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        }
        $company = Company::where('id', $user->mst_company_id)->first();
        $company->domain = explode("\r\n", $company->domain);
        if (count($company->domain) == 1){
            $company->domain = explode("\n", $company->domain[0]);
        }
        $email_domain_company = [];
        foreach($company->domain as $domain){
            $email_domain_company[$domain] = ltrim($domain,"@");
        }

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        $listPosition = Position::
            select('id' , 'position_name as text' , 'position_name as sort_name')
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);

                return $sort_name;
            })
            ->keyBy('id')
            ->sortBy('sort_name')
            ->toArray();

        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        $path = sys_get_temp_dir() .
            "/download-csv-usersetting-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($users as $user) {
            $department = isset($listDepartmentDetail[$user->info->mst_department_id]) ? $listDepartmentDetail[$user->info->mst_department_id]['text'] : '';
            $position_id = $user->info->mst_position_id;
            $row = [$user->email, $user->family_name, $user->given_name, $department,
                isset($listPosition[$position_id]) ? $listPosition[$position_id]['text'] : "",
                $user->info->postal_code, $user->info->address,
                $user->info->phone_number, $user->info->fax_number,
                '',// ホームページ
                0, '',
                $user->state_flg,
                $user->info->date_stamp_config,
                $user->info->api_apps,
                $user->info->mfa_type,
                $user->info->email_auth_dest_flg,
                $user->info->auth_email,
                $user->info->template_flg,
                $user->info->rotate_angle_flg,
                $user->password == "" ? "未設定" : "設定済"

            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * グループウェア専用利用者設定ファイルデータ取得
     * @param $user
     * @return false|string
     */
	public static function getOptionUserSetting($user, $params){
	    if (isset($params['without_email_flg']) && $params['without_email_flg']) {
            $users = User::where('mst_company_id', $user->mst_company_id)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_T)->where('option_flg',AppUtils::USER_OPTION)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        }else{
            $users = User::where('mst_company_id', $user->mst_company_id)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_F)->where('option_flg',AppUtils::USER_OPTION)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        }

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        $listPosition = Position::select('id' , 'position_name as text' , 'position_name as sort_name')
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                return $sort_name;
            })
            ->keyBy('id')
            ->sortBy('sort_name')
            ->toArray();

        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        $path = sys_get_temp_dir() .
            "/download-csv-option_user_setting-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($users as $user) {
            $department = isset($listDepartmentDetail[$user->info->mst_department_id]) ? $listDepartmentDetail[$user->info->mst_department_id]['text'] : '';
            $position_id = $user->info->mst_position_id;
            $row = [$user->email,$user->notification_email, $user->family_name, $user->given_name, $department,
                isset($listPosition[$position_id]) ? $listPosition[$position_id]['text'] : "",
                $user->info->postal_code, $user->info->address,
                $user->info->phone_number, $user->info->fax_number,
                $user->state_flg,
                $user->info->mfa_type,
                $user->reference,
                $user->password == "" ? "未設定" : "設定済"
            ];
            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
    }

    /**
     * 受信専用利用者設定ファイルデータ取得
     * @param $user
     * @return false|string
     */
    public static function getReceiveUserSetting($user, $params){

        if (isset($params['without_email_flg']) && $params['without_email_flg']) {
            $users = User::where('mst_company_id', $user->mst_company_id)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_T)->where('option_flg',AppUtils::USER_RECEIVE)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        }else{
            $users = User::where('mst_company_id', $user->mst_company_id)->where('without_email_flg',AppUtils::WITHOUT_EMAIL_F)->where('option_flg',AppUtils::USER_RECEIVE)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
        }

        $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);

        $listPosition = Position::select('id' , 'position_name as text' , 'position_name as sort_name')
            ->where('state',1)
            ->where('mst_company_id',$user->mst_company_id)
            ->get()
            ->map(function ($sort_name) {
                $sort_name->sort_name = str_replace(AppUtils::STR_KANJI, AppUtils::STR_SUUJI, $sort_name->sort_name);
                return $sort_name;
            })
            ->keyBy('id')
            ->sortBy('sort_name')
            ->toArray();

        // 上位部署の情報を取得する
        $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);

        $path = sys_get_temp_dir() .
            "/download-csv-option_user_setting-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($users as $user) {

            $department = isset($listDepartmentDetail[$user->info->mst_department_id]) ? $listDepartmentDetail[$user->info->mst_department_id]['text'] : '';
            $position_id = $user->info->mst_position_id;
            $row = [$user->email,$user->notification_email, $user->family_name, $user->given_name, $department,
                isset($listPosition[$position_id]) ? $listPosition[$position_id]['text'] : "",
                $user->info->postal_code, $user->info->address,
                $user->info->phone_number, $user->info->fax_number,
                0, '',
                $user->state_flg,
                $user->info->mfa_type,
                $user->reference,
                $user->password == "" ? "未設定" : "設定済"
            ];
            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
    }

    /**
     * 共通アドレス帳ファイルデータ取得
     *
     * @param $user
     * @param $params
     */
    public static function getAddressCommon($user, $params)
    {
        // PAC_5-1978 共通アドレス帳のCSVを全件出力できるように修正する Start
        $query = DB::table('address');
        if (!empty($params['cids'])) {
            $query = $query->whereIn('id', $params['cids']);
        } else {
            if(!empty($params['name'])){
                $query = $query->where('name', 'like', '%' . $params['name'] . '%');
            }
            if(!empty($params['email'])){
                $query = $query->where('email', 'like', '%' . $params['email'] . '%');
            }
            if(!empty($params['company_name'])){
                $query = $query->where('company_name', 'like', '%' . $params['company_name'] . '%');
            }
            if(!empty($params['position'])){
                $query = $query->where('position_name', 'like', '%' . $params['position'] . '%');
            }
            $orderBy = !empty($params['orderBy']) ? $params['orderBy'] : 'id';
            $orderDir = !empty($params['orderDir']) ? $params['orderDir']: 'desc';
            $query = $query->where('type','1')->where('mst_company_id',$user->mst_company_id)->orderBy($orderBy,$orderDir);
        }
        $address_list = $query->get()->toArray();
        // PAC_5-1978 End

        $path = sys_get_temp_dir() .
            "/download-csv-address-common-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
        foreach ($address_list as $address){
            $arr = explode(' ' , $address->name);
            if (count($arr)>1) {
                $row = [
                    $address->email,
                    $arr[0],
                    $arr[1],
                    $address->company_name,
                    $address->position_name,
                ];
            }else{
                $row = [
                    $address->email,
                    $address->name,
                    '',
                    $address->company_name,
                    $address->position_name,
                ];
            }

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

	/**
     * 部署ファイルデータ取得
     *
     * @param $user
     */
    public static function getDepertment($user)
    {
        $mst_company_id = $user->mst_company_id;

        $itemsDepartment = DepartmentUtils::getDepartmentTree($mst_company_id);
        $items = DepartmentUtils::buildDepartmentDetail($itemsDepartment);

        $contentsCsv = [];
        foreach ($items as $item) {
            // 出力対象判定(一番下階層のみ→IDがparent_idで検索結果なし)
            $parent_record_count = Department::where('parent_id', $item['id'])->count();
            if (!$parent_record_count) {
                // 部署ID、部署名、動作モード(2:更新)
                $contentsCsv[] = [$item['id'], $item['text'], 2];
            }
        }
        $names = array_column($contentsCsv, 1);
        array_multisort($names, SORT_ASC, $contentsCsv);

        $path = sys_get_temp_dir() .
            "/download-csv-department-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen($path, 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

        foreach ($contentsCsv as $item){
            fputcsv($output, $item);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * 役職ダウンロード処理
     *
     * @param $user
     * @param $params
     */
    public static function getPosition($user, $params)
    {
        $orderDir   = $params['orderDir'] ? $params['orderDir'] : 'asc';
        $itemsPosition = Position::
                select('id' , 'position_name' , 'position_name as sort_name')
                ->where('mst_company_id',$user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);

                    return $sort_name;
                });
        if(strtolower($orderDir) == 'asc'){
            $itemsPosition = $itemsPosition->sortBy('sort_name');
        }else{
            $itemsPosition = $itemsPosition->sortByDesc('sort_name');
        }

        $path = sys_get_temp_dir() .
            "/download-csv-position-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';

        $output = fopen($path, 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );

        foreach ($itemsPosition as $item){
            $row = [
                $item->position_name,
                ];
            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * 一人分の打刻履歴ダウンロード処理
     *
     * @param $user
     * @param $params
     */
    public static function getTimeCard($user, $targetMonth, $params)
    {
        try {
            $path = sys_get_temp_dir() .
                        "/download-csv-timecard-" .
                        AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
                        'csv';

            $date = Carbon::parse($targetMonth);
            $startTime = $date->toDateString();
            $endTime = $date->endOfMonth()->toDateTimeString();
            $newTimeCard = new NewTimeCard();
            $timeCards = $newTimeCard::where('mst_user_id', $params['userId'])
                ->whereBetween('punched_at', [$startTime, $endTime])
                ->get();

            $output = fopen($path, 'w');
            fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            if ($timeCards->isNotEmpty()) {
                $timeCards = $timeCards->groupBy(function ($item) {
                    return $item->punched_date;
                });
            }
            $tempArr = [];
            // 月の日数を取得する
            $days = $date->daysInMonth;

            for ($i = 1; $i <= $days; $i++) {
                // $tempStr = $params['targetMonth'] . '-' . str_pad($i, 2, 0, STR_PAD_LEFT);
                $tempStr = $targetMonth . '-' . str_pad($i, 2, 0, STR_PAD_LEFT);
                array_push($tempArr, str_replace('-', '/', $tempStr));
            }

            if ($timeCards->isNotEmpty()) {
                foreach ($tempArr as $key => $item) {
                    $row = [];
                    if ($key == 0) {
                        $row = ['日付', '出勤1', '退勤1', '出勤2', '退勤2', '出勤3', '退勤3', '出勤4', '退勤4', '出勤5', '退勤5'];
                        fputcsv($output, $row);
                        $row = [];
                    }
                    $row[] = $item;

                    if (isset($timeCards[$item])) {
                        $row = [
                            $item,
                            $timeCards[$item][0]->punch_data['start1'] ? Carbon::parse($timeCards[$item][0]->punch_data['start1'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['end1'] ? Carbon::parse($timeCards[$item][0]->punch_data['end1'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['start2'] ? Carbon::parse($timeCards[$item][0]->punch_data['start2'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['end2'] ? Carbon::parse($timeCards[$item][0]->punch_data['end2'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['start3'] ? Carbon::parse($timeCards[$item][0]->punch_data['start3'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['end3'] ? Carbon::parse($timeCards[$item][0]->punch_data['end3'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['start4'] ? Carbon::parse($timeCards[$item][0]->punch_data['start4'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['end4'] ? Carbon::parse($timeCards[$item][0]->punch_data['end4'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['start5'] ? Carbon::parse($timeCards[$item][0]->punch_data['start5'])->format('H:i') : '',
                            $timeCards[$item][0]->punch_data['end5'] ? Carbon::parse($timeCards[$item][0]->punch_data['end5'])->format('H:i') : '',
                        ];

                    } else {
                        for ($i = 0; $i < 10; $i++) {
                            array_push($row, '');
                        }
                    }
                    fputcsv($output, $row);
                }
            } else {
                // 空白の場合も出力する
                foreach ($tempArr as $key => $item) {
                    if ($key == 0) {
                        $row = ['日付', '出勤1', '退勤1', '出勤2', '退勤2', '出勤3', '退勤3', '出勤4', '退勤4', '出勤5', '退勤5'];
                        fputcsv($output, $row);
                    }
                    $row = [$item, '', '', '', '', '', '', '', '', '', ''];
                    fputcsv($output, $row);
                }
            }

            fclose($output);
            return \file_get_contents($path);

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return null;
        }

    }

    /**
     * 数人の打刻履歴ダウンロード処理
     *
     * @param $dl_request_id
     * @param $user
     * @param $is_sanitizing
     * @param $params
     */
    public static function getTimeCardManage($user, $targetMonth, $params)
    {
        try {
            $path = sys_get_temp_dir() .
                    "/download-csv-timecard-manage" .
                    AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
                    '.zip';
            $date = Carbon::parse($targetMonth);
            $startTime = $date->toDateTimeString();
            $endTime = $date->endOfMonth()->toDateTimeString();
            $newTimeCard = new NewTimeCard();
            $timeCards = $newTimeCard::whereIn('mst_user_id', $params['userId'])
                ->whereBetween('punched_at', [$startTime, $endTime])
                ->orderBy('mst_user_id')
                ->get();
            $newTimeCards = $timeCards->groupBy(function ($item) {
                $item->user_name = $item->user->getFullName();
                return $item->mst_user_id;
            })->sortKeys()->map(function($item) {
                return $item->groupBy(function($item) {
                    return $item->punched_date;
                });
            });

            $tempArr = [];
            // 月の日数を取得する
            $days = $date->daysInMonth;
            for ($i = 1; $i <= $days; $i++) {
                // $tempStr = $params['targetMonth'].'-'.str_pad($i,2,0,STR_PAD_LEFT);
                $tempStr = $targetMonth.'-'.str_pad($i,2,0,STR_PAD_LEFT);
                array_push($tempArr, str_replace('-', '/', $tempStr));
            }

            $zip = new \ZipArchive();
            if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == false) {
                throw new \Exception(__('message.false.download_request.zip_create'));
            }

            if($timeCards->isNotEmpty()) {
                $user_name_arr = [];
                foreach($newTimeCards as $key => $val) {
                    $user_name = '';
                    foreach($tempArr as $index => $item) {
                        if(isset($val[$item])) {
                            $user_name = $val[$item][0]->user_name;
                            break;
                        }
                    }
                    $nickname = preg_replace('/[\x00-\x1F\x7F]/', '', $user_name);
                    $nickname = preg_replace('/\.|\\\|\\/|\:|\*|\?|\"|\<|\>|\|/', '', $nickname);
                    $nickname = preg_replace('/[\\|\/|\r|\n|\t|\f]/', '', $nickname);
                    $nickname_str = md5($nickname);
                    if (!isset($user_name_arr[$nickname_str])){
                        $user_name_arr[$nickname_str] = 1;
                        $csv_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') . '_' . $nickname . '.csv';
                    } else {
                        $user_name_arr[$nickname_str]++;
                        $csv_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') . '_' . $nickname . '(' . ($user_name_arr[$nickname_str] - 1) . ')' . '.csv';
                    }
                    $csv_path = 'php://temp/csv-timecard-manage' . Carbon::now()->format('YmdHis') . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) . '_' . $key . '.csv';
                    $output = fopen($csv_path, 'r+');
                    fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
                    $row = ['日付','出勤1', '退勤1', '出勤2', '退勤2', '出勤3', '退勤3', '出勤4', '退勤4', '出勤5', '退勤5'];
                    fputcsv($output, $row);

                    foreach($tempArr as $index => $item) {
                        $row = [];
                        if(isset($val[$item])) {
                            $row = [
                                //$user_name,
                                $item,
                                $val[$item][0]->punch_data['start1'] ? Carbon::parse($val[$item][0]->punch_data['start1'])->format('H:i') : '',
                                $val[$item][0]->punch_data['end1'] ? Carbon::parse($val[$item][0]->punch_data['end1'])->format('H:i') : '',
                                $val[$item][0]->punch_data['start2'] ? Carbon::parse($val[$item][0]->punch_data['start2'])->format('H:i') : '',
                                $val[$item][0]->punch_data['end2'] ? Carbon::parse($val[$item][0]->punch_data['end2'])->format('H:i') : '',
                                $val[$item][0]->punch_data['start3'] ? Carbon::parse($val[$item][0]->punch_data['start3'])->format('H:i') : '',
                                $val[$item][0]->punch_data['end3'] ? Carbon::parse($val[$item][0]->punch_data['end3'])->format('H:i') : '',
                                $val[$item][0]->punch_data['start4'] ? Carbon::parse($val[$item][0]->punch_data['start4'])->format('H:i') : '',
                                $val[$item][0]->punch_data['end4'] ? Carbon::parse($val[$item][0]->punch_data['end4'])->format('H:i') : '',
                                $val[$item][0]->punch_data['start5'] ? Carbon::parse($val[$item][0]->punch_data['start5'])->format('H:i') : '',
                                $val[$item][0]->punch_data['end5'] ? Carbon::parse($val[$item][0]->punch_data['end5'])->format('H:i') : '',
                            ];
                        } else {
                            array_push($row, $item);
                            for ($i = 0; $i < 10; $i++) {
                                array_push($row, '');
                            }
                        }
                        fputcsv($output, $row);
                    }
                    rewind($output);
                    if (!array_key_exists($key, $user_name_arr)) {
                        $user_name_arr[$user_name] = 1;
                    } else {
                        $user_name_arr[$user_name]++;
                    }
                    $streamContent = str_replace(PHP_EOL, "\r\n", stream_get_contents($output));
                    $zip->addFromString($csv_name, $streamContent);
                    fclose($output);
                    unset($output);
                    unset($streamContent);
                }
            } else {
                $csv_name = DownloadControllerUtils::FILE_NAME[DownloadControllerUtils::TYPE_TIME_CARD_MANAGE] . Carbon::now()->format('YmdHis') .'.csv';
                $csv_path = 'php://temp/csv-timecard-manage' . Carbon::now()->format('YmdHis') . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) . '_0.csv';
                $output = fopen($csv_path, 'r+');
                fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
                $row = ['名前','日付','出勤1', '退勤1', '出勤2', '退勤2', '出勤3', '退勤3', '出勤4', '退勤4', '出勤5', '退勤5'];
                fputcsv($output, $row);
                // 空白の場合も出力する
                foreach ($tempArr as $key => $item) {
                    $row = [$item, '', '', '', '', '', '', '', '', '', ''];
                    fputcsv($output, $row);
                }
                rewind($output);
                $streamContent = str_replace(PHP_EOL, "\r\n", stream_get_contents($output));
                $zip->addFromString($csv_name, $streamContent);
                fclose($output);
            }
            unset($output);
            if (!$zip->close()) {
                throw new \Exception(__('message.false.download_request.zip_create'));
            }

            return \file_get_contents($path);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return null;
        }


    }

    /**
     * 操作履歴取得
     *
     * @param $type
     * @param $mst_company_id
     * @param $select_month
     * @param $orderBy
     * @param $orderDir
     * @param $where
     * @param $where_arg
     */
    public static function getOperationHistory($type, $mst_company_id, $select_month, $orderBy, $orderDir, $where, $where_arg,$arrOrder){


        $time=Carbon::now()->addMonthsNoOverflow(-2)->format('Y-m-d');

        $id_end=substr($mst_company_id,-1);

        if($type == 'admin'){
            if(!$select_month){
                $arrHistory = DB::table('operation_history as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',isset($arrOrder[$orderBy])?$orderDir : 'desc')
                ->leftJoin('mst_admin as U', 'H.user_id','U.id')
                ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email as email, U.department_name'))
                ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
                ->where('U.mst_company_id', $mst_company_id)
                ->whereRaw(implode(" AND ", $where), $where_arg);

            }elseif($select_month<=$time){

                $arrHistory = DB::table('operation_history'."$id_end".' as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',isset($arrOrder[$orderBy])?$orderDir : 'desc')
                ->leftJoin('mst_admin as U', 'H.user_id','U.id')
                ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email as email, U.department_name'))
                ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
                ->where('U.mst_company_id', $mst_company_id)
                ->whereRaw(implode(" AND ", $where), $where_arg);

            }else{

            $arrHistory = DB::table('operation_history as H')
                ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',isset($arrOrder[$orderBy])?$orderDir : 'desc')
                ->leftJoin('mst_admin as U', 'H.user_id','U.id')
                ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, CONCAT(U.family_name, U.given_name) as user_name,U.email as email, U.department_name'))
                ->where('auth_flg', OperationsHistoryUtils::HISTORY_FLG_ADMIN)
                ->where('U.mst_company_id', $mst_company_id)
                ->whereRaw(implode(" AND ", $where), $where_arg);
            }

        }else if($type == 'user'){
            if(!$select_month){
                $arrHistory = DB::table('operation_history as H')
                    ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',isset($arrOrder[$orderBy])?$orderDir : 'desc')
                    ->leftJoin('mst_audit as MA', function (JoinClause $join) {
                        $join->on("MA.id", "=", "H.user_id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER);
                    })
                    ->leftJoin('mst_user as U', function (JoinClause $join) {
                        $join->on("H.user_id", "=", "U.id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_user_info as UI', function (JoinClause $join) {
                        $join->on('UI.mst_user_id', "=", 'U.id')
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
                    ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id')
                    ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name'))
                    ->whereIn('auth_flg', [OperationsHistoryUtils::HISTORY_FLG_USER,OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER,])
                    ->where(function (Builder $query) use ($mst_company_id) {
                        $query->where('U.mst_company_id', $mst_company_id)
                            ->orWhere('MA.mst_company_id', $mst_company_id);
                    })
                    ->whereRaw(implode(" AND ", $where), $where_arg);
            }elseif($select_month<=$time){
                $arrHistory = DB::table('operation_history'."$id_end".' as H')
                    ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',isset($arrOrder[$orderBy])?$orderDir : 'desc')
                    ->leftJoin('mst_audit as MA', function (JoinClause $join) {
                        $join->on("MA.id", "=", "H.user_id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER);
                    })
                    ->leftJoin('mst_user as U', function (JoinClause $join) {
                        $join->on("H.user_id", "=", "U.id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_user_info as UI', function (JoinClause $join) {
                        $join->on('UI.mst_user_id', "=", 'U.id')
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
                    ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id')
                    ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name'))
                    ->whereIn('auth_flg', [OperationsHistoryUtils::HISTORY_FLG_USER,OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER,])
                    ->where(function (Builder $query) use ($mst_company_id) {
                        $query->where('U.mst_company_id', $mst_company_id)
                            ->orWhere('MA.mst_company_id', $mst_company_id);
                    })
                    ->whereRaw(implode(" AND ", $where), $where_arg);
            }else{
                $arrHistory = DB::table('operation_history as H')
                    ->orderBy(isset($arrOrder[$orderBy])?$arrOrder[$orderBy]:'H.id',isset($arrOrder[$orderBy])?$orderDir : 'desc')
                    ->leftJoin('mst_audit as MA', function (JoinClause $join) {
                        $join->on("MA.id", "=", "H.user_id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER);
                    })
                    ->leftJoin('mst_user as U', function (JoinClause $join) {
                        $join->on("H.user_id", "=", "U.id")
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_user_info as UI', function (JoinClause $join) {
                        $join->on('UI.mst_user_id', "=", 'U.id')
                            ->where("H.auth_flg", '=', OperationsHistoryUtils::HISTORY_FLG_USER);
                    })
                    ->leftJoin('mst_department as D', 'UI.mst_department_id','D.id')
                    ->leftJoin('mst_position as P', 'UI.mst_position_id','P.id')
                    ->select(DB::raw('H.id,H.mst_display_id, H.mst_operation_id, H.detail_info, H.result, H.ip_address, H.create_at, IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',CONCAT(U.family_name, U.given_name),MA.account_name) as user_name,IF(auth_flg='.OperationsHistoryUtils::HISTORY_FLG_USER.',U.email,MA.email) as email,D.id as department_id, D.department_name, P.position_name'))
                    ->whereIn('auth_flg', [OperationsHistoryUtils::HISTORY_FLG_USER,OperationsHistoryUtils::HISTORY_FLG_AUDIT_USER,])
                    ->where(function (Builder $query) use ($mst_company_id) {
                        $query->where('U.mst_company_id', $mst_company_id)
                            ->orWhere('MA.mst_company_id', $mst_company_id);
                    })
                    ->whereRaw(implode(" AND ", $where), $where_arg);
            }
        }

        $arrHistory =$arrHistory->get();
        return $arrHistory;

    }

    /**
     * 利用状況CSV取得
     *
     * @param $mst_company_id
     */
    public static function getCsvDataReport($mst_company_id = null){
        $now = Carbon::now();
        $last_year = Carbon::now()->subYear();
        $last_year_target = intval(($last_year->format('Y')).($last_year->format('m')));
        $infos = [];
        while(true){
            $year = $now->format('Y');
            $month = $now->format('m');
            $target = intval($year.$month);
            if($target < $last_year_target){
                break;
            }
            $info = DownloadControllerUtils::getDataReport($month, $year, $mst_company_id);
            $info['target'] = $target;
            $infos[] = $info;

            $now->subMonth();
        }

        return $infos;
    }

    /**
     * 利用状況CSV取得(ホスト)
     *
     * @param $host_company
     * @param $company_id
     * @param $is_guest
     */
    public static function getCsvHostDataReport($host_company, $company_id, $is_guest){
        $now = Carbon::now();
        $last_year = Carbon::now()->subYear();
        $last_year_target = intval(($last_year->format('Y')).($last_year->format('m')));
        $infos = [];
        while(true){
            $year = $now->format('Y');
            $month = $now->format('m');
            $target = intval($year.$month);
            if($target < $last_year_target){
                break;
            }
            $info = DownloadControllerUtils::_getHostDataReport($month, $year, $host_company, $company_id, $is_guest);
            $info['target'] = $target;
            $infos[] = $info;

            $now->subMonth();
        }

        return $infos;
    }

    /**
     * 利用状況取得
     *
     * @param $month
     * @param $year
     * @param $mst_company_id
     */
    public static function getDataReport($month, $year, $mst_company_id = null){
        $target = intval($year.$month);

        $query = UsageSituation::where('target_month', $target);
        if ($mst_company_id){
            // 通常企業検索
            $query->where('mst_company_id', $mst_company_id);
        }else{
            // 全企業
            $query->select(DB::raw('CAST(SUM(user_total_count) as UNSIGNED) as user_total_count,
                            CAST(SUM(total_name_stamp) as UNSIGNED) as total_name_stamp,
                            CAST(SUM(total_date_stamp) as UNSIGNED) as total_date_stamp,
                            CAST(SUM(total_common_stamp) as UNSIGNED) as total_common_stamp,
                            CAST(SUM(total_time_stamp) as UNSIGNED) as total_time_stamp,
                            CAST(SUM(storage_use_capacity) as UNSIGNED) as storage_use_capacity,
                            CAST(SUM(guest_user_total_count) as UNSIGNED) as guest_user_total_count,
                            CAST(SUM(same_domain_number) as UNSIGNED) as same_domain_number,
                            MAX(max_date) as max_date'));
        }
        $query->whereNull('guest_company_id');

        $info = $query->first();
        if ($info){
            if (!$info->user_total_count){
                $info->user_total_count = 0;
            }
            if (!$info->total_name_stamp){
                $info->total_name_stamp = 0;
            }
            if (!$info->total_date_stamp){
                $info->total_date_stamp = 0;
            }
            if (!$info->total_common_stamp){
                $info->total_common_stamp = 0;
            }
            if (!$info->total_time_stamp){
                $info->total_time_stamp = 0;
            }
            if (!$info->storage_use_capacity){
                $info->storage_use_capacity = 0;
            }
            if (!$info->guest_user_total_count){
                $info->guest_user_total_count = 0;
            }
            if (!$info->same_domain_number){
                $info->same_domain_number = 0;
            }
        }else{
            $info = new UsageSituation();
            $info->mst_company_id = $mst_company_id;
            $info->user_total_count = 0;
            $info->total_name_stamp = 0;
            $info->total_date_stamp = 0;
            $info->total_common_stamp = 0;
            $info->total_time_stamp = 0;
            $info->storage_use_capacity = 0;
            $info->guest_user_total_count = 0;
            $info->same_domain_number = 0;
            $info->max_date = null;
        }

        if ($info->storage_use_capacity > 512){
            $info->storage_use_capacity = round($info->storage_use_capacity/1024, 2);

            if ($info->storage_use_capacity > 512){
                $info->storage_use_capacity = round($info->storage_use_capacity/1024, 2).'GB';
            }else{
                $info->storage_use_capacity .='MB';
            }
        }else{
            $info->storage_use_capacity .='KB';
        }

        return $info;
    }

    /**
     * 利用状況取得(ホスト)
     *
     * @param $month
     * @param $year
     * @param $host_company
     * @param $company_id
     * @param $is_guest
     */
    public static function getHostDataReport($month, $year, $host_company, $company_id, $is_guest){
        $target = intval($year.$month);
        $query = UsageSituation::where('target_month', $target);

        if ($company_id){
            if($company_id == -1){
                // ゲスト企業の合計
                $query->select(DB::raw('CAST(SUM(user_total_count) as UNSIGNED) as user_total_count,
                            CAST(SUM(total_name_stamp) as UNSIGNED) as total_name_stamp,
                            CAST(SUM(total_date_stamp) as UNSIGNED) as total_date_stamp,
                            CAST(SUM(total_common_stamp) as UNSIGNED) as total_common_stamp,
                            CAST(SUM(total_time_stamp) as UNSIGNED) as total_time_stamp,
                            CAST(SUM(storage_use_capacity) as UNSIGNED) as storage_use_capacity,
                            CAST(SUM(guest_user_total_count) as UNSIGNED) as guest_user_total_count,
                            CAST(SUM(same_domain_number) as UNSIGNED) as same_domain_number,
                            MAX(max_date) as max_date'))
                    ->where('mst_company_id', $host_company)
                    ->whereNotNull('guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $query->where('mst_company_id', $host_company)->where('guest_company_id', $company_id);
            } else {
                // ホスト企業
                $query->where('mst_company_id', $host_company)->whereNull('guest_company_id');
            }

        } else {
            // 全企業
            $query->select(DB::raw('CAST(SUM(user_total_count) as UNSIGNED) as user_total_count,
                            CAST(SUM(total_name_stamp) as UNSIGNED) as total_name_stamp,
                            CAST(SUM(total_date_stamp) as UNSIGNED) as total_date_stamp,
                            CAST(SUM(total_common_stamp) as UNSIGNED) as total_common_stamp,
                            CAST(SUM(total_time_stamp) as UNSIGNED) as total_time_stamp,
                            CAST(SUM(storage_use_capacity) as UNSIGNED) as storage_use_capacity,
                            CAST(SUM(guest_user_total_count) as UNSIGNED) as guest_user_total_count,
                            CAST(SUM(same_domain_number) as UNSIGNED) as same_domain_number,
                            MAX(max_date) as max_date'))
                ->where('mst_company_id', $host_company);
        }


        $info = $query->first();
        if ($info){
            if (!$info->user_total_count){
                $info->user_total_count = 0;
            }
            if (!$info->total_name_stamp){
                $info->total_name_stamp = 0;
            }
            if (!$info->total_date_stamp){
                $info->total_date_stamp = 0;
            }
            if (!$info->total_common_stamp){
                $info->total_common_stamp = 0;
            }
            if (!$info->total_time_stamp){
                $info->total_time_stamp = 0;
            }
            if (!$info->storage_use_capacity){
                $info->storage_use_capacity = 0;
            }
            if (!$info->guest_user_total_count){
                $info->guest_user_total_count = 0;
            }
            if (!$info->same_domain_number){
                $info->same_domain_number = 0;
            }
        }else{
            $info = new UsageSituation();
            $info->mst_company_id = $company_id;
            $info->user_total_count = 0;
            $info->total_name_stamp = 0;
            $info->total_date_stamp = 0;
            $info->total_common_stamp = 0;
            $info->total_time_stamp = 0;
            $info->storage_use_capacity = 0;
            $info->guest_user_total_count = 0;
            $info->same_domain_number = 0;
            $info->max_date = null;
        }

        if ($info->storage_use_capacity > 512){
            $info->storage_use_capacity = round($info->storage_use_capacity/1024, 2);

            if ($info->storage_use_capacity > 512){
                $info->storage_use_capacity = round($info->storage_use_capacity/1024, 2).'GB';
            }else{
                $info->storage_use_capacity .='MB';
            }
        }else{
            $info->storage_use_capacity .='KB';
        }

        return $info;
    }

    /**
     * 回覧一覧情報を取得
     *
     * @param $finishedDate
     */
    public static function getCirculars($user, $params)
    {
        $useTemplate    = false;
        $selected_ids = $params['selected_ids'] ?? '';
        $search = $params['search'] ?? '';
        $orderDir   = $params['orderDir'] ?? 'DESC';
        $finishedDate = $params['finishedMonthHidden'] ?? '';
        $status = $params['status'] ?? '';
        $create_from_date = $params['create_fromdate'] ?? '';
        $create_to_date = $params['create_todate'] ?? '';
        $update_from_date = $params['update_fromdate'] ?? '';
        $update_to_date = $params['update_todate'] ?? '';
        $finished_from_date = $params['finished_fromdate'] ?? '';
        $finished_to_date = $params['finished_todate'] ?? '';
        $template_from_date = $params['template_fromdate'] ?? '';
        $template_to_date = $params['template_todate'] ?? '';
        $template_num = $params['template_num'] ?? '';
        $template_text = $params['template_text'] ?? '';
        $department = $params['department'] ?? '';

        if($status != CircularUtils::CIRCULAR_COMPLETED_STATUS){
            $orderBy = $params['orderBy'] ?? 'C.final_updated_date';
            if($orderBy == 'C.completed_date'){
                $orderBy = 'C.final_updated_date';
            }
        } else {
            $orderBy = $params['orderBy'] ?? 'C.completed_date';;
            if($orderBy == 'C.final_updated_date'){
                $orderBy = 'C.completed_date';
            }
        }

        $where = [];
        $where_arg = [];
        $where_temp = [];
        $where_arg_temp = [];
        if(!empty($search)){
            $w = [];
            $w[]        = ' D.file_names like ?';
            $w[]        = ' D.title like ?';
            $w[]        = ' CONCAT(A.family_name, A.given_name) like ?';

            $where[]        = '(' . implode(' OR ', $w) . ')';
            $where_arg[]    = '%'. $search .'%';
            $where_arg[]    = '%'. $search .'%';
            $where_arg[]    = '%'. $search .'%';
        }

        if(!empty($create_from_date)){
            $where[]        = 'DATE(C.applied_date) >= ?';
            $where_arg[]    = $create_from_date;
        }
        if(!empty($create_to_date)){
            $where[]        = 'DATE(C.applied_date) <= ?';
            $where_arg[]    = $create_to_date;
        }
        if(!empty($update_from_date)){
            $where[]        = 'DATE(C.final_updated_date) >= ?';
            $where_arg[]    = $update_from_date;
        }
        if(!empty($update_to_date)){
            $where[]        = 'DATE(C.final_updated_date) <= ?';
            $where_arg[]    = $update_to_date;
        }
        if(!empty($finished_from_date)){
            $where[]        = 'DATE(C.completed_date) >= ?';
            $where_arg[]    = $finished_from_date;
        }
        if(!empty($finished_to_date)){
            $where[]        = 'DATE(C.completed_date) <= ?';
            $where_arg[]    = $finished_to_date;
        }
        // PAC_5-1944 End
        if (!empty($template_from_date) || !empty($template_to_date)
            || !empty($template_num) || !empty($template_text) ){
            $useTemplate = true;
        }
        if (!empty($template_from_date)) {
            $where_temp[]     = 'date_data >= ?';
            $where_arg_temp[] = $template_from_date;
        }
        if (!empty($template_to_date)) {
            $where_temp[]     = 'date_data < ?';
            $where_arg_temp[] = $template_to_date;
        }
        if (!empty($template_num)) {
            $where_temp[]     = 'num_data = ?';
            $where_arg_temp[] = $template_num;
        }
        if (!empty($template_text)) {
            $where_temp[]     = 'text_data like ?';
            $where_arg_temp[] = '%'. $template_text .'%';
        }

        // PAC_5-1944 回覧一覧の検索条件変更 Start
        if (Schema::hasTable("circular$finishedDate")) {
            $query_sub = DB::table("circular$finishedDate as C")
                ->join("circular_user$finishedDate as U", function($join){
                    $join->on('C.id', 'U.circular_id');
                    $join->on('U.parent_send_order', DB::raw('0'));
                    $join->on('U.child_send_order', DB::raw('0'));
                })
                ->join("circular_document$finishedDate as D", function($join) use ($user){
                    $join->on('C.id', '=', 'D.circular_id');
                    $join->on(function($condition) use ($user){
                        $condition->on('confidential_flg', DB::raw('0'));
                        $condition->orOn(function($condition1) use ($user){
                            $condition1->on('confidential_flg', DB::raw('1'));
                            $condition1->on('create_company_id', DB::raw($user->mst_company_id));
                        });
                    });
                    $join->on(function($condition) use ($user){
                        $condition->on('origin_document_id', DB::raw('0'));
                        $condition->orOn('D.parent_send_order', 'U.parent_send_order');
                    });
                })
                ->select(DB::raw('C.id, U.title, GROUP_CONCAT(D.file_name  ORDER BY D.id ASC SEPARATOR \', \') as file_names'))
                ->groupBy(['C.id', 'U.title']);
            $data_query1 = DB::table("circular_user$finishedDate as U")
                ->select(DB::raw('C.id,GROUP_CONCAT(U.name, \'&lt;\',U.email,\'&gt;\' ORDER BY U.name,U.email ASC) as names'))
                ->leftJoin("circular$finishedDate as C",'C.id','U.circular_id')
                ->where('U.del_flg', CircularUserUtils::NOT_DELETE)
                ->groupBy('C.id');

            $data_query = DB::table("circular$finishedDate as C")
                ->leftJoinSub($query_sub, 'D', function ($join) {
                    $join->on('C.id', '=', 'D.id');
                })
                ->leftJoinSub($data_query1,'F',function ($join){
                    $join->on('C.id','=','F.id');
                })
                ->leftjoin('mst_user as A', 'C.mst_user_id', 'A.id')
                ->leftjoin('circular_auto_storage_history as auto_his', function ($query) use ($user) {
                    $query->on('auto_his.circular_id', 'C.id')
                        ->on('auto_his.mst_company_id', DB::raw($user->mst_company_id));
                })
                // PAC_5-2213    add C.completed_date
                ->select(DB::raw('C.id, C.applied_date, C.final_updated_date,C.completed_date, C.circular_status, D.file_names, A.email user_email,A.family_name, A.given_name,
                CONCAT(A.family_name, A.given_name) user_name, D.title, auto_his.result, F.names as user_names'))
                ->where('A.mst_company_id', $user->mst_company_id)
                ->where('C.edition_flg', DB::raw(config('app.pac_contract_app')))
                ->where('C.env_flg', DB::raw(config('app.pac_app_env')))
                ->where('C.server_flg', DB::raw(config('app.pac_contract_server')));
            if($where){
                $data_query->whereRaw(implode(" AND ", $where), $where_arg);
            }

            if(!empty($department)){
                $multiple_department_position_flg = DB::table('mst_company')->where('id', $user->mst_company_id)->select('multiple_department_position_flg')->first()->multiple_department_position_flg ?? 0;

                $departmentList = \Illuminate\Support\Facades\DB::table('mst_department')
                    ->select('id', 'parent_id')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->where('state', 1)
                    ->get()
                    ->toArray();
                $departmentIds = [];
                DepartmentUtils::getDepartmentChildIds($departmentList, $department, $departmentIds);

                if ($multiple_department_position_flg === 1) {
                    $data_query->leftjoin('mst_user_info as UI', 'A.id', 'UI.mst_user_id')
                        // PAC_5-1599 追加部署と役職 Start
                        ->where(function($query) use($departmentIds) {
                            $query->orWhereIn('UI.mst_department_id', $departmentIds)
                                ->orWhereIn('UI.mst_department_id_1', $departmentIds)
                                ->orWhereIn('UI.mst_department_id_2', $departmentIds);
                        });
                    // PAC_5-1599 End
                } else {
                    $data_query->leftjoin('mst_user_info as UI', 'A.id', 'UI.mst_user_id')
                        ->whereIn('UI.mst_department_id', $departmentIds);
                }
            }

            if ($status) {
                if ($status == CircularUtils::CIRCULAR_COMPLETED_STATUS) {
                    $data_query->whereIn('C.circular_status', [CircularUtils::CIRCULAR_COMPLETED_STATUS, CircularUtils::CIRCULAR_COMPLETED_SAVED_STATUS]);
                } else {
                    $data_query->where('C.circular_status', $status);
                }
            } else {
                $data_query->where('C.circular_status', CircularUtils::CIRCULATING_STATUS);
            }

            if($useTemplate) {
                $idByTemplates = DB::table('template_input_data')
                    ->select('circular_id')
                    ->whereRaw(implode(" AND ", $where_temp), $where_arg_temp)
                    ->distinct()
                    ->get();

                $ids = array();
                foreach ($idByTemplates as $value) {
                    $ids[] = $value->circular_id;
                }

                Log::debug($idByTemplates);
                Log::debug($ids);

                $data_query->whereIn('C.id', $ids);
            }

            $data_query = $data_query->orderBy($orderBy, $orderDir);

            if (!empty($selected_ids)) {
                $selected_ids = !is_array($selected_ids) ? (array)$selected_ids : $selected_ids;
                $data_query->whereIn('C.id', $selected_ids);
            }
        } else {
            $data_query = DB::table("circular")->where('id', 0)->select('id');
        }

        $itemsCircular = $data_query->get()->toArray();
        $path = sys_get_temp_dir() .
            "/download-csv-usersetting-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fwrite($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        $row = [
            '申請者',
            '回覧ルート',
            '件名',
            'ファイル名',
            '申請日時',
            $status != CircularUtils::CIRCULAR_COMPLETED_STATUS ? '最終更新日' : '完了日時',
            '回覧状態',
        ];

        fputcsv($output, $row);
        foreach ($itemsCircular as $item) {
            if (strpos($item->user_names, '&lt;') !== false || strpos($item->user_names, '&gt;') !== false) {
                $item->user_names = CommonUtils::replaceCharacter($item->user_names);
            }
            $item->user_names = str_replace(',',PHP_EOL,$item->user_names);
            $item->file_names = str_replace(', ',PHP_EOL,$item->file_names);
            $item->status_name = AppUtils::CIRCULAR_STATUS[$item->circular_status];

            $row = [
                $item->user_name . '<' . $item->user_email . '>',
                $item->user_names,
                $item->title,
                $item->file_names,
                date("Y/m/d H:i:s", strtotime($item->applied_date)),
                $status != CircularUtils::CIRCULAR_COMPLETED_STATUS ? date("Y/m/d H:i:s", strtotime($item->final_updated_date)) : date("Y/m/d H:i:s", strtotime($item->completed_date)),
                $item->status_name,
            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
    }

    /**
     * 事前申請様式一覧データ取得
     *
     * @param $user
     * @param $params
     */
    public static function getExpenseMFormAdv($user, $params)
    {
        $query = DB::table('eps_m_form as D');
        if (!empty($params['cids'])) {
            $query = $query
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ->whereIn('D.form_code', $params['cids'])
                    ;
        } else {
            $query = $query
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ;

            $where = ['1=1'];
            $where_arg = [];

            $where[] = 'INSTR(D.form_type, ?)';
            $where_arg[] = 1;
    
            if(!empty($params['form_code'])) {
                $where[] = 'INSTR(D.form_code, ?)'; 
                $where_arg[] = $params['form_code'];
            }
            if(!empty($params['form_name'])) {
                $where[] = 'INSTR(D.form_name, ?)'; 
                $where_arg[] = $params['form_name'];
            }
            if(!empty($params['validity_period_from'])) {
                $where[] = 'INSTR(D.validity_period_from, ?)'; 
                $where_arg[] = $params['validity_period_from'];
            }
            if(!empty($params['form_describe'])) {
                $where[] = 'INSTR(D.form_describe, ?)'; 
                $where_arg[] = $params['form_describe'];
            }
            $query = $query->whereRaw(implode(" AND ", $where), $where_arg);

            $orderBy = !empty($params['orderBy']) ? $params['orderBy'] : 'form_code';
            $orderDir = !empty($params['orderDir']) ? $params['orderDir']: 'asc';
            $query = $query->orderBy($orderBy,$orderDir);
        }
        $journal_list = $query->get()->toArray();

        $path = sys_get_temp_dir() .
            "/download-csv-expense-mformadv-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($journal_list as $journal) {
            $row = [
                $journal->form_code,
                $journal->form_name,
                $journal->validity_period_from,
            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * 精算申請様式一覧データ取得
     *
     * @param $user
     * @param $params
     */
    public static function getExpenseMFormExp($user, $params)
    {
        $query = DB::table('eps_m_form as D');
        if (!empty($params['cids'])) {
            $query = $query
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ->whereIn('D.form_code', $params['cids'])
                    ;
        } else {
            $query = $query
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ;

            $where = ['1=1'];
            $where_arg = [];

            $where[] = 'INSTR(D.form_type, ?)';
            $where_arg[] = 2;
    
            if(!empty($params['form_code'])) {
                $where[] = 'INSTR(D.form_code, ?)'; 
                $where_arg[] = $params['form_code'];
            }
            if(!empty($params['form_name'])) {
                $where[] = 'INSTR(D.form_name, ?)'; 
                $where_arg[] = $params['form_name'];
            }
            if(!empty($params['validity_period_from'])) {
                $where[] = 'INSTR(D.validity_period_from, ?)'; 
                $where_arg[] = $params['validity_period_from'];
            }
            if(!empty($params['form_describe'])) {
                $where[] = 'INSTR(D.form_describe, ?)'; 
                $where_arg[] = $params['form_describe'];
            }
            $query = $query->whereRaw(implode(" AND ", $where), $where_arg);

            $orderBy = !empty($params['orderBy']) ? $params['orderBy'] : 'form_code';
            $orderDir = !empty($params['orderDir']) ? $params['orderDir']: 'asc';
            $query = $query->orderBy($orderBy,$orderDir);
        }
        $journal_list = $query->get()->toArray();

        $path = sys_get_temp_dir() .
            "/download-csv-expense-mformexp-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($journal_list as $journal) {
            $row = [
                $journal->form_code,
                $journal->form_name,
                $journal->validity_period_from,
            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * 経費申請一覧データ取得
     *
     * @param $user
     * @param $params
     */
    public static function getExpenseAppList($user, $params)
    {
        $query = DB::table('eps_t_app as D');
        if (!empty($params['cids'])) {
            $query = $query
                    ->join('eps_m_form as F', function ($join) {
                        $join->on('D.mst_company_id', '=', 'F.mst_company_id');
                        $join->on('D.form_code', '=', 'F.form_code');
                    })
                    ->Join('mst_user as U', 'U.id','D.mst_user_id')
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ->whereIn('D.id', $params['cids'])
                    ->whereNull('D.deleted_at')
                    ->whereNull('F.deleted_at')
                    ->select(DB::raw('F.form_type, D.id, D.form_code, F.form_name, D.target_period_from, D.target_period_to, CONCAT(U.family_name, U.given_name) as user_name,U.email, TRUNCATE(D.eps_amt,0) as eps_amt , DATE_FORMAT(D.create_at, \'%Y%m%d\') AS create_at, DATE_FORMAT(D.suspay_date, \'%Y%m%d\') as suspay_date, DATE_FORMAT(D.diff_date, \'%Y%m%d\') as diff_date'))
                    ;
        } else {
            $id ='';
            if(!empty($params['id'])){
                $id = $params['id'];
            }
            $filter_user ='';
            if(!empty($params['username'])){
                $filter_user = $params['username'];
            }

            $query = $query
                    ->join('eps_m_form as F', function ($join) {
                        $join->on('D.mst_company_id', '=', 'F.mst_company_id');
                        $join->on('D.form_code', '=', 'F.form_code');
                    })
                    ->Join('mst_user as U', 'U.id','D.mst_user_id')
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ->where(DB::raw('CONCAT(U.family_name,U.given_name)'), 'like', "%$filter_user%")
                    ->where('D.id','like',"%$id%")
                    ->whereNull('D.deleted_at')
                    ->whereNull('F.deleted_at')
                    ->select(DB::raw('F.form_type, D.id, D.form_code, F.form_name, D.target_period_from, D.target_period_to, CONCAT(U.family_name, U.given_name) as user_name,U.email, TRUNCATE(D.eps_amt,0) as eps_amt , DATE_FORMAT(D.create_at, \'%Y%m%d\') AS create_at, DATE_FORMAT(D.suspay_date, \'%Y%m%d\') as suspay_date, DATE_FORMAT(D.diff_date, \'%Y%m%d\') as diff_date'))
                    ;   

            $where = ['1=1'];
            $where_arg = [];
            if(!empty($params['form_code'])) {
                $where[] = 'INSTR(D.form_code, ?)';
                $where_arg[] = $params['form_code'];
            }
            if(!empty($params['form_name'])) {
                $where[] = 'INSTR(F.form_name, ?)';
                $where_arg[] = $params['form_name'];
            }
            $query = $query->whereRaw(implode(" AND ", $where), $where_arg);

            $array_form_type = array();
            if($params['beforeapp']) {//事前申請
                array_push($array_form_type,$params['beforeapp']) ;
            }
            if($params['eps']) {//精算
                array_push($array_form_type,$params['eps']) ;
            }
            if($array_form_type){
                $query = $query->whereIn('F.form_type',$array_form_type);
            }

            if(!empty($params['submission_from'])){
                $query = $query->whereDate('D.create_at', '>=', substr($params['submission_from'], 0, 10));//yyyy-mm-ddを切り取る 提出日
            }
            if(!empty($params['submission_to'])){
                $query = $query->whereDate('D.create_at', '<=', substr($params['submission_to'], 0, 10));//yyyy-mm-ddを切り取る 提出日
            }
            if(!empty($params['suspay_from'])){
                $query = $query->whereDate('D.suspay_date', '>=', substr($params['suspay_from'], 0, 10));//yyyy-mm-ddを切り取る 仮払日
            }
            if(!empty($params['suspay_to'])){
                $query = $query->whereDate('D.suspay_date', '<=', substr($params['suspay_to'], 0, 10));//yyyy-mm-ddを切り取る 仮払日
            }
            if(!empty($params['diff_from'])){
                $query = $query->whereDate('D.diff_date', '>=', substr($params['diff_from'], 0, 10));//yyyy-mm-ddを切り取る
            }
            if(!empty($params['diff_to'])){
                $query = $query->whereDate('D.diff_date', '<=', substr($params['diff_to'], 0, 10));//yyyy-mm-ddを切り取る
            }
            $orderBy = !empty($params['orderBy']) ? $params['orderBy'] : 'id';
            $orderDir = !empty($params['orderDir']) ? $params['orderDir']: 'asc';
            $query = $query->orderBy($orderBy,$orderDir);
        }
        $journal_list = $query->get()->toArray();

        $path = sys_get_temp_dir() .
            "/download-csv-expense-applist-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($journal_list as $journal) {
            $row = [
                $journal->id,
                $journal->form_name,
                $journal->user_name,
                $journal->eps_amt,
                $journal->create_at,
                $journal->suspay_date,
                $journal->diff_date,
            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * 経費仕訳一覧データ取得
     *
     * @param $user
     * @param $params
     */
    public static function getJournalList($user, $params)
    {
        $query = DB::table('eps_t_journal as D');
        if (!empty($params['cids'])) {
            $query = $query
                    ->Join('eps_t_app_items as I', function ($join) {
                        $join->on('D.mst_company_id', '=', 'I.mst_company_id');
                        $join->on('D.eps_t_app_id', '=', 'I.t_app_id');
                        $join->on('D.eps_t_app_item_id', '=', 'I.id');
                    })
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ->whereIn('D.id', $params['cids'])
                    ->whereNull('D.deleted_at')
                    ->whereNull('I.deleted_at')
                    ->select(DB::raw('D.id, DATE_FORMAT(D.rec_date,\'%Y%m%d\') AS rec_date, DATE_FORMAT(I.expected_pay_date,\'%Y%m%d\') AS expected_pay_date, debit_account, debit_subaccount, TRUNCATE(D.debit_amount,0) as debit_amount, debit_tax_div, TRUNCATE(D.debit_tax,0) as debit_tax, credit_account, credit_subaccount, TRUNCATE(D.credit_amount,0) as credit_amount, credit_tax_div, TRUNCATE(D.credit_tax,0) as credit_tax, D.remarks'))
                    ;
        } else {
            $query = $query
                    ->Join('eps_t_app_items as I', function ($join) {
                        $join->on('D.mst_company_id', '=', 'I.mst_company_id');
                        $join->on('D.eps_t_app_id', '=', 'I.t_app_id');
                        $join->on('D.eps_t_app_item_id', '=', 'I.id');
                    })
                    ->where('D.mst_company_id',$user->mst_company_id)
                    ->whereNull('D.deleted_at')
                    ->whereNull('I.deleted_at')
                    ->select(DB::raw('D.id, DATE_FORMAT(D.rec_date,\'%Y%m%d\') AS rec_date, DATE_FORMAT(I.expected_pay_date,\'%Y%m%d\') AS expected_pay_date, debit_account, debit_subaccount, TRUNCATE(D.debit_amount,0) as debit_amount, debit_tax_div, TRUNCATE(D.debit_tax,0) as debit_tax, credit_account, credit_subaccount, TRUNCATE(D.credit_amount,0) as credit_amount, credit_tax_div, TRUNCATE(D.credit_tax,0) as credit_tax, D.remarks'))
                    ;

            $searchQuerySub =  DB::table('eps_t_journal as A')
            ->where('A.mst_company_id', $user->mst_company_id) //自分の会社のユーザのみが対象
            ->whereNull('A.deleted_at');
            ;
            if(!empty($params['rec_from'])) {
                $searchQuerySub->whereDate('A.rec_date', '>=', substr($params['rec_from'], 0, 10));
            }
            if(!empty($params['rec_to'])) {
                $searchQuerySub->whereDate('A.rec_date', '<=', substr($params['rec_to'], 0, 10));
            }
            if(!empty($params['expected_pay_from'])) {
                $searchQuerySub->whereDate('A.expected_pay_date', '>=', substr($params['expected_pay_from'], 0, 10));
            }
            if(!empty($params['expected_pay_to'])) {
                $searchQuerySub->whereDate('A.expected_pay_date', '<=', substr($params['expected_pay_to'], 0, 10));
            }
            if(!empty($params['accountspace'])) {
                $searchQuerySub->where(function ($query_small) {
                    $query_small->whereNull('A.debit_account')
                        ->orWhereRAW('A.debit_subaccount is null')
                        ->orWhereRAW('A.credit_account is null')
                        ->orWhereRAW('A.credit_subaccount is null');
                })
                ;
            }
            $searchQuerySub
            ->select('A.mst_company_id','A.eps_t_app_item_id')
            ->groupBy('A.mst_company_id','A.eps_t_app_item_id')
            ;
            if($searchQuerySub){
                $query
                ->JoinSub($searchQuerySub, 'A', function ($join) {
                    $join->on('D.mst_company_id', '=', 'A.mst_company_id');
                    $join->on('D.eps_t_app_item_id', '=', 'A.eps_t_app_item_id');
                });
            }

            $orderBy = !empty($params['orderBy']) ? $params['orderBy'] : 'D.eps_t_app_item_id';
            $orderDir = !empty($params['orderDir']) ? $params['orderDir']: 'asc';
            $query = $query->orderBy($orderBy,$orderDir);
        }
        $journal_list = $query->get()->toArray();

        $path = sys_get_temp_dir() .
            "/download-csv-journal-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen( $path, 'w');
        fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($journal_list as $journal) {
            $row = [
                $journal->rec_date,
                $journal->expected_pay_date,
                $journal->debit_account,
                $journal->debit_subaccount,
                $journal->debit_amount,
                $journal->debit_tax_div,
                $journal->debit_tax,
                $journal->credit_account,
                $journal->credit_subaccount,
                $journal->credit_amount,
                $journal->credit_tax_div,
                $journal->credit_tax,
                $journal->remarks,
            ];

            fputcsv($output, $row);
        }
        fclose($output);

        return \file_get_contents($path);
	}

    /**
     * シリアルチェック
     * @param $serial
     * @return bool
     */
    public static function checkSerial($serial){
        if(!preg_match("/^[0-9a-zA-Z#?!@$%^&*-]{16}$/", $serial)){
            return false;
        };
        return true;
    }

    /**
     * 利用者ファイル容量を取得
     * @param $range
     */
    public static function getDiskUsage($user, $params){

        $range = $params['range'];
        $mst_company_id = $user->mst_company_id;

        // usages_range
        $info_range = DB::table('usages_range as ur')
            ->join('mst_user as mt','ur.email','=','mt.email')
            ->join('mst_company','mst_company.id','=','ur.mst_company_id')
            ->where('ur.range', $range)
            ->where('ur.mst_company_id', $mst_company_id)
            ->whereNull('ur.guest_company_id')
            ->whereNotNull('ur.disk_usage_rank')
            ->select(['ur.mst_company_id as mst_company_id','ur.company_name as company_name','mst_company.company_name_kana as company_name_kana','ur.email as email','ur.disk_usage as disk_usage', 'ur.range as range'])
            ->orderBy('ur.disk_usage', 'desc')
            ->get();
        $maxValue = $info_range->first();

        $path = sys_get_temp_dir() .
            "/download-csv-disk-usage-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen($path, 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
        $header = [
            '企業名',
            '企業カナ',
            'メールアドレス',
            '対象期間　',
            '使用容量'
        ];
        fputcsv($output, $header);

        if($info_range->isNotEmpty()) {
            $unit = $maxValue->disk_usage <= 0.5 * 1024 * 1024 ? 'KB' : 'MB';
        }
        foreach ($info_range as $item){
            $dataSize = $unit === 'KB' ? round($item->disk_usage/1024,2) . $unit : round($item->disk_usage/(1024*1024),2) . $unit;

            $row = [
                $item->company_name,
                $item->company_name_kana,
                $item->email,
                $item->range . 'ヶ月',
                $dataSize
            ];
            fputcsv($output, $row);
        }
        fclose($output);
        return \file_get_contents($path);
    }

    /**
     * ホスト利用者ファイル容量を取得
     * @param $range
     */
    public static function getHostDiskUsage($user, $params){

        $range = $params['range'];
        $company_id = $params['company_id'];
        $host_company = $user->hasrole(PermissionUtils::ROLE_SHACHIHATA_ADMIN) ? $params['company_id'] : $user->mst_company_id;
        $is_guest = $params['isGuest'];

        // usages_range
        $info_range = DB::table('usages_range as ur')
            ->join('mst_user as mt','ur.email','=','mt.email')
            ->join('mst_company','mst_company.id','=','ur.mst_company_id')
            ->where('ur.range', $range)
            ->whereNotNull('ur.disk_usage_rank')
            ->select(['ur.mst_company_id as mst_company_id','ur.company_name as company_name','mst_company.company_name_kana as company_name_kana','ur.email as email','ur.disk_usage as disk_usage','ur.range as range'])
            ->orderBy('ur.disk_usage', 'desc');

        if ($company_id){
            if($company_id == -1){
                // ゲスト企業の合計
                $info_range->where('ur.mst_company_id', $host_company)
                    ->whereNotNull('ur.guest_company_id');
            } else if($is_guest){
                // ゲスト企業
                $info_range->where('ur.mst_company_id', $host_company)->where('ur.guest_company_id', $company_id);
            } else {
                // ホスト企業
                $info_range->where('ur.mst_company_id', $host_company)->whereNull('ur.guest_company_id');
            }
        } else {
            $info_range->where('ur.mst_company_id', $host_company);
        }

        $info_range = $info_range->get();
        $maxValue = $info_range->first();

        $path = sys_get_temp_dir() .
            "/download-csv-disk-usage-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        $output = fopen($path, 'w');
        fputs( $output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
        $header = [
            '企業名',
            '企業カナ',
            'メールアドレス',
            '対象期間',
            '使用容量'
        ];
        fputcsv($output, $header);

        if($info_range->isNotEmpty()) {
            $unit = $maxValue->disk_usage <= 0.5 * 1024 * 1024 ? 'KB' : 'MB';
        }
        foreach ($info_range as $item){
            $dataSize = $unit === 'KB' ? round($item->disk_usage/1024,2) . $unit : round($item->disk_usage/(1024*1024),2) . $unit;

            $row = [
                $item->company_name,
                $item->company_name_kana,
                $item->email,
                $item->range . 'ヶ月',
                $dataSize
            ];
            fputcsv($output, $row);
        }
        fclose($output);
        return \file_get_contents($path);
    }

    /**
     * 承認ルートダウンロード処理
     * @param $user
     * @param $params
     * @return void
     */
    public static function getTemplateRoute($user, $params)
    {
        $path = sys_get_temp_dir() .
            "/download-csv-template-route-" .
            AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $user->id) .
            'csv';
        try {
            // 全て部署
            $listDepartmentTree = DepartmentUtils::getDepartmentTree($user->mst_company_id);
            $listDepartmentDetail = DepartmentUtils::buildDepartmentDetail($listDepartmentTree);
            // 全て役職
            $position = new Position();
            $listPosition = $position
                ->select('id', 'position_name as text', 'position_name as sort_name')
                ->where('state', 1)
                ->where('mst_company_id', $user->mst_company_id)
                ->get()
                ->map(function ($sort_name) {
                    $sort_name->sort_name = str_replace(\App\Http\Utils\AppUtils::STR_KANJI, \App\Http\Utils\AppUtils::STR_SUUJI, $sort_name->sort_name);
                    return $sort_name;
                })
                ->keyBy('id')
                ->sortBy('sort_name')
                ->toArray();
            $query = [];
            // ルートに情報を取得
            $query_route = \Illuminate\Support\Facades\DB::table('circular_user_template_routes as r')
                ->select(DB::raw('r.template, GROUP_CONCAT(r.mst_department_id ORDER BY r.child_send_order ASC SEPARATOR \';\') as dep_pos_name,
                 GROUP_CONCAT(r.mst_position_id ORDER BY r.child_send_order ASC SEPARATOR \';\') as mst_position_id,
                 GROUP_CONCAT(r.mode ORDER BY r.child_send_order ASC SEPARATOR \';\') as mode,
                 GROUP_CONCAT(r.option ORDER BY r.child_send_order ASC SEPARATOR \';\') as options'));
            if ($params['department']) {
                $query_route = $query_route->whereRaw("
                    (SELECT COUNT(id) FROM circular_user_template_routes AS cutr1 WHERE cutr1.mst_department_id = ? AND cutr1.template = r.template ) >= 1",[$params['department']]
                );
            }
            if ($params['position']) {
                $query_route = $query_route->whereRaw("
                    (SELECT COUNT(id) FROM circular_user_template_routes AS cutr2 WHERE cutr2.mst_position_id = ?  AND cutr2.template = r.template ) >= 1",[$params['position']]
                );
            }
            $query_route = $query_route->groupBy('r.template');
            $query = DB::table('circular_user_templates as T')
                ->select(['T.id', 'T.name', 'T.state', 'T.update_at', 'R.mode', 'R.options', 'R.dep_pos_name', 'R.mst_position_id'])
                ->joinSub($query_route, 'R',function($join){
                    $join->on('R.template', '=', 'T.id');
                });
            // 名前によるファジークエリ
            if ($params['name']) {
                $query = $query->where('T.name', 'like', '%' . $params['name'] . '%');
            }
            // 有効な検索
            if ($params['state']) {
                $query = $query->where('T.state', 1);
            }
            if (is_array($params['ids'])&& count($params['ids'])>0){
                $query->whereIn('T.id',$params['ids']);
            }
            $query = $query->where('T.mst_company_id', $user->mst_company_id)
                ->where('T.state', '!=', TemplateRouteUtils::TEMPLATE_ROUTE_STATE_DELETES)
                ->get();
            // 「回覧先」と「合議設定」を設定
            $output = fopen($path, 'w');
            if(count($query)>0){
                fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
                $query->each(function ($item) use ($output, $listPosition, $listDepartmentDetail) {
                    $deps = explode(';', $item->dep_pos_name);
                    $poss = explode(';', $item->mst_position_id);
                    $modes = explode(';', $item->mode);
                    $options = explode(';', $item->options);
                    $arr = [];
                    $arr[] = $item->id;
                    $arr[] = $item->name;
                    $arr[] = $item->state;

                    foreach ($deps as $key => $dep) {
                        $dep_name = '';
                        $pos_name = '';
                        // 部署名設定
                        foreach ($listDepartmentDetail as $departmentDetail) {
                            if ((int)$dep == $departmentDetail['id']) {
                                $dep_name = $departmentDetail['text'];
                                break;
                            }
                        }
                        // 役職名設定
                        foreach ($listPosition as $position) {
                            if ((int)$poss[$key] == $position['id']) {
                                $pos_name = $position['text'];
                                break;
                            }
                        }
                        $arr[] = $dep_name;
                        $arr[] = $pos_name;
                        if (strcasecmp($modes[$key], '1')) {
                            $arr[] = 2;
                            $arr[] = $options[$key];
                        } else {
                            $arr[] = 1;
                            $arr[] = "";
                        }
                    }
                    fputcsv($output, $arr);
                })->toArray();
            }
            fclose($output);
            return \file_get_contents($path);
        }catch (\Throwable $throwable){
            Log::error($throwable->getMessage());
            return null;
        }
    }
    // public methods
    // ********************************************************** //

}
