<?php

use Illuminate\Database\Seeder;

class mst_access_privileges_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //初期値投入
        DB::table('mst_access_privileges')->updateOrInsert(
            ['id' => 1],
            [
                'mst_app_function_id' => 1,
                'privilege_code' => 1,
                'privilege_content' => "追加",
            ]
        );
        DB::table('mst_access_privileges')->updateOrInsert(
            ['id' => 2],
            [
                'mst_app_function_id' => 2,
                'privilege_code' => 1,
                'privilege_content' => "追加",
            ]
        );
    }
}
