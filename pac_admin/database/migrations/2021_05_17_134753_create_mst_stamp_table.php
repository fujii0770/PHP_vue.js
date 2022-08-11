<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstStampTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_stamp', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigInteger('id', true)->unsigned();
            $table->string('stamp_name', 32)->index('idx_mst_stamp_on_stamp_name');
            $table->integer('stamp_division')->comment('0：氏名印
1：日付印');
            $table->integer('font')->comment('-1：不明
0：楷書
1：古印
2：行書');
            $table->longtext('stamp_image')->charset('utf8')->collation('utf8_general_ci');
            $table->integer('width')->unsigned();
            $table->integer('height')->unsigned();
            $table->integer('date_x')->nullable()->comment('日付の描画位置(X座標)');
            $table->integer('date_y')->nullable()->comment('日付の描画位置(Y座標)');
            $table->integer('date_width')->nullable();
            $table->integer('date_height')->nullable();
            $table->datetime('create_at')->useCurrent();
            $table->string('create_user', 128)->nullable();
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
            $table->string('update_user', 128)->nullable();
            $table->string('serial', 32)->default('');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mst_stamp');
    }

}
