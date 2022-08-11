<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChatTrialFlgIntoMstCompanyTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('mst_company', 'chat_trial_flg')) {
            Schema::table('mst_company', function (Blueprint $table) {
                $table->unsignedTinyInteger('chat_trial_flg')->default(0)->after('chat_flg')
                    ->comment('0：非トライアル, 1：トライアル');
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
        if (Schema::hasColumn('mst_company', 'chat_trial_flg'))
        {
            Schema::table('mst_company', function (Blueprint $table)
            {
                $table->dropColumn('chat_trial_flg');
            });
        }
    }
}
