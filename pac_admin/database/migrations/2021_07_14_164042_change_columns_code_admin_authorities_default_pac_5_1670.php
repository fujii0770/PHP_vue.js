<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsCodeAdminAuthoritiesDefaultPac51670 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE admin_authorities_default SET code = '長期保管設定' WHERE code ='長期保存設定';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //permissionsテーブルの値を変更する必要があるので、ダウンメソッドは書いておりません。
        //戻す場合、permissionsテーブルのseederファイルを編集してからでないと画面に接続出来ません。
    }
}
