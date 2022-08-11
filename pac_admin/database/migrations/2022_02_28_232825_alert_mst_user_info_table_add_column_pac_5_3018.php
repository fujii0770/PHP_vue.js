<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertMstUserInfoTableAddColumnPac53018 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_user_info',function (Blueprint $table){
            if(!Schema::hasColumn('mst_user_info','phone_number_mobile')){
                $table->string('phone_number_mobile',15)->nullable()->comment('電話番号（携帯）');
            }
            if(!Schema::hasColumn('mst_user_info','phone_number_extension')){
                $table->string('phone_number_extension',15)->nullable()->comment('電話番号（内線）');
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
        Schema::table('mst_user_info', function (Blueprint $table) {
            $table->dropColumn('phone_number_mobile');
            $table->dropColumn('phone_number_extension');
        });
    }
}
