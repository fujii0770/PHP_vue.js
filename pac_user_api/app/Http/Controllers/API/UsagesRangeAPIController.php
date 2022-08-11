<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUsagesRangeAPIRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UsagesRangeAPIController extends AppBaseController
{
    public function storeTransfer(CreateUsagesRangeAPIRequest $request)
    {
        $input = $request->all();
        $usagesRange = $input['usages_range'];

        $companyIds = [];
//        $keys = [];
        $guest_company_app_env = null;
        $guest_company_contract_server = null;
        $limit = 100; // 毎回テーブル登録レコード数

        foreach($usagesRange as $usageRange){
            $companyIds[] = $usageRange['mst_company_id'];
//            $keys[] = $usageRange['mst_company_id'].'-'.$usageRange['guest_company_id'];
            $guest_company_app_env = $usageRange['guest_company_app_env'];
            $guest_company_contract_server = $usageRange['guest_company_contract_server'];
        }
        try {
            $companies = DB::table('mst_company')->select(['id', 'company_name', 'company_name_kana'])->whereIn('id', $companyIds)->get()->keyBy('id');
            foreach($usagesRange as $index => $usageRange){
                $usagesRange[$index]['company_name'] = $companies[$usageRange['mst_company_id']]->company_name;
                $usagesRange[$index]['company_name_kana'] = $companies[$usageRange['mst_company_id']]->company_name_kana;
                $usagesRange[$index]['create_at'] = Carbon::now();
            }

            DB::beginTransaction();
            DB::table('usages_range')->whereNotNull('guest_company_id')
                ->where('guest_company_app_env', $guest_company_app_env)
                ->where('guest_company_contract_server', $guest_company_contract_server)
                ->delete();

            // データ量多すぎを防ぐため、分割
            $usagesRangeLst = array_chunk($usagesRange,$limit);
            foreach ($usagesRangeLst as $usagesRangeEach){
                DB::table('usages_range')->insert($usagesRangeEach);
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
