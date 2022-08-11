<?php

use Illuminate\Database\Seeder;

class roles_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('roles')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'shachihata_admin',
                'guard_name' => 'web',
            ]);
        \DB::table('roles')->updateOrInsert(
            ['id' => 2],
            [
                'name' => 'company_manager',
                'guard_name' => 'web',
            ]);
        \DB::table('roles')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'company_normal_admin',
                'guard_name' => 'web',
            ]);

    }
}
