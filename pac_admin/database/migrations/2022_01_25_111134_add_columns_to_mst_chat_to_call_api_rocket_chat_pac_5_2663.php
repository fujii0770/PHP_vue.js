<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToMstChatToCallApiRocketChatPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_chat', 'service_status')) {
                $table->unsignedTinyInteger('service_status')->default(0)
                    ->after('status')
                    ->comment('0:未作成, 1:起動待ち, 2:初期化中, 3:実行中');
            }
            if (!Schema::hasColumn('mst_chat', 'service_status_at')) {
                $table->dateTime('service_status_at')
                    ->after('service_status')->nullable();
            }
            if (!Schema::hasColumn('mst_chat', 'version')) {
                $table->unsignedBigInteger('version')
                    ->default(0)->after('service_status_at');

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
        Schema::table('mst_chat', function (Blueprint $table) {
            if (Schema::hasColumn('mst_chat', 'service_status')) {
                $table->dropColumn('service_status');
            }
            if (Schema::hasColumn('mst_chat', 'service_status_at')) {
                $table->dropColumn('service_status_at');
            }
            if (Schema::hasColumn('mst_chat', 'version')) {
                $table->dropColumn('version');
            }
        });
    }
}
