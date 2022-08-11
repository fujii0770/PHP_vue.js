<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;


class ClearImportCsvHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_csv_history:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'バッチ処理で1月以前のcsv取込履歴を削除する';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('import-csv-daily')->debug("import_csv_history clear start");

        try{
            DB::beginTransaction();
            // 1月以前の時間取得
            $last_month = date("Y/m/d 00:00:00", strtotime("-1 month"));
            Log::channel('import-csv-daily')->debug('1月以前の時間取得:'.$last_month);
            // 1月以前のcsv取込履歴を削除する
            DB::table('csv_import_list')->where('create_at', '<', $last_month)->delete();
            // 1月以前のcsv取込履歴詳細を削除する
            DB::table('csv_import_detail')->where('create_at', '<', $last_month)->delete();
            // 1月以前のフォルダ 時間取得
            $delete_file_date = strtotime("-1 day", strtotime($last_month));
            $delete_year_folder = date('Y', $delete_file_date);
            $delete_month_folder = date('m', $delete_file_date);
            $delete_day_folder = date('d', $delete_file_date);
            // 1月以前のcsv取込フォルダを削除する
            if(is_dir(storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder.'/'.$delete_day_folder))){
                $this->delTree(storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder.'/'.$delete_day_folder));
                Log::channel('import-csv-daily')->debug('削除フォルダ:'.storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder.'/'.$delete_day_folder));
                // 月フォルダの下にファイルがありません。
                if(is_dir(storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder))){
                    $files_m = scandir(storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder));
                    if (empty($files_m[2])) {
                        $this->delTree(storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder));
                        Log::channel('import-csv-daily')->debug('削除フォルダ:'.storage_path('import_csv/'.$delete_year_folder.'/'.$delete_month_folder));
                    }
                }
                // 年フォルダの下にファイルがありません。
                if(is_dir(storage_path('import_csv/'.$delete_year_folder))){
                    $files_Y = scandir(storage_path('import_csv/'.$delete_year_folder));
                    if (empty($files_Y[2])) {
                        $this->delTree(storage_path('import_csv/'.$delete_year_folder));
                        Log::channel('import-csv-daily')->debug('削除フォルダ:'.storage_path('import_csv/'.$delete_year_folder));
                    }
                }
            }
            DB::commit();
            Log::channel('import-csv-daily')->debug('import_csv_history clear finished');
        }catch(\Exception $e){
            DB::rollBack();
            Log::channel('import-csv-daily')->error('import_csv_history clear failed');
            Log::channel('import-csv-daily')->error($e->getMessage().$e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * フォルダを削除
     *
     * @param $directory
     * @return bool
     */
    private function delTree($directory)
    {
        $files = array_diff(scandir($directory), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? $this->delTree("$directory/$file") : unlink("$directory/$file");
        }
        return rmdir($directory);
    }
}
