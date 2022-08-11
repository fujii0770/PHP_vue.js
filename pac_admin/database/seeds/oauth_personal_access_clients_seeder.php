<?php

use Illuminate\Database\Seeder;

class oauth_personal_access_clients_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('oauth_personal_access_clients')->insertOrIgnore([
            'id' => 1,
            'client_id' => 1,
        ]);
    }
}
