<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBbsCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbs_category', function (Blueprint $table) {
			$table->foreign('bbs_auth_id')->references('id')->on('bbs_auth')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('mst_user_id')->references('id')->on('mst_user')->onUpdate('CASCADE')->onDelete('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bbs_category', function (Blueprint $table) {
			$table->dropForeign('fk_bbs_auth_id_of_bbs_category');
			$table->dropForeign('fk_mst_user_id_of_bbs_category');
        });
    }
}
