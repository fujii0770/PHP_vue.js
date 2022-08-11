<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEpsTAppFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eps_t_app_files', function (Blueprint $table) {
            
            $table->dropColumn("eps_app_no");
            $table->dropColumn("eps_app_item_no");
            $table->dropColumn("attached_file_no");
            $table->unsignedBigInteger('t_app_items_id')->after('mst_company_id')->nullable();
            $table->unsignedBigInteger('t_app_id')->after('mst_company_id');
            $table->foreign('t_app_id')->references('id')->on('eps_t_app')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('t_app_items_id')->references('id')->on('eps_t_app_items')->onUpdate('RESTRICT')->onDelete('RESTRICT');

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
