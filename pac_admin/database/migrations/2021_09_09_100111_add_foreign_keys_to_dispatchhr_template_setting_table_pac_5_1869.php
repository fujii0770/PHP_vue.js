<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDispatchhrTemplateSettingTablePac51869 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('dispatchhr_template_setting', function(Blueprint $table)
		{
			$table->foreign('mst_company_id')->references('id')->on('mst_company')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('dispatchhr_template_id')->references('id')->on('dispatchhr_template')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatchhr_template_setting', function(Blueprint $table)
		{
			$table->dropForeign('dispatchhr_template_setting_mst_company_id_foreign');
			$table->dropForeign('dispatchhr_template_setting_dispatchhr_template_id_foreign');
		});
	}

}

