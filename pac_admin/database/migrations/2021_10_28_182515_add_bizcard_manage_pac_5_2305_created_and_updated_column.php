<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBizcardManagePac52305CreatedAndUpdatedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bizcard_manage', function (Blueprint $table) {
            $table->timestamps();
            $table->string('create_user', 128)->after('created_at')->nullable()->comment('作成者');
            $table->string('update_user', 128)->after('updated_at')->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bizcard_manage', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('create_user');
            $table->dropColumn('update_user');
        });
    }
}
