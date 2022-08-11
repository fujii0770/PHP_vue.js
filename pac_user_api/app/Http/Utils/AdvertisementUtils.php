<?php


namespace App\Http\Utils;


use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdvertisementUtils
{
    //位置
    const LOCATION_TOP = 1;
    const LOCATION_MIDDLE = 2;
    const LOCATION_END = 3;

    /**
     * 統合側の広告データの取得
     * @param $mst_company_id int 会社のID
     * @return array
     */
    public static function getDiskMailAdvertisement(int $mst_company_id): array
    {
        $disk_mail_adver_ids = DB::table('advertisement_management')
            ->select('mst_advertisement_id')
            ->where('mst_company_id',$mst_company_id)
            ->pluck('mst_advertisement_id')->toArray();

        $advertisements = ['top_advertisement' => '','middle_advertisement' => '','end_advertisement' => ''];

        if (!$disk_mail_adver_ids) return $advertisements;

        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client){
            Log::error('Cannot connect to ID App');
            return $advertisements;
        }

        $response = $client->post("advertisement",[
            RequestOptions::JSON => [
                'id' => "[".implode(',',  array_values($disk_mail_adver_ids))."]",
                'is_public' => 1,
                'disk_mail' => true
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $mstAdvertisements = json_decode((string) $response->getBody())->data;

            if ($mstAdvertisements){
                collect($mstAdvertisements)->map(function ($item) use(&$advertisements){
                    if ($item->position == self::LOCATION_TOP){
                        $advertisements['top_advertisement'] = ['url' => $item->url ,'path' => self::putContentToFile($item->banner_src,self::LOCATION_TOP)];
                    }elseif ($item->position == self::LOCATION_MIDDLE){
                        $advertisements['middle_advertisement'] = ['url' => $item->url,'path' => self::putContentToFile($item->banner_src,self::LOCATION_MIDDLE)];
                    }elseif ($item->position == self::LOCATION_END){
                        $advertisements['end_advertisement'] = ['url' => $item->url, 'path' => self::putContentToFile($item->banner_src,self::LOCATION_END)];
                    }
                });
            }
            return $advertisements;
        } else {
            Log::error('Get Advertisement statusCode ' . $response->getStatusCode() . ' and response body ' . $response->getBody());
            return ['top_advertisement' => '','middle_advertisement' => '','end_advertisement' => ''];
        }
    }

    /**
     * 広告ファイルパスの取得
     * @param $data string 画像データ
     * @param $type int 画像の位置
     * @return string 画像のパス
     */
    public static function putContentToFile($data, int $type): string
    {
        $path = storage_path("app/disk_mail_advertisement/");
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }
        if ($type == self::LOCATION_TOP){
            $path = $path . 'top.png';
        }elseif ($type == self::LOCATION_MIDDLE){
            $path = $path . 'middle.png';
        }elseif ($type == self::LOCATION_END){
            $path = $path . 'end.png';
        }
        if (!File::exists($path) || (File::exists($path) && md5(base64_decode($data)) != md5(file_get_contents($path)))){
            file_put_contents($path, base64_decode($data));
        }

        return $path;
    }
}