<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToLongTermCircularUserAndOperationHistoryByPac53171 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('long_term_circular_user', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_circular_user', 'is_skip')) {
                $table->tinyInteger("is_skip")->default(0)->nullable(false)->comment("スキップ(手動) 0 いいえ  1 はい、");
            }
        });

        Schema::table('long_term_circular_operation_history', function (Blueprint $table) {
            if (!Schema::hasColumn('long_term_circular_operation_history', 'is_skip')) {
                $table->tinyInteger("is_skip")->default(0)->nullable(false)->comment("スキップ(手動) 0 いいえ  1 はい、");
            }
        });

        Schema::table('circular_operation_history', function (Blueprint $table) {
            if (!Schema::hasColumn('circular_operation_history', 'is_skip')) {
                $table->tinyInteger("is_skip")->default(0)->nullable(false)->comment("スキップ(手動) 0 いいえ  1 はい、");
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
        Schema::table('long_term_circular_user', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_circular_user', 'is_skip')) {
                $table->dropColumn("is_skip");
            }
        });

        Schema::table('long_term_circular_operation_history', function (Blueprint $table) {
            if (Schema::hasColumn('long_term_circular_operation_history', 'is_skip')) {
                $table->dropColumn("is_skip");
            }
        });

        Schema::table('circular_operation_history', function (Blueprint $table) {
            if (Schema::hasColumn('circular_operation_history', 'is_skip')) {
                $table->dropColumn("is_skip");
            }
        });
    }
}
