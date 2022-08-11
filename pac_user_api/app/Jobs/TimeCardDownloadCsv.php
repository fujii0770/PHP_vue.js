<?php

namespace App\Jobs;

use App\Models\TimeCard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadRequestUtils;
use Carbon\Carbon;
use DB;

class TimeCardDownloadCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $targetMonth = null;
    private $targetUser = null;
    private $fileName = null;
    private $download_req_id = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($targetUser, $fileName, $targetMonth, $download_req_id)
    {
        $this->targetMonth = $targetMonth;
        $this->targetUser = $targetUser;
        $this->fileName = $fileName;
        $this->download_req_id = $download_req_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $user = $this->targetUser;

            // ヒント：管理者側のタイムカード履歴ダウンロードと位置が違う。
            $csv_path = '/var/www/pac/pac_user_api/storage/app/template-csv-download-' . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $this->download_req_id) . '.csv';
            // ローカルテスト用パス
            // $csv_path = '/home/pac/pac/pac_user_api/storage/app/template-csv-download-' . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $this->download_req_id) . '.csv';

            // 状態更新 ( 処理待ち:0 => 作成中:1)
            \DB::table('download_request')
                ->where('id', $this->download_req_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_CREATING
                ]);

            $dateStr = str_replace(['年', '月'], ['-', ''], $this->targetMonth);
            $date = Carbon::parse($dateStr);
            $startTime = $date->toDateString();
            $endTime = $date->endOfMonth()->toDateTimeString();

            $timeCards = TimeCard::where('mst_user_id', $user->id)
                ->whereBetween('punched_at', [$startTime, $endTime])
                ->get();

            $output = fopen($csv_path, 'w');
            fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            $tempArr = [];
            // 月の日数を取得する
            $days = $date->daysInMonth;

            for ($i = 1; $i <= $days; $i++) {
                $tempStr = $dateStr . '-' . str_pad($i, 2, 0, STR_PAD_LEFT);
                array_push($tempArr, str_replace('-', '/', $tempStr));
            }

            if ($timeCards->isNotEmpty()) {
                $timeCards = $timeCards->groupBy(function ($item) {
                    return $item->punched_date;
                });
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

            //ダウンロードデータDB保存
            $csv_data = base64_encode(\file_get_contents($csv_path));
            //                            $data = AppUtils::encrypt(base64_encode(\file_get_contents($csv_path)));
            $size = filesize($csv_path);

            DB::transaction(function () use ($csv_data, $size, $user) {
                DB::table('download_wait_data')
                    ->where('download_request_id', $this->download_req_id)
                    ->update([
                        'data' => $csv_data,
                        'update_at' => Carbon::now(),
                        'file_size' => $size,
                    ]);

                // 無害化サーバで無害化処理するか
                $isSanitizing = DB::table('mst_company')
                    ->where('id', $user->mst_company_id)->first()
                    ->sanitizing_flg;
                if ($isSanitizing == 1) {
                    // 状態更新 ( 作成中:1 => 無害化待ち:11)
                    $state = DownloadRequestUtils::REQUEST_SANITIZING_WAIT;
                } else {
                    // 状態更新 ( 作成中:1 => ダウンロード待ち:2)
                    $state = DownloadRequestUtils::REQUEST_DOWNLOAD_WAIT;
                }

                // 状態更新
                DB::table('download_request')
                    ->where('id', $this->download_req_id)
                    ->update([
                        'state' => $state,
                        'contents_create_at' => Carbon::now()
                    ]);
            });
            //ファイル削除

            \Log::info(glob($csv_path));
            array_map('unlink', glob($csv_path));

        } catch (\Exception $e) {
            // リトライ
            DB::table('download_request')
                ->where('id', $this->download_req_id)
                ->update([
                    'state' => DownloadRequestUtils::REQUEST_PROCESS_WAIT
                ]);
            throw $e;
        }
    }
}
