<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloumnIsInfiniteAndPurchaseCountToMstApplicationCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mst_application_companies', function (Blueprint $table) {
            $table->tinyInteger('is_infinite')->after('mst_company_id')->nullable()->default(null)->comment('無制限フラグ');
            $table->bigInteger('purchase_count')->after('is_infinite')->unsigned()->nullable()->default(null)->comment('購入数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mst_application_companies', function (Blueprint $table) {
            //
        });
    }
}
