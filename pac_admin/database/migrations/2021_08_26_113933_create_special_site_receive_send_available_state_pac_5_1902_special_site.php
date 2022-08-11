<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialSiteReceiveSendAvailableStatePac51902SpecialSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_site_receive_send_available_state', function (Blueprint $table) {
            $table->bigIncrements('id', true)
                ->comment('ID');
            $table->unsignedBigInteger('company_id')
                ->comment('会社ID');
            $table->integer('is_special_site_receive_available')
                ->default(0)
                ->comment('特設サイト受取機能利用可能フラグ');
            $table->integer('is_special_site_send_available')
                ->default(0)
                ->comment('特設サイト提出機能利用可能フラグ');
            $table->string('group_name', 256)
                ->nullable()
                ->comment('組織名');
            $table->string('region_name', 256)
                ->nullable()
                ->comment('県名');
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'))
                ->comment('作成日');
            $table->string('create_user', 128)
                ->comment('作成者のメールアドレス');
            $table->timestamp('updated_at')
                ->default(DB::raw('null on update CURRENT_TIMESTAMP'))
                ->nullable()
                ->comment('更新日');
            $table->string('update_user', 128)
                ->nullable()
                ->comment('更新者');
        });
        DB::statement("INSERT INTO special_site_receive_send_available_state ( company_id, is_special_site_receive_available, is_special_site_send_available, group_name, region_name, created_at, create_user ) SELECT
mst_company.id,
0,
0,
NULL,
NULL,
now(),
'Shachihata' 
FROM
	mst_company 
	");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_site_receive_send_available_state');
    }
}
