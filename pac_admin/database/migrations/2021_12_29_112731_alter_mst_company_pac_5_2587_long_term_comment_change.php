<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMstCompanyPac52587LongTermCommentChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('mst_company', function (Blueprint $table) {
            $table->integer('long_term_storage_flg')
                ->comment('文書長期保管')
                ->change();
            $table->integer('long_term_storage_option_flg')
                ->comment('長期保管文書検索')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
