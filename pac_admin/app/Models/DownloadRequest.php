<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DownloadRequest extends Model
{
    protected $table = 'download_request';
    
    const CREATED_AT = NULL;
    
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'mst_company_id',
        'mst_user_id',
        'user_auth',
        'file_name',
        'request_date',
        'state',
        'contents_create_at',
        'download_period',
        'retry_cnt',
        'sanitizing_request_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules($id){
        return [
            'id' => 'required|numeric',
            'mst_company_id' => 'required|numeric',
            'mst_user_id' => 'required|numeric',
            'user_auth' => 'required|numeric',
            'file_name' => 'nullable|string|max:256',
            'request_date' => 'required',
            'state' => 'required|numeric',
            'contents_create_at' => 'nullable',
            'download_period' => 'nullable',
            'retry_cnt' => 'nullable|numeric',
            'sanitizing_request_at' => 'nullable'
        ];
    }

    public static function enableDownloadRequestLimit($user_id, $dl_request_limit){
        // 保有可能ダウンロード要求数
        $dl_req = self::where('mst_user_id', $user_id)
            ->where('user_auth', AppUtils::AUTH_FLG_ADMIN)
            ->where('state', '!=', DownloadUtils::REQUEST_DELETED)
            ->get();

        if ($dl_request_limit !== 0 && $dl_req->count() >= $dl_request_limit) {
            return false;
        }

        return true;
    }

    public static function enableDownloadRequestLimitPerOneHour($user_id, $dl_request_limit_per_one_hour){
        // 現在から一時間以内のダウンロード要求
        // timestamp のためタイムゾーン注意
        $now_db_timezone = DB::select("SELECT CURRENT_TIMESTAMP")[0]->CURRENT_TIMESTAMP;
        $dl_req = self::where('mst_user_id', $user_id)
            ->where('user_auth', AppUtils::AUTH_FLG_ADMIN)
            ->where('request_date', '>', (new Carbon($now_db_timezone))->subHour())
            ->get();

        if ($dl_request_limit_per_one_hour !== 0 && $dl_req->count() >= $dl_request_limit_per_one_hour) {
            return false;
        }

        return true;
    }
}
