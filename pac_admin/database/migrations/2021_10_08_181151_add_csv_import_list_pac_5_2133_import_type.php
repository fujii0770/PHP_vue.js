<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCsvImportListPac52133ImportType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('csv_import_list', function (Blueprint $table) {
            if (!Schema::hasColumn('csv_import_list', 'import_type')) {
                $table->tinyInteger('import_type')->default(1)->comment('タイプ(1:利用者|2:承認ルート)');
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
        Schema::table('csv_import_list', function (Blueprint $table) {
            if (Schema::hasColumn('csv_import_list', 'import_type')) {
                $table->dropColumn('import_type');
            }
        });
    }
}
