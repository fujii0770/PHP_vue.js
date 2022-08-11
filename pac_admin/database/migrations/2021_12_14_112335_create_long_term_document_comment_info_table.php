<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermDocumentCommentInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_document_comment_info', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->integer('long_term_document_id')->nullable()->index('INX_long_term_document_id')->comment('回覧文書ID');
            $table->integer('long_term_operation_id')->comment('承認履歴情報ID');
            $table->integer('parent_send_order')->nullable()->comment('会社区分フラグ');
            $table->string('name', 128)->nullable()->comment('操作者名');
            $table->string('email', 128)->comment('操作者メールアドレス');
            $table->text('text')->comment('文書コメント');
            $table->integer('private_flg')->comment('宛先フラグ 1:社内宛|2:社外宛');
            $table->datetime('create_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('long_term_document_comment_info');
    }
}
