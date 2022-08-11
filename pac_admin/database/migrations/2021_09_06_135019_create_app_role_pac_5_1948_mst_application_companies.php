<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppRolePac51948MstApplicationCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_application_companies', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('アプリ企業マスタID');
            $table->bigInteger('mst_application_id')->unsigned()
                ->index('fk_mst_application_id_of_mst_application_companies_idx')
                ->comment('アプリマスタID');
            $table->bigInteger('mst_company_id')->unsigned()
                ->index('fk_mst_company_id_of_mst_application_companies_idx')
                ->comment('会社ID');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->timestamp('updated_at')->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日');
        });
        DB::statement("alter table mst_application_companies comment 'アプリ企業マスタ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_application_companies');
    }
}
