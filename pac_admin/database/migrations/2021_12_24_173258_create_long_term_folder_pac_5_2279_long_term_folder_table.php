<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermFolderPac52279LongTermFolderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_folder', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->bigInteger('mst_company_id')->unsigned()->comment('会社ID');
            $table->string('folder_name', 256)->comment('フォルダ名');
            $table->bigInteger('parent_id')->default(0)->unsigned()->comment('親フォルダID');
            $table->integer('display_no')->default(0)->unsigned()->comment('ソート');
            $table->string('tree',512)->nullable()->comment('トップ階層から、自分まで、「,」で分割、最後に「,」を付く');
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
        Schema::dropIfExists('long_term_folder');
    }
}
