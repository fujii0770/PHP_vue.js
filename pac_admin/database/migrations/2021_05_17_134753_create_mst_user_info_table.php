<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstUserInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_user_info', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_user_id')->unsigned()->index('idx_mst_user_info_on_mst_user_id');
			$table->bigInteger('mst_department_id')->unsigned()->nullable();
			$table->bigInteger('mst_position_id')->unsigned()->nullable();
			$table->string('phone_number', 15)->nullable();
			$table->string('fax_number', 15)->nullable();
			$table->string('postal_code', 8)->nullable();
			$table->string('address', 256)->nullable();
			$table->bigInteger('bizcard_id')->unsigned()->nullable()->comment('自身の名刺ID');
			$table->integer('date_stamp_config')->comment('0：任意の日付



1：当日のみ');
			$table->integer('api_apps')->comment('0：許可しない

1：許可する');
			$table->integer('approval_request_flg')->comment('0：無効 1：有効');
			$table->integer('browsed_notice_flg')->default(0)->comment('0：無効 1：有効');
			$table->integer('update_notice_flg')->default(0)->comment('0：無効 1：有効');
			$table->integer('mfa_type')->default(0)->comment('0:なし|1:メール認証|2:QRコード認証');
			$table->integer('email_auth_dest_flg')->default(0)->comment('0:登録メールアドレス|1:その他アドレス');
			$table->string('auth_email', 256)->nullable();
			$table->dateTime('create_at');
			$table->string('create_user', 128)->nullable();
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
			$table->integer('completion_notice_flg')->default(1)->comment('0：無効 1：有効');
			$table->string('comment1')->nullable()->default('承認をお願いします。');
			$table->string('comment2')->nullable()->default('至急確認をお願いします。');
			$table->string('comment3')->nullable()->default('了解。');
			$table->string('comment4')->nullable()->default('了解しました。');
			$table->string('comment5')->nullable()->default('承認しました。');
			$table->string('comment6')->nullable()->default('差戻します。');
			$table->string('comment7')->nullable()->default('いつもお世話になっております。');
			$table->string('template1', 256)->nullable();
			$table->string('template2', 256)->nullable();
			$table->string('template3', 256)->nullable();
			$table->string('page_display_first')->default('ホーム');
			$table->string('circular_info_first')->default('印鑑');
			$table->integer('time_stamp_permission')->default(0);
			$table->integer('operation_notice_flg')->default(1);
			$table->float('default_rotate_angle', 4, 1)->default(0.0);
			$table->integer('last_stamp_id')->nullable();
			$table->text('user_profile_data')->nullable();
			$table->integer('template_flg')->default(1)->comment('テンプレートフラグ');
			$table->integer('enable_email')->default(1)->comment('0:無効/1:有効');
			$table->integer('email_format')->default(1)->comment('0:テキスト/1:HTML');
        });
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_user_info');
	}

}
