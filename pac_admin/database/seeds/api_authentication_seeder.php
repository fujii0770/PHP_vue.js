<?php

use Illuminate\Database\Seeder;

class api_authentication_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('api_authentication')->updateOrInsert(
            ['id' => 1],
            [
                'api_name' => 'HomePage',
                'access_id' => 'EdKQsRT7QgMc6tZXHYrA',
                'access_code' => 'IdGh74PgOv7D5C30kz0UtHn8tfbw1WwsuVGXwgOspHGst8DYtb',
            ]);
    }
}
