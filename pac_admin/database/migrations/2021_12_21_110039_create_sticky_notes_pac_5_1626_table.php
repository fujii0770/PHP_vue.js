<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStickyNotesPac51626Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sticky_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('circular_id')->comment('回覧ID');
            $table->bigInteger('document_id')->comment('文書ID');

            $table->integer('note_format')->comment('付箋の種類：1~8です');

            $table->integer('page_num')->comment('ページ数');
            $table->string('top', 10)->comment('縦');
            $table->string('left', 10)->comment('横');
            $table->string('note_text', 512)->comment('付箋入力テキスト');

            $table->integer('edition_flg');
            $table->integer('env_flg');
            $table->integer('server_flg');
            $table->string('operator_email', 256);
            $table->string('operator_name', 128)->nullable();
            $table->integer('removed_flg')->default(0)->comment('取り外しフラグ 0：有効|1：取り外し');
            $table->integer('deleted_flg')->default(0)->comment('削除フラグ 0：未削除|1：削除');

            $table->datetime('create_at')->useCurrent();
            $table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
            $table->index(['circular_id', 'document_id'], 'INX_circular_id_document');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sticky_notes');
    }
}
