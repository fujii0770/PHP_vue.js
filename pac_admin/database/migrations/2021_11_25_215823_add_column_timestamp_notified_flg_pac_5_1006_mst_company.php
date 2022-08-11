<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTimestampNotifiedFlgPac51006MstCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_company', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_company', 'timestamp_check_flg')) {
                $table->integer('timestamp_notified_flg')->default(0)->comment('タイムスタンプ通知ステータス 0:通知なし1:通知済み');
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
        Schema::table('mst_company', function (Blueprint $table) {
            if (Schema::hasColumn('mst_company', 'timestamp_notified_flg')) {
                $table->dropColumn('timestamp_notified_flg');
            }
        });
    }
}
