<?php


namespace App\Chat;

use App\Chat\Exceptions\DataAccessException;
use Closure;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatUtils
{

    private function __construct()
    {
        // Ignore
    }

    /**
     * HTTPクライアントの作成
     *
     * @return Client
     */
    public static function makeHttpClient() {
        $client = new Client([
            'timeout' => config('app.guzzle_timeout'),
            'connect_timeout' => config('app.guzzle_connect_timeout'),
            'http_errors' => false,
            'verify' => false,
        ]);
        return $client;
    }

    /**
     * トランザクション
     */
    public static function transaction(Closure $proc) {
        $ret = null;
        DB::beginTransaction();
        try {
            $ret = $proc();
            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            Log::error ($e); // TODO 冗長な際は削除
            if ($e instanceof DataAccessException) {
                throw $e;
            } else {
                throw new DataAccessException ($e->getMessage(), 0, $e);
            }
        }
        return $ret;
    }

    /**
     * $valueがnullの場合に$nullvalueを返し、nullじゃない場合は$valueをそのまま返します。
     */
    public static function nullvalue($value, $nullvalue) {
        $ret = $value;
        if ($ret === null) {
            $ret = $nullvalue;
        }
        return $ret;
    }
}
