<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddressTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('id');
            $table->bigInteger('mst_company_id')->unsigned()->nullable()->comment('会社マスタID');
            $table->bigInteger('mst_user_id')->unsigned()->nullable()->index('address_mst_user_id_foreign')->comment('ユーザーマスタID');
            $table->integer('type')->comment("種別;0：個人アドレス帳\r\n1：共通アドレス帳");
            $table->string('name', 128)->generatedAs()->comment('名前');
            $table->string('email', 256)->comment('メールアドレス');
            $table->string('company_name', 256)->nullable()->comment('会社名');
            $table->string('position_name', 256)->nullable()->comment('役職名');
            $table->string('group_name', 256)->nullable()->comment('グループ名');
            $table->integer('state')->nullable()->comment("状態;0:無効\r\n1:有効");
            $table->datetime('create_at')->useCurrent()->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
        });
        DB::statement("alter table address comment 'アドレス帳テーブル';");
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('address');
    }

}
