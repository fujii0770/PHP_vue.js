<?php

use Illuminate\Database\Seeder;

class mst_operation_info_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 1],
            [
                'info' => 'ログイン',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 2],
            [
                'info' => 'ログアウト',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 3],
            [
                'info' => '利用状況表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 4],
//            [
//                'info' => '利用状況検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 5],
            [
                'info' => '管理者操作履歴表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 6],
//            [
//                'info' => '管理者操作履歴検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 7],
            [
                'info' => '利用者操作履歴表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 8],
//            [
//                'info' => '利用者操作履歴検索',
//                'role' => 0,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 9],
//            [
//                'info' => '利用者API呼出履歴画面を表示',
//                'role' => 0,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 10],
//            [
//                'info' => '利用者API呼出履歴検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 11],
            [
                'info' => 'ブランディング設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 12],
            [
                'info' => 'ブランディング設定更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 13],
            [
                'info' => '管理者権限初期値設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 14],
            [
                'info' => '管理者権限初期値設定更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 15],
            [
                'info' => 'パスワードポリシー設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 16],
            [
                'info' => 'パスワードポリシー設定更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 17],
            [
                'info' => '日付印設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 18],
            [
                'info' => '日付印設定更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 19],
            [
                'info' => '共通印設定表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 20],
//            [
//                'info' => '共通印検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 21],
            [
                'info' => '共通印名称更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 22],
            [
                'info' => '共通印削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 23],
            [
                'info' => '共通印申請書ダウンロード',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 24],
            [
                'info' => '制限設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 25],
            [
                'info' => '制限設定更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 26],
            [
                'info' => '管理者設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 27],
            [
                'info' => '管理者登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 28],
            [
                'info' => '管理者更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 29],
            [
                'info' => '管理者初期パスワード設定',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 30],
            [
                'info' => '管理者権限更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 31],
            [
                'info' => '利用者設定表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 32],
//            [
//                'info' => '利用者検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 33],
            [
                'info' => '利用者登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 34],
            [
                'info' => '利用者更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 35],
            [
                'info' => '利用者削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 36],
            [
                'info' => '利用者初期パスワード設定',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 37],
            [
                'info' => '利用者設定 - CSV取込',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 38],
            [
                'info' => '利用者設定 - CSVダウンロード',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 39],
            [
                'info' => '利用者設定 - 印面検索',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 40],
            [
                'info' => '利用者設定 - 印面割当',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 41],
            [
                'info' => '利用者設定 - 部署名入り日付印割当',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 42],
            [
                'info' => '利用者設定 - 印面削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 43],
            [
                'info' => '共通印割当表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 44],
//            [
//                'info' => '共通印割当 - 利用者検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 45],
            [
                'info' => '共通印割当',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 46],
            [
                'info' => '共通印割当解除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 47],
            [
                'info' => '共通アドレス帳表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 48],
//            [
//                'info' => '共通アドレス帳検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 49],
            [
                'info' => '共通アドレス帳登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 50],
            [
                'info' => '共通アドレス帳更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 51],
            [
                'info' => '共通アドレス帳削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 52],
            [
                'info' => '共通アドレス帳 - CSV取込',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 53],
//            [
//                'info' => '共通アドレス帳 - CSVダウンロード',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 54],
            [
                'info' => '承認ルート画面を表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 55],
            [
                'info' => '承認ルート登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 56],
            [
                'info' => '承認ルート更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 57],
            [
                'info' => '承認ルート削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 58],
            [
                'info' => '部署・役職表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 59],
            [
                'info' => '部署登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 60],
            [
                'info' => '部署名称変更',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 61],
            [
                'info' => '部署削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 62],
            [
                'info' => '役職登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 63],
            [
                'info' => '役職名称変更',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 64],
            [
                'info' => '役職削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 65],
            [
                'info' => '部署 - CSV出力リクエスト',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 66],
            [
                'info' => '部署 - CSVダウンロード',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 67],
            [
                'info' => '部署 - CSVファイル削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 68],
            [
                'info' => '回覧一覧表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 69],
//            [
//                'info' => '回覧一覧検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 70],
            [
                'info' => '回覧一覧 - 削除',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 71],
//            [
//                'info' => '回覧一覧 - 一括削除',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 72],
            [
                'info' => '回覧一覧 - ダウンロード',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 73],
//            [
//                'info' => '回覧一覧 - 一括ダウンロード',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 74],
            [
                'info' => '保存文書一覧表示',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 75],
//            [
//                'info' => '保存文書検索',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 76],
            [
                'info' => '保存文書一覧 - 削除',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 77],
//            [
//                'info' => '保存文書一覧 - 一括削除',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 78],
            [
                'info' => '保存文書一覧 - ダウンロード',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 79],
//            [
//                'info' => '保存文書一覧 - 一括ダウンロード',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 80],
            [
                'info' => '接続IP制限設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 81],
            [
                'info' => '接続IP制限設定更新',
                'role' => 0,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 82],
//            [
//                'info' => '認証コード入力画面を表示',
//                'role' => 0,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 83],
            [
                'info' => '認証コードによる認証',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 84],
            [
                'info' => '認証メール再送信',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 85],
            [
                'info' => '長期保管設定表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 86],
            [
                'info' => '長期保管一覧 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 87],
            [
                'info' => '長期保管一覧 - ダウンロード',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 88],
            [
                'info' => '長期保管一覧 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 89],
            [
                'info' => '監査用アカウント設定 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 90],
            [
                'info' => '監査用アカウント設定 - アカウント登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 91],
            [
                'info' => '監査用アカウント更新 - アカウント更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 92],
            [
                'info' => '監査用アカウント設定 - アカウント削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 93],
            [
                'info' => 'ダウンロード状況 -  ダウンロード',
                'role' => 0,
            ]);


        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 100],
            [
                'info' => 'ログイン',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 101],
//            [
//                'info' => '捺印依頼メールからログイン',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 102],
            [
                'info' => 'ログアウト',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 103],
//            [
//                'info' => 'パスワード設定画面を表示',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 104],
            [
                'info' => 'パスワード変更',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 105],
//            [
//                'info' => 'パスワード設定メール有効期限外',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 106],
            [
                'info' => 'パスワード設定メール再送',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 107],
            [
                'info' => '新規作成画面を表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 108],
            [
                'info' => 'アップロード',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 109],
//            [
//                'info' => '新規作成 - 印面一覧表示',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 110],
//            [
//                'info' => '新規作成 - ダウンロード',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 111],
//            [
//                'info' => '新規作成 - 捺印',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 112],
//            [
//                'info' => '新規作成 - テキスト追加',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 113],
//            [
//                'info' => '新規作成 - 印面並び順変更',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 114],
            [
                'info' => '文書一時保存',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 115],
            [
                'info' => '受信一覧表示',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 116],
//            [
//                'info' => '受信一覧 - 検索',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 117],
//            [
//                'info' => '受信一覧 - 詳細表示',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 118],
            [
                'info' => '送信一覧表示',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 119],
//            [
//                'info' => '送信一覧 - 検索',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 120],
//            [
//                'info' => '送信一覧 - 詳細表示',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 121],
//            [
//                'info' => '完了一覧 - ダウンロード',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 122],
            [
                'info' => '引戻し',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 123],
            [
                'info' => '再通知',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 124],
            [
                'info' => '送信一覧 - 文書削除',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 125],
//            [
//                'info' => '送信一覧 - 文書一括削除',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 126],
            [
                'info' => '下書き一覧表示',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 127],
//            [
//                'info' => '下書き一覧 - 検索',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 128],
            [
                'info' => '下書き一覧 - 再開',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 129],
            [
                'info' => '下書き一覧 - 文書削除',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 130],
//            [
//                'info' => '下書き一覧 - 文書一括削除',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 131],
            [
                'info' => 'アドレス帳表示',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 132],
//            [
//                'info' => 'アドレス帳 - 検索',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 133],
            [
                'info' => 'アドレス帳 - 新規登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 134],
            [
                'info' => 'アドレス帳 - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 135],
            [
                'info' => 'アドレス帳 - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 136],
            [
                'info' => '設定表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 137],
            [
                'info' => '設定 - 表示設定更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 138],
            [
                'info' => '設定 - コメント設定更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 139],
            [
                'info' => '設定 - メール受信設定更新',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 140],
//            [
//                'info' => '設定 - パスワード変更',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 141],
            [
                'info' => '文書申請画面を表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 142],
            [
                'info' => '文書申請 - アドレス帳表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 143],
            [
                'info' => '回覧文書申請',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 144],
            [
                'info' => '回覧文書画面を表示',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 145],
//            [
//                'info' => '回覧文書 - 印面一覧表示',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 146],
//            [
//                'info' => '回覧文書 - ダウンロード',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 147],
//            [
//                'info' => '回覧文書 - 捺印',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 148],
//            [
//                'info' => '回覧文書 - テキスト追加',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 149],
//            [
//                'info' => '回覧文書 - 印面並び順変更',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 150],
            [
                'info' => '回覧先設定画面を表示',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 151],
//            [
//                'info' => '回覧先設定 - アドレス帳表示',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 152],
            [
                'info' => '回覧文書承認',
                'role' => 1,
            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 153],
//            [
//                'info' => '回覧文書承認 - 回覧完了',
//                'role' => 1,
//            ]);
//        \DB::table('mst_operation_info')->updateOrInsert(
//            ['id' => 154],
//            [
//                'info' => '差戻し設定画面を表示',
//                'role' => 1,
//            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 155],
            [
                'info' => '差戻し',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 156],
            [
                'info' => '認証コードによる認証',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 157],
            [
                'info' => '認証メール再送信',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 158],
            [
                'info' => 'ポータル画面を表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 159],
            [
                'info' => 'マイページ - 設定表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 160],
            [
                'info' => 'マイページ - 設定保存',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 161],
            [
                'info' => 'お気に入り - 登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 162],
            [
                'info' => 'お気に入り - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 163],
            [
                'info' => 'お気に入り - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 164],
            [
                'info' => 'シヤチハタクラウド - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 165],
            [
                'info' => '掲示板 - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 166],
            [
                'info' => 'スケジューラ - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 167],
            [
                'info' => '掲示板（単独） - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 168],
            [
                'info' => 'スケジューラ（単独）- 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 169],
            [
                'info' => 'お知らせ - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 170],
            [
                'info' => 'お知らせ - すべて既読',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 171],
            [
                'info' => '個人設定',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 172],
            [
                'info' => 'マイグループ設定',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 173],
            [
                'info' => '通知設定',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 174],
            [
                'info' => '設定 - プロファイル画像変更',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 175],
            [
                'info' => 'ダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 176],
            [
                'info' => '捺印',
                'role' => 1,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 177],
            [
                'info' => 'テキスト追加',
                'role' => 1,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 178],
            [
                'info' => 'タイムカード -  有給登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 179],
            [
                'info' => 'タイムカード -  有給（半休）登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 180],
            [
                'info' => 'タイムカード -  特休登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 181],
            [
                'info' => 'タイムカード -  特休（半休）登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 182],
            [
                'info' => 'タイムカード -  代休登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 183],
            [
                'info' => 'タイムカード -  代休（半休）登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 184],
            [
                'info' => '日報 - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 185],
            [
                'info' => '日報 - 検索（日付変更）',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 186],
            [
                'info' => '日報 - 登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 187],
            [
                'info' => '勤務一覧 - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 188],
            [
                'info' => '勤務一覧 - 検索',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 189],
            [
                'info' => '勤務詳細 - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 190],
            [
                'info' => '勤務詳細 - 検索（月指定時）',
                'role' => 1,
            ]);
         \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 191],
            [
                'info' => '勤務詳細 - 勤務表回覧',
                'role' => 1,
            ]);
         \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 192],
            [
                'info' => '勤務詳細 - 勤務情報CSV出力',
                'role' => 1,
            ]);
         \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 193],
            [
                'info' => '勤務詳細 - 提出',
                'role' => 1,
            ]);
         \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 194],
            [
                'info' => '勤務情報 - CSV出力',
                'role' => 1,
            ]);
         \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 195],
            [
                'info' => '勤務情報 -更新　（勤務編集ダイアログ）',
                'role' => 1,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 196],
            [
                'info' => 'タイムカード - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 197],
            [
                'info' => 'タイムカード -  出勤時刻登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 198],
            [
                'info' => 'タイムカード -  退勤時刻登録',
                'role' => 1,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 217],
            [
                'info' => '勤務表一覧を表示',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 218],
            [
                'info' => '勤務情報 - CSV出力',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 219],
            [
                'info' => '勤務表一覧 - 一括承認',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 220],
            [
                'info' => '勤務詳細を表示',
                'role' => '0',
            ]);


        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 221],
            [
                'info' => '勤務詳細 - 更新',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 222],
            [
                'info' => '勤務詳細 - 一括承認',
	            'role' => '0',
            ]);


        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 223],
            [
                'info' => '勤務確認を表示',
                'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 224],
            [
                'info' => '勤務確認 - 更新',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 225],
            [
                'info' => '勤務確認 - 一括承認',
	            'role' => '0',
            ]);


        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 226],
            [
                'info' => '利用ユーザ登録を表示',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 227],
            [
                'info' => '利用ユーザ登録 - 登録',
	            'role' => '0',
            ]);



        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 228],
            [
                'info' => '利用ユーザ登録 - 更新',
	            'role' => '0',
            ]);



        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 229],
            [
                'info' => '利用ユーザ登録 - 一括利用登録',
                'role' => '0',
            ]);



        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 230],
            [
                'info' => '日報確認を表示',
	            'role' => '0',
            ]);


        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 231],
            [
                'info' => '日報確認 - 更新',
	            'role' => '0',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 232],
            [
                'info' => '文書の長期保管',
                'role' => '1',
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 233],
            [
                'info' => '長期保管文書の削除',
                'role' => '1',
            ]);


        // mst_operation_info ID range 300 - 399 for feature form_issuance
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 300],
            [
                'info' => '明細テンプレート一覧 - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 301],
            [
                'info' => '明細テンプレート一覧 - 検索',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 302],
            [
                'info' => '明細テンプレートの登録 -  登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 303],
            [
                'info' => '明細テンプレート設定 -  表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 304],
            [
                'info' => '明細テンプレート設定 -  登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 305],
            [
                'info' => '明細の作成 -  表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 306],
            [
                'info' => '明細の作成 -  明細作成（アップロード）',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 307],
            [
                'info' => '明細一覧 -  表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 308],
            [
                'info' => '利用ユーザ登録 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 309],
            [
                'info' => '利用ユーザ登録 - 検索',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 310],
            [
                'info' => '利用ユーザ登録 - 一括利用登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 311],
            [
                'info' => '利用ユーザ登録 - 一括利用削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 312],
            [
                'info' => '利用ユーザ登録詳細 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 313],
            [
                'info' => '利用ユーザ登録詳細 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 314],
            [
                'info' => '利用ユーザ登録詳細 - 更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 315],
            [
                'info' => '明細テンプレート一覧 - 有効化',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 316],
            [
                'info' => '明細テンプレート一覧 - 無効化',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 317],
            [
                'info' => '明細テンプレート一覧 - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 318],
            [
                'info' => '明細テンプレート一覧 - ダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 319],
            [
                'info' => '明細テンプレート一覧 - インポート元ファイルダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 320],
            [
                'info' => '明細テンプレート一覧 - インポート元ファイルダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 321],
            [
                'info' => '明細インポート - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 322],
            [
                'info' => '明細インポート - インポート',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 323],
            [
                'info' => '明細インポート - ダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 324],
            [
                'info' => '明細Expテンプレート - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 325],
            [
                'info' => '明細Expテンプレート - 検索',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 326],
            [
                'info' => '明細Expテンプレート - 登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 327],
            [
                'info' => '明細Expテンプレート - ダウンロード',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 328],
            [
                'info' => '明細Expテンプレート - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 330],
            [
                'info' => '利用ユーザ登録 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 332],
            [
                'info' => '利用ユーザ登録 - 一括利用登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 333],
            [
                'info' => '利用ユーザ登録 - 一括利用解除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 334],
            [
                'info' => '利用ユーザ登録詳細 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 335],
            [
                'info' => '利用ユーザ登録詳細 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 336],
            [
                'info' => '利用ユーザ登録詳細 - 更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 337],
            [
                'info' => '目的管理 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 339],
            [
                'info' => '目的管理 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 340],
            [
                'info' => '目的管理 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 341],
            [
                'info' => '目的管理 - 修正',
                'role' => 0,
            ]);
        // PAC_5-2284 START
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 342],
            [
                'info' => '完了一覧 - 文書削除',
                'role' => 1,
            ]);
        // PAC_5-2284 END
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 343],
            [
                'info' => 'ファイルメール便（単独）- 表示',
                'role' => 1,
            ]);
        //START PAC_5-2562
        // PAC_5_2663
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 345],
            [
                'info' => 'ササッとTalk利用者設定-表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 346],
            [
                'info' => 'ササッとTalk利用者設定-検索',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 347],
            [
                'info' => 'ササッとTalk利用者設定-一括登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 348],
            [
                'info' => 'ササッとTalk利用者設定-一括削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 349],
            [
                'info' => 'ササッとTalk利用者設定-一括停止',
                'role' => 0,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 350],
            [
                'info' => 'ササッとTalk利用者設定-一括停止解除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 351],
            [
                'info' => 'ササッとTalk利用者設定-詳細ダイアログ表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 352],
            [
                    'info' => 'ササッとTalk利用者設定-詳細ダイアログ登録',
                    'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 353],
            [
                    'info' => 'ササッとTalk利用者設定-詳細ダイアログ更新',
                    'role' => 0,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 354],
            [
                    'info' => 'ササッとTalk利用者設定-詳細ダイアログ停止',
                    'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 355],
            [
                    'info' => 'ササッとTalk利用者設定-詳細ダイアログ停止解除',
                    'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 356],
            [
                'info' => 'ササッとTalk利用者設定-詳細ダイアログ削除',
                'role' => 0,
            ]);
        //END PAC_5-2562
        // PAC_5-2527 利用者が一括ダウンロードしたときの操作履歴を取りたい
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 357],
            [
                'info' => '完了一覧 -  ダウンロード予約',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 358],
            [
                'info' => '長期保管 -  ダウンロード予約',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 359],
            [
                'info' => '明細一覧 -  ダウンロード予約',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 360],
            [
                'info' => '閲覧一覧 -  ダウンロード予約',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 361],
            [
                'info' => '回覧完了テンプレート一覧 -  ダウンロード予約',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 362],
            [
                'info' => 'ダウンロード状況 -  ダウンロード',
                'role' => 1,
            ]);
        // PAC_5-1824 掲示板 363 ~ 374 Start
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 363],
            [
                'info' => '掲示板カテゴリ -  追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 364],
            [
                'info' => '掲示板カテゴリ -  更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 365],
            [
                'info' => '掲示板カテゴリ -  削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 366],
            [
                'info' => '掲示板の投稿 -  検索',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 367],
            [
                'info' => '掲示板の投稿 - 投稿追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 368],
            [
                'info' => '掲示板の投稿 -  編集',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 369],
            [
                'info' => '掲示板の投稿 -  削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 370],
            [
                'info' => '掲示板のコメント -  追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 371],
            [
                'info' => '掲示板のコメント -  編集',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 372],
            [
                'info' => '掲示板のコメント -  削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 373],
            [
                'info' => '掲示板の投稿 - 下書保存',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 374],
            [
                'info' => '掲示板の下書一覧 - 削除',
                'role' => 1,
            ]);
        // 掲示板 End




        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 381],
            [
                'info' => 'サポート掲示板 - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 382],
            [
                'info' => 'サポート掲示板（単独） - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 412],
            [
                'info' => '用途管理 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 413],
            [
                'info' => '用途管理 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 414],
            [
                'info' => '用途管理 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 415],
            [
                'info' => '用途管理 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 416],
            [
                'info' => '勘定科目管理 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 417],
            [
                'info' => '勘定科目管理 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 418],
            [
                'info' => '勘定科目管理 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 419],
            [
                'info' => '勘定科目管理 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 420],
            [
                'info' => '仕訳設定 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 421],
            [
                'info' => '仕訳設定 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 422],
            [
                'info' => '仕訳設定 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 423],
            [
                'info' => '仕訳設定 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 424],
            [
                'info' => '事前申請様式一覧 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 425],
            [
                'info' => '事前申請様式一覧 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 426],
            [
                'info' => '事前申請様式一覧 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 427],
            [
                'info' => '事前申請様式一覧 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 428],
            [
                'info' => '精算申請様式一覧 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 429],
            [
                'info' => '精算申請様式一覧 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 430],
            [
                'info' => '精算申請様式一覧 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 431],
            [
                'info' => '精算申請様式一覧 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 432],
            [
                'info' => '経費申請一覧 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 433],
            [
                'info' => '経費申請一覧 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 434],
            [
                'info' => '経費申請一覧 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 435],
            [
                'info' => '経費申請一覧 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 436],
            [
                'info' => '経費申請一覧 - 添付ファイルダウンロード予約',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 437],
            [
                'info' => '経費仕訳一覧 - 表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 438],
            [
                'info' => '経費仕訳一覧 - 削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 439],
            [
                'info' => '経費仕訳一覧 - 登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 440],
            [
                'info' => '経費仕訳一覧 - 修正',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 441],
            [
                'info' => '経費仕訳一覧 - 添付ファイルダウンロード予約',
                'role' => 0,
            ]);
        // mst_operation_info ID range 400 - 499 for feature ....

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 383],
            [
                'info' => '特設サイト -  文書登録画面表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 384],
            [
                'info' => '特設サイト -  文書登録',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 385],
            [
                'info' => '特設サイト -  文書更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 386],
            [
                'info' => '特設サイト -  文書削除',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 387],
            [
                'info' => '特設サイト -  連携承認表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 388],
            [
                'info' => '特設サイト -  連携承認更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 389],
            [
                'info' => '特設サイト -  連携申請表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 390],
            [
                'info' => '特設サイト -  連携申請更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 400],
            [
                'info' => '勤務状況確認 -  表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 391],
            [
                'info' => '長期保管インデックスを登録',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 401],
            [
                'info' => '受信専用プラン - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 402],
            [
                'info' => '受信専用プラン（単独） - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 403],
            [
                'info' => 'Todoリスト - 非表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 404],
            [
                'info' => 'Todoリスト（単独）- 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 405],
            [
                'info' => 'Todoリストの個人タスク - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 406],
            [
                'info' => 'Todoリストの個人タスク - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 407],
            [
                'info' => 'Todoリストの個人タスク - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 408],
            [
                'info' => 'Todoリストの共有タスク - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 409],
            [
                'info' => 'Todoリストの共有タスク - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 410],
            [
                'info' => 'Todoリストの共有タスク - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 411],
            [
                'info' => 'Todoリストの受信一覧タスク - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 500],
            [
                'info' => 'ログイン(box捺印)',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 501],
            [
                'info' => 'ログアウト(box捺印)',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 502],
            [
                'info' => '送信(box捺印)',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 503],
            [
                'info' => '保存(box捺印)',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
          ['id' => 412],
          [
              'info' => '勤務表一覧 - 表示',
              'role' => 1,
          ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 413],
            [
                'info' => '勤務表一覧 - 検索',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 414],
            [
                'info' => '勤務表一覧 - 一括承認',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 415],
            [
                'info' => '勤務表一覧 - 詳細表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 416],
            [
                'info' => 'ユーザ勤務詳細 - 表示',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 417],
            [
                'info' => 'ユーザ勤務詳細 - 勤務情報CSV出力',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 418],
            [
                'info' => 'ユーザ勤務詳細 - 一括承認',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 504],
            [
                'info' => 'ファイルメール便 - 送信',
                'role' => 1,
            ]);

        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 505],
            [
                'info' => 'BOX自動保管画面表示',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 506],
            [
                'info' => 'BOX自動保管設定更新',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 507],
            [
                'info' => 'BOX自動保管再保存',
                'role' => 0,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 508],
            [
                'info' => 'サポート掲示板の投稿 - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 509],
            [
                'info' => 'サポート掲示板の投稿 - 編集',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 510],
            [
                'info' => 'サポート掲示板の投稿 - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 511],
            [
                'info' => 'サポート掲示板のコメント - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 512],
            [
                'info' => 'サポート掲示板のコメント - 編集',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 513],
            [
                'info' => 'サポート掲示板のコメント - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 524],
            [
                'info' => 'サポート掲示板の投稿 - 検索',
                'role' => 1,
            ]);
            
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 514],
            [
                'info' => 'ToDoリストの個人リスト - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 515],
            [
                'info' => 'ToDoリストの個人リスト - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 516],
            [
                'info' => 'ToDoリストの個人リスト - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 517],
            [
                'info' => 'ToDoリストの共有リスト - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 518],
            [
                'info' => 'ToDoリストの共有リスト - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 519],
            [
                'info' => 'ToDoリストの共有リスト - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 520],
            [
                'info' => 'ToDoリストのグループ - 追加',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 521],
            [
                'info' => 'ToDoリストのグループ - 更新',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 522],
            [
                'info' => 'ToDoリストのグループ - 削除',
                'role' => 1,
            ]);
        \DB::table('mst_operation_info')->updateOrInsert(
            ['id' => 523],
            [
                'info' => 'ToDoリストの通知設定 - 更新',
                'role' => 1,
            ]);
            
    }
}
