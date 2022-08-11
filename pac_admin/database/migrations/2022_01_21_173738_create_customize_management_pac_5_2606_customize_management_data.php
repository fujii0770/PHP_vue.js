<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomizeManagementPac52606CustomizeManagementData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customize_management', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('カスタムエリアの配信ID');
            $table->bigInteger('mst_customize_id')->unsigned()->comment('統合ID管理テーブルのカスタムエリアマスタID');
            $table->bigInteger('mst_company_id')->unsigned()->index('customize_management_mst_company_id_foreign')->comment('会社マスタID');
            $table->bigInteger('mst_department_id')->unsigned()->nullable()->index('customize_management_mst_department_id_foreign')->comment('部署マスタID');
            $table->bigInteger('mst_position_id')->unsigned()->nullable()->index('customize_management_mst_position_id_foreign')->comment('役職マスタID');
            $table->tinyInteger('location_type')->default(1)->comment('表示場所: 1 エリア1; 2 エリア2;');
            $table->tinyInteger('type')->comment('カスタムタイプ 1:お知らせ 2:広告 3:動画');
            $table->dateTime('create_at')->comment('作成日時');
            $table->string('create_user', 128)->comment('作成者');
            $table->dateTime('update_at')->nullable()->comment('更新日時');
            $table->string('update_user', 128)->nullable()->comment('更新者');
        });

        Schema::table('customize_management', function(Blueprint $table)
        {
            $table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('mst_department_id')->references('id')->on('mst_department')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('mst_position_id')->references('id')->on('mst_position')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customize_management', function(Blueprint $table)
        {
            $table->dropForeign('customize_management_mst_company_id_foreign');
            $table->dropForeign('customize_management_mst_department_id_foreign');
            $table->dropForeign('customize_management_mst_position_id_foreign');
        });
        Schema::dropIfExists('customize_management');
    }
}
