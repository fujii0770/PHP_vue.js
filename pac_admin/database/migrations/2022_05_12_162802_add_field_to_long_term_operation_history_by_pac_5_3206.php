<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToLongTermOperationHistoryByPac53206 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_circular_operation_history', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_circular_operation_history','circular_document_id')){
                $table->integer('circular_document_id')->default(0)->comment('回覧文書ID');
            }
            if (!Schema::hasColumn('long_term_circular_operation_history','file_name')){
                $table->string('file_name')->default('');
            }
            if (!Schema::hasColumn('long_term_circular_operation_history','file_size')){
                $table->integer('file_size')->default(0);
            }
        });
        Schema::table('long_term_stamp_info', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_stamp_info','circular_document_id')){
                $table->integer('circular_document_id')->default(0);
            }
        });
        Schema::table('long_term_text_info', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_text_info','circular_document_id')){
                $table->integer('circular_document_id')->default(0);
            }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('long_term_circular_operation_history', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_circular_operation_history','circular_document_id')){
                $table->dropColumn('circular_document_id');
            }
            if (Schema::hasColumn('long_term_circular_operation_history','file_name')){
                $table->dropColumn('file_name');
            }
            if (Schema::hasColumn('long_term_circular_operation_history','file_size')){
                $table->dropColumn('file_size');
            }
        });

        Schema::table('long_term_stamp_info', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_stamp_info','circular_document_id')){
                $table->dropColumn('circular_document_id');
            }
        });
        Schema::table('long_term_text_info', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_text_info','circular_document_id')){
                $table->dropColumn('circular_document_id');
            }
        });
    }
}
