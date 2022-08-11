<?php

use Illuminate\Database\Seeder;

class mst_stamp_convenient_division_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 1],
            [
                'division_name' => '角印',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 2],
            [
                'division_name' => '評価印',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 3],
            [
                'division_name' => '枠印',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 4],
            [
                'division_name' => 'その他',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        // PAC_5-2332 S  便利印のジャンルを追加したい
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 5],
            [
                'division_name' => 'サンプル印',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 6],
            [
                'division_name' => '記号印',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        \DB::table('mst_stamp_convenient_division')->updateOrInsert(
            ['id' => 7],
            [
                'division_name' => 'ビジネス印',
                'del_flg' => 0,
                'create_at' => '2021-03-16 18:44:30',
                'create_user' => 'admin',
                'update_at' => '2021-03-16 18:44:30',
                'update_user' => 'admin',
            ]);
        // PAC_5-2332 E
    }
}
