<?php


namespace App\Http\Utils;

use DB;
use Illuminate\Support\Carbon;
use App\Models\UsageSituation;

class StampUtils
{

    const NORMAL_STAMP = 0;
    const COMMON_STAMP = 1;
    const DEPART_STAMP = 2;
    const CONVENIENT_STAMP = 3;
    
    /**
     * PAC_5-620BEGIN
     * 管理者で共通印を確認すると日付が反映されていない（利用者で見ると反映されている）
     * @param $stamp
     * @param $company_id
     * @return string
     */
    public static function companyStampWithDate($stamp, $company_id){
        // show date
        $date = date("Y").'/01/01';
        // date style
        $dstamp_style = DB::table('mst_company')->where('id', $company_id)->select('dstamp_style')->pluck('dstamp_style')->first();
        if(!$dstamp_style)  {
            $dstamp_style = 'y.m.d';
        }

        $date = DateJPUtils::convert($date, $dstamp_style);
        $date_color = \App\Http\Utils\AppUtils::changeColorToRgbArray($stamp->date_color);

        $img_str = base64_decode($stamp->stamp_image);
        $png_img = imagecreatefromstring($img_str);
        //日付の画像データを作成
        $date_img = imagecreate($stamp->date_width, $stamp->date_height);
        imagecolortransparent($date_img, imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]));
        // font color red
        $fontColor = imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]);
        // font type
        $fontFile = public_path('fonts/arial.ttf');
        // get font size
        for ($size = 50; $size > 5; $size--) {
            $sizearray = imagettfbbox($size, 0, $fontFile, $date);
            $width = $sizearray[2] - $sizearray[6];
            $height = $sizearray[3] - $sizearray[7];
            if ($width <= ($stamp->date_width - 4) && $height <= ($stamp->date_height - 4)) {
                break;
            }
        }
        // image text
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

        imagealphablending($png_img, false);
        imagesavealpha($png_img, true);
        imagecopy($png_img, $date_img, $stamp->date_x, $stamp->date_y,  0, 0, $stamp->date_width, $stamp->date_height);

        ob_start();
        imagepng($png_img);
        $contents = ob_get_contents();
        ob_end_clean();

        return base64_encode($contents);
    }
    //PAC_5-620END

    /**
     * PAC_5-1055 BEGIN
     * 利用者設定の部分で日付が表示されるようにしてほしい
     * @param $stamp
     * @param $company_id
     * @return string
     */
    public static function companyStampWithDateArr($stamp, $company_id){
        // show date
        $date = date("Y").'/01/01';
        // date style
        $dstamp_style = DB::table('mst_company')->where('id', $company_id)->select('dstamp_style')->pluck('dstamp_style')->first();
        if(!$dstamp_style)  {
            $dstamp_style = 'y.m.d';
        }

        $date = DateJPUtils::convert($date, $dstamp_style);
        $date_color = \App\Http\Utils\AppUtils::changeColorToRgbArray($stamp['date_color']);

        $img_str = base64_decode($stamp['stamp_image']);
        $png_img = imagecreatefromstring($img_str);
        //日付の画像データを作成
        $date_img = imagecreate($stamp['date_width'], $stamp['date_height']);
        imagecolortransparent($date_img, imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]));
        // font color red
        $fontColor = imagecolorallocate($date_img, $date_color[0], $date_color[1], $date_color[2]);
        // font type
        $fontFile = public_path('fonts/arial.ttf');
        // get font size
        for ($size = 50; $size > 5; $size--) {
            $sizearray = imagettfbbox($size, 0, $fontFile, $date);
            $width = $sizearray[2] - $sizearray[6];
            $height = $sizearray[3] - $sizearray[7];
            if ($width <= ($stamp['date_width'] - 4) && $height <= ($stamp['date_height'] - 4)) {
                break;
            }
        }
        // image text
        imagettftext(
            $date_img,
            $size + 1,
            0,
            0,
            $stamp['date_height'] / 1.3,
            $fontColor,
            $fontFile,
            $date
        );

        imagealphablending($png_img, false);
        imagesavealpha($png_img, true);
        imagecopy($png_img, $date_img, $stamp['date_x'], $stamp['date_y'],  0, 0, $stamp['date_width'], $stamp['date_height']);

        ob_start();
        imagepng($png_img);
        $contents = ob_get_contents();
        ob_end_clean();

        return base64_encode($contents);
    }
    // PAC_5-1055 END

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

    /*
     *
     */
    public static function getUsageSituation($domainid){
        $usageSituation = [
            'total_name_stamp' => 0,
            'total_date_stamp' => 0,
            'total_common_stamp' => 0,
        ];

        $totalStamps = DB::table('mst_assign_stamp')
//                ->join('mst_user', 'mst_user.id', '=', 'mst_assign_stamp.mst_user_id')
            ->join('mst_user', function ($join) use ($domainid){
                $join->on('mst_user.id', 'mst_assign_stamp.mst_user_id')
                ->where('mst_user.mst_company_id', '=' , $domainid)
                ->where(function ($query0){
                    $query0->where(function ($query){

                        // 富士通(K5)以外場合、有効なユーザー
                        $query->whereIn('mst_user.state_flg',[AppUtils::STATE_VALID]);

                    })
                        ->orWhere(function($query1){
                            // 無効だが、今日無効したユーザー（今日統計されたことがある）
                            $query1->whereIn('mst_user.state_flg', [AppUtils::STATE_INVALID,AppUtils::STATE_INVALID_NOPASSWORD]);
                            $query1->where(DB::raw("DATE_FORMAT(mst_user.invalid_at, '%Y%m%d')"), Carbon::now()->format('Ymd'));

                        })
                    ;
                });
            })
            ->join('mst_company', 'mst_user.mst_company_id', '=', 'mst_company.id')
            ->leftJoin('mst_stamp', 'mst_assign_stamp.stamp_id', '=', 'mst_stamp.id')
            ->select(['mst_company.id as mst_company_id', 'mst_company.company_name as company_name','mst_company.company_name_kana as company_name_kana',
                DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_COMPANY.', 1, 0)) as count_common_stamp'),
                DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_DEPARTMENT.', 1, 0)) as count_department_stamp'),
                DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_NORMAL.' AND mst_stamp.stamp_division = 1, 1, 0)) as count_mst_stamp_date'),
                DB::raw('SUM(if(mst_assign_stamp.stamp_flg = '.AppUtils::STAMP_FLG_NORMAL.' AND mst_stamp.stamp_division = 0, 1, 0)) as count_mst_stamp_name'),
            ])
            ->whereIn('mst_assign_stamp.state_flg',[AppUtils::STATE_VALID])
            ->groupBy('mst_company.id', 'company_name', 'company_name_kana')
            ->get();

        foreach ($totalStamps as $totalStamp){
            // ホスト企業
            if ($totalStamp->mst_company_id == $domainid){
                $usageSituation['total_name_stamp'] = $totalStamp->count_mst_stamp_name;
                $usageSituation['total_date_stamp'] = $totalStamp->count_department_stamp + $totalStamp->count_mst_stamp_date;
                $usageSituation['total_common_stamp'] = $totalStamp->count_common_stamp;
            }
        }

        return $usageSituation;
    }

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
}