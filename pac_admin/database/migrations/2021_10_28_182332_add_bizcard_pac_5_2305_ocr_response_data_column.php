<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBizcardPac52305OcrResponseDataColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bizcard', function (Blueprint $table) {
            $table->string('name_kana', 128)->after('name')->nullable()->comment('名前（かな・カナ）');
            $table->string('name_romaji', 128)->after('name_kana')->nullable()->comment('名前（ローマ字）');
            $table->string('company_kana', 256)->after('company_name')->nullable()->comment('会社名（かな・カナ）');
            $table->string('address_name', 256)->after('address')->nullable()->comment('施設名称');
            $table->string('postal_code', 10)->after('address_name')->nullable()->comment('郵便番号');
            $table->string('address_en', 256)->after('postal_code')->nullable()->comment('住所（英語表記）');
            $table->string('person_title', 256)->after('position')->nullable()->comment('職種・資格・その他肩書等');
            $table->string('url', 256)->after('person_title')->nullable()->comment('URL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bizcard', function (Blueprint $table) {
            $table->dropColumn('name_kana');
            $table->dropColumn('name_romaji');
            $table->dropColumn('company_kana');
            $table->dropColumn('address_name');
            $table->dropColumn('postal_code');
            $table->dropColumn('address_en');
            $table->dropColumn('person_title');
            $table->dropColumn('url');
        });
    }
}
