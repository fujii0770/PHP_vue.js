<?php

use Illuminate\Database\Seeder;

class app_role_detail_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //初期値投入
        DB::table('app_role_detail')->updateOrInsert(
            ['id' => 1],
            [
                'app_role_id' => 1,
                'mst_access_privilege_id' => 1,
            ]
        );

        DB::table('app_role_detail')->updateOrInsert(
            ['id' => 2],
            [
                'app_role_id' => 1,
                'mst_access_privilege_id' => 2,
            ]
        );
    }
}
