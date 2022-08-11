<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCircularOperationHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circular_operation_history', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('circular_id')->comment('回覧ID');
			$table->integer('circular_document_id')->nullable()->index('INX_circular_document_id')->comment('回覧文書ID');
			$table->string('operation_email', 128)->comment('操作者メールアドレス');
			$table->string('operation_name', 128)->comment('操作者名');
			$table->string('acceptor_email', 128)->nullable()->comment('宛先メールアドレス');
			$table->string('acceptor_name', 128)->nullable()->comment('宛先');
			$table->integer('circular_status')->comment('回覧状態 1:作成|2:捺印|3:申請|4:承認|5:差戻し');
			$table->datetime('create_at')->useCurrent();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('circular_operation_history');
	}

}
