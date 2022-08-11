<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMstUserPac51550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("alter table mst_user " .
            "add hr_user_flg int(1) not null default 0 comment '0:無効|1:利用中' after password_change_date "
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_user', function (Blueprint $table) {
            $table->dropColumn('hr_user_flg');
        });
    }
}
