<?php

use App\Http\Utils\DepartmentUtils;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UpdateMstDepartmentPac52446TreeData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = DB::table('mst_company')->select('id')->get();

        DB::beginTransaction();
        try {
            foreach ($companies as $company) {
                //更新会社の部署のtree
                $trees = DepartmentUtils::updateCompanyDepartment($company->id);

                foreach ($trees as $id => $tree) {
                    DB::table('mst_department')->where('id', $id)
                        ->update(['tree' => $tree]);
                }
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getTraceAsString());
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
