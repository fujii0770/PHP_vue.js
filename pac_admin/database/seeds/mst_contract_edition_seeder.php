<?php

use Illuminate\Database\Seeder;

class mst_contract_edition_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mst_contract_edition')->updateOrInsert(
            ['id'=>1],
            [
                'contract_edition_name'=>'Standaradパック',
                'state_flg'=>'1',
                'board_flg'=>'1',
                'faq_board_flg'=>'0',
                'scheduler_flg'=>'0',
                'scheduler_limit_flg'=>'0',
                'scheduler_buy_count'=>'0',
                'caldav_flg'=>'0',
                'caldav_limit_flg'=>'0',
                'caldav_buy_count'=>'0',
                'google_flg'=>'0',
                'outlook_flg'=>'0',
                'apple_flg'=>'0',
                'file_mail_flg'=>'0',
                'file_mail_limit_flg'=>'0',
                'file_mail_buy_count'=>'0',
                'file_mail_extend_flg'=>'0',
                'attendance_flg'=>'0',
                'attendance_limit_flg'=>'0',
                'attendance_buy_count'=>'0',
                'create_user' => 'Shachihata',
                'create_at' => '2021-11-25 15:33:30',
                'shared_scheduler_flg' => 0,
                'to_do_list_flg'=>0,
                'to_do_list_limit_flg'=>0,
                'to_do_list_buy_count'=>0,
            ]);
        DB::table('mst_contract_edition')->updateOrInsert(
            ['id'=>2],
            [
                'contract_edition_name'=>'Bizパック',
                'state_flg'=>'1',
                'board_flg'=>'1',
                'faq_board_flg'=>'0',
                'scheduler_flg'=>'0',
                'scheduler_limit_flg'=>'0',
                'scheduler_buy_count'=>'0',
                'caldav_flg'=>'0',
                'caldav_limit_flg'=>'0',
                'caldav_buy_count'=>'0',
                'google_flg'=>'0',
                'outlook_flg'=>'0',
                'apple_flg'=>'0',
                'file_mail_flg'=>'0',
                'file_mail_limit_flg'=>'0',
                'file_mail_buy_count'=>'0',
                'file_mail_extend_flg'=>'0',
                'attendance_flg'=>'0',
                'attendance_limit_flg'=>'0',
                'attendance_buy_count'=>'0',
                'create_user' => 'Shachihata',
                'create_at' => '2021-11-25 15:33:30',
                'shared_scheduler_flg' => 0,
                'to_do_list_flg'=>0,
                'to_do_list_limit_flg'=>0,
                'to_do_list_buy_count'=>0,
            ]);
        DB::table('mst_contract_edition')->updateOrInsert(
            ['id'=>3],
            [
                'contract_edition_name'=>'Business Pro',
                'state_flg'=>'1',
                'board_flg'=>'0',
                'faq_board_flg'=>'0',
                'scheduler_flg'=>'0',
                'scheduler_limit_flg'=>'0',
                'scheduler_buy_count'=>'0',
                'caldav_flg'=>'0',
                'caldav_limit_flg'=>'0',
                'caldav_buy_count'=>'0',
                'google_flg'=>'0',
                'outlook_flg'=>'0',
                'apple_flg'=>'0',
                'file_mail_flg'=>'0',
                'file_mail_limit_flg'=>'0',
                'file_mail_buy_count'=>'0',
                'file_mail_extend_flg'=>'0',
                'attendance_flg'=>'0',
                'attendance_limit_flg'=>'0',
                'attendance_buy_count'=>'0',
                'create_user' => 'Shachihata',
                'create_at' => '2021-11-25 15:33:30',
                'shared_scheduler_flg' => 0,
                'to_do_list_flg'=>0,
                'to_do_list_limit_flg'=>0,
                'to_do_list_buy_count'=>0,
            ]);
        DB::table('mst_contract_edition')->updateOrInsert(
            ['id'=>4],
            [
                'contract_edition_name'=>'Trialパック',
                'state_flg'=>'1',
                'board_flg'=>'1',
                'faq_board_flg'=>'1',
                'scheduler_flg'=>'1',
                'scheduler_limit_flg'=>'1',
                'scheduler_buy_count'=>'0',
                'caldav_flg'=>'1',
                'caldav_limit_flg'=>'1',
                'caldav_buy_count'=>'0',
                'google_flg'=>'0',
                'outlook_flg'=>'0',
                'apple_flg'=>'0',
                'file_mail_flg'=>'0',
                'file_mail_limit_flg'=>'0',
                'file_mail_buy_count'=>'0',
                'file_mail_extend_flg'=>'0',
                'attendance_flg'=>'0',
                'attendance_limit_flg'=>'0',
                'attendance_buy_count'=>'0',
                'create_user' => 'Shachihata',
                'create_at' => '2021-11-25 15:33:30',
                'faq_board_limit_flg'=>'1',
                'faq_board_buy_count' => '0',
                'to_do_list_flg'=>0,
                'to_do_list_limit_flg'=>0,
                'to_do_list_buy_count'=>0,
                'shared_scheduler_flg' => 1,
            ]);
        DB::table('mst_contract_edition')->updateOrInsert(
            ['id'=>5],
            [
                'contract_edition_name'=>'グループウェア',
                'state_flg'=>'1',
                'board_flg'=>'1',
                'faq_board_flg'=>'0',
                'scheduler_flg'=>'0',
                'scheduler_limit_flg'=>'0',
                'scheduler_buy_count'=>'0',
                'caldav_flg'=>'0',
                'caldav_limit_flg'=>'0',
                'caldav_buy_count'=>'0',
                'google_flg'=>'0',
                'outlook_flg'=>'0',
                'apple_flg'=>'0',
                'file_mail_flg'=>'0',
                'file_mail_limit_flg'=>'0',
                'file_mail_buy_count'=>'0',
                'file_mail_extend_flg'=>'0',
                'attendance_flg'=>'0',
                'attendance_limit_flg'=>'0',
                'attendance_buy_count'=>'0',
                'create_user' => 'Shachihata',
                'create_at' => '2021-1-7 15:33:30',
                'shared_scheduler_flg' => 0,
                'to_do_list_flg'=>0,
                'to_do_list_limit_flg'=>0,
                'to_do_list_buy_count'=>0,
            ]);
    }
}
