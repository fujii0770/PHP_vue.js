<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCircularOperationHistoryPac51755Index extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circular_operation_history', function (Blueprint $table) {
            $table->index(['operation_email','circular_id','circular_status'],'INX_operation_email_circular_id_circular_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circular_operation_history', function (Blueprint $table) {
            $table->dropIndex('INX_operation_email_circular_id_circular_status');
        });
    }
}
