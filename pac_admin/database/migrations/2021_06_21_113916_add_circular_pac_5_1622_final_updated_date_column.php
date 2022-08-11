<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCircularPac51622FinalUpdatedDateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('circular', 'final_updated_date')) {
                $table->dateTime('final_updated_date')->nullable()->comment('最終更新日');
            }
        });

//        Schema::table('circular', function (Blueprint $table) {
//            //
//            $table->dropColumn('copy_flg');
//        });
//
//        Schema::table('circular_user', function (Blueprint $table) {
//            //
//            $table->dropColumn('copy_flg');
//        });
//
//        Schema::table('circular_document', function (Blueprint $table) {
//            //
//            $table->dropColumn('copy_flg');
//        });
//
//        Schema::table('document_data', function (Blueprint $table) {
//            //
//            $table->dropColumn('copy_flg');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular', function (Blueprint $table) {
            //
            $table->dropColumn('final_updated_date');
        });
    }
}
