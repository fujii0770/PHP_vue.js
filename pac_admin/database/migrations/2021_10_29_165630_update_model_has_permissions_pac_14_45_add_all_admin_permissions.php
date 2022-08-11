<?php

use Illuminate\Database\Migrations\Migration;

class UpdateModelHasPermissionsPac1445AddAllAdminPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $objAdmin = DB::table('mst_admin')
            ->where('state_flg',\App\Http\Utils\AppUtils::STATE_VALID)
            ->get();

        if(!empty($objAdmin)){
            foreach($objAdmin as $key=>$item){
                DB::table("model_has_permissions")->updateOrInsert([
                    "permission_id" => 123,
                    "model_type" => "App\CompanyAdmin",
                    "model_id" => $item->id,
                ]);
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
