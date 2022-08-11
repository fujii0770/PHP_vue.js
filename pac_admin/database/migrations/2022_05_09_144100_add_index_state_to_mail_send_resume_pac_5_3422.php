<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexStateToMailSendResumePac53422 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mail_send_resume', function (Blueprint $table) {
            $table->index('state','INX_state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mail_send_resume', function (Blueprint $table) {
            $table->dropIndex('INX_state');
        });
    }
}
