<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLongTermDocumentPac52279LongTermFolderId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_document', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_document', 'long_term_folder_id')) {
                $table->bigInteger('long_term_folder_id')->unsigned()->default(0)->comment('文書自動保管フォルダID');
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
            $table->dropColumn('long_term_folder_id');
        });
    }
}
