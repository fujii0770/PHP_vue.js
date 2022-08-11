<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMstDepartmentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mst_department', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('mst_company_id')->unsigned();
			$table->bigInteger('parent_id')->unsigned()->nullable()->comment('最上位の部署であればNULL');
			$table->string('department_name', 256)->charset('utf8mb4')->collation('utf8mb4_general_ci');
			$table->integer('state')->comment('0:無効

1:有効');
			$table->datetime('create_at')->useCurrent();
			$table->string('create_user', 128);
			$table->dateTime('update_at')->default(DB::raw('null on update CURRENT_TIMESTAMP'))->nullable();
			$table->string('update_user', 128)->nullable();
        });
        DB::statement("alter table mst_department comment '部署データを格納する。\r\n\r\n※現行のDomainMasterテーブル\r\n\r\n　役職マスタと同一テーブルだったが、\r\n\r\n　新エディ';");
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mst_department');
	}

}
