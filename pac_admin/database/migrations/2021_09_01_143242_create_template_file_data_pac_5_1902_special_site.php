<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateFileDataPac51902SpecialSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_file_data', function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedBigInteger('template_file_id')
                ->comment('テンプレートファイルID');
            $table->longtext('file_data')
                ->comment('Base64変換後、AES256にて暗号化し保持');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->string('create_user',128)
                ->comment('作成者のメールアドレス');
            $table->timestamp('updated_at')
                ->default(DB::raw('null on update CURRENT_TIMESTAMP'))
                ->nullable()
                ->comment('更新日');
            $table->string('update_user',128)
                ->nullable()
                ->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_file_data');
    }
}
