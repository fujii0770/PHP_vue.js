<?php


namespace App\Utils;


use Illuminate\Support\Facades\Cookie;

class AppUtils
{
    //ユーザー種類
    const ACCOUNT_TYPE_ADMIN = 'admin'; //管理者
    const ACCOUNT_TYPE_USER = 'user'; //利用者
    const ACCOUNT_TYPE_AUDIT = 'audit';//監査アカウント

    const TEMPLATE_TYPE_TEMPLATE = 'template';
    const TEMPLATE_TYPE_SPECIAL = 'template_special';
    const TEMPLATE_TYPE_FORM_ISSUANCE = 'form_issuance';
    const TEMPLATE_EDIT = 'template_edit';

    const AUTH_FLG_ADMIN = 2; //管理者
    const AUTH_FLG_USER = 1; //利用者

    public static function getUnique()
    {
        return strtoupper(md5(uniqid(session_create_id(), true)));
    }

    public static function encrypt($data, $withoutSlash = false)
    {
        $password = trim(config('app.aes256_pass'));

        // CBC has an IV and thus needs randomness every time a message is encrypted
        $method = 'aes-256-cbc';

        // Must be exact 32 chars (256 bit)
        // You must store this secret random key in a safe place of your system.
        $key = substr(hash('sha256', $password, true), 0, 32);

        // Most secure key
        //$key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

        // Most secure iv
        // Never ever use iv=0 in real life. Better use this iv:
        // $ivlen = openssl_cipher_iv_length($method);
        // $iv = openssl_random_pseudo_bytes($ivlen);

        // av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
        $encrypted = base64_encode(openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv));
        if ($withoutSlash) {
            $encrypted = str_replace('/', '*', $encrypted);
        }
        return $encrypted;
    }

    public static function decrypt($encrypted, $withoutSlash = false)
    {
        $password = trim(config('app.aes256_pass'));

        // CBC has an IV and thus needs randomness every time a message is encrypted
        $method = 'aes-256-cbc';

        // Must be exact 32 chars (256 bit)
        // You must store this secret random key in a safe place of your system.
        $key = substr(hash('sha256', $password, true), 0, 32);

        // Most secure key
        //$key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

        // Most secure iv
        // Never ever use iv=0 in real life. Better use this iv:
        // $ivlen = openssl_cipher_iv_length($method);
        // $iv = openssl_random_pseudo_bytes($ivlen);

        // My secret message 1234
        if ($withoutSlash) {
            $encrypted = str_replace('*', '/', $encrypted);
        }
        return $decrypted = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

    }

    /**
     * ハンコ解像度調整
     * @param $stampContents
     * @return string
     */
    public static function stampClarity($stampContents)
    {
        // 元画像を取得
        $stamp_img = imagecreatefromstring($stampContents);
        // 元画像の幅と高さを取得
        [$stampWidth,$stampHeight] = getimagesizefromstring($stampContents);
        // ハイビジョン画像を作成
        $stamp_tmp = imagecreatetruecolor($stampWidth, $stampHeight);
        // 透明色を設定
        $color = imagecolorallocate($stamp_tmp, 255, 255, 255);
        // tmpが透明化を設定
        imagecolortransparent($stamp_tmp, $color);
        // 座標0,0から領域をカラーで塗りつぶします
        imagefill($stamp_tmp, 0, 0, $color);
        // 元画像はハイビジョン画像に塗りつぶし
        imagecopyresampled($stamp_tmp, $stamp_img, 0, 0, 0, 0, $stampWidth, $stampHeight, $stampWidth, $stampHeight);

        // 画像を保存
        ob_start();
        imagepng($stamp_tmp);
        $contents = ob_get_contents();
        ob_end_clean();

        $png_img = imagecreatefromstring($contents);
        $x_size = imagesx($png_img);
        $y_size = imagesy($png_img);
        $begin_r = 255;
        $begin_g = 250;
        $begin_b = 225;
        imagesavealpha($png_img, true);
        $src_white = imagecolorallocatealpha($png_img, 255, 255, 255,127); // 白い透明なキャンバスを作成します。
        for ($x = 0; $x < $x_size; $x++) {
            for ($y = 0; $y < $y_size; $y++) {
                $rgb = imagecolorat($png_img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if($r==255 && $g==255 && $b == 255){
                    imagefill($png_img,$x, $y, $src_white); // 塗りつぶし
                    imagecolortransparent($png_img, $src_white); // 原図の色を透明色に置き換える
                }
                if (!($r <= $begin_r && $g <= $begin_g && $b <= $begin_b)) {
                    imagefill($png_img, $x, $y, $src_white); // 白に置換
                    imagecolortransparent($png_img, $src_white);
                }
            }
        }

        // 画像を保存
        ob_start();
        imagepng($png_img);
        $contents = ob_get_contents();
        ob_end_clean();

        // 画像はbase64を設定
        return base64_encode($contents);
    }

    public static function getCookieValue($cookieName, $encrypter = null){
        if (!$encrypter){
            $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);
        }

        $cookie = $encrypter->decrypt(Cookie::get($cookieName), false);
        $cookie = explode('|', $cookie);
        if (count($cookie) > 1){
            return $cookie[1];
        }else{
            return null;
        }
    }

    public static function generateStampUrl($is_move_to_lgwan = false)
    {
        // LGWAN の Private 環境下で登録される回覧は Public 環境の情報で登録
        if ((config('app.app_lgwan_flg') && config('app.stamp_lgwan_public_url')) || $is_move_to_lgwan) {
            $url = config('app.stamp_lgwan_public_url') . '/StampInfo';
        } else {
            $url = url('/StampInfo');
        }
        return $url;
    }
}
