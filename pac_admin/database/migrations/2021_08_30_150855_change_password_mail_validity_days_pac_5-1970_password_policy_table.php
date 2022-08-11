<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePasswordMailValidityDaysPac51970PasswordPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('password_policy', function (Blueprint $table) {
            if (Schema::hasColumn("password_policy","password_mail_validity_days")) {
                $table->integer('password_mail_validity_days')->comment('1～14：有効期間（日）')->change();
            }
        });
        DB::statement("update password_policy set password_mail_validity_days=7 where password_mail_validity_days=0");
        DB::statement("update password_policy set password_mail_validity_days=14 where password_mail_validity_days>14");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
