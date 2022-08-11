<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordPolicyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('password_policy', function(Blueprint $table)
		{
		    $table->charset = 'utf8';
		    $table->collation = 'utf8_general_ci';
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned();
			$table->integer('min_length')->comment('4～12で設定可能');
			$table->integer('validity_period')->comment('0：無期限 1～999：有効期間（日）');
			$table->integer('enable_password')->comment('0：無効（利用できない） 1：有効（利用できる）');
			$table->integer('password_mail_validity_days')->comment('0：無期限 1～999：有効期間（日）');
			$table->dateTime('create_at');
			$table->string('create_user', 128)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
			$table->integer('character_type_limit')->default(0);
			$table->integer('set_mail_as_password')->default(0);
        });
        DB::statement("alter table password_policy comment '各企業ごとのパスワード設定に関するデータを格納する';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('password_policy');
	}

}
