<?php

use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppUtils;
use App\Http\Utils\GwAppApiUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateMstUserInfoByPac52588 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 会社リスト
        $company_ids = DB::table('mst_company')
            ->where('option_user_flg', 1)
            ->orderBy('id')
            ->select('id')
            ->pluck('id')
            ->toArray();
        // 会社ループ
        foreach($company_ids as $company_id){
            // 会社関連ユーザリスト
            DB::table('mst_user')
                ->where ('mst_company_id', '=', $company_id)
                ->where('state_flg', '=', AppUtils::STATE_VALID)
                ->where('option_flg', '=', AppUtils::USER_OPTION)
                ->chunkById(200, function ($items) use ($company_id) {
                    $user_ids = $items->pluck('id')->toArray();
                    // ローカルに、グループウエア有効ユーザリスト
                    $local_user_ids = DB::table('mst_user')
                        ->join('mst_application_users as au', 'mst_user.id', '=', 'au.mst_user_id')
                        ->whereIn('mst_user.id', $user_ids)
                        ->where('au.mst_application_id', '!=', AppUtils::GW_APPLICATION_ID_BOARD)
                        ->whereNotNull('au.mst_user_id')
                        ->select([
                            'mst_user.id'
                        ])
                        ->pluck('mst_user.id')
                        ->toArray();
                    // ローカル有効ユーザは、gw_flgに1を更新します
                    if(count($local_user_ids)){
                        // ローカル無効ユーザで、GW側再判定が必要です。
                        $invalidUsers = array_diff($user_ids, $local_user_ids);
                        DB::table('mst_user_info')
                            ->whereIn('mst_user_id', $local_user_ids)
                            ->update(['gw_flg' => 1]);
                    }else{
                        $invalidUsers = $user_ids;
                    }

                    if(count($invalidUsers)){
                        // 企業管理者
                        $user_admin = DB::table('mst_admin')
                            ->where ('mst_company_id', '=', $company_id)
                            ->where('role_flg', '=', AppUtils::ADMIN_MANAGER_ROLE_FLG)
                            ->select(['mst_company_id', 'email'])
                            ->first();

                        // GW側ユーザ状態
                        $gwValidUsers = [];
                        // スケジュール
                        $response_user_2 = GwAppApiUtils::appUsersSearch($user_admin->email, $company_id, AppUtils::GW_APPLICATION_ID_SCHEDULE, null,
                            null, null, null, -1);
                        if ($response_user_2 === false) {
                            Log::error('Search roleuser portalCompanyId:' . $user_admin->email);
                            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                        }else{
                            foreach ($response_user_2["mstApplicationUsersStateLists"] as $value_user) {
                                if($value_user['enabled']){
                                    $gwValidUsers[] = $value_user['mstUser']['portalId'];
                                }
                            }
                        }
                        // カルダブ
                        $response_user_3 = GwAppApiUtils::appUsersSearch($user_admin->email, $company_id, AppUtils::GW_APPLICATION_ID_CALDAV, null,
                            null, null, null, -1);
                        if ($response_user_3 === false) {
                            Log::error('Search roleuser portalCompanyId:' . $user_admin->email);
                            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                        }else{
                            foreach ($response_user_3["mstApplicationUsersStateLists"] as $value_user) {
                                if($value_user['enabled']){
                                    $gwValidUsers[] = $value_user['mstUser']['portalId'];
                                }
                            }
                        }
                        // Google
                        $response_user_4 = GwAppApiUtils::appUsersSearch($user_admin->email, $company_id, AppUtils::GW_APPLICATION_ID_GOOGLE, null,
                            null, null, null, -1);
                        if ($response_user_4 === false) {
                            Log::error('Search roleuser portalCompanyId:' . $user_admin->email);
                            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                        }else{
                            foreach ($response_user_4["mstApplicationUsersStateLists"] as $value_user) {
                                if($value_user['enabled']){
                                    $gwValidUsers[] = $value_user['mstUser']['portalId'];
                                }
                            }
                        }
                        // OutLook
                        $response_user_5 = GwAppApiUtils::appUsersSearch($user_admin->email, $company_id, AppUtils::GW_APPLICATION_ID_OUTLOOK, null,
                            null, null, null, -1);
                        if ($response_user_5 === false) {
                            Log::error('Search roleuser portalCompanyId:' . $user_admin->email);
                            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                        }else{
                            foreach ($response_user_5["mstApplicationUsersStateLists"] as $value_user) {
                                if($value_user['enabled']){
                                    $gwValidUsers[] = $value_user['mstUser']['portalId'];
                                }
                            }
                        }
                        // Apple
                        $response_user_6 = GwAppApiUtils::appUsersSearch($user_admin->email, $company_id, AppUtils::GW_APPLICATION_ID_APPLE, null,
                            null, null, null, -1);
                        if ($response_user_6 === false) {
                            Log::error('Search roleuser portalCompanyId:' . $user_admin->email);
                            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]]]);
                        }else{
                            foreach ($response_user_6["mstApplicationUsersStateLists"] as $value_user) {
                                if($value_user['enabled']){
                                    $gwValidUsers[] = $value_user['mstUser']['portalId'];
                                }
                            }
                        }
                        // 重複idを削除
                        $gwValidUsers = array_unique($gwValidUsers);
                        // gw有効ユーザは、gw_flgに1を更新します
                        if(count($gwValidUsers)){
                            // gw有効、又は、オプション利用者
                            $gwInvalidUsers = array_intersect($invalidUsers, $gwValidUsers);
                            if(count($gwInvalidUsers)){
                                DB::table('mst_user_info')
                                    ->whereIn('mst_user_id', $gwInvalidUsers)
                                    ->update(['gw_flg' => 1]);
                            }
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('mst_user_info')
            ->update(['gw_flg' => 0]);
    }
}
