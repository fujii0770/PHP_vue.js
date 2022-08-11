<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChatFlgToMstCompanyTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('mst_company', 'chat_flg')) {
            Schema::table('mst_company', function (Blueprint $table) {
                $table->tinyInteger('chat_flg')->unsigned()->default(0)->after('hr_flg')
                    ->comment('0：無効, 1：有効');
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
        if (Schema::hasColumn('mst_company', 'chat_flg'))
        {
            Schema::table('mst_company', function (Blueprint $table)
            {
                $table->dropColumn('chat_flg');
            });
        }
    }
}
