<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnFinalUpdatedDateCircular202102Pac52638 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $env_flg = config('app.pac_app_env');
        $server_flg = config('app.pac_contract_server');

        if (($env_flg == 0 && ($server_flg == 1 || $server_flg == 2)) || ($env_flg == 1 && $server_flg == 0)){
            Schema::table('circular202102', function (Blueprint $table) {
                if (!Schema::hasColumn('circular202102', 'final_updated_date')) {
                    $table->dateTime('final_updated_date')->nullable()->comment('最終更新日');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $env_flg = config('app.pac_app_env');
        $server_flg = config('app.pac_contract_server');

        if (($env_flg == 0 && ($server_flg == 1 || $server_flg == 2)) || ($env_flg == 1 && $server_flg == 0)) {
            Schema::table('circular202102', function (Blueprint $table) {
                if (Schema::hasColumn('circular202102', 'final_updated_date')) {
                    $table->dropColumn('final_updated_date');
                }
            });
        }
    }
}
