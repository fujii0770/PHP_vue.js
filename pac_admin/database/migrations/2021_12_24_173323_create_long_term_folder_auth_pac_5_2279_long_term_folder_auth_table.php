<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermFolderAuthPac52279LongTermFolderAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_folder_auth', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->bigInteger('long_term_folder_id')->unsigned()->comment('長期保管フォルダID');
            $table->integer('auth_kbn')->comment('権限区分|0:全体|1:役職|2:部署|3:個人');
            $table->integer('auth_link_id')->comment('権限関連ID|権限区分0:0|権限区分1:役職マスタID|権限区分2:部署マスタID|権限区分3:マスタユーザID');
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
        Schema::dropIfExists('long_term_folder_auth');
    }
}
