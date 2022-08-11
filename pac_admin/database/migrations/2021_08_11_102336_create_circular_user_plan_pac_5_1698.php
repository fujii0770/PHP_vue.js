<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircularUserPlanPac51698 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circular_user_plan', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('PK連番自動設定');
            $table->bigInteger('circular_id')->unsigned()->comment('回覧ID');
            $table->integer('child_send_order')->comment('企業内の回覧順序 申請者:０|承認者:１~');
            $table->integer('mode')->nullable(true)->default(null)->comment('承認方法 全員承認:1|n人以上承認:3');
            $table->integer('score')->nullable(true)->default(null)->comment('必要な承認の人数');
            $table->integer('state')->comment('有効:1|削除:9');
            $table->datetime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circular_user_plan');
    }
}
