<?php

use Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\DB;

class permissions_display_no_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $sort_arr = [
            [
                'ids' => [8, 9],
                'display_no' => 966
            ],
            [
                'ids' => [10, 11],
                'display_no' => 967
            ],
            [
                'ids' => [12, 13],
                'display_no' => 968
            ],
            [
                'ids' => [15, 14],
                'display_no' => 969
            ],
            [
                'ids' => [19, 18, 16],
                'display_no' => 970
            ],
            [
                'ids' => [20, 21],
                'display_no' => 971
            ],
            [
                'ids' => [55, 56],
                'display_no' => 972
            ],
            [
                'ids' => [46, 68, 45, 67],
                'display_no' => 973
            ],
            [
                'ids' => [47, 48, 59, 60, 115, 116, 117, 118],
                'display_no' => 974
            ],
            [
                'ids' => [61, 62],
                'display_no' => 973
            ],
            [
                'ids' => [69, 70],
                'display_no' => 975
            ],
            [
                'ids' => [22, 23, 24, 25],
                'display_no' => 976
            ],
            [
                'ids' => [107, 108, 109, 110],
                'display_no' => 977
            ],
            [
                'ids' => [115, 116, 117, 118],
                'display_no' => 978
            ],
            [
                'ids' => [191, 192, 193, 194],
                'display_no' => 979
            ],
            [
                'ids' => [152, 153, 154, 155],
                'display_no' => 977
            ],
            [
                'ids' => [26, 27],
                'display_no' => 978
            ],
            [
                'ids' => [28, 29, 30, 31],
                'display_no' => 979
            ],
            [
                'ids' => [35, 36, 37, 38],
                'display_no' => 980
            ],
            [
                'ids' => [86, 87, 88],
                'display_no' => 981
            ],
            [
                'ids' => [97, 98, 99, 100],
                'display_no' => 982
            ],
            [
                'ids' => [101, 102, 103, 104],
                'display_no' => 983
            ],
            [
                'ids' => [156, 157, 158],
                'display_no' => 984
            ],
            [
                'ids' => [89, 90, 91, 92],
                'display_no' => 984
            ],
            [
                'ids' => [71, 72, 74, 76],
                'display_no' => 985
            ],
            [
                'ids' => [77, 78, 79, 80],
                'display_no' => 986
            ],
            [
                'ids' => [73, 75],
                'display_no' => 987
            ],
            [
                'ids' => [119, 120, 121, 122],
                'display_no' => 988
            ],
            [
                'ids' => [123, 124, 125, 126],
                'display_no' => 989
            ],
            [
                'ids' => [81, 82, 83, 84],
                'display_no' => 990
            ],
            [
                'ids' => [191, 192, 193, 194],
                'display_no' => 988
            ],
        ];
        DB::beginTransaction();
        foreach ($sort_arr as $arr) {
            DB::table('permissions')
                ->whereIn('id', $arr['ids'])
                ->update([
                    'display_no' => $arr['display_no']
                ]);
        }
        DB::commit();
    }
}
