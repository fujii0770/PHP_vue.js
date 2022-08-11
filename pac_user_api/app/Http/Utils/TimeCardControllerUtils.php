<?php

namespace App\Http\Utils;

use DB;
use Carbon\Carbon;

use App\Http\Utils\AppUtils;
use App\Models\TimeCard;
use App\Models\User;

/**
 * タイムカードコントローラユーティリティクラス
 * Class TimeCardControllerUtils
 * @package App\Http\Utils
 */
class TimeCardControllerUtils
{
	/**
     * タイムカード情報CSVデータ取得
     * @param $user 利用者情報
     * @param string $targetMonth 選択月
     * @param integer $download_req_id ダウンロード要求ID
     * 
     */
	public static function getTimeCardCsvData($user, $targetMonth, $download_req_id){
		
        try {
            // ヒント：管理者側のタイムカード履歴ダウンロードと位置が違う。
            $csv_path = '/var/www/pac/pac_user_api/storage/app/template-csv-download-' . AppUtils::getUniqueName(config('app.pac_contract_app'), config('app.pac_app_env'), config('app.pac_contract_server'), $user->mst_company_id, $download_req_id) . '.csv';

            $dateStr = str_replace(['年', '月'], ['-', ''], $targetMonth);
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
                $tempStr = $date->format('Y-m') . '-' . str_pad($i, 2, 0, STR_PAD_LEFT);
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
            
            $data = \file_get_contents($csv_path);

            //ファイル削除
            \Log::info(glob($csv_path));
            array_map('unlink', glob($csv_path));

            //ダウンロードデータDB保存
			return $data;

        } catch (\Throwable $th) {
            \Log::error($th->getMessage().$th->getTraceAsString());
			return null;
        }
	}
}