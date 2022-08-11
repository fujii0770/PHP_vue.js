<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFrmTemplateStampPac52645StampAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('frm_template_stamp', function (Blueprint $table) {
            $table->renameColumn('mst_company_stamp_id', 'stamp_id');
            $table->integer('stamp_flg')->default(1)->comment('0：通常印（印面マスタ）1：共通印（企業印面マスタ）2：部署印（部署印面マスタ）');
            $table->string('stamp_date',12)->comment('日付印の日付　個別設定 YYYY-MM-DD');
        });

        // stamp_date 既存データデフォルト値設定（登録日）
        DB::update("UPDATE frm_template_stamp SET stamp_date = DATE_FORMAT(create_at,'%Y-%m-%d');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('frm_template_stamp', function (Blueprint $table) {
            $table->renameColumn('stamp_id','mst_company_stamp_id');
            $table->dropColumn('stamp_flg');
            $table->dropColumn('stamp_date');
        });
    }
}
