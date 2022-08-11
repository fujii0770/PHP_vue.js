<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 11/13/19
 * Time: 14:45
 */

namespace App\Http\Utils;


class StampUtils
{

    const NORMAL_STAMP = 0;
    const COMMON_STAMP = 1;
    const DEPART_STAMP = 2;
    const CONVENIENT_STAMP = 3;
    const SPECIAL_STAMP_NAMES = ['（土）吉田','（土）吉川','（土）吉村'];

    // PAC_5-107 BEGIN
    /**
     * 紫色
     */
    const COLOR_PURPLE = '01';

    /**
     * 赤色
     */
    const COLOR_RED = '02';

    /**
     * 藍色
     */
    const COLOR_BLUE = '03';

    /**
     * 黒色
     */
    const COLOR_BLACK = '04';

    /**
     * 朱色
     */
    const COLOR_VERMEIL = '05';

    /**
     * 緑色
     */
    const COLOR_GREEN = '06';

    /**
     * 白い背景を追加
     * @param $image
     * @return string
     */
    public static function imageConvert($image)
    {
        $img_str = base64_decode($image);
        //画像サイズと日付印の横幅の対比をもとに計算
        $png_img = imagecreatefromstring($img_str);
        $x_size = imagesx($png_img);
        $y_size = imagesy($png_img);
        $dst_img = imagecreatetruecolor($x_size, $y_size);
        $color = imagecolorallocate($dst_img, 255, 255, 255);
        imagefill($dst_img, 0, 0, $color);
        imagecopy($dst_img, $png_img, 0, 0, 0, 0, $x_size, $y_size);

        ob_start();
        imagepng($dst_img);
        $contents = ob_get_contents();
        ob_end_clean();

        return base64_encode($contents);
    }

    /**
     * 部署名入り日付印 透過処理
     * @param $png_img
     * @param $x_size
     * @param $y_size
     * @return mixed
     */
    public static function stampTransparency($png_img,$x_size,$y_size){
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
        return $png_img;
    }

    public static function processStampImage($stamp, $date, $is_template = false){
        $fontFile = public_path('fonts/arial.ttf');
        $stampImage = $stamp->stamp_image;
        if ($stamp->stamp_division !== 0){
            // date stamp

            $img_str = base64_decode($stamp->stamp_image);
            //画像サイズと日付印の横幅の対比をもとに計算
            $png_img = imagecreatefromstring($img_str);
            $x_size = imagesx($png_img);
            $date_width = $x_size * 0.75;
            // 1763一時対応 共通印の場合、固定widthでなく
            if($stamp->stamp_flg == StampUtils::COMMON_STAMP || $stamp->stamp_flg == StampUtils::CONVENIENT_STAMP){
                $date_width = $stamp->date_width;
            }
            //日付の画像データを作成
            $date_img = imagecreate($date_width, $stamp->date_height);
            imagealphablending($date_img, true);
            imagesavealpha($date_img, false);
            // 日付の色を設定する PAC_5-107 BEGIN
            // 部署名入り日付印 透過処理
            if($stamp->stamp_division === null){
                if($stamp->color == StampUtils::COLOR_PURPLE) {
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 77, 67, 153));
                    $fontColor = imagecolorallocate($date_img, 88, 67, 126);
                }else if ($stamp->color == StampUtils::COLOR_BLUE){
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 0, 151, 224));
                    $fontColor = imagecolorallocate($date_img, 26, 141, 205);
                }else if ($stamp->color == StampUtils::COLOR_BLACK){
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 0, 0, 0));
                    $fontColor = imagecolorallocate($date_img, 40, 22, 18);
                }else if ($stamp->color == StampUtils::COLOR_VERMEIL){
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 236, 108, 0));
                    $fontColor = imagecolorallocate($date_img, 220, 113, 15);
                }else if ($stamp->color == StampUtils::COLOR_GREEN){
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 0, 154, 68));
                    $fontColor = imagecolorallocate($date_img, 25, 141, 65);
                }else {
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 255, 0, 0));
                    $fontColor = imagecolorallocate($date_img, 188, 19, 31);
                }
                $png_img = StampUtils::stampTransparency($png_img,$x_size,imagesy($png_img));
            }else{
                $date_color = AppUtils::changeColorToRgbArray($stamp->color);
                imagecolortransparent($date_img, imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]));
                $fontColor = imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]);
            }
            // PAC_5-107 END

            $max_size = $stamp->stamp_flg == StampUtils::NORMAL_STAMP && in_array($stamp->stamp_name,StampUtils::SPECIAL_STAMP_NAMES) ? 60 : 50;
            for ($size = $max_size; $size > 5; $size--) {
                $sizearray = imagettfbbox($size, 0, $fontFile, $date);
                $width = $sizearray[2] - $sizearray[6];
                $height = $sizearray[3] - $sizearray[7];
                if ($width <= ($date_width - 4) && $height <= ($stamp->date_height - 4)) {
                    //you've got your $size
                    //    echo $size.'<br/>';
                    break;
                }
            }
            imagettftext(
                $date_img,
                $size + 1,
                0,
                0,
                $stamp->date_height / 1.3,
                $fontColor,
                $fontFile,
                $date
            );

            //引数にあるdate_xは画像サイズに合っていないので再計算
            $x = ($x_size - $date_width) / 2;
            if($stamp->stamp_division == 1) {
                imagealphablending($png_img, false);
                imagesavealpha($png_img, true);
            }
            if ($stamp->stamp_flg == StampUtils::COMMON_STAMP || $stamp->stamp_flg == StampUtils::CONVENIENT_STAMP){
                imagecopy($png_img, $date_img, $stamp->date_x, $stamp->date_y,  0, 0, $date_width, $stamp->date_height);
            }else{
                imagecopy($png_img, $date_img, $x, $stamp->date_y,  0, 0, $date_width, $stamp->date_height);
            }
            ob_start();
            if($stamp->stamp_flg == StampUtils::DEPART_STAMP) {
                imagegif($png_img);
            }else{
                imagepng($png_img);
            }
            $contents = ob_get_contents();
            ob_end_clean();

            $stampImage = base64_encode($contents);
        }

        if($stamp->stamp_flg == StampUtils::DEPART_STAMP) {
            $create_at = new \DateTime($stamp->create_at);
            $to_date = new \DateTime('2020-05-15');
            if($create_at < $to_date) {
                $im = new \Imagick();
                $im->readImageBlob(base64_decode($stampImage));
                $im->setImageResolution(72, 72);
                $im->setImageFormat("png");

                $imgBuff = $im->getimageblob();

                $png_img = imagecreatefromstring($imgBuff);
                imagealphablending($png_img, true);
                $transparent_color = imagecolorallocate($png_img, 255,255,255);
                imagecolortransparent($png_img, $transparent_color);

                ob_start();
                imagegif ($png_img);
                $contents = ob_get_contents();
                ob_end_clean();

                $stampImage = base64_encode($contents);
            }
        }
        if(($stamp->stamp_flg == StampUtils::COMMON_STAMP || $stamp->stamp_flg == StampUtils::CONVENIENT_STAMP) && AppUtils::getFileSize($stamp->stamp_image) > 204800) {
            // resize image if the company stamp is too big
            $img_str = base64_decode($stamp->stamp_image);
            //画像サイズと日付印の横幅の対比をもとに計算
            $png_img = imagecreatefromstring($img_str);

            $stamp_width = $stamp->width*3.7795275591/1000;
            $stamp_height = $stamp->height*3.7795275591/1000;
            if ($is_template){
                $stamp_width = $stamp->width*3.7795275591;
                $stamp_height = $stamp->height*3.7795275591;
            }

            $new_image = imagecreatetruecolor ( $stamp_width, $stamp_height ); // new wigth and height

            imagealphablending($new_image , false);

            imagesavealpha($new_image , true);

            imagecopyresampled ( $new_image, $png_img, 0, 0, 0, 0, $stamp_width, $stamp_height, imagesx ( $png_img ), imagesy ( $png_img ) );

            $image = $new_image;

            imagealphablending($image , false);

            imagesavealpha($image , true);

            ob_start();
            imagepng ($image);
            $contents = ob_get_contents();
            ob_end_clean();

            $stampImage = base64_encode($contents);
        }
        return $stampImage;
    }
}