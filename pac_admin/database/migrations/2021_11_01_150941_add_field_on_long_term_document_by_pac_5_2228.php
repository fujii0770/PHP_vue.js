<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldOnLongTermDocumentByPac52228 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_document', function (Blueprint $table) {
            if (!Schema::hasColumn("long_term_document", "circular_attachment_json")) {
                $table->text("circular_attachment_json")->nullable()->comment("添付ファイル情報");
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
            $table->dropColumn("circular_attachment_json");
        });
    }
}
