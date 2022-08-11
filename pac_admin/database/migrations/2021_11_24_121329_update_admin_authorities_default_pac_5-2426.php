<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateAdminAuthoritiesDefaultPac52426 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::beginTransaction();
        try {
            $delete_ids = [];
            $update_ids = [];
            DB::table('admin_authorities_default')
                ->where('code', '制限設定')
                ->groupBy('mst_company_id')
                ->having(DB::raw('COUNT(mst_company_id)'), 3)
                ->select('mst_company_id')
                ->pluck('mst_company_id')
                ->each(function ($company_id) use (&$delete_ids) {
                    $delete_ids[] = DB::table('admin_authorities_default')
                        ->where('code', '制限設定')
                        ->where('mst_company_id', $company_id)
                        ->max('id');
                });
            DB::table('admin_authorities_default')
                ->whereIn('id', $delete_ids)
                ->delete();
            DB::table('admin_authorities_default')
                ->where('code', '制限設定')
                ->groupBy('mst_company_id')
                ->having(DB::raw('COUNT(mst_company_id)'), 2)
                ->select('mst_company_id')
                ->pluck('mst_company_id')
                ->each(function ($company_id) use (&$update_ids) {
                    $update_ids[] = DB::table('admin_authorities_default')
                        ->where('code', '制限設定')
                        ->where('mst_company_id', $company_id)
                        ->max('id');
                });

            DB::table('admin_authorities_default')
                ->whereIn('id', $update_ids)
                ->update([
                    'code' => 'スケジューラ制限設定'
                ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
