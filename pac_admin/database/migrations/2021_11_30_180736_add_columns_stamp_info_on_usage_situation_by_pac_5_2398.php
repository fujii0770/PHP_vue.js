<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsStampInfoOnUsageSituationByPac52398 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_situation', function (Blueprint $table) {
            if (!Schema::hasColumn('usage_situation', 'convenient_upper_limit')) {
                $table->integer('convenient_upper_limit')->unsigned()->default(0)->comment('便利印契約数');
            }
            if (!Schema::hasColumn('usage_situation', 'total_convenient_stamp')) {
                $table->integer('total_convenient_stamp')->unsigned()->default(0)->comment('便利印合計');
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
        Schema::table('usage_situation', function (Blueprint $table) {
            if (Schema::hasColumn('usage_situation', 'convenient_upper_limit')) {
                $table->dropColumn('convenient_upper_limit');
            }
            if (Schema::hasColumn('usage_situation', 'total_convenient_stamp')) {
                $table->dropColumn('total_convenient_stamp');
            }
        });
    }
}
