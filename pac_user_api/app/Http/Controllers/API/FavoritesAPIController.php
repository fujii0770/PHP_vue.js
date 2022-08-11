<?php

namespace App\Http\Controllers\API;

use App\Http\Utils\AppUtils;
use App\Http\Utils\EnvApiUtils;
use App\Models\Favorite;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class FavoriteAPIController
 * @package App\Http\Controllers\API
 */

class FavoritesAPIController extends AppBaseController
{
    var $table = 'favorite_route';
    var $model = null;

    public function __construct(Favorite $favorite)
    {
        $this->model = $favorite;
    }

    /**
     * Display a listing of the Favorite.
     * GET|HEAD /favorites
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user(); 
        $strFavoriteName = $request->get('favorite_name');
        /* PAC_5-1982 S */
        $favoriteFlg = $request->get('favorite_flg')?AppUtils::FAVORITE_FLG_VIEW:AppUtils::FAVORITE_FLG_DEFAULT;
        /* PAC_5-1982 S */
        if(!$user || !$user->id) {
            $user = $request['user'];
        }       
        $objCurrentModel = $this->model;
        if(!empty($strFavoriteName)){
            $strFavoriteName = str_replace(["%","_"],["\%","\_"],$strFavoriteName);
            $objCurrentModel = $objCurrentModel->where("favorite_name",'like',"%$strFavoriteName%");
        }
        /* PAC_5-1982 favorite_flg お気に入り登録:0:宛先、回覧順｜1:閲覧ユーザー設定 U */
        $arrFavorite = $objCurrentModel->where('mst_user_id', $user->id)->where('favorite_flg', $favoriteFlg)->orderBy('favorite_no','asc')
            ->orderBy('display_no','asc')
            ->get();

        if(count($arrFavorite)){
            $arrNew = [];
            foreach($arrFavorite as $favorite){
                $arrNew[$favorite->favorite_no][] = $favorite;
            }
            //合議判定
            foreach ($arrNew as $favorite_no){
                foreach ($favorite_no as $item){
                    if ($item->child_send_order > 0){
                        array_map(function ($arr){
                            $arr->use_plan_flg = 1;
                            return $arr;
                        },$favorite_no);
                        break;
                    }
                }
            }
            $arrFavorite = array_values($arrNew);
        }
        return $this->sendResponse($arrFavorite, 'Favorite retrieved successfully');
    }

    /**
     * Store a newly created Favorite in storage.
     * POST /favorites
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $user           = $request->user();
        $favorite_no    = $this->model->where('mst_user_id', $user->id)->max('favorite_no');
        if(!$favorite_no) $favorite_no = 1;
        else $favorite_no++;

        $items = $request->get('items');
        $planIds=array_column($items,"plan_id");
        $plans=DB::table("circular_user_plan")->select(["id","mode","score"])->whereIn('id',$planIds)->get()->keyBy("id");
        $planInfoItems=[];
        /* PAC_5-1982 S*/
        //ユーザーが存在
        if(isset($items[0]['favorite_flg']) && $items[0]['favorite_flg']){
            $searchUsers = collect();
            //viewuser get env 
            $email_env_flg      = config('app.server_env');
            $email_edition_flg  = config('app.edition_flg');
            $email_server_flg   = config('app.server_flg');
            $arr_email_user_id = array_column($items,'email_user_id');
            $searchUsers = DB::table("mst_user")
                ->select(["id","email","mst_company_id"])
                ->whereIn('id',$arr_email_user_id)
                ->where('mst_company_id', $items[0]["email_company_id"])
                ->where('state_flg', AppUtils::STATE_VALID)
                ->get();
        } 
        /* PAC_5-1982 E*/
        foreach($items as $i => &$item){
            $item['mst_user_id']    = $user->id;
            $item['favorite_no']    = $favorite_no;
            $item['display_no']     = $i;
            $item['create_at']      = Carbon::now();
            $item['mode']           = isset($plans[$item['plan_id']])?$plans[$item['plan_id']]->mode:null;
            $item['score']           = isset($plans[$item['plan_id']])?$plans[$item['plan_id']]->score:null;
            /* PAC_5-1982 S*/
            $item['favorite_flg']   = isset($item['favorite_flg'])?$item['favorite_flg']:0;
            if($item['favorite_flg']) {
                
                if (!$searchUsers->contains('email', $item['email'])){
                    return $this->sendError($item['email']."閲覧のユーザーが存在しません",\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $searchUsers->some(function($su) use ($item) {
                    if ($su->email == $item['email']) {
                        if ($su->id == $item['email_user_id'] && $su->mst_company_id == $item['email_company_id']) {
                            return true;
                        }else{
                            return $this->sendError($item['email']."閲覧のユーザーが存在しません",\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                });
                $item['email_env_flg']      = $email_env_flg;
                $item['email_edition_flg']  = $email_edition_flg;
                $item['email_server_flg']   = $email_server_flg;
            }
            /* PAC_5-1982 E*/
            unset($item['plan_id']);
        }
        try{
            $this->model->insert($items);           
            return $this->sendResponse($items, 'Favorite add successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified Favorite from storage.
     * DELETE /favorites/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($favorite_no, Request $request)
    {
        $user   = $request->user();
        /* PAC_5-1982 S*/
        $favorite_flg=$request->input('favorite_flg',0);
        /* PAC_5-1982 E*/
        if(!$user || !$user->id) {
            $user = $request['user'];
        }    
        try{
            $this->model->where('mst_user_id', $user->id)->where('favorite_no', $favorite_no)
                /* PAC_5-1982 S*/
                ->where('favorite_flg', $favorite_flg)
                /* PAC_5-1982 E*/
                ->delete();
            return $this->sendSuccess('Favorite delete successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sort the specified Favorite from storage.
     * POST /favorites/sort
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function sort(Request $request)
    {
        $user   = $request->user();
        $sorts = $request->get('sorts');
        try{
            DB::beginTransaction();
            foreach($sorts as $i => $favoritesID){
                DB::table('favorite_route')->where('mst_user_id', $user->id)
                        ->whereIn('id', $favoritesID)
                        ->update(['favorite_no' => $i])
                            ;
            }
            DB::commit();
            return $this->sendSuccess('Favorite sort successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * お気に入りの編集
     * @param $favorite_no int favorite_route.favorite_no
     * @param Request $request
     * -------------------------------------
     * { users:{0:{通常のユーザー
     *             id:'IDMのユーザID',
     *             user_id:'各環境のmst_user.id',
     *　           company_id:'会社のID',
     *　           company_name:'会社の名前',
     *             email:'ユーザのメールアドレス',
     *             name:'ユーザの名前',
     *             user_auth:'ユーザーの種類',
     *             edition_flg:'現行|新NE',
     *             env_flg:'AWS|K5',
     *             server_flg:'サーバフラグ',
     *             system_name:'契約Edition',
     *            }
     *          ,
     *          1：{ゲストユーザ
     *             email:'ユーザのメールアドレス',
     *             user_id: null
     *          }，...}
     * }
     * -------------------------------------
     * @return mixed
     */
    public function update(int $favorite_no, Request $request){

        $user = $request->user();

        $add_users = $request->get('users');
        if (!$user) $user = $request->get('user');
        //環境情報
        $system_edition_flg = config('app.edition_flg');
        $system_env_flg     = config('app.server_env');
        $system_server_flg  = config('app.server_flg');
        try {
            //最大のソートID
            $last_favorite = DB::table('favorite_route')
                ->where('mst_user_id', $user->id)
                ->where('favorite_no',$favorite_no)
                ->orderBy('display_no','desc')->first();
            $display_no = $last_favorite->display_no;
            $add_favorites = [];
            foreach ($add_users as $add_user){
                $item = [
                    'mst_user_id' => $user->id,
                    'favorite_no' => $favorite_no,
                    'display_no' => ++$display_no,
                    'name' => isset($add_user['name']) ? $add_user['name'] : '',
                    'email' => $add_user['email'],
                    'email_company_id' => isset($add_user['company_id']) ? $add_user['company_id'] : null,
                    'email_company_name' => isset($add_user['company_name']) ? $add_user['company_name'] : null,
                    'email_edition_flg' => isset($add_user['edition_flg']) ? $add_user['edition_flg'] : $system_edition_flg,
                    'email_env_flg' => isset($add_user['env_flg']) ? $add_user['env_flg'] : $system_env_flg,
                    'email_server_flg' => isset($add_user['server_flg']) ? $add_user['server_flg'] : $system_server_flg,
                    'favorite_name' => $last_favorite->favorite_name,
                    'favorite_flg' => $last_favorite->favorite_flg,
                    'create_at' => Carbon::now(),
                ];
                //PublicDmain || ゲストユーザ
                if ((isset($add_user['edition_flg']) && $add_user['edition_flg'] != $system_edition_flg) || !$add_user['user_id']){
                    $item['email_user_id'] = null;
                    $add_favorites[] = $item;
                }else if (isset($add_user['user_id']) && $add_user['env_flg'] == $system_env_flg && $add_user['server_flg'] == $system_server_flg){//現在の環境
                    $user_info = DB::table('mst_user')->select('id')->where('email',$add_user['email'])->where('state_flg', AppUtils::STATE_VALID)->first();
                    if ($user_info){
                        $item['email_user_id'] = $user_info->id;
                        $add_favorites[] = $item;
                    }
                }else if (isset($add_user['user_id']) && ($add_user['env_flg'] != $system_env_flg || $add_user['server_flg'] != $system_server_flg)){//その他の環境
                    $envClient = EnvApiUtils::getAuthorizeClient($add_user['env_flg'], $add_user['server_flg']);
                    if (!$envClient){
                        Log::error('Cannot connect to Env Api');
                        return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $response = $envClient->get("getUserInfo/" . $add_user['email'], []);
                    if ($response->getStatusCode() == Response::HTTP_OK) {
                        $envUserInfo = json_decode($response->getBody())->data;
                        if ($envUserInfo) {
                            $item['email_user_id'] = $envUserInfo->mst_user_id;
                            $add_favorites[] = $item;
                        }
                    } else {
                        Log::warning('Cannot get Env UserInfo from other env');
                        Log::warning($response->getBody());
                    }
                }
            }
            if ($add_favorites){
                DB::table('favorite_route')->insert($add_favorites);
            }
            $favorites = DB::table('favorite_route')
                ->where('mst_user_id', $user->id)
                ->where('favorite_no',$favorite_no)
                ->orderBy('display_no','asc')
                ->get();
            return $this->sendResponse($favorites,__('message.success.favorite_update'));
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.favorite_update'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 回覧順編集
     * @param Request $request
     * @return mixed
     */
    public function sortFavoriteItem(Request $request){
        try {
            $user = $request->user();
            if (!$user) $user = $request->get('user');

            $from_favorite = $request->get('from_favorite');
            $to_favorite   = $request->get('to_favorite');
            DB::beginTransaction();

            DB::table('favorite_route')
                ->where('id',$from_favorite['id'])
                ->update(['display_no' => $to_favorite['display_no']]);

            DB::table('favorite_route')
                ->where('id',$to_favorite['id'])
                ->update(['display_no' => $from_favorite['display_no']]);

            DB::commit();
            $favorite_routes = DB::table('favorite_route')
                ->where('mst_user_id',$user->id)
                ->where('favorite_no',$from_favorite['favorite_no'])
                ->orderBy('display_no','asc')
                ->get();

            return $this->sendResponse($favorite_routes,__('message.success.favorite_item_sort'));
        }catch (\Exception $ex){
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.favorite_item_sort'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 回覧順の変更
     * @param Request $request
     * @return mixed
     */
    public function deleteFavoriteItem($favorite_route_id,Request $request){
        try {
            $user = $request->user();
            if (!$user) $user = $request->get('user');
            $sort = 1;

            //削除回覧ユーザー
            $delete_item =  DB::table('favorite_route')->where('id', $favorite_route_id)->first();
            if (!$delete_item)
                return $this->sendError(__('message.false.favorite_item_delete'), Response::HTTP_INTERNAL_SERVER_ERROR);

            $favorite_no = $delete_item->favorite_no;
            //回覧順序の更新が必要なユーザー
            $update_items = DB::table('favorite_route')
                ->where('mst_user_id', $user->id)
                ->where('favorite_no', $delete_item->favorite_no)
                ->where('display_no','>',$delete_item->display_no)
                ->get();

            DB::beginTransaction();

            $update_items->each(function ($update_item) use(&$sort){
               DB::table('favorite_route')
                   ->where('id',$update_item->id)
                   ->update([
                       'display_no' => $update_item->display_no - $sort
                   ]);
            });
            DB::table('favorite_route')->where('id',$favorite_route_id)->delete();
            DB::commit();
            $favorite_routes = DB::table('favorite_route')
                ->where('mst_user_id',$user->id)
                ->where('favorite_no',$favorite_no)
                ->orderBy('display_no','asc')
                ->get();
            return $this->sendResponse($favorite_routes,__('message.success.favorite_item_delete'));
        }catch (\Exception $ex){
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(__('message.false.favorite_item_delete'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
