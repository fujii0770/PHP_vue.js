<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoredMailSendResumeDel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stored_function = '
        DROP procedure IF EXISTS mail_send_resume_DEL;
        CREATE PROCEDURE mail_send_resume_DEL(in Days int)
        
        BEGIN
            DECLARE RowCount INT;
            SET RowCount = 1;
            
            WHILE RowCount > 0 DO
                DELETE FROM pac_app.mail_send_resume WHERE (create_at < DATE_SUB(CURDATE(), INTERVAL Days DAY)) LIMIT 10000;
                SELECT COUNT(1) INTO RowCount FROM pac_app.mail_send_resume WHERE (create_at < DATE_SUB(CURDATE(),INTERVAL Days DAY));
            END WHILE;
        END;
    ';

    DB::unprepared($stored_function);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = "DROP procedure IF EXISTS mail_send_resume_DEL";
        DB::connection()->getPdo()->exec($sql); 
    }
}
