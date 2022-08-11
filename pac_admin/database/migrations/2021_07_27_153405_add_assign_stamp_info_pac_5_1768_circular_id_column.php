<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignStampInfoPac51768CircularIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assign_stamp_info', function (Blueprint $table) {
            $table->bigInteger('circular_id')->unsigned()->after('assign_stamp_id')->comment('回覧ID');
            $table->string('name',128)->comment('捺印ユーザー名');
            $table->string('email', 256)->comment('捺印ユーザーメールアドレス');
            $table->string('circular_title')->nullable()->comment('文書名');
            $table->string('file_name',256)->comment('ファイル名');
            $table->string('serial', 32)->default('')->comment('印鑑シリアル');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assign_stamp_info', function (Blueprint $table) {
            $table->dropColumn('circular_id');
            $table->dropColumn('name');
            $table->dropColumn('email');
            $table->dropColumn('circular_title');
            $table->dropColumn('file_name');
            $table->dropColumn('serial');
        });
    }
}
