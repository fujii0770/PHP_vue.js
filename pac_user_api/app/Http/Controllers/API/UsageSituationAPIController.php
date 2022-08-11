<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUsageSituationAPIRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsageSituationAPIController extends AppBaseController
{
    public function storeTransfer(CreateUsageSituationAPIRequest $request)
    {
        $input = $request->all();
        $usageSituations = $input['usage_situations'];
        $targetMonth = $input['target_month'];

        $companyIds = [];
        //        $keys = [];
        $guest_company_app_env = null;
        $guest_company_contract_server = null;
        $limit = 100; // 毎回テーブル登録レコード数

        foreach($usageSituations as $usageSituation){
            $companyIds[] = $usageSituation['mst_company_id'];
            //            $keys[] = $usageSituation['mst_company_id'].'-'.$usageSituation['guest_company_id'];
            $guest_company_app_env = $usageSituation['guest_company_app_env'];
            $guest_company_contract_server = $usageSituation['guest_company_contract_server'];
        }
        try {
            $companies = DB::table('mst_company')->select(['id', 'company_name', 'company_name_kana'])->whereIn('id', $companyIds)->get()->keyBy('id');
            foreach($usageSituations as $index => $usageSituation){
                $usageSituations[$index]['company_name'] = $companies[$usageSituation['mst_company_id']]->company_name;
                $usageSituations[$index]['company_name_kana'] = $companies[$usageSituation['mst_company_id']]->company_name_kana;
                $usageSituations[$index]['create_at'] = Carbon::now();
                $usageSituations[$index]['target_month'] = $targetMonth;
            }

            // 最大値編集
            $oldUsageSituations = DB::table('usage_situation')
                ->where('target_month', $targetMonth)
                ->whereNotNull('guest_company_id')
                ->where('guest_company_app_env', $guest_company_app_env)
                ->where('guest_company_contract_server', $guest_company_contract_server)
                ->get()
                ->keyBy('guest_company_id');

            foreach ($oldUsageSituations as $company_id => $oldUsageSituation){
                if (key_exists($company_id, $usageSituations)){

                    $oldStampTotal = $oldUsageSituation->total_name_stamp + $oldUsageSituation->total_date_stamp + $oldUsageSituation->total_common_stamp;
                    $newStampTotal = $usageSituations[$company_id]['total_name_stamp'] + $usageSituations[$company_id]['total_date_stamp'] + $usageSituations[$company_id]['total_common_stamp'];

                    // 統計数（印面の合計数）を基準にして、最大の日のレコードで表示
                    if($oldStampTotal > $newStampTotal){
                        $usageSituations[$company_id]['user_total_count'] = $oldUsageSituation->user_total_count;
                        $usageSituations[$company_id]['total_name_stamp'] = $oldUsageSituation->total_name_stamp;
                        $usageSituations[$company_id]['total_date_stamp'] = $oldUsageSituation->total_date_stamp;
                        $usageSituations[$company_id]['total_common_stamp'] = $oldUsageSituation->total_common_stamp;
                        $usageSituations[$company_id]['max_date'] = $oldUsageSituation->max_date;
                    }
                }
            }
            DB::beginTransaction();
            DB::table('usage_situation')
                ->where('target_month', $targetMonth)
                ->whereNotNull('guest_company_id')
                ->where('guest_company_app_env', $guest_company_app_env)
                ->where('guest_company_contract_server', $guest_company_contract_server)
                ->delete();

            // データ量多すぎを防ぐため、分割
            $usageSituationsLst = array_chunk($usageSituations,$limit);
            foreach ($usageSituationsLst as $usageSituationsEach){
                DB::table('usage_situation')->insert($usageSituationsEach);
            }
            DB::commit();

            return $this->sendResponse([], '');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError('');
        }
    }
}
