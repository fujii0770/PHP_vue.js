<?php

use Illuminate\Database\Seeder;

class bbs_auth_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('bbs_auth')->updateOrInsert(
            ['id' => 1],
            [
                'auth_code' => 1,
                'auth_content' => 'すべてのユーザが閲覧、返信可',
            ]);
        \DB::table('bbs_auth')->updateOrInsert(
            ['id' => 2],
            [
                'auth_code' => 2,
                'auth_content' => 'すべてのユーザが閲覧可、所属メンバーのみ返信可',
            ]);
        \DB::table('bbs_auth')->updateOrInsert(
            ['id' => 3],
            [
                'auth_code' => 3,
                'auth_content' => '所属メンバーのみ閲覧、返信可',
            ]);                
        \DB::table('bbs_auth')->updateOrInsert(
            ['id' => 4],
            [
                'auth_code' => 4,
                'auth_content' => '自分のみ閲覧、返信可',
            ]);                
    }
    
}      
