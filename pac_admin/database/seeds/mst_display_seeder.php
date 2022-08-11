<?php

use Illuminate\Database\Seeder;

class mst_display_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 1],
            [
                'display_name' => 'ログイン',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 2],
            [
                'display_name' => '画面共通',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 3],
            [
                'display_name' => '利用状況',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 4],
            [
                'display_name' => '管理者操作履歴',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 5],
            [
                'display_name' => '利用者操作履歴',
                'role' => 0,
            ]);
//        \DB::table('mst_display')->updateOrInsert(
//            ['id' => 6],
//            [
//                'display_name' => '利用者API呼出履歴',
//                'role' => 0,
//            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 7],
            [
                'display_name' => 'ブランディング設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 8],
            [
                'display_name' => '管理者権限初期値設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 9],
            [
                'display_name' => 'パスワードポリシー設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 10],
            [
                'display_name' => '日付印設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 11],
            [
                'display_name' => '共通印設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 12],
            [
                'display_name' => '制限設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 13],
            [
                'display_name' => '管理者設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 14],
            [
                'display_name' => '利用者設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 15],
            [
                'display_name' => '共通印割当',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 16],
            [
                'display_name' => '共通アドレス帳',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 17],
            [
                'display_name' => '承認ルート',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 18],
            [
                'display_name' => '部署・役職',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 19],
            [
                'display_name' => '回覧一覧',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 20],
            [
                'display_name' => '保存文書一覧',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 21],
            [
                'display_name' => '接続IP制限設定',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 22],
            [
                'display_name' => '認証コード入力',
                'role' => 0,
            ]);

        \DB::table('mst_display')->updateOrInsert(
            ['id' => 28],
            [
                'display_name' => '勤務表一覧',
	                'role' => '0',
            ]);

        \DB::table('mst_display')->updateOrInsert(
            ['id' => 29],
            [
                'display_name' => '勤務詳細',
                'role' => '0',
            ]);


        \DB::table('mst_display')->updateOrInsert(
            ['id' => 30],
            [
                'display_name' => '勤務状況確認',
                'role' => '0',
            ]);

        \DB::table('mst_display')->updateOrInsert(
            ['id' => 31],
            [
                'display_name' => '利用ユーザ登録',
	            'role' => '0',
            ]);

        \DB::table('mst_display')->updateOrInsert(
            ['id' => 32],
            [
                'display_name' => '日報確認',
	            'role' => '0',
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 33],
            [
                'display_name' => '長期保管設定',
                'role' => '0',
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 34],
            [
                'display_name' => '長期保管一覧',
                'role' => '0',
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 35],
            [
                'display_name' => '監査用アカウント設定',
                'role' => '0',
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 36],
            [
                'display_name' => 'ダウンロード状況',
                'role' => 0,
            ]);


        \DB::table('mst_display')->updateOrInsert(
            ['id' => 50],
            [
                'display_name' => 'ログイン',
                'role' => 1,
            ]);
//        \DB::table('mst_display')->updateOrInsert(
//            ['id' => 51],
//            [
//                'display_name' => '捺印依頼メール',
//                'role' => 1,
//            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 52],
            [
                'display_name' => '画面共通',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 53],
            [
                'display_name' => '初期パスワードの通知メール',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 54],
            [
                'display_name' => 'パスワード設定画面',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 55],
            [
                'display_name' => 'パスワード設定メール有効期限外',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 56],
            [
                'display_name' => '新規作成',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 57],
            [
                'display_name' => '受信一覧',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 58],
            [
                'display_name' => '送信一覧',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 59],
            [
                'display_name' => '下書き一覧',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 60],
            [
                'display_name' => 'アドレス帳',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 61],
            [
                'display_name' => '設定',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 62],
            [
                'display_name' => '文書申請',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 63],
            [
                'display_name' => '回覧文書',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 64],
            [
                'display_name' => '回覧先設定',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 65],
            [
                'display_name' => '差戻し設定',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 66],
            [
                'display_name' => '認証コード入力',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 67],
            [
                'display_name' => 'ポータル画面',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 68],
            [
                'display_name' => 'ダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 69],
            [
                'display_name' => '捺印',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 70],
            [
                'display_name' => 'テキスト追加',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 71],
            [
                'display_name' => 'HR機能',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 72],
            [
                'display_name' => '長期保管',
                'role' => 1,
            ]);

        \DB::table('mst_display')->updateOrInsert(
            ['id' => 73],
            [
                'display_name' => 'ササッと明細',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 74],
            [
                'display_name' => 'ササッと明細',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 75],
            [
                'display_name' => '経費精算',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 76],
            [
                'display_name' => '経費精算',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 77],
            [
                'display_name' => '完了一覧',
                'role' => 1,
            ]);
        // PAC_5_2663
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 78],
            [
                    'display_name' => 'ササッとTalk',
                    'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 79],
            [
                'display_name' => 'ダウンロード状況',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 80],
            [
                'display_name' => '回覧完了テンプレート一覧',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 81],
            [
                'display_name' => '閲覧一覧',
                'role' => 1,
            ]);
        // 掲示板 Start
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 82],
            [
                'display_name' => '掲示板',
                'role' => 1,
            ]);
        // 掲示板 End
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 83],
            [
                'display_name' => 'サポート掲示板',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 84],
            [
                'display_name' => '特設サイト-文書登録',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 85],
            [
                'display_name' => '特設サイト-連携承認',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 86],
            [
                'display_name' => '特設サイト-連携申請',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 87],
            [
                'display_name' => 'with box',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
          ['id' => 88],
          [
              'display_name' => 'ユーザー勤務詳細',
              'role' => 0,
          ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 95],
            [
                'display_name' => 'ファイルメール便',
                'role' => 1,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 89],
            [
                'display_name' => '精算申請様式一覧',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 90],
            [
                'display_name' => '経費申請一覧',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 91],
            [
                'display_name' => '経費仕訳一覧',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 92],
            [
                'display_name' => '目的管理',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 93],
            [
                'display_name' => '用途管理',
                'role' => 0,
            ]);
        
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 94],
            [
                'display_name' => '勘定科目管理',
                'role' => 0,
            ]);
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 96],
            [
                'display_name' => '仕訳設定',
                'role' => 0,
            ]);

        \DB::table('mst_display')->updateOrInsert(
            ['id' => 97],
            [
                'display_name' => 'BOX自動保管',
                'role' => 0,
            ]);
            
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 99],
            [
                'display_name' => '事前申請様式一覧',
                'role' => 0,
            ]);

        
        \DB::table('mst_display')->updateOrInsert(
            ['id' => 98],
            [
                'display_name' => 'ToDoリスト',
                'role' => 1,
            ]);
    }
}
