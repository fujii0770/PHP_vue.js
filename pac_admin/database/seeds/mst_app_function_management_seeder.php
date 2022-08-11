<?php

use Illuminate\Database\Seeder;

class mst_app_function_management_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //初期値投入
        DB::table('mst_app_function_management')->updateOrInsert(
            ['id' => 1],
            [
                'mst_application_id' => 1,
                'mst_app_function_id' => 1,
            ]
        );
        DB::table('mst_app_function_management')->updateOrInsert(
            ['id' => 2],
            [
                'mst_application_id' => 1,
                'mst_app_function_id' => 2,
            ]
        );
    }
}
