<?php


namespace App\Http\Utils;


use App\Models\Circular;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InsertUsageSituationUtils
{

    /**
     * 対象期間のユーザーファイルの使用容量
     * @param $month int 1,3,6
     * @param $doc_start_time Carbon 統計開始時間
     * @param $doc_end_time Carbon 統計終了時間
     * @param $now string 統計開始時間
     * @return array|Collection 統計結果
     */
    public static function getMonthsUsageSituation($month, $doc_start_time, $doc_end_time, $now){
        $user = new User();
        $items = [];
        if ($month != '' || $month != 0){
            for ($i=0; $i <= $month; $i++){
                $carbon_now = Carbon::parse($now);
                $finished_month = $i == 0 ? '' : $carbon_now->subMonthsNoOverflow($i)->format('Ym');
                $usages = $user->getUsageSituation($finished_month, $doc_start_time, $doc_end_time);
                $usages->each(function ($usage) use(&$items){
                    if (in_array($usage->email,array_keys($items))){
                        $items[$usage->email]->datasize += $usage->datasize;
                    }else{
                        $items[$usage->email] = $usage;
                    }
                });
            }
            $items = collect(array_values($items));
        }
        return $items;
    }

    /**
     * 容量集計
     * @param $type int 1:ユーザー数,2:ドキュメントデータ容量,3:添付ファイルデータ容量,4:社外経由数（送信）,5:社外経由数（受信）
     * @param $now string 現在の時刻
     * @param $targetDay string format yyyy-mm-dd
     * @param $company_id int 会社のID
     * @return array 統計結果
     */
    public static function getCircularUsageDetail(int $type, string $now, string $targetDay = '', int $company_id = 0): array
    {
        $circular = new Circular();
        $usage_situation_details = [];
        $carbon_now = Carbon::parse($now);
        for ($i=0; $i < 12; $i++){
            $finished_month = $i == 0 ? '' : $carbon_now->subMonthNoOverflow()->format('Ym');
            switch ($type){
                case AppUtils::CIRCULAR_USER_COUNT://ユーザー数（アクティビティ＋アクティビティ率）
                    $usages = $circular->getCircularUserCount($finished_month, $targetDay);
                    $usages->each(function ($usage) use(&$usage_situation_details){
                        if (in_array($usage->mst_company_id,array_keys($usage_situation_details))){
                            $usage_situation_details[$usage->mst_company_id]->activity_user_cnt += $usage->activity_user_cnt;
                        }else{
                            $usage_situation_details[$usage->mst_company_id] = $usage;
                        }
                    });
                    break;
                case AppUtils::CIRCULAR_DOCUMENT_DATA_SIZE://ドキュメントデータ容量
                    $usages = $circular->getCircularDocumentDataSize($finished_month, $now, $company_id);
                    $usages->each(function ($usage) use(&$usage_situation_details){
                        if (in_array($usage->create_company_id,array_keys($usage_situation_details))){
                            $usage_situation_details[$usage->create_company_id]->storage_size += $usage->storage_size;
                        }else{
                            $usage_situation_details[$usage->create_company_id] = $usage;
                        }
                    });
                    break;
                case AppUtils::CIRCULAR_ATTACHMENT_DATA_SIZE://添付ファイルデータ容量
                    $usages = $circular->getCircularAttachmentSize($finished_month, $now, $company_id);
                    $usages->each(function ($usage) use(&$usage_situation_details){
                        if (in_array($usage->create_company_id,array_keys($usage_situation_details))){
                            $usage_situation_details[$usage->create_company_id]->storage_size += $usage->storage_size;
                        }else{
                            $usage_situation_details[$usage->create_company_id] = $usage;
                        }
                    });
                    break;
                case AppUtils::CIRCULAR_OUTSIDE_SEND_COUNT://社外経由数（送信）
                    $usages = $circular->getCircularOutsideSendCount($finished_month, $targetDay);
                    $usages->each(function ($usage) use(&$usage_situation_details){
                        if (in_array($usage->mst_company_id,array_keys($usage_situation_details))){
                            $usage_situation_details[$usage->mst_company_id]->request_count += $usage->request_count;
                        }else{
                            $usage_situation_details[$usage->mst_company_id] = $usage;
                        }
                    });
                    break;
                case AppUtils::CIRCULAR_OUTSIDE_RECEIVE_COUNT://社外経由数（受信）
                    $usages = $circular->getCircularOutsideReceiveCount($finished_month, $targetDay);
                    $usages->each(function ($usage) use(&$usage_situation_details){
                        if (in_array($usage->mst_company_id,array_keys($usage_situation_details))){
                            $usage_situation_details[$usage->mst_company_id]->request_count += $usage->request_count;
                        }else{
                            $usage_situation_details[$usage->mst_company_id] = $usage;
                        }
                    });
                    break;
            }
        }
        return $usage_situation_details;
    }
}