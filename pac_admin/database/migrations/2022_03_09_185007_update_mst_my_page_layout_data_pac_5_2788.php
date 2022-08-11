<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use  \Illuminate\Support\Facades\DB;

class UpdateMstMyPageLayoutDataPac52788 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ini_set('memory_limit', '1024m');
        $limit = 2000;
        $total = DB::table('mypage')
            ->where('layout', 'like', '%available%')->count();
        $number = intval(ceil($total / $limit));

        for ($i = 0; $i < $number; $i++) {
            $myPages = DB::table('mypage')
                ->where('layout', 'like', '%available%')
                ->offset($limit * $i)
                ->limit($limit)
                ->get();

            $enableList = $myPages->filter(function ($item) {
                $layout = json_decode($item->layout, true);
                return !(collect($layout)->some(function ($item) {
                    return $item['name'] == 'receive_plan';
                }));
            });
            foreach ($enableList as $index => $myPage) {
                $layout = json_decode($myPage->layout);
                $max_y = 0;
                foreach ($layout as $component) {
                    if ($max_y < ($component->y + $component->h)) {
                        $max_y = $component->y + $component->h;
                    }
                }
                $layout[] = (object)[
                    'x' => 0,
                    'y' => $max_y,
                    'w' => 24,
                    'h' => 28,
                    'i' => 15,
                    'name' => 'receive_plan',
                    'static' => false,
                    "minW" => 6,
                    "minH" => 28,
                    "maxW" => 32,
                    "maxH" => 50,
                    "show" => true,
                    "available" => true,
                    "resizing" => false,
                    "hasData" => true,
                    "moved" => false,
                ];
                \DB::table('mypage')->where('id', '=', $myPage->id)->update(['layout' => json_encode($layout)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
