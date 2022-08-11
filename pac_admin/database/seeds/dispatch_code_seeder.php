<?php

use Illuminate\Database\Seeder;

class dispatch_code_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 1],
            [
                'kbn' => 1,
                'code' => 1,
                'name' => '派遣先の社休日に準ずる。',
                'order' => 0,
                'remarks' => 'kbn[1]：休日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 2],
            [
                'kbn' => 2,
                'code' => 1,
                'name' => '制服の貸与',
                'order' => 0,
                'remarks' => 'kbn[2]：福利厚生',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 3],
            [
                'kbn' => 2,
                'code' => 2,
                'name' => 'ロッカー',
                'order' => 1,
                'remarks' => 'kbn[2]：福利厚生',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);              
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 4],
            [
                'kbn' => 2,
                'code' => 3,
                'name' => '休憩室',
                'order' => 2,
                'remarks' => 'kbn[2]：福利厚生',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);           
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 5],
            [
                'kbn' => 3,
                'code' => 1,
                'name' => '四捨五入',
                'order' => 0,
                'remarks' => 'kbn[3]：端数処理',
                'create_user' => 'system',
                'update_user' => 'system',
            ]); 
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 6],
            [
                'kbn' => 3,
                'code' => 2,
                'name' => '切り捨て',
                'order' => 1,
                'remarks' => 'kbn[3]：端数処理',
                'create_user' => 'system',
                'update_user' => 'system',
            ]); 
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 7],
            [
                'kbn' => 3,
                'code' => 3,
                'name' => '切り上げ',
                'order' => 2,
                'remarks' => 'kbn[3]：端数処理',
                'create_user' => 'system',
                'update_user' => 'system',
            ]); 
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 8],
            [
                'kbn' => 4,
                'code' => 1,
                'name' => '既存取引先 ',
                'order' => 0,
                'remarks' => 'kbn[4]：ステータス',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 9],
            [
                'kbn' => 4,
                'code' => 2,
                'name' => '新規営業中',
                'order' => 1,
                'remarks' => 'kbn[4]：ステータス',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 10],
            [
                'kbn' => 5,
                'code' => 1,
                'name' => '紹介派遣である',
                'order' => 1,
                'remarks' => 'kbn[5]：紹介派遣有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 11],
            [
                'kbn' => 5,
                'code' => 2,
                'name' => '紹介派遣ではない',
                'order' => 2,
                'remarks' => 'kbn[5]：紹介派遣有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 12],
            [
                'kbn' => 6,
                'code' => 1,
                'name' => '時間の定めあり',
                'order' => 1,
                'remarks' => 'kbn[6]：期間の定め有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 13],
            [
                'kbn' => 6,
                'code' => 2,
                'name' => '時間の定めなし',
                'order' => 2,
                'remarks' => 'kbn[6]：期間の定め有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 14],
            [
                'kbn' => 7,
                'code' => 1,
                'name' => '自動的に更新する',
                'order' => 1,
                'remarks' => 'kbn[7]：契約更新の有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 15],
            [
                'kbn' => 7,
                'code' => 2,
                'name' => '更新する場合があり得る',
                'order' => 2,
                'remarks' => 'kbn[7]：契約更新の有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 16],
            [
                'kbn' => 7,
                'code' => 3,
                'name' => '契約の更新はしない',
                'order' => 3,
                'remarks' => 'kbn[7]：契約更新の有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 17],
            [
                'kbn' => 7,
                'code' => 4,
                'name' => 'その他',
                'order' => 4,
                'remarks' => 'kbn[7]：契約更新の有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 18],
            [
                'kbn' => 8,
                'code' => 1,
                'name' => '月',
                'order' => 1,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 19],
            [
                'kbn' => 8,
                'code' => 2,
                'name' => '火',
                'order' => 2,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 20],
            [
                'kbn' => 8,
                'code' => 3,
                'name' => '水',
                'order' => 3,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 21],
            [
                'kbn' => 8,
                'code' => 4,
                'name' => '木',
                'order' => 4,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 22],
            [
                'kbn' => 8,
                'code' => 5,
                'name' => '金',
                'order' => 5,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 23],
            [
                'kbn' => 8,
                'code' => 6,
                'name' => '土',
                'order' => 6,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 24],
            [
                'kbn' => 8,
                'code' => 7,
                'name' => '日',
                'order' => 7,
                'remarks' => 'kbn[8]：出勤曜日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 25],
            [
                'kbn' => 9,
                'code' => 999,
                'name' => '無制限',
                'order' => 0,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 26],
            [
                'kbn' => 9,
                'code' => 0,
                'name' => '0時間',
                'order' => 1,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 27],
            [
                'kbn' => 9,
                'code' => 5,
                'name' => '5時間',
                'order' => 2,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 28],
            [
                'kbn' => 9,
                'code' => 10,
                'name' => '10時間',
                'order' => 3,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 29],
            [
                'kbn' => 9,
                'code' => 15,
                'name' => '15時間',
                'order' => 4,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 30],
            [
                'kbn' => 9,
                'code' => 20,
                'name' => '20時間',
                'order' => 5,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 31],
            [
                'kbn' => 9,
                'code' => 25,
                'name' => '25時間',
                'order' => 6,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 32],
            [
                'kbn' => 9,
                'code' => 30,
                'name' => '30時間',
                'order' => 7,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 33],
            [
                'kbn' => 9,
                'code' => 35,
                'name' => '35時間',
                'order' => 8,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 34],
            [
                'kbn' => 9,
                'code' => 40,
                'name' => '40時間',
                'order' => 9,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 35],
            [
                'kbn' => 9,
                'code' => 45,
                'name' => '45時間',
                'order' => 10,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 36],
            [
                'kbn' => 9,
                'code' => 50,
                'name' => '50時間',
                'order' => 11,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 37],
            [
                'kbn' => 9,
                'code' => 55,
                'name' => '55時間',
                'order' => 12,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 38],
            [
                'kbn' => 9,
                'code' => 60,
                'name' => '60時間',
                'order' => 13,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 39],
            [
                'kbn' => 9,
                'code' => 65,
                'name' => '65時間',
                'order' => 14,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 40],
            [
                'kbn' => 9,
                'code' => 70,
                'name' => '70時間',
                'order' => 15,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 41],
            [
                'kbn' => 9,
                'code' => 75,
                'name' => '75時間',
                'order' => 16,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 42],
            [
                'kbn' => 9,
                'code' => 80,
                'name' => '80時間',
                'order' => 17,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 43],
            [
                'kbn' => 9,
                'code' => 85,
                'name' => '85時間',
                'order' => 18,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 44],
            [
                'kbn' => 9,
                'code' => 90,
                'name' => '90時間',
                'order' => 19,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 45],
            [
                'kbn' => 9,
                'code' => 95,
                'name' => '95時間',
                'order' => 20,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 46],
            [
                'kbn' => 9,
                'code' => 100,
                'name' => '100時間',
                'order' => 21,
                'remarks' => 'kbn[9]：月最大時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 47],
            [
                'kbn' => 10,
                'code' => 999,
                'name' => '無制限',
                'order' => 0,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 48],
            [
                'kbn' => 10,
                'code' => 0,
                'name' => '0時間',
                'order' => 1,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 49],
            [
                'kbn' => 10,
                'code' => 1,
                'name' => '1時間',
                'order' => 2,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 50],
            [
                'kbn' => 10,
                'code' => 2,
                'name' => '2時間',
                'order' => 3,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 51],
            [
                'kbn' => 10,
                'code' => 3,
                'name' => '3時間',
                'order' => 4,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 52],
            [
                'kbn' => 10,
                'code' => 4,
                'name' => '4時間',
                'order' => 5,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 53],
            [
                'kbn' => 10,
                'code' => 5,
                'name' => '5時間',
                'order' => 6,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 54],
            [
                'kbn' => 10,
                'code' => 6,
                'name' => '6時間',
                'order' => 7,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 55],
            [
                'kbn' => 10,
                'code' => 7,
                'name' => '7時間',
                'order' => 8,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 56],
            [
                'kbn' => 10,
                'code' => 8,
                'name' => '8時間',
                'order' => 9,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 57],
            [
                'kbn' => 10,
                'code' => 9,
                'name' => '9時間',
                'order' => 10,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 58],
            [
                'kbn' => 10,
                'code' => 10,
                'name' => '10時間',
                'order' => 11,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 59],
            [
                'kbn' => 10,
                'code' => 11,
                'name' => '11時間',
                'order' => 12,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 60],
            [
                'kbn' => 10,
                'code' => 12,
                'name' => '12時間',
                'order' => 13,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 61],
            [
                'kbn' => 10,
                'code' => 13,
                'name' => '13時間',
                'order' => 14,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 62],
            [
                'kbn' => 10,
                'code' => 14,
                'name' => '14時間',
                'order' => 15,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 63],
            [
                'kbn' => 10,
                'code' => 15,
                'name' => '15時間',
                'order' => 16,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 64],
            [
                'kbn' => 10,
                'code' => 16,
                'name' => '16時間',
                'order' => 17,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 65],
            [
                'kbn' => 10,
                'code' => 17,
                'name' => '17時間',
                'order' => 18,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 66],
            [
                'kbn' => 10,
                'code' => 18,
                'name' => '18時間',
                'order' => 19,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 67],
            [
                'kbn' => 10,
                'code' => 19,
                'name' => '19時間',
                'order' => 20,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 68],
            [
                'kbn' => 10,
                'code' => 20,
                'name' => '20時間',
                'order' => 21,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 69],
            [
                'kbn' => 10,
                'code' => 21,
                'name' => '21時間',
                'order' => 22,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 70],
            [
                'kbn' => 10,
                'code' => 22,
                'name' => '22時間',
                'order' => 23,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 71],
            [
                'kbn' => 10,
                'code' => 23,
                'name' => '23時間',
                'order' => 24,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 72],
            [
                'kbn' => 10,
                'code' => 24,
                'name' => '24時間',
                'order' => 25,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 73],
            [
                'kbn' => 10,
                'code' => 25,
                'name' => '25時間',
                'order' => 26,
                'remarks' => 'kbn[10]：最大週時間外労働',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 74],
            [
                'kbn' => 11,
                'code' => 7,
                'name' => '無制限',
                'order' => 0,
                'remarks' => 'kbn[11]：最大週勤務日数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 75],
            [
                'kbn' => 11,
                'code' => 6,
                'name' => '6',
                'order' => 1,
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 76],
            [
                'kbn' => 11,
                'code' => 5,
                'name' => '5',
                'order' => 2,
                'remarks' => 'kbn[11]：最大週勤務日数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 77],
            [
                'kbn' => 11,
                'code' => 4,
                'name' => '4',
                'order' => 3,
                'remarks' => 'kbn[11]：最大週勤務日数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 78],
            [
                'kbn' => 11,
                'code' => 3,
                'name' => '3',
                'order' => 4,
                'remarks' => 'kbn[11]：最大週勤務日数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 79],
            [
                'kbn' => 11,
                'code' => 2,
                'name' => '2',
                'order' => 5,
                'remarks' => 'kbn[11]：最大週勤務日数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 80],
            [
                'kbn' => 11,
                'code' => 1,
                'name' => '1',
                'order' => 6,
                'remarks' => 'kbn[11]：最大週勤務日数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 81],
            [
                'kbn' => 12,
                'code' => 1,
                'name' => '1',
                'order' => 0,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 82],
            [
                'kbn' => 12,
                'code' => 2,
                'name' => '2',
                'order' => 1,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 83],
            [
                'kbn' => 12,
                'code' => 3,
                'name' => '3',
                'order' => 2,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 84],
            [
                'kbn' => 12,
                'code' => 4,
                'name' => '4',
                'order' => 3,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 85],
            [
                'kbn' => 12,
                'code' => 5,
                'name' => '5',
                'order' => 4,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 86],
            [
                'kbn' => 12,
                'code' => 6,
                'name' => '6',
                'order' => 5,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 87],
            [
                'kbn' => 12,
                'code' => 7,
                'name' => '7',
                'order' => 6,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 88],
            [
                'kbn' => 12,
                'code' => 8,
                'name' => '8',
                'order' => 7,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 89],
            [
                'kbn' => 12,
                'code' => 9,
                'name' => '9',
                'order' => 8,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 90],
            [
                'kbn' => 12,
                'code' => 10,
                'name' => '10',
                'order' => 9,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 91],
            [
                'kbn' => 12,
                'code' => 11,
                'name' => '11',
                'order' => 10,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 92],
            [
                'kbn' => 12,
                'code' => 12,
                'name' => '12',
                'order' => 11,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 93],
            [
                'kbn' => 12,
                'code' => 13,
                'name' => '13',
                'order' => 12,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 94],
            [
                'kbn' => 12,
                'code' => 14,
                'name' => '14',
                'order' => 13,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 95],
            [
                'kbn' => 12,
                'code' => 15,
                'name' => '15',
                'order' => 14,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 96],
            [
                'kbn' => 12,
                'code' => 16,
                'name' => '16',
                'order' => 15,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 97],
            [
                'kbn' => 12,
                'code' => 17,
                'name' => '17',
                'order' => 16,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 98],
            [
                'kbn' => 12,
                'code' => 18,
                'name' => '18',
                'order' => 17,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 99],
            [
                'kbn' => 12,
                'code' => 19,
                'name' => '19',
                'order' => 18,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 100],
            [
                'kbn' => 12,
                'code' => 20,
                'name' => '20',
                'order' => 19,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 101],
            [
                'kbn' => 12,
                'code' => 21,
                'name' => '21',
                'order' => 20,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 102],
            [
                'kbn' => 12,
                'code' => 22,
                'name' => '22',
                'order' => 21,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 103],
            [
                'kbn' => 12,
                'code' => 23,
                'name' => '23',
                'order' => 22,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 104],
            [
                'kbn' => 12,
                'code' => 24,
                'name' => '24',
                'order' => 23,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 105],
            [
                'kbn' => 12,
                'code' => 25,
                'name' => '25',
                'order' => 24,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 106],
            [
                'kbn' => 12,
                'code' => 26,
                'name' => '26',
                'order' => 25,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 107],
            [
                'kbn' => 12,
                'code' => 27,
                'name' => '27',
                'order' => 26,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 108],
            [
                'kbn' => 12,
                'code' => 28,
                'name' => '28',
                'order' => 27,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 109],
            [
                'kbn' => 12,
                'code' => 99,
                'name' => '末日',
                'order' => 28,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 110],
            [
                'kbn' => 12,
                'code' => 100,
                'name' => '日払い',
                'order' => 29,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 111],
            [
                'kbn' => 12,
                'code' => 101,
                'name' => '週払い',
                'order' => 30,
                'remarks' => 'kbn[12]：締日',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 112],
            [
                'kbn' => 13,
                'code' => 5,
                'name' => '5',
                'order' => 0,
                'remarks' => 'kbn[13]：時間丸め単位',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 113],
            [
                'kbn' => 13,
                'code' => 10,
                'name' => '10',
                'order' => 1,
                'remarks' => 'kbn[13]：時間丸め単位',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 114],
            [
                'kbn' => 13,
                'code' => 15,
                'name' => '15',
                'order' => 2,
                'remarks' => 'kbn[13]：時間丸め単位',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 115],
            [
                'kbn' => 13,
                'code' => 30,
                'name' => '30',
                'order' => 3,
                'remarks' => 'kbn[13]：時間丸め単位',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 116],
            [
                'kbn' => 14,
                'code' => 1,
                'name' => '時間制',
                'order' => 0,
                'remarks' => 'kbn[14]：時間定額制',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 117],
            [
                'kbn' => 14,
                'code' => 2,
                'name' => '定額制',
                'order' => 1,
                'remarks' => 'kbn[14]：時間定額制',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 118],
            [
                'kbn' => 14,
                'code' => 3,
                'name' => '定額制(日額)',
                'order' => 2,
                'remarks' => 'kbn[14]：時間定額制',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 119],
            [
                'kbn' => 15,
                'code' => 1,
                'name' => '有',
                'order' => 0,
                'remarks' => 'kbn[15]：有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 120],
            [
                'kbn' => 15,
                'code' => 2,
                'name' => '無',
                'order' => 1,
                'remarks' => 'kbn[15]：有無',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 121],
            [
                'kbn' => 16,
                'code' => 20120401,
                'name' => '4条-1号　情報処理システム開発',
                'order' => 0,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 122],
            [
                'kbn' => 16,
                'code' => 20120402,
                'name' => '4条-2号　機械設計',
                'order' => 1,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 123],
            [
                'kbn' => 16,
                'code' => 20120403,
                'name' => '4条-3号　事務用機器操作',
                'order' => 2,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 124],
            [
                'kbn' => 16,
                'code' => 20120404,
                'name' => '4条-4号　通訳、翻訳、速記',
                'order' => 3,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 125],
            [
                'kbn' => 16,
                'code' => 20120405,
                'name' => '4条-5号　秘書',
                'order' => 4,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 126],
            [
                'kbn' => 16,
                'code' => 20120406,
                'name' => '4条-6号　ファイリング',
                'order' => 5,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 127],
            [
                'kbn' => 16,
                'code' => 20120407,
                'name' => '4条-7号　調査',
                'order' => 6,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 128],
            [
                'kbn' => 16,
                'code' => 20120408,
                'name' => '4条-8号　財務',
                'order' => 7,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 129],
            [
                'kbn' => 16,
                'code' => 20120409,
                'name' => '4条-9号　貿易',
                'order' => 8,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 130],
            [
                'kbn' => 16,
                'code' => 20120410,
                'name' => '4条-10号　デモンストレーション',
                'order' => 9,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 131],
            [
                'kbn' => 16,
                'code' => 20120411,
                'name' => '4条-11号　添乗',
                'order' => 10,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 132],
            [
                'kbn' => 16,
                'code' => 20120412,
                'name' => '4条-12号　受付・案内',
                'order' => 11,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 133],
            [
                'kbn' => 16,
                'code' => 20120413,
                'name' => '4条-13号　研究開発',
                'order' => 12,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 134],
            [
                'kbn' => 16,
                'code' => 20120414,
                'name' => '4条-14号　事業の実施体制の企画、立案',
                'order' => 13,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 135],
            [
                'kbn' => 16,
                'code' => 20120415,
                'name' => '4条-15号　書籍等の制作・編集',
                'order' => 14,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 136],
            [
                'kbn' => 16,
                'code' => 20120416,
                'name' => '4条-16号　広告デザイン',
                'order' => 15,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 137],
            [
                'kbn' => 16,
                'code' => 20120417,
                'name' => '4条-17号　OAインストラクション',
                'order' => 16,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 138],
            [
                'kbn' => 16,
                'code' => 20120418,
                'name' => '4条-18号　セールスエンジニアの営業、金融商品の営業',
                'order' => 17,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 139],
            [
                'kbn' => 16,
                'code' => 20120501,
                'name' => '5条-1号　放送機器等操作',
                'order' => 18,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 140],
            [
                'kbn' => 16,
                'code' => 20120502,
                'name' => '5条-2号　放送番組等の制作',
                'order' => 19,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 141],
            [
                'kbn' => 16,
                'code' => 20120503,
                'name' => '5条-3号　建築物清掃',
                'order' => 20,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 142],
            [
                'kbn' => 16,
                'code' => 20120504,
                'name' => '5条-4号　建築設備運転等',
                'order' => 21,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 143],
            [
                'kbn' => 16,
                'code' => 20120505,
                'name' => '5条-5号　駐車場管理等',
                'order' => 22,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 144],
            [
                'kbn' => 16,
                'code' => 20120506,
                'name' => '5条-6号　インテリアコーディネート',
                'order' => 23,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 145],
            [
                'kbn' => 16,
                'code' => 20120507,
                'name' => '5条-7号　アナウンサー',
                'order' => 24,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 146],
            [
                'kbn' => 16,
                'code' => 20120508,
                'name' => '5条-8号　テレマーケティングの営業',
                'order' => 25,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 147],
            [
                'kbn' => 16,
                'code' => 20120509,
                'name' => '5条-9号　放送番組等における大道具・小道具',
                'order' => 26,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 148],
            [
                'kbn' => 16,
                'code' => 20120510,
                'name' => '5条-10号　水道施設等の設備運転等',
                'order' => 27,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 149],
            [
                'kbn' => 16,
                'code' => 20140001,
                'name' => '自由化業務',
                'order' => 28,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 150],
            [
                'kbn' => 16,
                'code' => 1,
                'name' => '1号 ソフトウェア開発',
                'order' => 29,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 151],
            [
                'kbn' => 16,
                'code' => 2,
                'name' => '2号 機械設計',
                'order' => 30,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 152],
            [
                'kbn' => 16,
                'code' => 3,
                'name' => '3号 放送機器操作',
                'order' => 31,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 153],
            [
                'kbn' => 16,
                'code' => 4,
                'name' => '4号 放送番組等演出',
                'order' => 32,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 154],
            [
                'kbn' => 16,
                'code' => 5,
                'name' => '5号 事務用機器操作',
                'order' => 33,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 155],
            [
                'kbn' => 16,
                'code' => 6,
                'name' => '6号 通訳、翻訳、速記',
                'order' => 34,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 156],
            [
                'kbn' => 16,
                'code' => 7,
                'name' => '7号 秘書',
                'order' => 35,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 157],
            [
                'kbn' => 16,
                'code' => 8,
                'name' => '8号 ファイリング',
                'order' => 36,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 158],
            [
                'kbn' => 16,
                'code' => 9,
                'name' => '9号 調査',
                'order' => 37,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 159],
            [
                'kbn' => 16,
                'code' => 10,
                'name' => '10号 財務処理',
                'order' => 38,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 160],
            [
                'kbn' => 16,
                'code' => 11,
                'name' => '11号 取引文書作成',
                'order' => 39,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 161],
            [
                'kbn' => 16,
                'code' => 12,
                'name' => '12号 デモンストレーション',
                'order' => 40,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 162],
            [
                'kbn' => 16,
                'code' => 13,
                'name' => '13号 添乗',
                'order' => 41,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 163],
            [
                'kbn' => 16,
                'code' => 14,
                'name' => '14号 建築物清掃',
                'order' => 42,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 164],
            [
                'kbn' => 16,
                'code' => 15,
                'name' => '15号 建築設備運転等',
                'order' => 43,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 165],
            [
                'kbn' => 16,
                'code' => 16,
                'name' => '16号 受付、案内、駐車場管理等',
                'order' => 44,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 166],
            [
                'kbn' => 16,
                'code' => 17,
                'name' => '17号 研究開発',
                'order' => 45,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 167],
            [
                'kbn' => 16,
                'code' => 18,
                'name' => '18号 事業の実施体制の企画、立案',
                'order' => 46,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 168],
            [
                'kbn' => 16,
                'code' => 19,
                'name' => '19号 書籍等の製作・編集',
                'order' => 47,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 169],
            [
                'kbn' => 16,
                'code' => 20,
                'name' => '20号 広告デザイン',
                'order' => 48,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 170],
            [
                'kbn' => 16,
                'code' => 21,
                'name' => '21号 インテリアコーディネーター',
                'order' => 49,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 171],
            [
                'kbn' => 16,
                'code' => 22,
                'name' => '22号 アナウンサー',
                'order' => 50,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 172],
            [
                'kbn' => 16,
                'code' => 23,
                'name' => '23号 OAインストラクション',
                'order' => 51,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 173],
            [
                'kbn' => 16,
                'code' => 24,
                'name' => '24号 テレマーケティングの営業',
                'order' => 52,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 174],
            [
                'kbn' => 16,
                'code' => 25,
                'name' => '25号 セールスエンジニアの営業、金融商品の営業',
                'order' => 53,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 175],
            [
                'kbn' => 16,
                'code' => 26,
                'name' => '26号 放送番組における大道具・小道具',
                'order' => 54,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 176],
            [
                'kbn' => 16,
                'code' => 2021022001,
                'name' => '01　管理的公務員',
                'order' => 55,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 177],
            [
                'kbn' => 16,
                'code' => 2021022002,
                'name' => '02　法人・団体役員',
                'order' => 56,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 178],
            [
                'kbn' => 16,
                'code' => 2021022003,
                'name' => '03　法人・団体管理職員',
                'order' => 57,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 179],
            [
                'kbn' => 16,
                'code' => 2021022004,
                'name' => '04　その他の管理的職業従事者',
                'order' => 58,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 180],
            [
                'kbn' => 16,
                'code' => 2021022005,
                'name' => '05　研究者',
                'order' => 59,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 181],
            [
                'kbn' => 16,
                'code' => 2021022006,
                'name' => '06　農林水産技術者',
                'order' => 60,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 182],
            [
                'kbn' => 16,
                'code' => 2021022007,
                'name' => '07,08　製造技術者',
                'order' => 61,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 183],
            [
                'kbn' => 16,
                'code' => 2021022008,
                'name' => '09　建築・土木・測量技術者',
                'order' => 62,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 184],
            [
                'kbn' => 16,
                'code' => 2021022009,
                'name' => '10　情報処理・通信技術者',
                'order' => 63,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 185],
            [
                'kbn' => 16,
                'code' => 2021022010,
                'name' => '11　その他の技術者',
                'order' => 64,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 186],
            [
                'kbn' => 16,
                'code' => 2021022011,
                'name' => '12　医師,歯科医師,獣医師,薬剤師',
                'order' => 65,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 187],
            [
                'kbn' => 16,
                'code' => 2021022012,
                'name' => '13　保健師,助産師,看護師',
                'order' => 66,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 188],
            [
                'kbn' => 16,
                'code' => 2021022013,
                'name' => '14　医療技術者',
                'order' => 67,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 189],
            [
                'kbn' => 16,
                'code' => 2021022014,
                'name' => '15　その他の保健医療従事者',
                'order' => 68,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 190],
            [
                'kbn' => 16,
                'code' => 2021022015,
                'name' => '16　社会福祉専門職業従事者',
                'order' => 69,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 191],
            [
                'kbn' => 16,
                'code' => 2021022016,
                'name' => '17　法務従事者',
                'order' => 70,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 192],
            [
                'kbn' => 16,
                'code' => 2021022017,
                'name' => '18　経営・金融・保険専門職業従事者',
                'order' => 71,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 193],
            [
                'kbn' => 16,
                'code' => 2021022018,
                'name' => '19　教員',
                'order' => 72,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 194],
            [
                'kbn' => 16,
                'code' => 2021022019,
                'name' => '20　宗教家',
                'order' => 73,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 195],
            [
                'kbn' => 16,
                'code' => 2021022020,
                'name' => '21　著述家,記者,編集者',
                'order' => 74,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 196],
            [
                'kbn' => 16,
                'code' => 2021022021,
                'name' => '22　美術家,デザイナー,写真家,映像撮影者',
                'order' => 75,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 197],
            [
                'kbn' => 16,
                'code' => 2021022022,
                'name' => '23　音楽家,舞台芸術家',
                'order' => 76,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 198],
            [
                'kbn' => 16,
                'code' => 2021022023,
                'name' => '24　その他の専門的職業従事者',
                'order' => 77,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 199],
            [
                'kbn' => 16,
                'code' => 2021022024,
                'name' => '25　一般事務従事者',
                'order' => 78,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 200],
            [
                'kbn' => 16,
                'code' => 2021022025,
                'name' => '26　会計事務従事者',
                'order' => 79,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 201],
            [
                'kbn' => 16,
                'code' => 2021022026,
                'name' => '27　生産関連事務従事者',
                'order' => 80,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 202],
            [
                'kbn' => 16,
                'code' => 2021022027,
                'name' => '28　営業・販売事務従事者',
                'order' => 81,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 203],
            [
                'kbn' => 16,
                'code' => 2021022028,
                'name' => '29　外勤事務従事者',
                'order' => 82,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 204],
            [
                'kbn' => 16,
                'code' => 2021022029,
                'name' => '30　運輸・郵便事務従事者',
                'order' => 83,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 205],
            [
                'kbn' => 16,
                'code' => 2021022030,
                'name' => '31　事務用機器操作員',
                'order' => 84,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 206],
            [
                'kbn' => 16,
                'code' => 2021022031,
                'name' => '32　商品販売従事者',
                'order' => 85,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 207],
            [
                'kbn' => 16,
                'code' => 2021022032,
                'name' => '33　販売類似職業従事者',
                'order' => 86,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 208],
            [
                'kbn' => 16,
                'code' => 2021022033,
                'name' => '34　営業職業従事者',
                'order' => 87,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 209],
            [
                'kbn' => 16,
                'code' => 2021022034,
                'name' => '35　家庭生活支援サービス職業従事者',
                'order' => 88,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 210],
            [
                'kbn' => 16,
                'code' => 2021022035,
                'name' => '36　介護サービス職業従事者',
                'order' => 89,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 211],
            [
                'kbn' => 16,
                'code' => 2021022036,
                'name' => '37　保健医療サービス職業従事者',
                'order' => 90,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 212],
            [
                'kbn' => 16,
                'code' => 2021022037,
                'name' => '38　生活衛生サービス職業従事者',
                'order' => 91,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 213],
            [
                'kbn' => 16,
                'code' => 2021022038,
                'name' => '39　飲食物調理従事者',
                'order' => 92,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 214],
            [
                'kbn' => 16,
                'code' => 2021022039,
                'name' => '40　接客・給仕職業従事者',
                'order' => 93,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 215],
            [
                'kbn' => 16,
                'code' => 2021022040,
                'name' => '41　居住施設・ビル等管理人',
                'order' => 94,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 216],
            [
                'kbn' => 16,
                'code' => 2021022041,
                'name' => '42　その他のサービス職業従事者',
                'order' => 95,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 217],
            [
                'kbn' => 16,
                'code' => 2021022042,
                'name' => '43,44,45　自衛官・司法警察職員等',
                'order' => 96,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 218],
            [
                'kbn' => 16,
                'code' => 2021022043,
                'name' => '46　農業従事者',
                'order' => 97,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 219],
            [
                'kbn' => 16,
                'code' => 2021022044,
                'name' => '47　林業従事者',
                'order' => 98,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 220],
            [
                'kbn' => 16,
                'code' => 2021022045,
                'name' => '48　漁業従事者',
                'order' => 99,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 221],
            [
                'kbn' => 16,
                'code' => 2021022046,
                'name' => '49,50　生産設備制御・監視従事者',
                'order' => 100,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 222],
            [
                'kbn' => 16,
                'code' => 2021022047,
                'name' => '51　機械組立設備制御・監視従事者',
                'order' => 101,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 223],
            [
                'kbn' => 16,
                'code' => 2021022048,
                'name' => '52,53　製品製造・加工処理従事者',
                'order' => 102,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 224],
            [
                'kbn' => 16,
                'code' => 2021022049,
                'name' => '54　機械組立従事者',
                'order' => 103,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 225],
            [
                'kbn' => 16,
                'code' => 2021022050,
                'name' => '55　機械整備・修理従事者',
                'order' => 104,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 226],
            [
                'kbn' => 16,
                'code' => 2021022051,
                'name' => '56,57　製品検査従事者',
                'order' => 105,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 227],
            [
                'kbn' => 16,
                'code' => 2021022052,
                'name' => '58　機械検査従事者',
                'order' => 106,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 228],
            [
                'kbn' => 16,
                'code' => 2021022053,
                'name' => '59　生産関連・生産類似作業従事者',
                'order' => 107,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 229],
            [
                'kbn' => 16,
                'code' => 2021022054,
                'name' => '60　鉄道運転従事者',
                'order' => 108,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 230],
            [
                'kbn' => 16,
                'code' => 2021022055,
                'name' => '61　自動車運転従事者',
                'order' => 109,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 231],
            [
                'kbn' => 16,
                'code' => 2021022056,
                'name' => '62　船舶・航空機運転従事者',
                'order' => 110,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 232],
            [
                'kbn' => 16,
                'code' => 2021022057,
                'name' => '63　その他の輸送従事者',
                'order' => 111,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 233],
            [
                'kbn' => 16,
                'code' => 2021022058,
                'name' => '64　定置・建設機械運転従事者',
                'order' => 112,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 234],
            [
                'kbn' => 16,
                'code' => 2021022059,
                'name' => '65　建設躯体工事従事者',
                'order' => 113,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 235],
            [
                'kbn' => 16,
                'code' => 2021022060,
                'name' => '66　建設従事者（建設躯体工事従事者を除く）',
                'order' => 114,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 236],
            [
                'kbn' => 16,
                'code' => 2021022061,
                'name' => '67　電気工事従事者',
                'order' => 115,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 237],
            [
                'kbn' => 16,
                'code' => 2021022062,
                'name' => '68　土木作業従事者',
                'order' => 116,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 238],
            [
                'kbn' => 16,
                'code' => 2021022063,
                'name' => '69　採掘従事者',
                'order' => 117,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 239],
            [
                'kbn' => 16,
                'code' => 2021022064,
                'name' => '70　運搬従事者',
                'order' => 118,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 240],
            [
                'kbn' => 16,
                'code' => 2021022065,
                'name' => '71　清掃従事者',
                'order' => 119,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 241],
            [
                'kbn' => 16,
                'code' => 2021022066,
                'name' => '72　包装従事者',
                'order' => 120,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 242],
            [
                'kbn' => 16,
                'code' => 2021022067,
                'name' => '99　分類不能の職業',
                'order' => 121,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 243],
            [
                'kbn' => 16,
                'code' => 2021022068,
                'name' => '1号　情報処理システム開発',
                'order' => 122,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 244],
            [
                'kbn' => 16,
                'code' => 2021022069,
                'name' => '2号　機械設計',
                'order' => 123,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 245],
            [
                'kbn' => 16,
                'code' => 2021022070,
                'name' => '3号　事務用機器操作',
                'order' => 124,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 246],
            [
                'kbn' => 16,
                'code' => 2021022071,
                'name' => '4号　通訳、翻訳、速記',
                'order' => 125,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 247],
            [
                'kbn' => 16,
                'code' => 2021022072,
                'name' => '5号　秘書',
                'order' => 126,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 248],
            [
                'kbn' => 16,
                'code' => 2021022073,
                'name' => '6号　ファイリング',
                'order' => 127,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 249],
            [
                'kbn' => 16,
                'code' => 2021022074,
                'name' => '7号　調査',
                'order' => 128,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 250],
            [
                'kbn' => 16,
                'code' => 2021022075,
                'name' => '8号　財務',
                'order' => 129,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 251],
            [
                'kbn' => 16,
                'code' => 2021022076,
                'name' => '9号　貿易',
                'order' => 130,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 252],
            [
                'kbn' => 16,
                'code' => 2021022077,
                'name' => '10号　デモンストレーション',
                'order' => 131,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 253],
            [
                'kbn' => 16,
                'code' => 2021022078,
                'name' => '11号　添乗',
                'order' => 132,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 254],
            [
                'kbn' => 16,
                'code' => 2021022079,
                'name' => '12号　受付・案内',
                'order' => 133,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 255],
            [
                'kbn' => 16,
                'code' => 2021022080,
                'name' => '13号　研究開発',
                'order' => 134,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 256],
            [
                'kbn' => 16,
                'code' => 2021022081,
                'name' => '14号　事業の実施体制の企画、立案',
                'order' => 135,
                'remarks' => 'kbn[16]：業務内容',
               'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 257],
            [
                'kbn' => 16,
                'code' => 2021022082,
                'name' => '15号　書籍等の制作・編集',
                'order' => 136,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 258],
            [
                'kbn' => 16,
                'code' => 2021022083,
                'name' => '16号　広告デザイン',
                'order' => 137,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 259],
            [
                'kbn' => 16,
                'code' => 2021022084,
                'name' => '17号　OAインストラクション',
                'order' => 138,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 260],
            [
                'kbn' => 16,
                'code' => 2021022085,
                'name' => '18号　セールスエンジニアの営業、金融商品の営業',
                'order' => 139,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 261],
            [
                'kbn' => 16,
                'code' => 2021022086,
                'name' => 'その他',
                'order' => 140,
                'remarks' => 'kbn[16]：業務内容',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 262],
            [
                'kbn' => 17,
                'code' => 1,
                'name' => '無期雇用派遣労働者に限定する',
                'order' => 0,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 263],
            [
                'kbn' => 17,
                'code' => 2,
                'name' => '無期雇用派遣労働者　且つ60歳以上に限定する',
                'order' => 1,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 264],
            [
                'kbn' => 17,
                'code' => 3,
                'name' => '無期雇用派遣労働者　且つ60歳未満に限定する',
                'order' => 2,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 265],
            [
                'kbn' => 17,
                'code' => 4,
                'name' => '有期雇用派遣労働者に限定する',
                'order' => 3,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 266],
            [
                'kbn' => 17,
                'code' => 5,
                'name' => '無期雇用派遣労働者　且つ60歳以上に限定する',
                'order' => 4,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 267],
            [
                'kbn' => 17,
                'code' => 6,
                'name' => '無期雇用派遣労働者　且つ60歳未満に限定する',
                'order' => 5,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 268],
            [
                'kbn' => 17,
                'code' => 7,
                'name' => '無期雇用派遣労働者または60歳以上の者に限定しない',
                'order' => 6,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 269],
            [
                'kbn' => 17,
                'code' => 8,
                'name' => '限定しない',
                'order' => 7,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 270],
            [
                'kbn' => 17,
                'code' => 9,
                'name' => '限定する',
                'order' => 8,
                'remarks' => 'kbn[17]：無期雇用労働者または60歳以上ものに限定するか否かの別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 271],
            [
                'kbn' => 18,
                'code' => 1,
                'name' => '満60歳以上',
                'order' => 0,
                'remarks' => 'kbn[18]：無期雇用派遣労働者－理由',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 272],
            [
                'kbn' => 18,
                'code' => 2,
                'name' => '有期プロジェクトの業務',
                'order' => 1,
                'remarks' => 'kbn[18]：無期雇用派遣労働者－理由',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 273],
            [
                'kbn' => 18,
                'code' => 3,
                'name' => '日数限定業務',
                'order' => 2,
                'remarks' => 'kbn[18]：無期雇用派遣労働者－理由',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 274],
            [
                'kbn' => 18,
                'code' => 4,
                'name' => '産前・産後・育児・介護休業等の代替要員としての業務',
                'order' => 3,
                'remarks' => 'kbn[18]：無期雇用派遣労働者－理由',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 275],
            [
                'kbn' => 19,
                'code' => 1,
                'name' => '法第40条の２第1項に定める60歳以上の者であるため期間制限はなし',
                'order' => 0,
                'remarks' => 'kbn[19]：無期雇用派遣労働者－詳細',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 276],
            [
                'kbn' => 19,
                'code' => 2,
                'name' => '法第40条の２第１項第３号イに該当する業務に該当する業務',
                'order' => 1,
                'remarks' => 'kbn[19]：無期雇用派遣労働者－詳細',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 277],
            [
                'kbn' => 19,
                'code' => 3,
                'name' => '法第40条の２第１項第３号ロに該当',
                'order' => 2,
                'remarks' => 'kbn[19]：無期雇用派遣労働者－詳細',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 278],
            [
                'kbn' => 20,
                'code' => 1,
                'name' => '男',
                'order' => 0,
                'remarks' => 'kbn[20]：性別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 279],
            [
                'kbn' => 20,
                'code' => 2,
                'name' => '女',
                'order' => 1,
                'remarks' => 'kbn[20]：性別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 280],
            [
                'kbn' => 20,
                'code' => 3,
                'name' => '不明',
                'order' => 2,
                'remarks' => 'kbn[20]：性別',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 281],
            [
                'kbn' => 21,
                'code' => 1,
                'name' => '契約期間満了時の業務量        勤務成績、態度    能力    会社の経営状況
従事している業務の進捗状況    その他',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 282],
            [
                'kbn' => 21,
                'code' => 2,
                'name' => '派遣',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 283],
            [
                'kbn' => 21,
                'code' => 3,
                'name' => '1日5時間、2ヶ月81時間、年間360時間を越えない範囲で行わせる。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 284],
            [
                'kbn' => 21,
                'code' => 4,
                'name' => '連続のVDT作業に常時従事させる場合は、連続作業時間が1時間を越えないようにし、次の連続までの間に10～15分間の作業休止期間を設け、かつ、一連続作業時間内において１～2回程度の小休止を設ける。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 285],
            [
                'kbn' => 21,
                'code' => 5,
                'name' => '派遣労働者の責に帰すべき事由以外の事由によって派遣契約中途解除を行う場合、1ヶ月以上の猶予期間をもって通知すると共に、甲及び乙は、当該派遣労働者の次の就業先の確保に努めることとする。また、当該予告を行わない場合は、甲は速やかに、当該派遣労働者の少なくとも３０日分以上に相当する額についての損害賠償を行う事とする。甲が予告した日と労働者派遣契約の解除を行おうとする日の間の期間が３０日に満たない場合には、少なくとも派遣労働者の当該予告の日と労働者派遣契約の解除を行おうとする日の３０日前の日との間の期間の日数分以上の賃金に相当する額についての損害の賠償を行うこととする。その他甲は乙と十分に協議した上で適切な前後処理方法を講ずることとする。また、乙及び甲双方の責に帰すべき事由がある場合には、乙及び甲のそれぞれの責に帰すべき部分の割合についても十分に考慮することとする。甲は、労働者派遣契約の契約期間が満了する前に労働者派遣契約の解除を行おうとする場合であって、乙から請求があったときは、労働者派遣契約の解除を行った理由を乙に対し明らかにすることとする。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 286],
            [
                'kbn' => 21,
                'code' => 6,
                'name' => '派遣労働者の責に帰すべき事由以外の事由によって派遣契約中途解除を行う場合、1ヶ月以上の猶予期間をもって通知すると共に、派遣先及び派遣元は、当該派遣労働者の次の就業先の確保に努めることとする。また、３０日前に予告しないときは労働基準法第２０条第１項に基づく解雇予告手当を支払うこと、休業させる場合には労働基準法第２６条に基づく休業手当を支払う等、雇用主に係る労働基準法等の責任を負うこととする。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 287],
            [
                'kbn' => 21,
                'code' => 7,
                'name' => '派遣労働者からの苦情の申し出があった場合、甲及び乙で連絡・協議し、遅滞のないよう誠実に対応するよう努める。そして、その結果について必ず派遣労働者に通知することとする。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 288],
            [
                'kbn' => 21,
                'code' => 8,
                'name' => '派遣労働者から場合、甲及び乙で連絡・協議し、遅滞のないよう誠実に対応するの結果について必ず派遣労働者に通知することとする。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 289],
            [
                'kbn' => 21,
                'code' => 9,
                'name' => '労働者派遣の役務の提供の終了後、当該派遣労働者を派遣先が雇用する場合には、職業紹介を経由して行うこととし、手数料として、派遣先は派遣元事業主に対して、支払われた賃金額の●●分の●●に相当する額を支払うものとする。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 290],
            [
                'kbn' => 21,
                'code' => 10,
                'name' => '派遣先の事業所における派遣可能期間の延長について、当該手続きを適正に行っていない場合や、上記個人単位の抵触日を超えて労働者派遣の役務の提供を受けた場合は派遣先は労働契約申込みみなし制度の対象となる。',
                'order' => 0,
                'remarks' => 'kbn[21]：デフォルト文言',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 291],
            [
                'kbn' => 22,
                'code' => 1,
                'name' => '仮登録',
                'order' => 0,
                'remarks' => 'kbn[22]：登録区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 292],
            [
                'kbn' => 22,
                'code' => 2,
                'name' => '本登録',
                'order' => 1,
                'remarks' => 'kbn[22]：登録区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 293],
            [
                'kbn' => 22,
                'code' => 3,
                'name' => '登録解除',
                'order' => 2,
                'remarks' => 'kbn[22]：登録区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 294],
            [
                'kbn' => 22,
                'code' => 4,
                'name' => '紹介休止',
                'order' => 3,
                'remarks' => 'kbn[22]：登録区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 295],
            [
                'kbn' => 23,
                'code' => 1,
                'name' => '電話',
                'order' => 0,
                'remarks' => 'kbn[23]：希望連絡方法',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 296],
            [
                'kbn' => 23,
                'code' => 2,
                'name' => '携帯電話',
                'order' => 1,
                'remarks' => 'kbn[23]：希望連絡方法',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 297],
            [
                'kbn' => 23,
                'code' => 3,
                'name' => 'FAX',
                'order' => 2,
                'remarks' => 'kbn[23]：希望連絡方法',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);    
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 298],
            [
                'kbn' => 23,
                'code' => 4,
                'name' => 'メール ',
                'order' => 3,
                'remarks' => 'kbn[23]：希望連絡方法',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);  
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 299],
            [
                'kbn' => 23,
                'code' => 5,
                'name' => '携帯メール',
                'order' => 4,
                'remarks' => 'kbn[23]：希望連絡方法',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);  
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 300],
            [
                'kbn' => 24,
                'code' => 1,
                'name' => '正社員',
                'order' => 0,
                'remarks' => 'kbn[24]：現在の就業状況',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 301],
            [
                'kbn' => 24,
                'code' => 2,
                'name' => '派遣、契約',
                'order' => 1,
                'remarks' => 'kbn[24]：現在の就業状況',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 302],
            [
                'kbn' => 24,
                'code' => 3,
                'name' => '個人事業者',
                'order' => 2,
                'remarks' => 'kbn[24]：現在の就業状況',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 303],
            [
                'kbn' => 24,
                'code' => 4,
                'name' => 'アルバイト',
                'order' => 3,
                'remarks' => 'kbn[24]：現在の就業状況',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 304],
            [
                'kbn' => 24,
                'code' => 5,
                'name' => '非就労',
                'order' => 4,
                'remarks' => 'kbn[24]：現在の就業状況',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 305],
            [
                'kbn' => 24,
                'code' => 6,
                'name' => 'その他',
                'order' => 5,
                'remarks' => 'kbn[24]：現在の就業状況',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
                        
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 306],
            [
                'kbn' => 25,
                'code' => 1,
                'name' => '未確認',
                'order' => 0,
                'remarks' => 'kbn[25]：就業状況区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 307],
            [
                'kbn' => 25,
                'code' => 2,
                'name' => '非就業',
                'order' => 1,
                'remarks' => 'kbn[25]：就業状況区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 308],
            [
                'kbn' => 25,
                'code' => 3,
                'name' => '稼働中',
                'order' => 2,
                'remarks' => 'kbn[25]：就業状況区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 309],
            [
                'kbn' => 26,
                'code' => 1,
                'name' => '未受講',
                'order' => 0,
                'remarks' => 'kbn[26]：受講済未実施区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 310],
            [
                'kbn' => 26,
                'code' => 2,
                'name' => '受講済',
                'order' => 1,
                'remarks' => 'kbn[26]：受講済未実施区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 311],
            [
                'kbn' => 27,
                'code' => 1,
                'name' => '未',
                'order' => 0,
                'remarks' => 'kbn[27]：未済区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 312],
            [
                'kbn' => 27,
                'code' => 2,
                'name' => '済',
                'order' => 1,
                'remarks' => 'kbn[27]：未済区分',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 313],
            [
                'kbn' => 28,
                'code' => 1,
                'name' => '東京都内',
                'order' => 0,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 314],
            [
                'kbn' => 28,
                'code' => 2,
                'name' => '東京都下',
                'order' => 1,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 315],
            [
                'kbn' => 28,
                'code' => 3,
                'name' => '横浜川崎',
                'order' => 2,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 316],
            [
                'kbn' => 28,
                'code' => 4,
                'name' => '埼玉',
                'order' => 3,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 317],
            [
                'kbn' => 28,
                'code' => 5,
                'name' => '千葉',
                'order' => 4,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 318],
            [
                'kbn' => 28,
                'code' => 6,
                'name' => '福岡',
                'order' => 5,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 319],
            [
                'kbn' => 28,
                'code' => 7,
                'name' => '大阪',
                'order' => 6,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 320],
            [
                'kbn' => 28,
                'code' => 8,
                'name' => '静岡',
                'order' => 7,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 321],
            [
                'kbn' => 28,
                'code' => 9,
                'name' => '名古屋',
                'order' => 8,
                'remarks' => 'kbn[28]：勤務地',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 322],
            [
                'kbn' => 29,
                'code' => 1,
                'name' => '正社員',
                'order' => 0,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 323],
            [
                'kbn' => 29,
                'code' => 2,
                'name' => '契約社員',
                'order' => 1,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 324],
            [
                'kbn' => 29,
                'code' => 3,
                'name' => '派遣社員',
                'order' => 2,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 325],
            [
                'kbn' => 29,
                'code' => 4,
                'name' => '紹介予定派遣',
                'order' => 3,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 326],
            [
                'kbn' => 29,
                'code' => 5,
                'name' => 'アルバイト・パート',
                'order' => 4,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 327],
            [
                'kbn' => 29,
                'code' => 6,
                'name' => '業務委託',
                'order' => 5,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 328],
            [
                'kbn' => 29,
                'code' => 7,
                'name' => 'その他',
                'order' => 6,
                'remarks' => 'kbn[29]：就業形態',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 329],
            [
                'kbn' => 30,
                'code' => 1,
                'name' => '時給1000-1500',
                'order' => 0,
                'remarks' => 'kbn[30]：希望金額',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 330],
            [
                'kbn' => 30,
                'code' => 1,
                'name' => '時給1500-2000',
                'order' => 0,
                'remarks' => 'kbn[30]：希望金額',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 331],
            [
                'kbn' => 30,
                'code' => 2,
                'name' => '時給2000-2500',
                'order' => 1,
                'remarks' => 'kbn[30]：希望金額',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 332],
            [
                'kbn' => 30,
                'code' => 3,
                'name' => '時給2500-3000',
                'order' => 2,
                'remarks' => 'kbn[30]：希望金額',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 333],
            [
                'kbn' => 30,
                'code' => 4,
                'name' => '時給3000以上',
                'order' => 3,
                'remarks' => 'kbn[30]：希望金額',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
    
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 334],
            [
                'kbn' => 31,
                'code' => 1,
                'name' => '一般事務',
                'order' => 0,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 335],
            [
                'kbn' => 31,
                'code' => 2,
                'name' => '軽作業',
                'order' => 1,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 336],
            [
                'kbn' => 31,
                'code' => 3,
                'name' => '電話業務',
                'order' => 2,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 337],
            [
                'kbn' => 31,
                'code' => 4,
                'name' => '営業',
                'order' => 3,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 338],
            [
                'kbn' => 31,
                'code' => 5,
                'name' => 'データ入力',
                'order' => 4,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 339],
            [
                'kbn' => 31,
                'code' => 6,
                'name' => 'システム関連',
                'order' => 5,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 340],
            [
                'kbn' => 31,
                'code' => 7,
                'name' => 'モデル',
                'order' => 6,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 341],
            [
                'kbn' => 31,
                'code' => 8,
                'name' => '営業、企画営業、コンサルティング営業',
                'order' => 7,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 342],
            [
                'kbn' => 31,
                'code' => 9,
                'name' => '技術営業、システム営業',
                'order' => 8,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 343],
            [
                'kbn' => 31,
                'code' => 10,
                'name' => '内勤営業、カウンターセールス',
                'order' => 9,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 344],
            [
                'kbn' => 31,
                'code' => 11,
                'name' => 'コールセンター運営、管理',
                'order' => 10,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 345],
            [
                'kbn' => 31,
                'code' => 12,
                'name' => 'カスタマーサポート、ヘルプデスク',
                'order' => 11,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 346],
            [
                'kbn' => 31,
                'code' => 13,
                'name' => '販売、サービス、飲食関連スタッフ',
                'order' => 12,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 347],
            [
                'kbn' => 31,
                'code' => 14,
                'name' => 'Webプロデューサー、ディレクター',
                'order' => 13,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 348],
            [
                'kbn' => 31,
                'code' => 15,
                'name' => 'Webデザイナー、コンテンツ企画',
                'order' => 14,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 349],
            [
                'kbn' => 31,
                'code' => 16,
                'name' => 'マルチメディア、ゲーム関連',
                'order' => 15,
                'remarks' => 'kbn[31]：希望職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 350],
            [
                'kbn' => 32,
                'code' => 1,
                'name' => '1年未満',
                'order' => 0,
                'remarks' => 'kbn[32]：経験年数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);	
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 351],
            [
                'kbn' => 32,
                'code' => 2,
                'name' => '1年～３年未満',
                'order' => 1,
                'remarks' => 'kbn[32]：経験年数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);	
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 352],
            [
                'kbn' => 32,
                'code' => 3,
                'name' => '３年以上',
                'order' => 2,
                'remarks' => 'kbn[32]：経験年数',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);	

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 353],
            [
                'kbn' => 33,
                'code' => 1,
                'name' => 'ラグジュアリーブランド',
                'order' => 0,
                'remarks' => 'kbn[33]：経験職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);	
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 354],
            [
                'kbn' => 33,
                'code' => 2,
                'name' => '百貨店、商業施設直雇用',
                'order' => 1,
                'remarks' => 'kbn[33]：経験職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);	
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 355],
            [
                'kbn' => 33,
                'code' => 3,
                'name' => '宝飾品、アパレル、化粧品',
                'order' => 2,
                'remarks' => 'kbn[33]：経験職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);	
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 356],
            [
                'kbn' => 33,
                'code' => 4,
                'name' => 'その他',
                'order' => 3,
                'remarks' => 'kbn[33]：経験職種',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 357],
            [
                'kbn' => 34,
                'code' => 1,
                'name' => '1',
                'order' => 4,
                'remarks' => 'kbn[34]：５段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 358],
            [
                'kbn' => 34,
                'code' => 2,
                'name' => '2',
                'order' => 3,
                'remarks' => 'kbn[34]：５段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 359],
            [
                'kbn' => 34,
                'code' => 3,
                'name' => '3',
                'order' => 2,
                'remarks' => 'kbn[34]：５段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 360],
            [
                'kbn' => 34,
                'code' => 4,
                'name' => '4',
                'order' => 1,
                'remarks' => 'kbn[34]：５段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 361],
            [
                'kbn' => 34,
                'code' => 5,
                'name' => '5',
                'order' => 0,
                'remarks' => 'kbn[34]：５段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 362],
            [
                'kbn' => 35,
                'code' => 1,
                'name' => 'A:できる',
                'order' => 0,
                'remarks' => 'kbn[35]：ABC段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 363],
            [
                'kbn' => 35,
                'code' => 2,
                'name' => 'B:あまりできない',
                'order' => 1,
                'remarks' => 'kbn[35]：ABC段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 364],
            [
                'kbn' => 35,
                'code' => 3,
                'name' => 'C:できない',
                'order' => 2,
                'remarks' => 'kbn[35]：ABC段階評価',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 365],
            [
                'kbn' => 36,
                'code' => 1,
                'name' => '誰に対しても明るい笑顔で自分から挨拶ができる',
                'order' => 0,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 366],
            [
                'kbn' => 36,
                'code' => 2,
                'name' => '他スタッフとすれ違う際、積極的に会釈や笑顔を交わしている',
                'order' => 1,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 367],
            [
                'kbn' => 36,
                'code' => 3,
                'name' => 'TPOをわきまえ、常に身だしなみを整えている',
                'order' => 2,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 368],
            [
                'kbn' => 36,
                'code' => 4,
                'name' => 'メイクは健康的で清潔感がある',
                'order' => 3,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 369],
            [
                'kbn' => 36,
                'code' => 5,
                'name' => '接客マイスターに相応しい洗練された印象である',
                'order' => 4,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 370],
            [
                'kbn' => 36,
                'code' => 6,
                'name' => '常に穏やかな表情を保ち、不満等を顔に出さない',
                'order' => 5,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 371],
            [
                'kbn' => 36,
                'code' => 7,
                'name' => '常に見られている意識を持ち、表情、姿勢に気を付けている',
                'order' => 6,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 372],
            [
                'kbn' => 36,
                'code' => 8,
                'name' => '礼儀・節度ある言動をわきまえている（丁寧な言葉遣い・態度）',
                'order' => 7,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 373],
            [
                'kbn' => 36,
                'code' => 9,
                'name' => '職場の整理整頓・清潔清掃を心がけている',
                'order' => 8,
                'remarks' => 'kbn[36]：基本態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 374],
            [
                'kbn' => 37,
                'code' => 1,
                'name' => '職場における秩序、規律、ルールを理解し守っている',
                'order' => 0,
                'remarks' => 'kbn[37]：勤務態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 375],
            [
                'kbn' => 37,
                'code' => 2,
                'name' => '健康・体調管理に留意し、勤怠状況が良好である',
                'order' => 1,
                'remarks' => 'kbn[37]：勤務態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 376],
            [
                'kbn' => 37,
                'code' => 3,
                'name' => '指導やアドバイスを素直にきく',
                'order' => 2,
                'remarks' => 'kbn[37]：勤務態度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 377],
            [
                'kbn' => 38,
                'code' => 1,
                'name' => '周囲の人々と協力して仕事を進めている',
                'order' => 0,
                'remarks' => 'kbn[38]：チームワーク　協調性',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 378],
            [
                'kbn' => 38,
                'code' => 2,
                'name' => '周囲の人々への配慮ができている',
                'order' => 1,
                'remarks' => 'kbn[38]：チームワーク　協調性',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 379],
            [
                'kbn' => 38,
                'code' => 3,
                'name' => '誰とでも笑顔で接し、進んで皆の中に溶け込んでいける',
                'order' => 2,
                'remarks' => 'kbn[38]：チームワーク　協調性',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 380],
            [
                'kbn' => 38,
                'code' => 4,
                'name' => '人の嫌がることでも率先して行っている',
                'order' => 3,
                'remarks' => 'kbn[38]：チームワーク　協調性',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 381],
            [
                'kbn' => 39,
                'code' => 1,
                'name' => '周囲の良い関係を作るよう積極的にコミュニケーションをとっている',
                'order' => 0,
                'remarks' => 'kbn[39]：コミュニケーション　異文化適応力',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 382],
            [
                'kbn' => 39,
                'code' => 2,
                'name' => '日本の社会文化を理解している（主張、言い訳、自己過大評価）',
                'order' => 1,
                'remarks' => 'kbn[39]：コミュニケーション　異文化適応力',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 383],
            [
                'kbn' => 39,
                'code' => 3,
                'name' => '日本語能力の向上に努めている（正しい敬語、電話対応が可能）',
                'order' => 2,
                'remarks' => 'kbn[39]：コミュニケーション　異文化適応力',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 384],
            [
                'kbn' => 40,
                'code' => 1,
                'name' => '会社理念や方針を理解し、それに合った行動をしている',
                'order' => 0,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 385],
            [
                'kbn' => 40,
                'code' => 2,
                'name' => '経費節約などコスト意識がある',
                'order' => 1,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 386],
            [
                'kbn' => 40,
                'code' => 3,
                'name' => '契約内容を十分に理解している',
                'order' => 2,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 387],
            [
                'kbn' => 40,
                'code' => 4,
                'name' => '正社員としての役割認識がある',
                'order' => 3,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 388],
            [
                'kbn' => 40,
                'code' => 5,
                'name' => '接客マイスターの仕事内容を理解している',
                'order' => 4,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 389],
            [
                'kbn' => 40,
                'code' => 6,
                'name' => '与えられた仕事に責任を持ち、一生懸命取り組んでいる',
                'order' => 5,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 390],
            [
                'kbn' => 40,
                'code' => 7,
                'name' => 'コンプライアンスを遵守している',
                'order' => 6,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);
        \DB::table('dispatch_code')->updateOrInsert(
            ['id' => 391],
            [
                'kbn' => 40,
                'code' => 8,
                'name' => 'キャリアに関して目標とビジョンを持ち、自己研鑽に努めている',
                'order' => 7,
                'remarks' => 'kbn[40]：組織運営への協力度　参画度、理解度',
                'create_user' => 'system',
                'update_user' => 'system',
            ]);

    }
}
