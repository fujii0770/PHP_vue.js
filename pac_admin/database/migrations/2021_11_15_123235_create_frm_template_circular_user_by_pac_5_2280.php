<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrmTemplateCircularUserByPac52280 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frm_template_circular_user', function (Blueprint $table) {
            $table->bigIncrements('id', true)->comment('帳票テンプレート印鑑ID');
            $table->bigInteger('frm_template_id')->unsigned()->comment('帳票テンプレートID');
            $table->integer('edition_flg')->comment('0：現行|1：新エディション');
            $table->integer('env_flg')->comment('0：AWS|1：K5');
            $table->integer('server_flg')->comment('サーバーフラグ');
            $table->integer('parent_send_order')->comment('企業間の順番');
            $table->integer('child_send_order')->comment('企業内の送信順');
            $table->integer('return_flg')->comment('0：窓口へ戻す|1：窓口へ戻さない');
            $table->bigInteger('mst_company_id')->unsigned()->nullable()->comment('企業ID');
            $table->string('mst_company_name', 256)->nullable()->comment('企業名');
            $table->bigInteger('mst_user_id')->unsigned()->nullable()->comment('ユーザーマスタID');
            $table->string('email', 256)->comment('メールアドレス');
            $table->string('name', 128)->nullable()->comment('名前');
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('create_user', 128);
            $table->dateTime('update_at')->nullable();
            $table->string('update_user',128)->nullable();
            $table->bigInteger('version')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frm_template_circular_user');
    }
}
