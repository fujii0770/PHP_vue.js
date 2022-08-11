<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCircularPac51622Data extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $month = date('Ym');
        for ($i = 0; $i < 12; $i++) {
            $month = date('Ym', strtotime("$month -$i month"));
            // テーブル存在、クラム存在しない
            if (Schema::hasTable("circular$month")) {
//                if (Schema::hasColumn("circular$month", 'copy_flg')) {
//                    Schema::table("circular$month", function (Blueprint $table) {
//                        $table->dropColumn('copy_flg');
//                    });
//                }
                if (!Schema::hasColumn("circular$month", 'final_updated_date')) {
                    Schema::table("circular$month", function (Blueprint $table) {
                        $table->dateTime('final_updated_date')->nullable()->comment('最終更新日');
                    });
                }
                DB::statement("update circular$month set final_updated_date = update_at");
            }

//            if (Schema::hasTable("circular_document$month") && Schema::hasColumn("circular_document$month", 'copy_flg')){
//                Schema::table("circular_document$month", function (Blueprint $table) {
//                    $table->dropColumn('copy_flg');
//                });
//            }

//            if (Schema::hasTable("circular_user$month") && Schema::hasColumn("circular_user$month", 'copy_flg')){
//                Schema::table("circular_user$month", function (Blueprint $table) {
//                    $table->dropColumn('copy_flg');
//                });
//            }
//
//            if (Schema::hasTable("document_data$month") && Schema::hasColumn("document_data$month", 'copy_flg')){
//                Schema::table("document_data$month", function (Blueprint $table) {
//                    $table->dropColumn('copy_flg');
//                });
//            }
        }
        DB::statement("update circular set final_updated_date = update_at");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
