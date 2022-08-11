<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstConstraintsTableExpTemplateSizeLimitPac51598 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_constraints', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_constraints', 'template_size_limit')) {
                $table->integer('template_size_limit')->default(1)->unsigned();
            }            
            if (!Schema::hasColumn('mst_constraints', 'exp_template_size_limit')) {
                $table->integer('exp_template_size_limit')->default(1)->unsigned();
            }
            if (!Schema::hasColumn('mst_constraints', 'max_template_file')) {
                $table->integer('max_template_file')->default(20)->unsigned();
            }
            if (!Schema::hasColumn('mst_constraints', 'exp_max_template_file')) {
                $table->integer('exp_max_template_file')->default(5)->unsigned();
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
        Schema::table('mst_constraints', function (Blueprint $table) {
            if (Schema::hasColumn('mst_constraints', 'template_size_limit')) {
                $table->dropColumn('template_size_limit');
            }             
            if (Schema::hasColumn('mst_constraints', 'exp_template_size_limit')) {
                $table->dropColumn('exp_template_size_limit');
            }
            if (Schema::hasColumn('mst_constraints', 'max_template_file')) {
                $table->dropColumn('max_template_file');
            }
            if (Schema::hasColumn('mst_constraints', 'exp_max_template_file')) {
                $table->dropColumn('exp_max_template_file');
            }
        });
    }
}
