<?php

use Illuminate\Database\Seeder;

class mst_app_function_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //初期値投入
        DB::table('mst_app_function')->updateOrInsert(
            ['id' => 1],
            [
                'function_code' => 1,
                'function_name' => "掲示板カテゴリ操作",
            ]
        );
        DB::table('mst_app_function')->updateOrInsert(
            ['id' => 2],
            [
                'function_code' => 2,
                'function_name' => "掲示板トピック操作",
            ]
        );
    }
}
