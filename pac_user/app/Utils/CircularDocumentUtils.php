<?php namespace App\Utils;

class CircularDocumentUtils
{
    //status
    const SAVING_STATUS = 0; // 保存中(アップロード直後)
    const CIRCULATING_STATUS = 1; // 回覧中（送信直後の状態）
    const CIRCULAR_COMPLETED_STATUS = 2; // 回覧完了（最後の承認者が承認した時点でこの状態になる。）
    const CIRCULAR_COMPLETED_SAVED_STATUS = 3; // 回覧完了(保存済)。依頼者が文書をダウンロードするとこの状態になる。
    const SEND_BACK_STATUS = 4; // 差戻　（差戻直後のみこの状態。差戻後に再度承認を行うと回覧中に戻る。）
    const RETRACTION_STATUS = 5; // 引戻（削除と同様。依頼者の引き戻し）
    const DELETE_STATUS = 9; // 削除（回覧を削除するとこの状態になる。）

    public static function charactersReplace($fileName)
    {
        $standardCharacter = array("が","ぎ","ぐ","げ","ご","ざ","じ","ず","ぜ","ぞ","だ","ぢ","づ","で","ど","ば","び","ぶ","べ","ぼ","ぱ","ぴ","ぷ","ぺ","ぽ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ");
        $realCharacter = array("が","ぎ","ぐ","げ","ご","ざ","じ","ず","ぜ","ぞ","だ","ぢ","づ","で","ど","ば","び","ぶ","べ","ぼ","ぱ","ぴ","ぷ","ぺ","ぽ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ");
        $realFileName =  str_replace($realCharacter, $standardCharacter, $fileName);

        return $realFileName;
    }
}