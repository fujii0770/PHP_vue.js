<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCircularPac51453HasSignature extends Migration
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
                if (!Schema::hasColumn("circular$month", 'has_signature')) {
                    Schema::table("circular$month", function (Blueprint $table) {
                        $table->integer('has_signature')->nullable();
                    });
                }
            } else {
                // テーブルを作成
                DB::statement("CREATE TABLE IF NOT EXISTS circular$month LIKE circular");
                DB::statement("CREATE TABLE IF NOT EXISTS circular_user$month LIKE circular_user");
                DB::statement("CREATE TABLE IF NOT EXISTS circular_document$month LIKE circular_document");
                DB::statement("CREATE TABLE IF NOT EXISTS document_data$month LIKE document_data");

                // 自増削除
                DB::statement("ALTER TABLE circular$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
                DB::statement("ALTER TABLE circular_user$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
                DB::statement("ALTER TABLE circular_document$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
                DB::statement("ALTER TABLE document_data$month CHANGE id id BIGINT(0) UNSIGNED NOT NULL;");
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
        $month = date('Ym');
        for ($i = 0; $i < 12; $i++) {
            $month = date('Ym', strtotime("$month -$i month"));
            Schema::dropIfExists("circular$month");
        }
    }
}
