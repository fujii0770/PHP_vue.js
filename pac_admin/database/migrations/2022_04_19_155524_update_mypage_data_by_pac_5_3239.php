<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use  \Illuminate\Support\Facades\DB;

class UpdateMypageDataByPac53239 extends Migration
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
                    return $item['name'] === 'top_menu' && $item['static'] === false;
                }));
            });
            foreach ($enableList as $index => $myPage) {
                $layout = json_decode($myPage->layout);
                foreach ($layout as $component) {
                    if (in_array($component->name, ['top_menu', 'top_screen'])) {
                        $component->static = false;
                        $component->minW = 16;
                    }
                    if (!is_string($component->i)) {
                        $component->i = (string)$component->i;
                    }
                }
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
