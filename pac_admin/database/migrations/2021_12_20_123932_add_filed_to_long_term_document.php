<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledToLongTermDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_document', function (Blueprint $table) {
            if (!Schema::hasColumn("long_term_document", "upload_status,upload_id")) {
                $table->tinyInteger("upload_status")->default(0);
                $table->integer('upload_id')->default(0);
                $table->index('upload_id');
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
            if (Schema::hasColumn('long_term_document', 'upload_status,upload_id')) {
                $table->dropColumn("upload_status");
                $table->dropColumn("upload_id");
            }
        });
    }
}
