<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampInfoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stamp_info', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('circular_document_id')->unsigned()->index('idx_stamp_info_on_circular_document_id');
			$table->integer('circular_operation_id')->nullable()->index('INX_circular_operation_id')->comment('承認履歴情報ID');
			$table->bigInteger('mst_assign_stamp_id')->nullable();
			$table->integer('parent_send_order')->nullable()->comment('会社区分フラグ');
			$table->longText('stamp_image')->charset('utf8')->collation('utf8_general_ci');
			$table->string('name', 128)->nullable();
			$table->string('email', 256);
			$table->bigInteger('bizcard_id')->unsigned()->nullable();
			$table->integer('env_flg')->default(0);
			$table->integer('server_flg')->default(0);
			$table->integer('edition_flg')->default(1);
			$table->string('info_id', 128)->index('index_info_id')->comment('URLの最後のパスになる文字列');
			$table->string('file_name', 256);
			$table->datetime('create_at')->useCurrent();
			$table->integer('time_stamp_permission')->default(0);
			$table->string('serial', 32)->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stamp_info');
	}

}
