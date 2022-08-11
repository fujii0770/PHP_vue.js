<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUsagesDailyAPIRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UsagesDailyAPIController extends AppBaseController
{
    public function storeTransfer(CreateUsagesDailyAPIRequest $request)
    {
        $input = $request->all();
        $usagesDaily = $input['usages_daily'];
        $targetDay = $input['date'];

        $companyIds = [];
//        $keys = [];
        $guest_company_app_env = null;
        $guest_company_contract_server = null;
        $limit = 100; // 毎回テーブル登録レコード数

        foreach($usagesDaily as $usageDaily){
            $companyIds[] = $usageDaily['mst_company_id'];
//            $keys[] = $usageDaily['mst_company_id'].'-'.$usageDaily['guest_company_id'];
            $guest_company_app_env = $usageDaily['guest_company_app_env'];
            $guest_company_contract_server = $usageDaily['guest_company_contract_server'];
        }
        try {
            $companies = DB::table('mst_company')->select(['id', 'company_name', 'company_name_kana'])->whereIn('id', $companyIds)->get()->keyBy('id');
            foreach($usagesDaily as $index => $usageDaily){
                $usagesDaily[$index]['company_name'] = $companies[$usageDaily['mst_company_id']]->company_name;
                $usagesDaily[$index]['company_name_kana'] = $companies[$usageDaily['mst_company_id']]->company_name_kana;
                $usagesDaily[$index]['create_at'] = Carbon::now();
            }

            DB::beginTransaction();
            DB::table('usages_daily')->where('date', $targetDay)
                ->whereNotNull('guest_company_id')
                ->where('guest_company_app_env', $guest_company_app_env)
                ->where('guest_company_contract_server', $guest_company_contract_server)
                ->delete();

            // データ量多すぎを防ぐため、分割
            $usagesDailyLst = array_chunk($usagesDaily,$limit);
            foreach ($usagesDailyLst as $usagesDailyEach){
                DB::table('usages_daily')->insert($usagesDailyEach);
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
