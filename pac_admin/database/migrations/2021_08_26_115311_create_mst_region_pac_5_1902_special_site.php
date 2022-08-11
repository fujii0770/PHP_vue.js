<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstRegionPac51902SpecialSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_region', function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedBigInteger('region_id')
                ->comment('地域ID');
            $table->string('region_name', 256)
                ->comment('県名');
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
        Schema::dropIfExists('mst_region_pac_5_1902_special_site');
    }
}
