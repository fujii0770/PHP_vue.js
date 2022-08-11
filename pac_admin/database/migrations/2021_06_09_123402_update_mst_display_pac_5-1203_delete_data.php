<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstDisplayPac51203DeleteData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ■削除系
        $ids = [
            6,    //利用者API呼出履歴
            51,    //捺印依頼メール
        ];

        DB::table('mst_display')
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 6],
            [
                'display_name' => '利用者API呼出履歴',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 51],
            [
                'display_name' => '捺印依頼メール',
                'role' => 1,
            ]);
    }
}
