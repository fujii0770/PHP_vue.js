<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 12/9/19
 * Time: 09:51
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\PublicGenerateStampRequest;
use App\Http\Utils\StatusCodeUtils;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicAPIController extends AppBaseController
{
    public function generateStamp(PublicGenerateStampRequest $request) {
        try {
            $dstamp_style = 'y.m.d';
            $date = new \DateTime($request['date']);
            $name = $request['name'];
            $date = \App\Http\Utils\DateJPUtils::convert($date, $dstamp_style);

            $arrStamp = [];
            $fontFile = public_path('fonts/arial.ttf');

            for($i=0;$i<2; $i++){ // stamp_division
                for($j=0;$j<3; $j++){ // font
                    $client = new Client(['base_uri' => config('app.stamp_api_base_url'), 'timeout' => config('app.guzzle_timeout'), 'connect_timeout' => config('app.guzzle_connect_timeout')]);

                    $result = $client->get("/ananke/shaadvservice/api/v1/rqst/$i/$j/".rawurlencode("$name")."/0/");

                    if($result->getStatusCode() == StatusCodeUtils::HTTP_OK) {
                        $stamp = json_decode((string) $result->getBody());
                        if($stamp->contents){
                            $stamp->stamp_division = $i;
                            $stamp->font = $j;
                            $arrStamp[] = (object)[
                                'stamp_name' => $name,
                                'stamp_division' => $i,
                                'font' => $j,
                                'stamp_image' => $stamp->contents,
                                'width' => floatval($stamp->realWidth) * 100,
                                'height' => floatval($stamp->realHeight) * 100,
                                'date_x' => $stamp->datex,
                                'date_y' => $stamp->datey,
                                'date_width' => $stamp->datew,
                                'date_height' => $stamp->dateh,
                            ];
                        }
                    }
                }
            }

            foreach ($arrStamp as $key => $stamp){
                if ($stamp->stamp_division == 1){
                    // date stamp

                    $img_str = base64_decode($stamp->stamp_image);
                    //画像サイズと日付印の横幅の対比をもとに計算
                    $png_img = imagecreatefromstring($img_str);
                    $x_size = imagesx($png_img);
                    $date_width = $x_size * 0.75;
                    //日付の画像データを作成
                    $date_img = imagecreate($date_width, $stamp->date_height);
                    imagealphablending($date_img, true);
                    imagesavealpha($date_img, false);
                    imagecolortransparent($date_img, imagecolorallocate($date_img, 255, 0, 0));
                    $fontColor = imagecolorallocate($date_img, 255, 0, 0);
                    for ($size = 50; $size > 5; $size--) {
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
                    imagealphablending($png_img, false);
                    imagesavealpha($png_img, true);
                    imagecopy($png_img, $date_img, $x, $stamp->date_y,  0, 0, $date_width, $stamp->date_height);

                    ob_start();
                    imagepng($png_img);
                    $contents = ob_get_contents();
                    ob_end_clean();

                    $arrStamp[$key]->stamp_image = base64_encode($contents);
                }
            }

            return $this->sendResponse($arrStamp, '印面の作成処理に成功しました。');

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}