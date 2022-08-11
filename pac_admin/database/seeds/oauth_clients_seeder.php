<?php

use Illuminate\Database\Seeder;

class oauth_clients_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('oauth_clients')->insertOrIgnore([
            'id' => 1,
            'name' => 'Laravel Personal Access Client',
            'secret' => 'CIAtlsBGvrXvIeWU63pzEaOd9kNu1EXl9V823COg',
            'redirect' => 'http://localhost',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
        ]);

        DB::table('oauth_clients')->insertOrIgnore([
            'id' => 2,
            'name' => 'Laravel Password Grant Client',
            'secret' => 'PavbMAx9xe0xRRVWxzA4dutRGWSgJhyvdEvZpBdt',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
        ]);
    }
}
