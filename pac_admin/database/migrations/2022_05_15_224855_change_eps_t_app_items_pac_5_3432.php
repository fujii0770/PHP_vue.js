<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEpsTAppItemsPac53432 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eps_t_app_items', function (Blueprint $table) {
           
            $table->Integer('unit_price')->unsigned()->default(0)->nullable()->change();
            $table->Integer('expected_pay_amt')->unsigned()->default(0)->nullable()->change();
                    
        });

       
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
