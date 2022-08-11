<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMstConstraintsPac51398MaxAttachmentSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->integer('max_attachment_size')->default(0)->comment('添付ファイル容量');
            $table->integer('max_total_attachment_size')->default(0)->comment('添付ファイル合計容量');
            $table->integer('max_attachment_count')->default(0)->comment('添付ファイル数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            $table->dropColumn('max_attachment_size','max_total_attachment_size','max_attachment_count');
        });
    }
}
