<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcedureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::unprepared("DROP PROCEDURE IF EXISTS `api_key_access_events_DEL`;
                CREATE DEFINER=`Admin`@`%` PROCEDURE `api_key_access_events_DEL`()
                BEGIN
                DECLARE RowCount INT;
                SET RowCount = 1;
                WHILE RowCount > 0 DO
                DELETE FROM pac_app.api_key_access_events WHERE (created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY)) LIMIT 10000;
                SELECT COUNT(id) INTO RowCount FROM pac_app.api_key_access_events WHERE (created_at < DATE_SUB(CURDATE(),INTERVAL 7 DAY));
                END WHILE;
                END;;");

        DB::unprepared("DROP PROCEDURE IF EXISTS `oauth_access_tokens_DEL`;
                CREATE DEFINER=`Admin`@`%` PROCEDURE `oauth_access_tokens_DEL`()
                BEGIN
                DECLARE RowCount INT;
                SET RowCount = 1;
                WHILE RowCount > 0 DO
                DELETE FROM pac_app.oauth_access_tokens WHERE revoked = 1 LIMIT 10000;
                SELECT COUNT(id) INTO RowCount FROM pac_app.oauth_access_tokens WHERE revoked = 1;
                END WHILE;
                END;;");

        DB::unprepared("DROP PROCEDURE IF EXISTS `user_password_resets_DEL`;
                CREATE DEFINER=`Admin`@`%` PROCEDURE `user_password_resets_DEL`()
                BEGIN
                DECLARE RowCount INT;
                SET RowCount = 1;
                WHILE RowCount > 0 DO
                DELETE FROM pac_app.user_password_resets WHERE (created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY)) LIMIT 10000;
                SELECT COUNT(email) INTO RowCount FROM pac_app.user_password_resets WHERE (created_at < DATE_SUB(CURDATE(),INTERVAL 7 DAY));
                END WHILE;
                END;;");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

	}

}
