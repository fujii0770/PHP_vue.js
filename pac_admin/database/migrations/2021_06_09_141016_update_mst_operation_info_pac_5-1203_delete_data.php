<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMstOperationInfoPac51203DeleteData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //　operation_history　auth_flg　コメント修正
        Schema::table('operation_history', function (Blueprint $table) {
            $table->integer('auth_flg')
                ->comment('0：管理者   1：利用者')
                ->change();
        });

        // operation_history 処理
        // ■削除系
        $ids = [
            4,    //利用状況検索
            9,    //利用者API呼出履歴画面を表示
            10,   //利用者API呼出履歴検索
            53,   //共通アドレス帳 - CSVダウンロード
            82,   //認証コード入力画面を表示
            103,  //パスワード設定画面を表示
            105,  //パスワード設定メール有効期限外
            109,  //新規作成 - 印面一覧表示
            110,  //新規作成 - ダウンロード
            111,  //新規作成 - 捺印
            112,  //新規作成 - テキスト追加
            113,  //新規作成 - 印面並び順変更
            116,  //受信一覧 - 検索
            117,  //受信一覧 - 詳細表示
            119,  //送信一覧 - 検索
            120,  //送信一覧 - 詳細表示
            121,  //完了一覧 - ダウンロード
            127,  //下書き一覧 - 検索
            132,  //アドレス帳 - 検索
            140,  //設定 - パスワード変更
            145,  //回覧文書 - 印面一覧表示
            146,  //回覧文書 - ダウンロード
            147,  //回覧文書 - 捺印
            148,  //回覧文書 - テキスト追加
            149,  //回覧文書 - 印面並び順変更
            151,  //回覧先設定 - アドレス帳表示
            153,  //回覧文書承認 - 回覧完了
            154,  //差戻し設定画面を表示
        ];

        // 既存データ処理　＝＞　mst_operation_info 削除要データ削除
        DB::table('mst_operation_info')
            ->whereIn('id', $ids)
            ->delete();

        // ■削除系
        $ids = [
            4,    //利用状況検索
            9,    //利用者API呼出履歴画面を表示
            10,   //利用者API呼出履歴検索
            53,   //共通アドレス帳 - CSVダウンロード
            82,   //認証コード入力画面を表示
            103,  //パスワード設定画面を表示
            105,  //パスワード設定メール有効期限外
            109,  //新規作成 - 印面一覧表示
            113,  //新規作成 - 印面並び順変更
            116,  //受信一覧 - 検索
            117,  //受信一覧 - 詳細表示
            119,  //送信一覧 - 検索
            120,  //送信一覧 - 詳細表示
            127,  //下書き一覧 - 検索
            132,  //アドレス帳 - 検索
            137,  //設定 - 印面一覧表示
            138,  //設定 - 印面並び順変更
            139,  //設定 - メール受信設定更新
            140,  //設定 - パスワード変更
            145,  //回覧文書 - 印面一覧表示
            149,  //回覧文書 - 印面並び順変更
            151,  //回覧先設定 - アドレス帳表示
            153,  //回覧文書承認 - 回覧完了
            154,  //差戻し設定画面を表示
        ];

        // 既存データ処理　＝＞　operation_history 削除要データ削除
        DB::table('operation_history')
            ->whereIn('mst_operation_id', $ids)
            ->delete();

        for($i=0;$i<10;$i++){
            DB::table('operation_history'.$i)
                ->whereIn('mst_operation_id', $ids)
                ->delete();
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
            ['id'=>4,'info'=>'利用状況検索','role'=>0],
            ['id'=>9,'info'=>'利用者API呼出履歴画面を表示','role'=>0],
            ['id'=>10,'info'=>'利用者API呼出履歴検索','role'=>0],
            ['id'=>53,'info'=>'共通アドレス帳 - CSVダウンロード','role'=>0],
            ['id'=>82,'info'=>'認証コード入力画面を表示','role'=>0],
            ['id'=>103,'info'=>'パスワード設定画面を表示','role'=>1],
            ['id'=>105,'info'=>'パスワード設定メール有効期限外','role'=>1],
            ['id'=>109,'info'=>'新規作成 - 印面一覧表示','role'=>1],
            ['id'=>110,'info'=>'新規作成 - ダウンロード','role'=>1],
            ['id'=>111,'info'=>'新規作成 - 捺印','role'=>1],
            ['id'=>112,'info'=>'新規作成 - テキスト追加','role'=>1],
            ['id'=>113,'info'=>'新規作成 - 印面並び順変更','role'=>1],
            ['id'=>116,'info'=>'受信一覧 - 検索','role'=>1],
            ['id'=>117,'info'=>'受信一覧 - 詳細表示','role'=>1],
            ['id'=>119,'info'=>'送信一覧 - 検索','role'=>1],
            ['id'=>120,'info'=>'送信一覧 - 詳細表示','role'=>1],
            ['id'=>121,'info'=>'完了一覧 - ダウンロード','role'=>1],
            ['id'=>127,'info'=>'下書き一覧 - 検索','role'=>1],
            ['id'=>132,'info'=>'アドレス帳 - 検索','role'=>1],
            ['id'=>140,'info'=>'設定 - パスワード変更','role'=>1],
            ['id'=>145,'info'=>'回覧文書 - 印面一覧表示','role'=>1],
            ['id'=>146,'info'=>'回覧文書 - ダウンロード','role'=>1],
            ['id'=>147,'info'=>'回覧文書 - 捺印','role'=>1],
            ['id'=>148,'info'=>'回覧文書 - テキスト追加','role'=>1],
            ['id'=>149,'info'=>'回覧文書 - 印面並び順変更','role'=>1],
            ['id'=>151,'info'=>'回覧先設定 - アドレス帳表示','role'=>1],
            ['id'=>153,'info'=>'回覧文書承認 - 回覧完了','role'=>1],
            ['id'=>154,'info'=>'差戻し設定画面を表示','role'=>1],
        ];

        DB::table('mst_operation_info')
            ->insert($infos);
    }
}
