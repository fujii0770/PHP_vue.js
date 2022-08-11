<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstContractEditionPac52649Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('mst_contract_edition');
        Schema::dropIfExists('mst_contract_edition_info');
        Schema::create('mst_contract_edition', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contract_edition_name',256)->comment('契約エディション名');
            $table->string('memo',256)->nullable()->comment('メモ');
            $table->integer('state_flg')->default(0)->comment('ステータス 1:有効、0:無効、9:削除');
            $table->integer('board_flg')->default(0)->comment('グループウェア機能・掲示板');
            $table->integer('faq_board_flg')->default(0)->comment('サポート掲示板');
            $table->integer('pdf_annotation_flg')->default(0)->comment('捺印情報表示(PDF)');
            $table->integer('scheduler_flg')->default(0)->comment('スケジューラー');
            $table->integer('scheduler_limit_flg')->default(0)->comment('スケジューラー無制限');
            $table->integer('scheduler_buy_count')->default(0)->comment('スケジューラー購入数');
            $table->integer('caldav_flg')->default(0)->comment('CalDAV(カレンダー連携)');
            $table->integer('caldav_limit_flg')->default(0)->comment('CalDAV無制限');
            $table->integer('caldav_buy_count')->default(0)->comment('CalDAV購入数');
            $table->integer('google_flg')->default(0)->comment('Google連携');
            $table->integer('outlook_flg')->default(0)->comment('Outlook連携');
            $table->integer('apple_flg')->default(0)->comment('Apple連携');
            $table->integer('file_mail_flg')->default(0)->comment('ファイルメール便');
            $table->integer('file_mail_limit_flg')->default(0)->comment('ファイルメール便無制限');
            $table->integer('file_mail_buy_count')->default(0)->comment('ファイルメール便購入数');
            $table->integer('attendance_flg')->default(0)->comment('タイムカード');
            $table->integer('attendance_limit_flg')->default(0)->comment('タイムカード無制限');
            $table->integer('attendance_buy_count')->default(0)->comment('タイムカード購入数');
            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->string('create_user',128)->comment('作成者');
            $table->dateTime('update_at')->nullable()->comment('更新日時');
            $table->string('update_user',128)->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_contract_edition');
    }
}
