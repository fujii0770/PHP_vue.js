<?php

use \Illuminate\Database\Seeder;

class app_role_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //初期値投入
        DB::table('app_role')->updateOrInsert(
            ['id' => 1],
            [
                'mst_application_id' => 1,
                'name' => "掲示板基本ロール",
                'memo' => "基本ロールの更新削除は不可",
                'is_default' => 1
            ]
        );
    }
}