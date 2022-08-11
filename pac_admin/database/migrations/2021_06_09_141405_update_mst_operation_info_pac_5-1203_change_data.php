<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstOperationInfoPac51203ChangeData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // operation_history 処理
        // ■まとめる系
        $change_opt_id_list = array();
        // 管理者操作履歴検索 -> 管理者操作履歴表示 (4,6)->(4,5)
        $change_opt_id_list[] = [
            'old_opt_id' => 6,
            'new_opt_id' => 5,
        ];
        // 利用者操作履歴検索 -> 利用者操作履歴表示 (5,8)->(5,7)
        $change_opt_id_list[] = [
            'old_opt_id' => 8,
            'new_opt_id' => 7,
        ];
        // 共通印検索 -> 共通印検索 (11,20)->(11,19)
        $change_opt_id_list[] = [
            'old_opt_id' => 20,
            'new_opt_id' => 19,
        ];
        // 利用者検索 -> 利用者設定表示 (14,32)->(14,31)
        $change_opt_id_list[] = [
            'old_opt_id' => 32,
            'new_opt_id' => 31,
        ];
        // 共通印割当 - 利用者検索 -> 共通印割当表示 (15,44)->(15,43)
        $change_opt_id_list[] = [
            'old_opt_id' => 44,
            'new_opt_id' => 43,
        ];
        // 共通アドレス帳検索 -> 共通アドレス帳表示 (16,48)->(16,47)
        $change_opt_id_list[] = [
            'old_opt_id' => 48,
            'new_opt_id' => 47,
        ];
        // 回覧一覧検索 -> 回覧一覧表示 (19,69)->(19,68)
        $change_opt_id_list[] = [
            'old_opt_id' => 69,
            'new_opt_id' => 68,
        ];
        // 回覧一覧 - 一括削除 -> 回覧一覧 - 削除 (19,71)->(19,70)
        $change_opt_id_list[] = [
            'old_opt_id' => 71,
            'new_opt_id' => 70,
        ];
        // 回覧一覧 - 一括ダウンロード -> 回覧一覧 - ダウンロード (19,73)->(19,72)
        $change_opt_id_list[] = [
            'old_opt_id' => 73,
            'new_opt_id' => 72,
        ];
        // 保存文書検索 -> 保存文書一覧画面を表示 (20,75)->(20,74)
        $change_opt_id_list[] = [
            'old_opt_id' => 75,
            'new_opt_id' => 74,
        ];
        // 保存文書一覧 - 一括削除 -> 保存文書一覧 - 削除 (20,77)->(20,76)
        $change_opt_id_list[] = [
            'old_opt_id' => 77,
            'new_opt_id' => 76,
        ];
        // 保存文書一覧 - ダウンロード -> 保存文書一覧 - 一括ダウンロード (20,79)->(20,78)
        $change_opt_id_list[] = [
            'old_opt_id' => 79,
            'new_opt_id' => 78,
        ];
        // 捺印依頼メールからログイン -> ログイン (51,101)->(50,100)
        $change_opt_id_list[] = [
            'old_opt_id' => 101,
            'new_opt_id' => 100,
            'new_dis_id' => 50,
        ];
        // 送信一覧 - 文書一括削除 -> 送信一覧 - 文書削除 (58,125)->(58,124)
        $change_opt_id_list[] = [
            'old_opt_id' => 125,
            'new_opt_id' => 124,
        ];
        // 下書き一覧 - 文書一括削除 -> 下書き一覧 - 文書削除 (59,130)->(59,129)
        $change_opt_id_list[] = [
            'old_opt_id' => 130,
            'new_opt_id' => 129,
        ];

        foreach ($change_opt_id_list as $change_opt_id){
            if(isset($change_opt_id['new_dis_id'])){
                DB::table('operation_history')
                    ->where('mst_operation_id', $change_opt_id['old_opt_id'])
                    ->update(['mst_display_id' => $change_opt_id['new_dis_id']]);

                for($i=0;$i<10;$i++) {
                    DB::table('operation_history'.$i)
                        ->where('mst_operation_id', $change_opt_id['old_opt_id'])
                        ->update(['mst_display_id' => $change_opt_id['new_dis_id']]);
                }
            }
            DB::table('operation_history')
                ->where('mst_operation_id', $change_opt_id['old_opt_id'])
                ->update(['mst_operation_id' => $change_opt_id['new_opt_id']]);

            for($i=0;$i<10;$i++) {
                DB::table('operation_history'.$i)
                    ->where('mst_operation_id', $change_opt_id['old_opt_id'])
                    ->update(['mst_operation_id' => $change_opt_id['new_opt_id']]);
            }

            DB::table('mst_operation_info')
                ->where('id', $change_opt_id['old_opt_id'])
                ->delete();
        }

        // operation_history
        // 新規作成 - ダウンロード (56,110)⇒(68,175)
        DB::table('operation_history')
            ->where('mst_display_id', 56)
            ->where('mst_operation_id', 110)
            ->update([
                'mst_display_id'   => 68,
                'mst_operation_id' => 175
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 56)
                ->where('mst_operation_id', 110)
                ->update([
                    'mst_display_id' => 68,
                    'mst_operation_id' => 175
                ]);
        }

        // 新規作成 - 捺印 (56,111)⇒(69,176)
        DB::table('operation_history')
            ->where('mst_display_id', 56)
            ->where('mst_operation_id', 111)
            ->update([
                'mst_display_id'   => 69,
                'mst_operation_id' => 176
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 56)
                ->where('mst_operation_id', 111)
                ->update([
                    'mst_display_id' => 69,
                    'mst_operation_id' => 176
                ]);
        }

        // 新規作成 - テキスト追加 (56,112)⇒(70,177)
        DB::table('operation_history')
            ->where('mst_display_id', 56)
            ->where('mst_operation_id', 112)
            ->update([
                'mst_display_id'   => 70,
                'mst_operation_id' => 177
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 56)
                ->where('mst_operation_id', 112)
                ->update([
                    'mst_display_id' => 70,
                    'mst_operation_id' => 177
                ]);
        }

        // 完了一覧 - ダウンロード (58,121)⇒(68,175)
        DB::table('operation_history')
            ->where('mst_display_id', 58)
            ->where('mst_operation_id', 121)
            ->update([
                'mst_display_id'   => 68,
                'mst_operation_id' => 175
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 58)
                ->where('mst_operation_id', 121)
                ->update([
                    'mst_display_id' => 68,
                    'mst_operation_id' => 175
                ]);
        }

        // 回覧文書 - ダウンロード (63,146)⇒(68,175)
        DB::table('operation_history')
            ->where('mst_display_id', 63)
            ->where('mst_operation_id', 146)
            ->update([
                'mst_display_id'   => 68,
                'mst_operation_id' => 175
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 63)
                ->where('mst_operation_id', 146)
                ->update([
                    'mst_display_id' => 68,
                    'mst_operation_id' => 175
                ]);
        }

        // 回覧文書 - 捺印 (63,147)⇒(69,176)
        DB::table('operation_history')
            ->where('mst_display_id', 63)
            ->where('mst_operation_id', 147)
            ->update([
                'mst_display_id'   => 69,
                'mst_operation_id' => 176
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 63)
                ->where('mst_operation_id', 147)
                ->update([
                    'mst_display_id' => 69,
                    'mst_operation_id' => 176
                ]);
        }

        // 回覧文書 - テキスト追加 (63,148)⇒(70,177)
        DB::table('operation_history')
            ->where('mst_display_id', 63)
            ->where('mst_operation_id', 148)
            ->update([
                'mst_display_id'   => 70,
                'mst_operation_id' => 177
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 63)
                ->where('mst_operation_id', 148)
                ->update([
                    'mst_display_id' => 70,
                    'mst_operation_id' => 177
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ■削除系
        $infos = [
            ['id'=>6,'info'=>'管理者操作履歴検索','role'=>0],
            ['id'=>8,'info'=>'利用者操作履歴検索','role'=>0],
            ['id'=>20,'info'=>'共通印検索','role'=>0],
            ['id'=>32,'info'=>'利用者検索','role'=>0],
            ['id'=>44,'info'=>'共通印割当 - 利用者検索','role'=>0],
            ['id'=>48,'info'=>'共通アドレス帳検索','role'=>0],
            ['id'=>69,'info'=>'回覧一覧検索','role'=>0],
            ['id'=>71,'info'=>'回覧一覧 - 一括削除','role'=>0],
            ['id'=>73,'info'=>'回覧一覧 - 一括ダウンロード','role'=>0],
            ['id'=>75,'info'=>'保存文書検索','role'=>0],
            ['id'=>77,'info'=>'保存文書一覧 - 一括削除','role'=>0],
            ['id'=>79,'info'=>'保存文書一覧 - ダウンロード','role'=>0],
            ['id'=>101,'info'=>'捺印依頼メールからログイン','role'=>1],
            ['id'=>125,'info'=>'送信一覧 - 文書一括削除','role'=>1],
            ['id'=>130,'info'=>'下書き一覧 - 文書一括削除','role'=>1],
        ];

        DB::table('mst_operation_info')->insert($infos);


        // operation_history
        // 新規作成 - ダウンロード (56,110)⇒(68,175)
        DB::table('operation_history')
            ->where('mst_display_id', 68)
            ->where('mst_operation_id', 175)
            ->update([
                'mst_display_id'   => 56,
                'mst_operation_id' => 110
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 68)
                ->where('mst_operation_id', 175)
                ->update([
                    'mst_display_id' => 56,
                    'mst_operation_id' => 110
                ]);
        }

        // 新規作成 - 捺印 (56,111)⇒(69,176)
        DB::table('operation_history')
            ->where('mst_display_id', 69)
            ->where('mst_operation_id', 176)
            ->update([
                'mst_display_id'   => 56,
                'mst_operation_id' => 111
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 69)
                ->where('mst_operation_id', 176)
                ->update([
                    'mst_display_id' => 56,
                    'mst_operation_id' => 111
                ]);
        }

        // 新規作成 - テキスト追加 (56,112)⇒(70,177)
        DB::table('operation_history')
            ->where('mst_display_id', 70)
            ->where('mst_operation_id', 177)
            ->update([
                'mst_display_id'   => 56,
                'mst_operation_id' => 112
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 70)
                ->where('mst_operation_id', 177)
                ->update([
                    'mst_display_id' => 56,
                    'mst_operation_id' => 112
                ]);
        }

        // 完了一覧 - ダウンロード (58,121)⇒(68,175)
        DB::table('operation_history')
            ->where('mst_display_id', 68)
            ->where('mst_operation_id', 175)
            ->update([
                'mst_display_id'   => 58,
                'mst_operation_id' => 121
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 68)
                ->where('mst_operation_id', 175)
                ->update([
                    'mst_display_id' => 58,
                    'mst_operation_id' => 121
                ]);
        }

        // 回覧文書 - ダウンロード (63,146)⇒(68,175)
        DB::table('operation_history')
            ->where('mst_display_id', 68)
            ->where('mst_operation_id', 175)
            ->update([
                'mst_display_id'   => 63,
                'mst_operation_id' => 146
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 68)
                ->where('mst_operation_id', 175)
                ->update([
                    'mst_display_id' => 63,
                    'mst_operation_id' => 146
                ]);
        }

        // 回覧文書 - 捺印 (63,147)⇒(69,176)
        DB::table('operation_history')
            ->where('mst_display_id', 69)
            ->where('mst_operation_id', 176)
            ->update([
                'mst_display_id'   => 63,
                'mst_operation_id' => 147
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 69)
                ->where('mst_operation_id', 176)
                ->update([
                    'mst_display_id' => 63,
                    'mst_operation_id' => 147
                ]);
        }

        // 回覧文書 - テキスト追加 (63,148)⇒(70,177)
        DB::table('operation_history')
            ->where('mst_display_id', 70)
            ->where('mst_operation_id', 177)
            ->update([
                'mst_display_id'   => 63,
                'mst_operation_id' => 148
            ]);
        for($i=0;$i<10;$i++) {
            DB::table('operation_history'.$i)
                ->where('mst_display_id', 70)
                ->where('mst_operation_id', 177)
                ->update([
                    'mst_display_id' => 63,
                    'mst_operation_id' => 148
                ]);
        }
    }
}
