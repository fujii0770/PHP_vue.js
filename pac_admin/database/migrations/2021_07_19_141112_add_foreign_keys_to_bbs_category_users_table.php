<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBbsCategoryUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbs_category_users', function (Blueprint $table) {
			$table->foreign('bbs_category_id')->references('id')->on('bbs_category')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        Schema::table('bbs_category_users', function (Blueprint $table) {
			$table->dropForeign('fk_bbs_category_id_of_bbs_category_users_idx');
			$table->dropForeign('fk_user_id_of_bbs_category_users_idx');
        });
    }
}
