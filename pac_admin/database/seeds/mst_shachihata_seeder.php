<?php

use Illuminate\Database\Seeder;

class mst_shachihata_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('mst_shachihata')->insertOrIgnore([
            'id' => 1,
            'name' => 'Shachihata',
            'email' => 'master-pro@shachihata.co.jp',
            'password' => '$2y$10$boHxReOoJ9Z3QSsggYAwlu82xaA/fM0WFgkXW3T.PCtf2jS5.bCzm',
            'remember_token' => 'KuNl3IrxYf1SbPxHKioN0GD4uSdaFF5HKuB50Dn2HyyU7BMZ2yl768laK47t',
            'email_auth_flg' => 0,
            'email_auth_dest_flg' => 0,
        ]);
    }
}
