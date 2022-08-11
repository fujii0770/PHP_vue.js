<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstChatServerGroupsTablePac52663 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mst_chat_server_groups')) {
            Schema::create('mst_chat_server_groups', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('group_domain', 20);
                $table->string('ecs_cluster_name', 256)->comment('統合IDからShachihataCloudの管理参照用に受け取る。原則画面からは更新されない。');
                $table->string('ecs_cluster_arn', 256)->comment('統合IDからShachihataCloudの管理参照用に受け取る。原則画面からは更新されない。');
                $table->unsignedTinyInteger('status')->default(0)->comment('0：無効, 1：有効, 9：削除');
                $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
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
        Schema::dropIfExists('mst_chat_server_groups');
    }
}
