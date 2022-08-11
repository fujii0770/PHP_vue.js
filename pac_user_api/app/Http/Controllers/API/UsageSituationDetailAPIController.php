<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUsageSituationDetailAPIRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsageSituationDetailAPIController extends AppBaseController
{
    public function storeTransfer(CreateUsageSituationDetailAPIRequest $request)
    {
        $input = $request->all();
        $usageSituationDetails = $input['usage_situation_details'];
        $targetDay = $input['target_date'];

        $companyIds = [];
        $guest_company_app_env = null;
        $guest_company_contract_server = null;
        $limit = 100; // 毎回テーブル登録レコード数

        foreach($usageSituationDetails as $usageSituationDetail){
            $companyIds[] = $usageSituationDetail['mst_company_id'];
            $guest_company_app_env = $usageSituationDetail['guest_company_app_env'];
            $guest_company_contract_server = $usageSituationDetail['guest_company_contract_server'];
        }
        try {
            $companies = DB::table('mst_company')->select(['id', 'company_name', 'company_name_kana'])->whereIn('id', $companyIds)->get()->keyBy('id');
            foreach($usageSituationDetails as $index => $usageSituationDetail){
                $usageSituationDetails[$index]['company_name'] = $companies[$usageSituationDetail['mst_company_id']]->company_name;
                $usageSituationDetails[$index]['company_name_kana'] = $companies[$usageSituationDetail['mst_company_id']]->company_name_kana;
                $usageSituationDetails[$index]['create_at'] = Carbon::now();
            }

            DB::beginTransaction();
            DB::table('usage_situation_detail')
                ->where('target_date', $targetDay)
                ->whereNotNull('guest_company_id')
                ->where('guest_company_app_env', $guest_company_app_env)
                ->where('guest_company_contract_server', $guest_company_contract_server)
                ->delete();

            // データ量多すぎを防ぐため、分割
            $usageSituationDetailsLst = array_chunk($usageSituationDetails,$limit);
            foreach ($usageSituationDetailsLst as $usageSituationDetailsEach){
                DB::table('usage_situation_detail')->insert($usageSituationDetailsEach);
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
