<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCreateAuthorityDeteteAuthorityToAdminAuthoritiesDefaultPac51590 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            DB::beginTransaction();
            DB::update("UPDATE admin_authorities_default SET create_authority = '2', delete_authority = '2' WHERE code IN ('勤務表一覧','勤務確認','日報確認') ;");
            DB::update("UPDATE admin_authorities_default SET delete_authority = '2' WHERE code IN ('利用ユーザ登録') ;");
			DB::commit();
	        } catch (\Exception $e) {
	            DB::rollback();
	        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       // try {
          //  DB::beginTransaction();
          //  DB::update("UPDATE admin_authorities_default SET create_authority = '0', delete_authority = '0' WHERE code IN ('勤務表一覧','勤務確認','日報確認') ;");
          //  DB::update("UPDATE admin_authorities_default SET delete_authority = '0' WHERE code IN ('利用ユーザ登録') ;");
		//	DB::commit();
	      //  } catch (\Exception $e) {
	      //      DB::rollback();
	     //   }
    }
}
