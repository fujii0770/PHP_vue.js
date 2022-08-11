<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMypageTableColumnLayout extends Migration
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

                // special
                if (!isset($layout->show_special)) {
                    $layout->show_special = true;
                    if ($myPage->mst_mypage_layout_id == 1) {
                        if (!in_array('special', $layout->layout[0]->component)) {
                            array_unshift($layout->layout[0]->component, 'special');
                        }
                    } elseif ($myPage->mst_mypage_layout_id == 2) {
                        if (!in_array('special', $layout->layout[1]->component)) {
                            array_unshift($layout->layout[1]->component, 'special');
                        }
                    } elseif ($myPage->mst_mypage_layout_id == 3) {
                        if (!in_array('special', $layout->layout[0]->component)) {
                            array_unshift($layout->layout[0]->component, 'special');
                        }
                    } elseif ($myPage->mst_mypage_layout_id == 4) {
                        if (!in_array('special', $layout->layout[1]->component)) {
                            array_unshift($layout->layout[1]->component, 'special');
                        }
                    } else {
                        continue;
                    }
                }

                // タイムカード
                if (!isset($layout->show_time_card)) {
                    $layout->show_time_card = true;
                    $addOrNot = true;
                    foreach($layout->layout as $item) {
                        // dd($item->component);
                        if(in_array('time_card', $item->component)) {
                            $addOrNot = false;
                        }
                    }
                    if($addOrNot) {
                        array_unshift($layout->layout[0]->component, 'time_card');
                    }
                    // \DB::table('mypage')->where('mst_user_id', '=', $myPage->mst_user_id)->update(['layout' => json_encode($layout)]);
                }

                // メール便
                if (!isset($layout->show_file_mail)) {
                    $layout->show_file_mail = true;
                    array_push($layout->layout, ['width' => 12, 'component' => ['file_mail']]);
                    // DB::table('mypage')->where('mst_user_id', '=', $myPage->mst_user_id)->update(['layout' => json_encode($layout)]);
                }

                DB::table('mypage')->where('mst_user_id', '=', $myPage->mst_user_id)->update(['layout' => json_encode($layout)]);
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
