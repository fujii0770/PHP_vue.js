<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchAreaAgencyPac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('dispatcharea_agency', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('派遣会社ID');
            $table->bigInteger('mst_admin_id')->unsigned()
                ->comment('作成者ID');
            $table->bigInteger('mst_company_id')->unsigned()
                ->comment('作成会社ID');
            $table->string('company_name', 128)
                ->comment('会社名');
            $table->string('office_name', 128)
                ->nullable()            
                ->comment('事業所名(支店名など)');
            $table->date('conflict_date')
                ->nullable()            
                ->comment('事業者抵触日');
            $table->string('postal_code', 8)
                ->nullable()            
                ->comment('郵便番号');
            $table->string('address1', 128)
                ->nullable()            
                ->comment('住所1');
            $table->string('address2', 128)
                ->nullable()            
                ->comment('住所2');
            $table->bigInteger('billing_address')->unsigned()
                ->nullable()            
                ->comment('請求先部署');
            $table->integer('del_flg')->unsigned()
                ->default(0)
                ->comment('削除フラグ');                
            $table->timestamp('create_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->string('create_user', 128)
                ->comment('作成者');
            $table->timestamp('update_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->nullable()
                ->comment('更新日');
            $table->string('update_user', 128)
                ->comment('更新者');
        });
        DB::statement("alter table dispatcharea_agency comment '派遣先会社テーブル';");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatcharea_agency');
    }
}
