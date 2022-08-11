<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDefaultValueCloumnUserMaxLimitInMstChatPac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_chat', function (Blueprint $table) {
            if (Schema::hasColumn('mst_chat', 'user_max_limit')) {
                $table->unsignedInteger('user_max_limit')->default(1)
                    ->comment('1～int上限')->change();
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

    }
}
