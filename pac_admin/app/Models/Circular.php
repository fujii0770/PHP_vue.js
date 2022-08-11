<?php

namespace App\Models;

use App\Http\Utils\AppUtils;
use App\Http\Utils\CircularAttachmentUtils;
use App\Http\Utils\CircularUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Circular extends Model
{
    protected $table = 'circular';

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_user_id',
        'access_code_flg',
        'access_code',
        'outside_access_code_flg',
        'outside_access_code',
        'hide_thumbnail_flg',
        're_notification_day',
        'circular_status',
        'create_at',
        'create_user',
        'update_at',
        'update_user',
        'address_change_flg',
        'first_page_data',
        'env_flg',
        'edition_flg',
        'server_flg',
        'origin_circular_id',
        'current_aws_circular_id',
        'current_k5_circular_id',
        'applied_date',
        'completed_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [  ];

    public function rules(){
        return [ ];
    }

    /**
     * 1月ユーザー数（アクティビティ＋アクティビティ率）
     * @param $month int|string finished month format '202204' || ''
     * @param $targetDay string format '2022-04-07'
     * @return mixed
     */
    public function getCircularUserCount($month, $targetDay){
        return DB::query()->fromSub(function ($query) use ($month, $targetDay){
            $query->from("circular_user$month")
                ->where('env_flg',config('app.pac_app_env'))
                ->where('edition_flg',config('app.pac_contract_app'))
                ->where('server_flg',config('app.pac_contract_server'))
                ->where(function($query) use ($targetDay){
                    $query->where(DB::raw("DATE_FORMAT(create_at, '%Y-%m-%d')"),'=',$targetDay)
                        ->orWhere(function($query) use ($targetDay) {
                            $query->where(DB::raw("DATE_FORMAT(update_at, '%Y-%m-%d')"),'=' ,$targetDay);
                        });
                })->whereIn('circular_status',[3,4,5])
                ->groupBy('mst_company_id','email')
                ->select('mst_company_id','email');
        }, 'T1')->groupBy('mst_company_id')->selectRaw('mst_company_id,count(mst_company_id) as activity_user_cnt')
            ->get();
    }

    /**
     * 1月ドキュメントデータ容量
     * @param $month int|string finished month format '202204' || ''
     * @param $now string 現在の時刻
     * @param int $company_id 会社のID
     * @return Collection
     */
    public function getCircularDocumentDataSize($month, $now, int $company_id = 0): Collection
    {
        return DB::table("circular_document$month as circular_document")
            ->select(DB::raw('create_company_id, sum(file_size) as storage_size'))
            ->join("circular$month as circular", 'circular.id', 'circular_document.circular_id')
            ->where('circular.circular_status', '!=', CircularUtils::DELETE_STATUS)
            ->where('origin_edition_flg', config('app.pac_contract_app'))
            ->where('origin_env_flg', config('app.pac_app_env'))
            ->where('origin_server_flg', config('app.pac_contract_server'))
            ->where(function ($query) use($month, $now){
                if (!$month){
                    $query->whereNull('circular.completed_date')
                        ->orWhere('circular.completed_date','>',Carbon::parse($now)->firstOfMonth());
                }
            })
            ->where(function ($query) use($company_id){
                if ($company_id)
                    $query->where('create_company_id', $company_id);
            })
            ->groupBy('create_company_id')
            ->get();
    }

    /**
     * 1月添付ファイルデータ容量
     * @param $month int|string finished month format '202204' || ''
     * @param $now string 現在の時刻
     * @param int $company_id 会社のID
     * @return Collection
     */
    public function getCircularAttachmentSize($month, $now,int $company_id = 0): Collection
    {
        return DB::table('circular_attachment')
            ->select(DB::raw('create_company_id,sum(file_size) as storage_size'))
            ->join("circular$month as circular", 'circular.id', 'circular_attachment.circular_id')
            ->where('circular.circular_status', '!=', CircularUtils::DELETE_STATUS)
            ->where('status','!=',CircularAttachmentUtils::ATTACHMENT_DELETE_STATUS)
            ->where('circular_attachment.edition_flg',config('app.pac_contract_app'))
            ->where('circular_attachment.env_flg',config('app.pac_app_env'))
            ->where('circular_attachment.server_flg',config('app.pac_contract_server'))
            ->where(function ($query) use($month, $now){
                if (!$month){
                    $query->whereNull('circular.completed_date')
                        ->orWhere('circular.completed_date','>',Carbon::parse($now)->firstOfMonth());
                }
            })
            ->where(function ($query) use($company_id){
                if ($company_id)
                    $query->where('create_company_id', $company_id);
            })
            ->groupBy('create_company_id')
            ->get();
    }

    /**
     * 1月社外経由数（送信）
     * @param $month int|string finished month format '202204' || ''
     * @param $targetDay string format '2022-04-07'
     * @return Collection
     */
    public function getCircularOutsideSendCount($month,$targetDay): Collection
    {
        return DB::table("circular$month as circular")
            ->join('mst_user','circular.mst_user_id','=','mst_user.id')
            // 当日申請
            ->where(DB::raw("DATE_FORMAT(circular.applied_date, '%Y-%m-%d')"), '=', $targetDay)
            // 会社跨ぐ
            ->whereExists(function ($query) use($month){
                $query->select(DB::raw(1))
                    ->from("circular_user$month as circular_user")
                    ->whereRaw('circular.id = circular_user.circular_id')
                    ->groupBy('circular_id')
                    ->havingRaw('max(parent_send_order) <> 0');
            })
            // 当環境申請
            ->where('circular.edition_flg',config('app.pac_contract_app'))
            ->where('circular.env_flg',config('app.pac_app_env'))
            ->where('circular.server_flg',config('app.pac_contract_server'))
            ->select(['mst_company_id', DB::raw('count(circular.id) as request_count')])
            ->groupBy('mst_company_id')
            ->get();
    }

    /**
     * 1月社外経由数（受信）
     * @param $month int|string finished month format '202204' || ''
     * @param $targetDay string format '2022-04-07'
     * @return Collection
     */
    public function getCircularOutsideReceiveCount($month,$targetDay): Collection
    {
        $query =  DB::table("circular$month as circular")
            ->join("circular_user$month as circular_user",'circular.id','=','circular_user.circular_id')
            // 当日申請
            ->where(DB::raw("DATE_FORMAT(circular.applied_date, '%Y-%m-%d')"), '=', $targetDay)
            // 会社跨ぐ
            ->whereExists(function ($query) use($month){
                $query->select(DB::raw(1))
                    ->from("circular_user$month as circular_user")
                    ->whereRaw('circular.id = circular_user.circular_id')
                    ->groupBy('circular_id')
                    ->havingRaw('max(parent_send_order) <> 0');
            })
            // 当環境回覧ユーザー
            ->where('circular_user.edition_flg',config('app.pac_contract_app'))
            ->where('circular_user.env_flg',config('app.pac_app_env'))
            ->where('circular_user.server_flg',config('app.pac_contract_server'))
            // 申請会社以外
            ->where('circular_user.parent_send_order','<>',DB::raw('0'))
            ->select(['mst_company_id', 'circular.id'])
            ->groupBy('mst_company_id','circular.id');
        return DB::query()->fromSub($query,'T')
            ->select(['mst_company_id', DB::raw('count(T.id) as request_count')])
            ->groupBy('T.mst_company_id')
            ->get();
    }
}
