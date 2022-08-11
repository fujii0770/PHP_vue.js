<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class mst_template_placeholder_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 1],
            [
                'special_template_placeholder' => '${now}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 2],
            [
                'special_template_placeholder' => '${today}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 3],
            [
                'special_template_placeholder' => '${user.name}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 4],
            [
                'special_template_placeholder' => '${user.email}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 5],
            [
                'special_template_placeholder' => '${user.company}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 6],
            [
                'special_template_placeholder' => '${user.department}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 7],
            [
                'special_template_placeholder' => '${user.position}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 8],
            [
                'special_template_placeholder' => '${user.phone}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 9],
            [
                'special_template_placeholder' => '${user.fax}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 10],
            [
                'special_template_placeholder' => '${user.address}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);
        DB::table('mst_template_placeholder')->updateOrInsert(
            ['id' => 11],
            [
                'special_template_placeholder' => '${user.postalCode}',
                'template_create_at' => '2021-03-02 20:08:50',
                'template_create_user' => 'admin'
            ]);


    }
}
