<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstCompanyAddColumnPac52508 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('mst_company', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('mst_company','template_approval_route_flg')) {
                $table->integer('template_approval_route_flg')->default(0)->comment('テンプレート承認ルート:0:無効|1:有効');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('mst_company', function (Blueprint $table) {
            //
            if (Schema::hasColumn('mst_company','template_approval_route_flg')) {
                $table->dropColumn('template_approval_route_flg');
            }
        });
    }
}
