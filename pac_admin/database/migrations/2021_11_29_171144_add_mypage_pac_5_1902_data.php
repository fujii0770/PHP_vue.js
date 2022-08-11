<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMypagePac51902Data extends Migration
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
        $total = DB::table('mypage')->count();
        $number = intval(ceil($total / $limit));

        for ($i = 0; $i < $number; $i++) {
            $myPages = DB::table('mypage')
                ->offset($limit * $i)
                ->limit($limit)
                ->get();

            foreach ($myPages as $myPage) {
                $layout = json_decode($myPage->layout);
                if (!isset($layout->show_special)) {
                    $layout->show_special = true;
                    if ($myPage->mst_mypage_layout_id == 1) {
                        if (!isset($layout->layout[0]->component['special'])) {
                            array_unshift($layout->layout[0]->component, 'special');
                        }
                    } elseif ($myPage->mst_mypage_layout_id == 2) {
                        if (!isset($layout->layout[1]->component['special'])) {
                            array_unshift($layout->layout[1]->component, 'special');
                        }
                    } elseif ($myPage->mst_mypage_layout_id == 3) {
                        if (!isset($layout->layout[0]->component['special'])) {
                            array_unshift($layout->layout[0]->component, 'special');
                        }
                    } elseif ($myPage->mst_mypage_layout_id == 4) {
                        if (!isset($layout->layout[1]->component['special'])) {
                            array_unshift($layout->layout[1]->component, 'special');
                        }
                    } else {
                        continue;
                    }
                    DB::table('mypage')->where('mst_user_id', '=', $myPage->mst_user_id)->update(['layout' => json_encode($layout)]);
                }
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
    }
}
