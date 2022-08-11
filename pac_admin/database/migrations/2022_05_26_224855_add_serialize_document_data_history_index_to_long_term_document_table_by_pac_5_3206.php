<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerializeDocumentDataHistoryIndexToLongTermDocumentTableByPac53206 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_operation_history', function (Blueprint $table) {
            $table->index(["circular_id","circular_document_id"],"INX_cid_with_cdids");
        });
        Schema::table('long_term_circular_operation_history', function (Blueprint $table) {
            $table->index(["id","circular_id",'long_term_document_id'],"INX_cid_with_cdid_with_ltdis");
        });

        Schema::table('long_term_stamp_info', function (Blueprint $table) {
            $table->index(["id","circular_document_id"],"INX_id_with_cdid_long_terms");
            $table->index(["id","long_term_document_id",'circular_document_id'],"INX_id_with_cdid_long_term_cdids");
        });

        Schema::table('long_term_text_info', function (Blueprint $table) {
            $table->index(["id","circular_document_id"],"INX_id_with_cdid_long_term");
            $table->index(["id","long_term_document_id",'circular_document_id'],"INX_id_with_cdid_long_term_cdids");
        });

        Schema::table('long_term_document_comment_info', function (Blueprint $table) {
            $table->index(["id","circular_document_id"],"INX_id_with_cdid_long_term");
            $table->index(["id","long_term_document_id",'circular_document_id'],"INX_id_with_cdid_long_term_cdids");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
