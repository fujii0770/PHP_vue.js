<?php

use Illuminate\Database\Migrations\Migration;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InsertDiskMailUserInfoPac52915OldUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mst_application_users_id = DB::table('mst_application_users')
            ->where('mst_application_id',AppUtils::GW_APPLICATION_ID_FILE_MAIL)
            ->pluck('mst_user_id')->toArray();
        foreach ($mst_application_users_id as $mst_application_user_id){
            DB::table('disk_mail_user_info')->insert([
                'mst_user_id' => $mst_application_user_id,
                'create_at' => Carbon::now(),
                'create_user' => 'admin',
                'comment1' => '確認をお願いします。',
                'comment2' => 'ご確認をお願い致します。',
                'comment3' => '至急確認をお願いします。',
                'comment4' => '至急ご確認をお願い致します。',
                'comment5' => 'ご確認の程よろしくお願い申し上げます。',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
