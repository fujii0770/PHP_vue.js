<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CeateDispatchhrPac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatchhr', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()
                ->comment('人材ID');
            $table->bigInteger('mst_admin_id')->unsigned()
                ->comment('作成者ID');
            $table->bigInteger('mst_company_id')->unsigned()
                ->comment('作成会社ID');
            $table->string('name', 256)
                ->comment('氏名');
            $table->string('furigana', 256)
                ->comment('ふりがな');
            $table->integer('regist_kbn')->unsigned()
                ->nullable()
                ->comment('登録区分');
            $table->integer('gender_type')->unsigned()
                ->nullable()
                ->comment('性別');  
            $table->date('birthdate')
                ->nullable()
                ->comment('生年月日');
            $table->integer('age')->unsigned()
                ->nullable()
                ->comment('年齢');
            $table->string('phone_no', 15)
                ->nullable()
                ->comment('電話番号');
            $table->string('mobile_phone_no', 15)
                ->nullable()
                ->comment('携帯番号');
            $table->string('fax_no', 15)
                ->nullable()
                ->comment('FAX番号');
            $table->string('email', 256)
                ->nullable()
                ->comment('メールアドレス');                
            $table->integer('mail_send_flg')->unsigned()
                ->nullable()
                ->comment('送信先に指定');
            $table->string('mobile_email', 256)
                ->nullable()
                ->comment('携帯メールアドレス');                
            $table->integer('mobile_mail_send_flg')->unsigned()
                ->nullable()
                ->comment('送信先に指定');
            $table->integer('contact_method1')->unsigned()
                ->nullable()
                ->comment('希望連絡方法1');
            $table->integer('contact_method2')->unsigned()
                ->nullable()
                ->comment('希望連絡方法2');
            $table->integer('contact_method3')->unsigned()
                ->nullable()
                ->comment('希望連絡方法3');
            $table->integer('contact_method4')->unsigned()
                ->nullable()
                ->comment('希望連絡方法4');
            $table->integer('contact_method5')->unsigned()
                ->nullable()
                ->comment('希望連絡方法5');
            $table->string('nearest_station', 64)
                ->nullable()
                ->comment('最寄駅');
            $table->string('postal_code', 8)
                ->nullable()
                ->comment('郵便番号');
            $table->string('address1', 128)
                ->nullable()
                ->comment('住所1');
            $table->string('address2', 128)
                ->nullable()
                ->comment('住所2');
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
        DB::statement("alter table dispatchhr comment '人材テーブル';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatchhr');
    }
}
