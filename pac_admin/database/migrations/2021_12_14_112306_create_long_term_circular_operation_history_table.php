<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermCircularOperationHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_circular_operation_history', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->integer('circular_id')->comment('回覧ID');
            $table->integer('long_term_document_id')->nullable()->index('INX_long_term_document_id')->comment('回覧文書ID');
            $table->string('operation_email', 128)->comment('操作者メールアドレス');
            $table->string('operation_name', 128)->comment('操作者名');
            $table->string('acceptor_email', 128)->nullable()->comment('宛先メールアドレス');
            $table->string('acceptor_name', 128)->nullable()->comment('宛先');
            $table->integer('circular_status')->comment('回覧状態 1:作成|2:捺印|3:申請|4:承認|5:差戻し');
            $table->datetime('create_at')->useCurrent();
            $table->index(['operation_email','circular_id','circular_status'],'INX_operation_email_circular_id_circular_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('long_term_circular_operation_history');
    }
}
