<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToLongTermDocumentTablePac52395 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_document', function (Blueprint $table) {
            if (!Schema::hasColumn("long_term_document", "user_id")) {
                $table->integer('user_id')->nullable();
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
            if (Schema::hasColumn('long_term_document', 'user_id')) {
                $table->dropColumn("user_id");
            }
        });
    }
}
