<?php

use App\Models\Authority;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAdminAuthoritiesDefaultPac52614Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = DB::table('mst_company')
            ->select('id','portal_flg')
            ->distinct()
            ->get();

        $company_gw_authorities = DB::table('admin_authorities_default')
            ->select('mst_company_id')
            ->where('code','マスタ同期設定')
            ->get()->pluck('mst_company_id')->toArray();

        foreach ($companies as $company){
            if ($company->portal_flg){
                if (!in_array($company->id, $company_gw_authorities)){
                    (new Authority())->initDefaultValuePortal($company->id, 'Shachihata');
                }
            }
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
