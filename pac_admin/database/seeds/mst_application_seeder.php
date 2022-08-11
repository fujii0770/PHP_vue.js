<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class mst_application_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mst_application')->updateOrInsert(
            ['id' => 1],
            [
                'app_code' => '001',
                'app_name' => '掲示板',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('mst_application')->updateOrInsert(
            ['id' => 7],
            [
                'app_code' => '007',
                'app_name' => 'タイムカード',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('mst_application')->updateOrInsert(
            ['id' => 8],
            [
                'app_code' => '008',
                'app_name' => 'ファイルメール便',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('mst_application')->updateOrInsert(
            ['id' => 9],
            [
                'app_code' => '009',
                'app_name' => 'サポート掲示板',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('mst_application')->updateOrInsert(
            ['id' => 11],
            [
                'app_code' => '011',
                'app_name' => 'ファイルメール便_送信履歴保持延長',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('mst_application')->updateOrInsert(
            ['id' => 12],
            [
                'app_code' => '012',
                'app_name' => 'ToDoリスト',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('mst_application')->updateOrInsert(
            ['id' => 13],
            [
                'app_code' => '013',
                'app_name' => '利用者名簿',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
