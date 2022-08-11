<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldIsDelAtLongTermDocumentByPac52740 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('long_term_document', function (Blueprint $table) {
            $table->tinyInteger('is_del')->comment('ユーザー側の削除FLG: 1 削除 0 保存中')->default(0);
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
            if (Schema::hasColumn('long_term_document', 'is_del')) {
                $table->dropColumn('is_del');
            }
        });
    }
}
