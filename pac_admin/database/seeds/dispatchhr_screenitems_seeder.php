<?php

use Illuminate\Database\Seeder;

class dispatchhr_screenitems_seeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 1], ['dispatchhr_template_id' => 1, 'row' => 1, 'col' => 1, 'remarks' => '現在の就業状況', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 24, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 2], ['dispatchhr_template_id' => 1, 'row' => 2, 'col' => 1, 'remarks' => 'その他', 'type' => 'text', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 3], ['dispatchhr_template_id' => 2, 'row' => 1, 'col' => 1, 'remarks' => '就業状況区分', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 25, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 4], ['dispatchhr_template_id' => 3, 'row' => 1, 'col' => 1, 'remarks' => '有期雇用契約雇入れ日', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 5], ['dispatchhr_template_id' => 4, 'row' => 1, 'col' => 1, 'remarks' => '無期雇用転換日', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 6], ['dispatchhr_template_id' => 5, 'row' => 1, 'col' => 1, 'remarks' => '就業可能日', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 7], ['dispatchhr_template_id' => 6, 'row' => 1, 'col' => 1, 'remarks' => '契約日(帳票用)', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 8], ['dispatchhr_template_id' => 7, 'row' => 1, 'col' => 1, 'remarks' => '派遣開始日', 'type' => 'label', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 9], ['dispatchhr_template_id' => 8, 'row' => 1, 'col' => 1, 'remarks' => '派遣終了日', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 10], ['dispatchhr_template_id' => 9, 'row' => 1, 'col' => 1, 'remarks' => '派遣終了理由', 'type' => 'text', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 11], ['dispatchhr_template_id' => 10, 'row' => 1, 'col' => 1, 'remarks' => '雇用安定措置', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 12], ['dispatchhr_template_id' => 11, 'row' => 1, 'col' => 1, 'remarks' => '教育訓練の状況', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 13], ['dispatchhr_template_id' => 12, 'row' => 1, 'col' => 1, 'remarks' => 'キャリアコンサルティングの実施状況', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 14], ['dispatchhr_template_id' => 13, 'row' => 1, 'col' => 1, 'remarks' => '入社日', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 15], ['dispatchhr_template_id' => 14, 'row' => 1, 'col' => 1, 'remarks' => '昇給月', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 16], ['dispatchhr_template_id' => 15, 'row' => 1, 'col' => 1, 'remarks' => '一時面接', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 17], ['dispatchhr_template_id' => 16, 'row' => 1, 'col' => 1, 'remarks' => '二次面接', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 18], ['dispatchhr_template_id' => 17, 'row' => 1, 'col' => 1, 'remarks' => '研修①一般常識', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 26, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 19], ['dispatchhr_template_id' => 17, 'row' => 1, 'col' => 2, 'remarks' => '研修①一般常識 コメント', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 20], ['dispatchhr_template_id' => 18, 'row' => 1, 'col' => 1, 'remarks' => '研修②接客の基本', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 26, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 21], ['dispatchhr_template_id' => 18, 'row' => 1, 'col' => 2, 'remarks' => '研修②接客の基本 コメント', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 22], ['dispatchhr_template_id' => 19, 'row' => 1, 'col' => 1, 'remarks' => '研修③異文化理解', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 26, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 23], ['dispatchhr_template_id' => 19, 'row' => 1, 'col' => 2, 'remarks' => '研修③異文化理解 コメント', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 24], ['dispatchhr_template_id' => 20, 'row' => 1, 'col' => 1, 'remarks' => '研修④英語', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 26, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 25], ['dispatchhr_template_id' => 20, 'row' => 1, 'col' => 2, 'remarks' => '研修④英語 コメント', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 26], ['dispatchhr_template_id' => 21, 'row' => 1, 'col' => 1, 'remarks' => '研修⑤メイク', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 26, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 27], ['dispatchhr_template_id' => 21, 'row' => 1, 'col' => 2, 'remarks' => '研修⑤メイク コメント', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 28], ['dispatchhr_template_id' => 22, 'row' => 1, 'col' => 1, 'remarks' => '面接対策①', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 29], ['dispatchhr_template_id' => 23, 'row' => 1, 'col' => 1, 'remarks' => '面接対策②', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 30], ['dispatchhr_template_id' => 24, 'row' => 1, 'col' => 1, 'remarks' => 'キャリアカウンセリング', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 31], ['dispatchhr_template_id' => 25, 'row' => 1, 'col' => 1, 'remarks' => '確認事項読み合わせ', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 27, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 32], ['dispatchhr_template_id' => 26, 'row' => 1, 'col' => 1, 'remarks' => '退職日', 'type' => 'date', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 33], ['dispatchhr_template_id' => 27, 'row' => 1, 'col' => 1, 'remarks' => '勤務地', 'type' => 'checkbox', 'code_flg' => 1, 'dispatch_code_kbn' => 28, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 34], ['dispatchhr_template_id' => 28, 'row' => 1, 'col' => 1, 'remarks' => '就業形態', 'type' => 'checkbox', 'code_flg' => 1, 'dispatch_code_kbn' => 29, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 35], ['dispatchhr_template_id' => 29, 'row' => 1, 'col' => 1, 'remarks' => '希望金額', 'type' => 'checkbox', 'code_flg' => 1, 'dispatch_code_kbn' => 30, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 36], ['dispatchhr_template_id' => 30, 'row' => 1, 'col' => 1, 'remarks' => '希望職種', 'type' => 'checkbox', 'code_flg' => 1, 'dispatch_code_kbn' => 31, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 37], ['dispatchhr_template_id' => 31, 'row' => 1, 'col' => 1, 'remarks' => '希望職種その他', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 38], ['dispatchhr_template_id' => 32, 'row' => 1, 'col' => 1, 'remarks' => '販売経験', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 32, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 39], ['dispatchhr_template_id' => 33, 'row' => 1, 'col' => 1, 'remarks' => '通訳経験', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 32, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 40], ['dispatchhr_template_id' => 34, 'row' => 1, 'col' => 1, 'remarks' => '経験職種', 'type' => 'checkbox', 'code_flg' => 1, 'dispatch_code_kbn' => 33, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);

    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 41], ['dispatchhr_template_id' => 35, 'row' => 1, 'col' => 1, 'remarks' => 'その他資格', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 42], ['dispatchhr_template_id' => 36, 'row' => 1, 'col' => 1, 'remarks' => '語学', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 43], ['dispatchhr_template_id' => 37, 'row' => 1, 'col' => 1, 'remarks' => '接客の知識', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 44], ['dispatchhr_template_id' => 38, 'row' => 1, 'col' => 1, 'remarks' => 'コミュニケーション', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 45], ['dispatchhr_template_id' => 39, 'row' => 1, 'col' => 1, 'remarks' => '規則', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 46], ['dispatchhr_template_id' => 40, 'row' => 1, 'col' => 1, 'remarks' => '接客の技術', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 47], ['dispatchhr_template_id' => 41, 'row' => 1, 'col' => 1, 'remarks' => '基本的態度', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 36, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 48], ['dispatchhr_template_id' => 42, 'row' => 1, 'col' => 1, 'remarks' => '勤務態度', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 37, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 49], ['dispatchhr_template_id' => 43, 'row' => 1, 'col' => 1, 'remarks' => 'チームワーク', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 38, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 50], ['dispatchhr_template_id' => 44, 'row' => 1, 'col' => 1, 'remarks' => 'コミュニケーション/異文化適応力', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 39, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 51], ['dispatchhr_template_id' => 45, 'row' => 1, 'col' => 1, 'remarks' => '組織運営への協力度/参画度、理解度', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 40, 'regist_table_kbn' => 1, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 52], ['dispatchhr_template_id' => 46, 'row' => 1, 'col' => 1, 'remarks' => '基本的態度', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 34, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 53], ['dispatchhr_template_id' => 47, 'row' => 1, 'col' => 1, 'remarks' => '勤務態度', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 34, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 54], ['dispatchhr_template_id' => 48, 'row' => 1, 'col' => 1, 'remarks' => 'チームワーク', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 34, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 55], ['dispatchhr_template_id' => 49, 'row' => 1, 'col' => 1, 'remarks' => 'コミュニケーション/異文化適応力', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 34, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 56], ['dispatchhr_template_id' => 50, 'row' => 1, 'col' => 1, 'remarks' => '組織運営への協力度/参画度、理解度', 'type' => 'radio', 'code_flg' => 1, 'dispatch_code_kbn' => 34, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 57], ['dispatchhr_template_id' => 51, 'row' => 1, 'col' => 1, 'remarks' => 'コメント', 'type' => 'textarea', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 58], ['dispatchhr_template_id' => 52, 'row' => 1, 'col' => 1, 'remarks' => '担当者', 'type' => 'text', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    \DB::table('dispatchhr_screenitems')->updateOrInsert(
      ['id' => 59], ['dispatchhr_template_id' => 53, 'row' => 1, 'col' => 1, 'remarks' => '個人情報出力', 'type' => 'checkbox', 'code_flg' => 0, 'dispatch_code_kbn' => null, 'regist_table_kbn' => 0, 'create_user' => 'system', 'update_user' => 'system', ]);
    
  }
}