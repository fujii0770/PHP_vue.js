<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeCardIntoMypageTable extends Migration
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
        $total = \DB::table('mypage')->count();
        $number = intval(ceil($total / $limit));

        for ($i = 0; $i < $number; $i++) {
            $myPages = \DB::table('mypage')
                ->offset($limit * $i)
                ->limit($limit)
                ->get();

            foreach ($myPages as $index => $myPage) {
                $layout = json_decode($myPage->layout);
                if (!isset($layout->show_time_card)) {
                    $layout->show_time_card = true;
                    if (!isset($layout->layout[0]->component['time_card'])) {
                        array_unshift($layout->layout[0]->component, 'time_card');
                    }
                    \DB::table('mypage')->where('mst_user_id', '=', $myPage->mst_user_id)->update(['layout' => json_encode($layout)]);
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
        //
    }
}
