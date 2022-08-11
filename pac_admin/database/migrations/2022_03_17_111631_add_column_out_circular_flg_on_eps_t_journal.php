<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnOutCircularFlgOnEpsTJournal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('eps_t_journal')) {
            Schema::table('eps_t_journal', function (Blueprint $table) {
                 if (!Schema::hasColumn('eps_t_journal','out_cirular_flg')){
                     $table->integer('out_cirular_flg')->after('remarks')->default(0)->comment('回覧:0|回覧外:1');
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
        if (Schema::hasTable('eps_t_journal')) {
            Schema::table('eps_t_journal', function (Blueprint $table) {
                if (Schema::hasColumn('eps_t_journal','out_cirular_flg')) {
                    $table->dropColumn('out_cirular_flg');
                }
            });
        }
    }
}
