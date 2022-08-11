<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsageSituationDetailPac3234UsageSituationRecount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usage_situation_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('target_date')->comment('基準日');

            $table->integer('mst_company_id');
            $table->string('company_name', 256)->comment('会社名');
            $table->string('company_name_kana', 256)->comment('会社名(カナ)');

            $table->integer('guest_company_id')->unsigned()->nullable()->comment('ゲスト企業ID');
            $table->string('guest_company_name', 256)->nullable()->comment('ゲスト企業名前');
            $table->string('guest_company_name_kana', 256)->nullable()->comment('ゲスト企業名前カナ');
            $table->integer('guest_company_app_env')->comment('ゲスト会社名env');
            $table->integer('guest_company_contract_server')->comment('ゲスト会社名server');

            $table->integer('user_count_valid')->unsigned()->comment('ユーザー数（有効）');
//            $table->integer('user_count_leftover')->comment('ユーザー数（残りライセンス）');
            $table->integer('user_count_activity')->unsigned()->comment('ユーザー数（アクティビティ）');
           // $table->decimal('user_activity_rate',11,2)->comment('ユーザーアクティビティ率');

            $table->bigInteger('storage_stamp')->unsigned()->comment('ストレージ_印面');
            $table->bigInteger('storage_document')->unsigned()->comment('ストレージ_文書');
            $table->bigInteger('storage_operation_history')->unsigned()->comment('ストレージ_操作履歴');
            $table->bigInteger('storage_mail')->unsigned()->comment('ストレージ_メール');
            $table->bigInteger('storage_sum')->unsigned()->comment('ストレージ_合計');
//            $table->decimal('storage_rate',11,2)->unsigned()->comment('ストレージ_比例');

            $table->bigInteger('storage_stamp_re')->unsigned()->comment('ストレージ_印面_再計算');
            $table->bigInteger('storage_document_re')->unsigned()->comment('ストレージ_文書_再計算');
            $table->bigInteger('storage_operation_history_re')->unsigned()->comment('ストレージ_操作履歴_再計算');
            $table->bigInteger('storage_mail_re')->unsigned()->comment('ストレージ_メール_再計算');
            $table->bigInteger('storage_sum_re')->unsigned()->comment('ストレージ_合計_再計算');
//            $table->decimal('storage_rate_re',11,2)->unsigned()->comment('ストレージ_比例_再計算');

            $table->integer('stamp_contract')->unsigned()->comment('契約数');
            $table->integer('stamp_count')->unsigned()->comment('印鑑数');
            $table->integer('stamp_over_count')->comment('超過登録印面数');
            $table->integer('timestamp_count')->unsigned()->comment('タイムスタンプ数');
            $table->integer('timestamp_leftover_count')->comment('タイムスタンプ残り数');

            $table->integer('circular_applied_count')->unsigned()->comment('回覧申請数');
            $table->integer('circular_completed_count')->unsigned()->comment('回覧完了数');
            $table->integer('circular_completed_total_time')->unsigned()->comment('完了回覧総時間');
            //$table->decimal('circular_completed_rate',11,2)->comment('回覧完了率');

            $table->integer('multi_comp_out')->unsigned()->comment('社外経由数（送信）');
            $table->integer('multi_comp_in')->unsigned()->comment('社外経由数（受信）');

            $table->integer('upload_count_pdf')->unsigned()->comment('アップロード数_PDF');
            $table->integer('upload_count_excel')->unsigned()->comment('アップロード数_EXCEL');
            $table->integer('upload_count_word')->unsigned()->comment('アップロード数_WORD');
            $table->integer('download_count_pdf')->unsigned()->comment('ダウンロード数_PDF');

            $table->dateTime('create_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('update_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usage_situation_detail');
    }
}
