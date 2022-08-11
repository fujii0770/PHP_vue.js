<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchAreaPac51868 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatcharea', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('派遣ID');
            $table->bigInteger('dispatcharea_agency_id')->unsigned()
                ->comment('派遣先会社ID');
            $table->string('department', 128)
                ->comment('部署名');
            $table->string('position', 128)
                ->nullable()
                ->comment('部署長役職');
            $table->date('department_date')
                ->nullable()
                ->comment('部署抵触日');
            $table->string('postal_code', 8)
                ->nullable()
                ->comment('郵便番号');
            $table->string('address1', 128)
                ->nullable()
                ->comment('住所1');
            $table->string('address2', 128)
                ->nullable()
                ->comment('住所2');
            $table->string('main_phone_no', 15)
                ->nullable()
                ->comment('代表電話番号');
            $table->string('mobile_phone_no', 15)
                ->nullable()
                ->comment('携帯電話番号');
            $table->string('fax_no', 15)
                ->nullable()
                ->comment('FAX番号');
            $table->string('email', 256)
                ->nullable()
                ->comment('メールアドレス');
            $table->string('responsible_name', 128)
                ->nullable()
                ->comment('派遣先責任者');
            $table->string('responsible_phone_no', 15)
                ->nullable()
                ->comment('派遣先責任者電話番号');
            $table->string('commander_name', 128)
                ->nullable()
                ->comment('指揮命令者');
            $table->string('commander_phone_no', 15)
                ->nullable()
                ->comment('指揮命令者電話番号');
            $table->string('troubles_name', 128)
                ->nullable()
                ->comment('苦情申出先');
            $table->string('troubles_phone_no', 15)
                ->nullable()
                ->comment('苦情申出先電話番号');
            $table->integer('dispatcharea_holiday')->unsigned()
                ->default(1)
                ->comment('派遣時の休日');  
            $table->string('dispatcharea_holiday_other', 128)
                ->nullable()
                ->comment('派遣時の休日（その他）');
            $table->integer('welfare_kbn')->unsigned()
                ->default(2)
                ->comment('派遣時の福利厚生');  
            $table->string('welfare_other', 128)
                ->nullable()
                ->comment('派遣時の福利厚生（その他）');
            $table->string('separate_clause', 256)
                ->nullable()
                ->comment('別条項');
            $table->string('remarks', 256)
                ->nullable()
                ->comment('備考');
            $table->integer('fraction_type')->unsigned()
                ->default(3)
                ->comment('1円未満端数処理');  
            $table->string('memo', 256)
                ->nullable()
                ->comment('その他メモ');
                $table->string('manager_office_name', 256)
                ->nullable()            
                ->comment('担当事業所');
            $table->string('manager_name', 128)
                ->nullable()
                ->comment('担当者');
            $table->string('caution', 256)
                ->nullable()
                ->comment('注意事項');
            $table->integer('status_kbn')->unsigned()
                ->default(3)
                ->comment('取引ステータス');  
            $table->string('evaluation', 256)
                ->nullable()
                ->comment('派遣先からの評価');  
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
        DB::statement("alter table dispatcharea comment '派遣先テーブル';");    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatcharea');
    }
}
