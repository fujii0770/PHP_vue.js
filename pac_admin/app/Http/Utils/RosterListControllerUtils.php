<?php

namespace App\Http\Utils;

use App\Models\NewTimeCard;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularUtils;
use App\Http\Utils\DepartmentUtils;
use App\Http\Utils\DownloadUtils;
use App\Http\Utils\OperationsHistoryUtils;
use App\Http\Utils\PermissionUtils;

use App\Models\User;
use App\Models\Company;
use App\Models\Position;
use App\Models\Department;
use App\Models\UsageSituation;
use App\Models\DownloadRequest;
use App\Models\DownloadWaitData;

/**
 * 名簿リスト処理ユーティリティクラス
 * Class DownloadControllerUtils
 * @package App\Http\Utils
 */
class RosterListControllerUtils
{
	/**
     * タイムカード利用者一覧CSVデータ取得
     * @param $user 利用者情報
     * @param $hr_timecard
     * @param $hr_timecard_detail
     * @param $outputList
     * @param $hr_timecard_ids
     * 
     */
	public static function getRosterListCsvFileName($mst_user, $hr_timecard, $hr_timecard_detail, $outputList, $hr_timecard_ids){
		try {
	
			$header = ["ユーザ名", "メールアドレス"];
			$headerFormat = RosterListControllerUtils::getRosterListCsvHeader($header, $outputList);

			if(count($hr_timecard_ids) == 1){
				$data = RosterListControllerUtils::getRosterListCsv(
							$mst_user, $hr_timecard, $hr_timecard_detail, $hr_timecard_ids, $outputList, $headerFormat
						);
				return $data[1];
			}

			$timeCardDetailExport = [];
			foreach ($hr_timecard_ids as $id) {
				$data = RosterListControllerUtils::getRosterListCsv(
							$mst_user, $hr_timecard, $hr_timecard_detail, $hr_timecard_ids, $outputList, $headerFormat
						);
				if (!$data) {
					continue;
				}
				$streamContent = $data[0];
				$csv_name = $data[1];
				$user = $data[2];

				$timeCardDetailExport[$user][$csv_name] = $streamContent;
			}

			$list_user = [];
			$single_user_zip_name = '';
			foreach ($timeCardDetailExport as $user => $list_csv) {
				$file_zip_user = $user . '.zip';
				$single_user_zip_name = $file_zip_user;
				$path_zip_user = sys_get_temp_dir().'/zip-user'. $file_zip_user;
				$zip = new \ZipArchive();
				$zip->open($path_zip_user, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
				foreach ($list_csv as $name => $content) {
					$zip->addFromString($name, $content);
				}
				$zip->close();
				$list_user[$file_zip_user] = \file_get_contents($path_zip_user);
			}

			if (count($list_user) == 1) {
				$fileName = $single_user_zip_name;
			} else {
				$now = Carbon::now()->format('YmdHis');
				$fileName = '月勤務情報_' . $now . '.zip';
			}
	
			return $fileName;

		} catch (\Throwable $th) {
			Log::error($th->getMessage().$th->getTraceAsString());
			return "";
		}
	}

	/**
     * 利用者タイムカードリストデータ取得
     * 
     * @param $mst_user
     * @param $hr_timecard
     * @param $hr_timecard_detail
     * @param $param
     */
	public static function getRosterListData($mst_user, $hr_timecard, $hr_timecard_detail, $param){
        $outputList 		= $param['outputList'];
        $hr_timecard_ids 	= $param['timecard_ids'];
		$header = ["ユーザ名", "メールアドレス"];
        $headerFormat = RosterListControllerUtils::getRosterListCsvHeader($header, $outputList);
		$dl_data = "";
		// 選択数が1つ
        if (count($hr_timecard_ids) == 1) {
			$data = RosterListControllerUtils::getRosterListCsv(
						$mst_user, $hr_timecard, $hr_timecard_detail, $hr_timecard_ids, $outputList, $headerFormat
						);
            $dl_data = $data[0];
		}else{
			$timeCardDetailExport = [];
            foreach ($hr_timecard_ids as $id) {
                $data = RosterListControllerUtils::getRosterListCsv(
							$mst_user, $hr_timecard, $hr_timecard_detail, $hr_timecard_ids, $outputList, $headerFormat
						);
                if (!$data) {
                    continue;
                }
                $streamContent = $data[0];
                $csv_name = $data[1];
                $user = $data[2];

                $timeCardDetailExport[$user][$csv_name] = $streamContent;
            }

            $list_user = [];
            $single_user_zip_name = '';
            $single_user_zip_path = '';
            foreach ($timeCardDetailExport as $user => $list_csv) {
                $file_zip_user = $user . '.zip';
                $single_user_zip_name = $file_zip_user;
                $path_zip_user = sys_get_temp_dir().'/zip-user'. $file_zip_user;
                $single_user_zip_path = $path_zip_user;
                $zip = new \ZipArchive();
                $zip->open($path_zip_user, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                foreach ($list_csv as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
                $list_user[$file_zip_user] = \file_get_contents($path_zip_user);
            }

            if (count($list_user) == 1) {
                $fileName = $single_user_zip_name;
                $path = $single_user_zip_path;
            } else {
                $now = Carbon::now()->format('YmdHis');
                $fileName = '月勤務情報_' . $now . '.zip';
                $path = sys_get_temp_dir().'/zip-user'. $fileName .'.zip';
                $new_zip = new \ZipArchive();
                $new_zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                foreach ($list_user as $name => $content) {
                    $new_zip->addFromString($name, $content);
                }
                $new_zip->close();
            }

			$dl_data = \file_get_contents($path);
		}

		return $dl_data;
	}

	/**
     * 利用者タイムカードリストCSVのヘッダ取得
     * 
     * @param $headerFormat
     * @param $outputList
     */
	public static function getRosterListCsvHeader($headerFormat, $outputList) {
        if ($outputList['work_date']) {
            array_push($headerFormat, "業務日(yyyymmdd)");
        }
        if ($outputList['work_start_time']) {
            array_push($headerFormat, "出勤時間(yyyymmdd　hh:nn)");
        }
        if ($outputList['work_end_time']) {
            array_push($headerFormat, "退勤時間(yyyymmdd　hh:nn)");
        }
        if ($outputList['break_time']) {
            array_push($headerFormat, "休憩時間(nn)");
        }
        if ($outputList['working_time']) {
            array_push($headerFormat, "稼働時間");
        }
        if ($outputList['overtime']) {
            array_push($headerFormat, "残業時間");
        }
        if ($outputList['late_flg']) {
            array_push($headerFormat, "遅刻フラグ");
        }
        if ($outputList['earlyleave_flg'])
            array_push($headerFormat, "早退フラグ");{
        }
        if ($outputList['paid_vacation_flg']) {
            array_push($headerFormat, "有給フラグ");
        }
        if ($outputList['sp_vacation_flg']) {
            array_push($headerFormat, "特休フラグ");
        }
        if ($outputList['day_off_flg']) {
            array_push($headerFormat, "代休フラグ");
        }
        if ($outputList['memo']) {
            array_push($headerFormat, "備考");
        }
        if ($outputList['admin_memo']) {
            array_push($headerFormat, "管理者コメント");
        }
        return $headerFormat;
    }

    /**
     * 利用者タイムカードリストCSV取得
     * 
     * @param $mst_user
     * @param $hr_timecard
     * @param $hr_timecard_detail
     * @param $hr_timecard_ids
     * @param $outputList
     * @param $headerFormat
     */
	public static function getRosterListCsv($mst_user, $hr_timecard, $hr_timecard_detail, $hr_timecard_ids, $outputList, $headerFormat) {
        $hr_timecard = $hr_timecard->where('id', $hr_timecard_ids)->first();
        $mst_user = $mst_user->where('id', $hr_timecard->mst_user_id)->first();
        $year = substr($hr_timecard->working_month, 0, 4);
        $month = substr($hr_timecard->working_month, 4, 2);
        $timeCardDetails = $hr_timecard_detail
            ->where([
                'mst_user_id' => $mst_user->id,
                ['work_date', 'LIKE', $hr_timecard->working_month . '%']
            ])->exportWorkListCSV()->orderBy('work_date', 'asc')->get();

        $csv_name = $year . '年' . $month . '月勤務情報(' . $mst_user->email . ')' . Carbon::now()->format('YmdHis') . '.csv';
        $fd = fopen('php://temp/'.$csv_name, 'r+');
        fputcsv($fd, $headerFormat);
        if (count($timeCardDetails) != 0) {
            foreach ($timeCardDetails as $timeCardDetail) {
                $user_name = $mst_user->family_name . '　' . $mst_user->given_name;
                $email = $mst_user->email;
                $timeCard = [];
                array_push($timeCard, $user_name);
                array_push($timeCard, $email);
                $record = RosterListControllerUtils::getRosterListCsvRowPart($timeCard, $timeCardDetail, $outputList);
                fputcsv($fd, $record);
            }
        }
        rewind($fd);
        // Convert CRLF
        $streamContent = str_replace(PHP_EOL, "\r\n", stream_get_contents($fd));
        fclose($fd);
        $user = $mst_user->family_name . $mst_user->given_name . '.'. $mst_user->id;

        // Convert row data from UTF-8 to Shift-JS
        $streamContent = mb_convert_encoding($streamContent, 'SJIS', 'UTF-8');
        return [$streamContent, $csv_name, $user];
    }

    /**
     * 利用者タイムカードリストCSV1行取得
     * 
     * @param $timeCard
     * @param $timeCardDetail
     * @param $outputList
     */
	public static function getRosterListCsvRowPart($timeCard, $timeCardDetail, $outputList) {
        if ($outputList['work_date']) {
            if (isset($timeCardDetail->work_date)) {
                array_push($timeCard, $timeCardDetail->work_date);
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['work_start_time']) {
            if (isset($timeCardDetail->work_start_time)) {
                $workStartTime = Carbon::parse($timeCardDetail->work_start_time)->format('Ymd　H:i');
                array_push($timeCard, $workStartTime);
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['work_end_time']) {
            if (isset($timeCardDetail->work_end_time)) {
                $workEndTime = Carbon::parse($timeCardDetail->work_end_time)->format('Ymd　H:i');
                array_push($timeCard, $workEndTime);
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['break_time']) {
            if (isset($timeCardDetail->break_time) && isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                if ($timeCardDetail->break_time > 0) {
                    array_push($timeCard, $timeCardDetail->break_time);
                } else if ($timeCardDetail->break_time == 0) {
                    if (isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                        array_push($timeCard, "0");
                    }
                }
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['working_time']) {
            if (isset($timeCardDetail->working_time) && isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                $hours = intdiv($timeCardDetail->working_time, 60);
                if ($hours < 10) {
                    $hours = '0' . $hours;
                }
                $minutes = ($timeCardDetail->working_time % 60);
                $minutes = date('i', mktime(0, $minutes));
                $time = $hours . ':' . $minutes;
                array_push($timeCard, $time);
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['overtime']) {
            if (isset($timeCardDetail->overtime) && isset($timeCardDetail->work_start_time) && isset($timeCardDetail->work_end_time)) {
                $hours = intdiv($timeCardDetail->overtime, 60);
                if ($hours < 10) {
                    $hours = '0' . $hours;
                }
                $minutes = ($timeCardDetail->overtime % 60);
                $minutes = date('i', mktime(0, $minutes));
                $time = $hours . ':' . $minutes;
                array_push($timeCard, $time);
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['late_flg']) {
            if ($timeCardDetail->late_flg == 1) {
                array_push($timeCard, "遅刻");
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['earlyleave_flg']) {
            if ($timeCardDetail->earlyleave_flg == 1) {
                array_push($timeCard, "早退");
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['paid_vacation_flg']) {
            if ($timeCardDetail->paid_vacation_flg == 1) {
                array_push($timeCard, "有給");
            } else if ($timeCardDetail->paid_vacation_flg == 2) {
                array_push($timeCard, "有給（半休）");
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['sp_vacation_flg']) {
            if ($timeCardDetail->sp_vacation_flg == 1) {
                array_push($timeCard, "特休");
            } else if ($timeCardDetail->sp_vacation_flg == 2) {
                array_push($timeCard, "特休（半休）");
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['day_off_flg']) {
            if ($timeCardDetail->day_off_flg == 1) {
                array_push($timeCard, "代休");
            } else if ($timeCardDetail->day_off_flg == 2) {
                array_push($timeCard, "代休（半休）");
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['memo']) {
            if (isset($timeCardDetail->memo)) {
                $memo = $timeCardDetail->memo;
                $memo = str_replace(',', "','", $memo);
                $memo = str_replace("\n", "（改行）", $memo);
                array_push($timeCard, $memo);
            } else {
                array_push($timeCard, "");
            }
        }
        if ($outputList['admin_memo']) {
            if (isset($timeCardDetail->admin_memo)) {
                $adminMemo = $timeCardDetail->admin_memo;
                $adminMemo = str_replace(',', "','", $adminMemo);
                array_push($timeCard, $adminMemo);
            } else {
                array_push($timeCard, "");
            }
        }
        return $timeCard;
    }
}