<?php

namespace App\Jobs;

use App\Http\Controllers\Csv\CsvController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CsvUtils;
use App\Http\Utils\DepartmentUtils;
use App\Models\ApiUsers;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\RequestInfo;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class PeCreateCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request_id;
    protected $login_id;
    public static $arr_res = [];

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
        try {
            // 「実行開始時間」を更新する
            $request_info = RequestInfo::where('id', $this->request_id)->first();
            $request_info->execution_start_datetime = Carbon::now();
            $request_info->execution_flg = 0;
            $request_info->save();
            $apiUser = ApiUsers::where('login_id',$this->login_id)->first();
            $addresses = array_filter(explode(';',$apiUser->email_addresses));// 結果通知先メールアドレス
            $date = Carbon::now();
            $mst_company_id = $request_info->mst_company_id;

            if (Storage::disk('csv')->exists(CsvUtils::CREATE_PATH . $mst_company_id . '/')) {
                Storage::disk('csv')->deleteDirectory(CsvUtils::CREATE_PATH . $mst_company_id . '/');
            }
            Storage::disk('csv')->makeDirectory(CsvUtils::CREATE_PATH . $mst_company_id . '/');

        }catch (Exception $e){
            // 異常処理
            $this->failed($e);
        }

        try {
            // 部署情報一覧出力
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

            // CSV作成
            // ファイル名
            $department_filename = 'pe_department_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv';
            $csv = Writer::createFromPath(storage_path(CsvUtils::CREATE_PATH) .$mst_company_id . '/' . $department_filename, 'w');
            $csv->addStreamFilter('convert.iconv.UTF-8/CP932');
            // 改行コード
            $csv->setNewline("\r\n");
            $csv->insertAll($contentsCsv);

        }catch (Exception $e){
            // 異常処理
            $this->failed($e);
        }

        try {
            // 役職情報一覧出力
            $itemsPosition = Position::select('position_name')
                ->where('mst_company_id',$mst_company_id)
                ->where('state',1)
                ->get()
                ->toArray();

            // CSV作成
            // ファイル名
            $position_filename = 'pe_position_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv';
            $csv = Writer::createFromPath(storage_path(CsvUtils::CREATE_PATH) .$mst_company_id . '/' . $position_filename, 'w');
            $csv->addStreamFilter('convert.iconv.UTF-8/CP932');
            // 改行コード
            $csv->setNewline("\r\n");
            $csv->insertAll($itemsPosition);

        }catch (Exception $e){
            // 異常処理
            $this->failed($e);
        }

        try {
            // 利用者情報CSV出力
            $users = User::where('mst_company_id', $mst_company_id)->where('state_flg',"!=",AppUtils::STATE_DELETE)->get();
            $company = Company::where('id', $mst_company_id)->first();
            $company->domain = explode("\r\n", $company->domain);
            if (count($company->domain) == 1){
                $company->domain = explode("\n", $company->domain[0]);
            }
            $email_domain_company = [];
            foreach($company->domain as $domain){
                $email_domain_company[$domain] = ltrim($domain,"@");
            }

            $listDepartmentTree = DepartmentUtils::getDepartmentTree($mst_company_id);

            $listPosition = Position::select('id' , 'position_name as text' , 'position_name as sort_name')
                ->where('state',1)
                ->where('mst_company_id',$mst_company_id)
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
            $members = [];
            foreach ($users as $user) {
                $department = isset($listDepartmentDetail[$user->info->mst_department_id]) ? $listDepartmentDetail[$user->info->mst_department_id]['text'] : '';
                $position_id = $user->info->mst_position_id;
                $members[] = [$user->email, $user->family_name, $user->given_name, $department,
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
            }
            //CSV作成
            //ファイル名
            $members_filename = 'pe_members_' . $date->format('Ymd') . '-' . $date->format('His') . '.csv';
            $csv = Writer::createFromPath(storage_path(CsvUtils::CREATE_PATH) .$mst_company_id . '/' . $members_filename, 'w');
            $csv->addStreamFilter('convert.iconv.UTF-8/CP932');
            // 改行コード
            $csv->setNewline("\r\n");
            $csv->insertAll($members);

            //to sftp
            $filepath =  $apiUser->sftp_username . '/download/';
            Storage::disk('csv_user')->put($filepath . $date->format('Ymd') . '/' . $members_filename, file_get_contents(storage_path(config("app.create_path")) .$mst_company_id . '/' . $members_filename));
            Storage::disk('csv_user')->put($filepath . $date->format('Ymd') . '/' . $department_filename, file_get_contents(storage_path(config("app.create_path")) .$mst_company_id . '/' . $department_filename));
            Storage::disk('csv_user')->put($filepath . $date->format('Ymd') . '/' . $position_filename, file_get_contents(storage_path(config("app.create_path")).$mst_company_id . '/' . $position_filename));

            // 処理が正常終了した場合
            $request_info->result = 1;
            $request_info->execution_flg = 1;
            $request_info->execution_end_datetime = Carbon::now();
            $request_info->save();

            CsvUtils::sendMail($this->request_id, $addresses,$filepath . Carbon::now()->format('Ymd') . '/' );
        }catch (Exception $e){
            // 異常処理
            $this->failed($e);
        }
    }

    public function failed(Exception $e)
    {
        Log::error($e->getMessage());
        Log::error($e->getTraceAsString());
        $apiUser = ApiUsers::where('login_id',$this->login_id)->first();
        $addresses = array_filter(explode(';',$apiUser->email_addresses));// 結果通知先メールアドレス

        // 処理が異常終了した場合
        $request_info = RequestInfo::where('id', $this->request_id)->first();
        $request_info->result = 0;
        $request_info->execution_flg = 1;
        $request_info->execution_end_datetime = Carbon::now();
        $request_info->message = $e->getMessage();
        $request_info->save();

        CsvUtils::sendMail($this->request_id,$addresses,'');
        // 処理结束
    }
}
