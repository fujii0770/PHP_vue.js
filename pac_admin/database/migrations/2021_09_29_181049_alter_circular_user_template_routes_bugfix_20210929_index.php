<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCircularUserTemplateRoutesBugfix20210929Index extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_user_template_routes',function (Blueprint $table){
            $table->index(['template','mst_position_id','mst_department_id'],'INX_template_routes');
        });
        Schema::table('circular_user_templates',function (Blueprint $table){
            $table->index(['mst_company_id'],'INX_templates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular_user_template_routes', function (Blueprint $table) {
            $table->dropIndex('INX_template_routes');
        });
        Schema::table('circular_user_templates',function (Blueprint $table){
            $table->dropIndex('INX_templates');
        });
    }
}
