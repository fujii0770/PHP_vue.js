<?php

use Illuminate\Database\Seeder;

class mst_server_info_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('mst_server_info')->updateOrInsert(
            ['id' => 1],
            [
                'contract_app' => config('app.pac_contract_app'),
                'app_env' => config('app.pac_app_env'),
                'contract_server' => config('app.pac_contract_server'),
            ]);
    }
}
