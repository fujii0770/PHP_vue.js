<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstChatTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mst_chat')) {
            Schema::create('mst_chat', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('mst_company_id');
                $table->dateTime('trial_start_date')->nullable();
                $table->dateTime('trial_end_date')->nullable();
                $table->dateTime('contract_start_date')->nullable();
                $table->dateTime('contract_end_date')->nullable();
                $table->unsignedInteger('user_max_limit')->default(0)->comment('1～int上限');
                $table->string('url', 2048)->nullable()->comment('https://xxxxx.xxxxx');
                $table->string('domain', 128)->nullable()->comment('希望サブドメイン部分');
                $table->unsignedInteger('storage_max_limit')->default(1)->comment('1-9999');
                $table->unsignedTinyInteger('contract_type')->default(0)->comment('0：standard, 1：business, 2：pro');
                $table->unsignedTinyInteger('status')->default(0)->comment('0 ：無効, 1 ：有効, 9：削除');
                $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->string('create_user', 128);
                $table->dateTime('update_at')->nullable();
                $table->string('update_user',128)->nullable();
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
        Schema::dropIfExists('mst_chat');
    }
}
