<?php

use Illuminate\Database\Seeder;

class dispatchhr_template_seeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 1], ['tabno' => '1', 'order' => '9', 'remarks' => '現在の就業状況', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 2], ['tabno' => '1', 'order' => '10', 'remarks' => '就業状況区分', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 3], ['tabno' => '1', 'order' => '11', 'remarks' => '有期雇用契約雇入れ日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 4], ['tabno' => '1', 'order' => '12', 'remarks' => '無期雇用転換日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 5], ['tabno' => '1', 'order' => '13', 'remarks' => '就業可能日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 6], ['tabno' => '1', 'order' => '14', 'remarks' => '契約日(帳票用)', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 7], ['tabno' => '1', 'order' => '15', 'remarks' => '派遣開始日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 8], ['tabno' => '1', 'order' => '16', 'remarks' => '派遣終了日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 9], ['tabno' => '1', 'order' => '17', 'remarks' => '派遣終了理由', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 10], ['tabno' => '1', 'order' => '18', 'remarks' => '雇用安定措置', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 11], ['tabno' => '1', 'order' => '19', 'remarks' => '教育訓練の状況', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 12], ['tabno' => '1', 'order' => '20', 'remarks' => 'キャリアコンサルティングの実施状況', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 13], ['tabno' => '1', 'order' => '21', 'remarks' => '入社日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 14], ['tabno' => '1', 'order' => '22', 'remarks' => '昇給月', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 15], ['tabno' => '1', 'order' => '23', 'remarks' => '一時面接', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 16], ['tabno' => '1', 'order' => '24', 'remarks' => '二次面接', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 17], ['tabno' => '1', 'order' => '25', 'remarks' => '研修①一般常識', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 18], ['tabno' => '1', 'order' => '26', 'remarks' => '研修②接客の基本', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 19], ['tabno' => '1', 'order' => '27', 'remarks' => '研修③異文化理解', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 20], ['tabno' => '1', 'order' => '28', 'remarks' => '研修④英語', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 21], ['tabno' => '1', 'order' => '29', 'remarks' => '研修⑤メイク', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 22], ['tabno' => '1', 'order' => '30', 'remarks' => '面接対策①', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 23], ['tabno' => '1', 'order' => '31', 'remarks' => '面接対策②', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 24], ['tabno' => '1', 'order' => '32', 'remarks' => 'キャリアカウンセリング', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 25], ['tabno' => '1', 'order' => '33', 'remarks' => '確認事項読み合わせ', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 26], ['tabno' => '1', 'order' => '34', 'remarks' => '退職日', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 27], ['tabno' => '2', 'order' => '1', 'remarks' => '勤務地', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 28], ['tabno' => '2', 'order' => '2', 'remarks' => '就業形態', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 29], ['tabno' => '2', 'order' => '3', 'remarks' => '希望金額', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 30], ['tabno' => '2', 'order' => '4', 'remarks' => '希望職種', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 31], ['tabno' => '2', 'order' => '5', 'remarks' => '希望職種その他', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 32], ['tabno' => '3', 'order' => '1', 'remarks' => '販売経験', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 33], ['tabno' => '3', 'order' => '2', 'remarks' => '通訳経験', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 34], ['tabno' => '3', 'order' => '3', 'remarks' => '経験職種', 'create_user' => 'system', 'update_user' => 'system', ]);

    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 35], ['tabno' => '4', 'order' => '1', 'remarks' => 'その他資格', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 36], ['tabno' => '4', 'order' => '2', 'remarks' => '語学', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 37], ['tabno' => '4', 'order' => '3', 'remarks' => '接客の知識', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 38], ['tabno' => '4', 'order' => '4', 'remarks' => 'コミュニケーション', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 39], ['tabno' => '4', 'order' => '5', 'remarks' => '規則', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 40], ['tabno' => '4', 'order' => '6', 'remarks' => '接客の技術', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 41], ['tabno' => '6', 'order' => '1', 'remarks' => '基本的態度', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 42], ['tabno' => '6', 'order' => '2', 'remarks' => '勤務態度', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 43], ['tabno' => '6', 'order' => '3', 'remarks' => 'チームワーク', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 44], ['tabno' => '6', 'order' => '4', 'remarks' => 'コミュニケーション/異文化適応力', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 45], ['tabno' => '6', 'order' => '5', 'remarks' => '組織運営への協力度/参画度、理解度', 'create_user' => 'system', 'update_user' => 'system', ]);


    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 46], ['tabno' => '7', 'order' => '1', 'remarks' => '基本的態度', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 47], ['tabno' => '7', 'order' => '2', 'remarks' => '勤務態度', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 48], ['tabno' => '7', 'order' => '3', 'remarks' => 'チームワーク', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 49], ['tabno' => '7', 'order' => '4', 'remarks' => 'コミュニケーション/異文化適応力', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 50], ['tabno' => '7', 'order' => '5', 'remarks' => '組織運営への協力度/参画度、理解度', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 51], ['tabno' => '7', 'order' => '6', 'remarks' => 'コメント', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 52], ['tabno' => '7', 'order' => '7', 'remarks' => '担当者', 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_template')->updateOrInsert(
      ['id' => 53], ['tabno' => '7', 'order' => '8', 'remarks' => '個人情報出力', 'create_user' => 'system', 'update_user' => 'system', ]);
    
  }
}