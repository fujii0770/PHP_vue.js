<?php

namespace App\Console\Commands;

use App\Http\Utils\AppUtils;
use App\CompanyAdmin;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\MailUtils;
use App\Models\Department;
use App\Models\DepartmentCsv;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentExportCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'department:exportcsv {mst_company_id} {recordID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export department to csv';

    private $department;
    private $departmentCsv;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Department $department, DepartmentCsv $departmentCsv)
    {
        parent::__construct();
        $this->department = $department;
        $this->departmentCsv = $departmentCsv;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $mst_company_id = $this->argument('mst_company_id');
            $recordID = $this->argument('recordID');
            Log::debug("Export Department for: mst_company_id = $mst_company_id and recordID = $recordID");

            $itemsDepartment = DepartmentUtils::getDepartmentTree($mst_company_id);
            $items = DepartmentUtils::buildDepartmentDetail($itemsDepartment);

            $contentsCsv = [];
            foreach ($items as $item) {
                // 出力対象判定(一番下階層のみ→IDがparent_idで検索結果なし)
                $parent_record_count = $this->department->where('parent_id', $item['id'])->count();
                if (!$parent_record_count) {
                    // 部署ID、部署名、動作モード(2:更新)
                    $contentsCsv[] = [$item['id'], $item['text'], 2];
                }
            }
            $names = array_column($contentsCsv, 1);
            array_multisort($names, SORT_ASC, $contentsCsv);

            $item = $this->departmentCsv->find($recordID);
            $item->contents = json_encode($contentsCsv);
            $item->contents_create_at = date("Y-m-d H:i:s");
            $item->file_name = "部署_" . date("YmdHis") . ".csv";
            $item->state = 1;

            $admin = CompanyAdmin::find($item->mst_user_id);
            $data['adminName'] = $admin->getFullName();

            DB::beginTransaction();

            $item->save();
            DB::commit();
            Log::debug("Export Department: finish");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . $e->getTraceAsString());
        }

        // send mail alert to create user
        $admin = CompanyAdmin::find($item->mst_user_id);
        $data['adminName'] = $admin->getFullName();

        //管理者:部署CSVダウンロード出力通知
        MailUtils::InsertMailSendResume(
            // 送信先メールアドレス
            $admin->email,
            // メールテンプレート
            MailUtils::MAIL_DICTIONARY['EXPORT_DEPARTMENT_ALERT']['CODE'],
            // パラメータ
            json_encode($data,JSON_UNESCAPED_UNICODE),
            // タイプ
            AppUtils::MAIL_TYPE_ADMIN,
            // 件名
            config('app.mail_environment_prefix') . trans('mail.prefix.admin') . trans('mail.SendExportDepartmentAlert.subject'),
            // メールボディ
            trans('mail.SendExportDepartmentAlert.body',$data)
        );


        Log::debug("Export Department: finish");
    }
}
