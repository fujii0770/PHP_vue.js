<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularPac52976TemplateEditFlgColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular', function (Blueprint $table) {
            //
            $month = date('Ym');
            for ($i = 0; $i < 20; $i++) {
                $month = date('Ym', strtotime("$month -$i month"));
                // テーブル存在、クラム存在しない
                if (Schema::hasTable("circular$month")) {
                    if (!Schema::hasColumn("circular$month", 'template_edit_flg')) {
                        Schema::table("circular$month", function (Blueprint $table) {
                            $table->integer('template_edit_flg')->unsigned()->default(0);
                        });
                    }
                }
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
        Schema::table('circular', function (Blueprint $table) {
            //
            $month = date('Ym');
            for ($i = 0; $i < 20; $i++) {
                if (Schema::hasColumn("circular$month", 'template_edit_flg')) {
                    $table->dropColumn('template_edit_flg');
                }
            }
        });
    }
}
