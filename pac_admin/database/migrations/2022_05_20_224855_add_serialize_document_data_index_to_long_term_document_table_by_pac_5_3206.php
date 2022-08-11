<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerializeDocumentDataIndexToLongTermDocumentTableByPac53206 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_circular_operation_history', function (Blueprint $table) {
            $table->index(["id","long_term_document_id"],"INX_id_with_lidid");
            $table->index(["circular_id","long_term_document_id","circular_document_id"],"INX_cid_with_ltdid_with_cdid");
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
