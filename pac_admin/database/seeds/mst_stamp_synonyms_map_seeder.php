<?php

use Illuminate\Database\Seeder;

class mst_stamp_synonyms_map_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('mst_stamp_synonyms_map')->updateOrInsert(
            ['id' => 1],
            [
                'origin' => '吉田',
                'synonym' => '吉田',
            ]);
        DB::table('mst_stamp_synonyms_map')->updateOrInsert(
            ['id' => 2],
            [
                'origin' => '吉田',
                'synonym' => '（土）吉田',
            ]);
        DB::table('mst_stamp_synonyms_map')->updateOrInsert(
            ['id' => 3],
            [
                'origin' => '吉川',
                'synonym' => '吉川',
            ]);
        DB::table('mst_stamp_synonyms_map')->updateOrInsert(
            ['id' => 4],
            [
                'origin' => '吉川',
                'synonym' => '（土）吉川',
            ]);
        DB::table('mst_stamp_synonyms_map')->updateOrInsert(
            ['id' => 5],
            [
                'origin' => '吉村',
                'synonym' => '吉村',
            ]);
        DB::table('mst_stamp_synonyms_map')->updateOrInsert(
            ['id' => 6],
            [
                'origin' => '吉村',
                'synonym' => '（土）吉村',
            ]);
    }
}
