<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFrmTemplatePac52828 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frm_template', function (Blueprint $table) {
            $table->string('frm_template_code',15)->nullable()->change();
            $table->dropUnique(['mst_company_id', 'frm_template_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frm_template', function (Blueprint $table) {
            $table->string('frm_template_code',15)->change();
            $table->unique(['mst_company_id', 'frm_template_code']);
        });
    }
}
