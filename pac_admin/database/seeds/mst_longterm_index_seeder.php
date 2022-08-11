<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class mst_longterm_index_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        

        DB::table('mst_longterm_index')
            ->updateOrInsert(
            ['id' => 1],
            [
                'mst_company_id' => '0',
                'mst_user_id' => '0',
                'index_name' => '取引年月日',
                'data_type' => '2',
                'permission' => '0',
                'create_at' => Carbon::now(),
                'create_user' => 'admin',
                'sort_id' => 1,
            ]);

        DB::table('mst_longterm_index')
            ->updateOrInsert(
            ['id' => 2],
            [
                'mst_company_id' => '0',
                'mst_user_id' => '0',
                'index_name' => '金額',
                'data_type' => '0',
                'permission' => '0',
                'create_at' => Carbon::now(),
                'create_user' => 'admin',
                'sort_id' => 2,
            ]);

    }
}
