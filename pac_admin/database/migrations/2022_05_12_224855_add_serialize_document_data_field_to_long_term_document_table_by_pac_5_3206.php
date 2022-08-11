<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerializeDocumentDataFieldToLongTermDocumentTableByPac53206 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_document', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_document','is_other_env_circular_flg')){
                $table->tinyInteger('is_other_env_circular_flg')->unsigned()->default(0)->nullable()->comment('0 current env 1 other env');
            }
        });

        Schema::table('long_term_document_comment_info', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_document_comment_info','circular_document_id')){
                $table->integer('circular_document_id')->nullable()->index('INX_circular_document_id')->default(0)->comment('回覧文書ID');
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
        Schema::table('long_term_document', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_document','is_other_env_circular_flg')){
                $table->dropColumn('is_other_env_circular_flg');
            }
        });
        Schema::table('long_term_document_comment_info', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_document_comment_info','circular_document_id')){
                $table->dropColumn('circular_document_id');
            }
        });
        
    }
}
