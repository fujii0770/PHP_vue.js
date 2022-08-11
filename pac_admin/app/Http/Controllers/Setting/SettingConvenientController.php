<?php

namespace App\Http\Controllers\Setting;

use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\AppUtils;
use App\Models\StampConvenient;
use App\Models\User;
use Carbon\Carbon;
use Defuse\Crypto\File;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\CompanyStamp;
use App\Models\StampConvenientDivision;
use Auth;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class SettingConvenientController extends AdminController
{

    private $model;

    private $stampConvenient;

    public function __construct(CompanyStamp $model,StampConvenient $stampConvenient)
    {
        parent::__construct();
        $this->model = $model;
        $this->stampConvenient = $stampConvenient;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $divisionList = StampConvenientDivision::where('del_flg',0)->get();
        // PAC_5-2332 add S
        $one='';
        foreach ($divisionList as $key=>$val){
            if($val->id==4){
                $one=$val;
                unset($divisionList[$key]);
                $divisionList[]=$one;
            }
        }
        // PAC_5-2332 E
        $this->assign('divisionList', $divisionList);

        $this->setMetaTitle("便利印設定");
        $this->addStyleSheet('select2', asset("/css/select2@4.0.12/dist/select2.min.css"));
        $this->addScript('select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js');
        $this->addScript('select2-init', '$(\'.select-2\').select2();', false);
        //色の選択用のライブラリを追加
        $this->addScript('jscolor', asset("/js/libs/jscolor.js"));
        return $this->render('SettingConvenient.index');
    }

    public function search(Request $request)
    {
        $name = $request->get('name','');
        $stamp_division = $request->get('stamp_division','');
        $where_arg = [];
        $where = ['1=1'];
        $per_page = $request->get('limit') ? $request->get('limit') : 10;

        if($name){
            $where[] = 'stamp_name like ?';
            $where_arg[] = "%$name%";
        }
        if($stamp_division){
            $where[] = 'stamp_division = ?';
            $where_arg[] = $stamp_division;
        }

        $items = $this->stampConvenient
            ->whereRaw(implode(" AND ", $where), $where_arg)->where('del_flg', 0)->paginate($per_page);

        foreach ($items as $key => $item) {
            if ($item->stamp_date_flg != 0){
                $item->stamp_image = $this->convenientStampWithDate($item);
            }
            $items[$key]['background_color'] = $items[$key]['date_color'];
            $items[$key]['date_color'] = \App\Http\Utils\AppUtils::changeDateColorLists($items[$key]);
        }
        return response()->json(['status' => true, 'items' => $items]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();

        $id = intval($request->get('id'));

        $item = $this->stampConvenient->find($id);
        if(!$item){
            return response()->json(['status' => false, 'message' => [__('message.false.save_convenient_stamp')] ]);
        }

        $item->stamp_name = trim($request->get('stamp_name'));
        $item->stamp_division = trim($request->get('stamp_division'));
        $item->update_user = $user->getFullName();
        $item->stamp_date_flg = $request->get('stamp_date_flg');
        $item->date_dpi = $request->get('date_dpi');
        $item->date_x = $request->get('date_x');
        $item->date_y = $request->get('date_y');
        $item->date_width = $request->get('date_width');
        $item->date_height = $request->get('date_height');
        $item->date_color = $request->get('date_color');

        $validator = Validator::make($item->toArray(), $this->stampConvenient->rules($id));

        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        if ($request->get('stamp_date_flg') != 0){
            // 日付で表示します
            $show_stamp_image = $this->convenientStampWithDate($item);
        }else{
            $show_stamp_image = $item->stamp_image;
        }

        DB::beginTransaction();
        try{
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['status' => false,'show_stamp_image' => $item->stamp_image,'message' => [__('message.false.save_convenient_stamp')] ]);
        }
        return response()->json(['status' => true,'show_stamp_image' => $show_stamp_image , 'message' => [__('message.success.save_convenient_stamp')]]);
    }

    public function destroy($id)
    {
        $user = \Auth::user();

        $item = $this->stampConvenient->find($id);
        if(!$item){
            return response()->json(['status' => false, 'message' => [__('message.false.delete_convenient_stamp')] ]);
        }

        DB::beginTransaction();
        try{

            // PAC_5-1770 ▼
            $assignedCompanyStampInfos = DB::table('mst_assign_stamp')
                ->join('mst_company_stamp_convenient', 'mst_company_stamp_convenient.id', '=', 'mst_assign_stamp.stamp_id')
                ->join('mst_user', 'mst_assign_stamp.mst_user_id', '=', 'mst_user.id')
                ->join('mst_company','mst_user.mst_company_id', '=', 'mst_company.id')
                ->select('mst_user.mst_company_id','mst_assign_stamp.mst_user_id','mst_assign_stamp.stamp_flg','mst_assign_stamp.state_flg',
                    'mst_company.contract_edition','mst_company.company_name','mst_company.system_name')
                ->where('mst_company_stamp_convenient.mst_stamp_convenient_id', $id)
                ->where('mst_company_stamp_convenient.del_flg','=',0)
                ->where('mst_assign_stamp.stamp_flg',AppUtils::STAMP_FLG_CONVENIENT)
                ->where('mst_assign_stamp.state_flg',AppUtils::STATE_VALID)
                ->get()
                ->toArray();

            foreach ($assignedCompanyStampInfos as $assignedCompanyStampInfo) {
                $this->updateUserState($assignedCompanyStampInfo, $user);
            }
            // PAC_5-1770 ▲

            $results = DB::table('mst_company_stamp_convenient')
                ->where('mst_stamp_convenient_id',$id)
                ->get()->toArray();

            $ids = array_column($results,'id');

            // 企業便利印マスタを削除する
            DB::table('mst_company_stamp_convenient')
                ->where('mst_stamp_convenient_id',$id)
                ->update(['del_flg' => 1,
                    'update_at' =>  Carbon::now(),
                    'update_user' =>  $user->getFullName(),
                ]);
            //　割当印面マスタを削除する
            DB::table('mst_assign_stamp')
                ->whereIn('stamp_id',$ids)
                ->where('stamp_flg','=',3)
                ->update(['state_flg' => AppUtils::STATE_DELETE,
                    'delete_at' => Carbon::now(),
                    'update_at' =>  Carbon::now(),
                    'update_user' =>  $user->getFullName(),
                    ]);
            $item->del_flg = 1;
            $item->update_user = $user->getFullName();
            $item->save();
            DB::commit();
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            DB::rollBack();
            return response()->json(['status' => false, 'message' => [__('message.false.delete_convenient_stamp')] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.delete_convenient_stamp')]]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    function uploadStamps(Request $request){
        $user = \Auth::user();

        $items = $request->get('items');

        foreach($items as $item_info){
            $item = new $this->stampConvenient;
            $fileName = $item_info['filename'];
            if(!str_ends_with($fileName,'.png')){
                $image = Image::make($item_info['stamp_image']);
                $image->encode('png');
                $imageBase64 = (string) $image->encode('data-url');
                $imageBase64 = explode(',', $imageBase64);
                $item_info['stamp_image'] = $imageBase64[1];
            }
            $item->fill($item_info);
            $item->stamp_name = $item->stamp_name;
            $item->del_flg = 0;
            $item->create_user = $user->getFullName();

            DB::beginTransaction();
            try{
                $item->save();

                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                Log::error($e->getMessage().$e->getTraceAsString());
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
            }
        }

        return response()->json(['status' => true, 'id' => isset($item)?$item->id:0,'message' => [__('message.success.save_convenient_stamp')]
        ]);
    }

    private function updateUserState($assignedCompanyStampInfo, $user){
        $User = new User();
        $itemUser = $User->find($assignedCompanyStampInfo->mst_user_id);
        $stamps = $itemUser->getStamps($assignedCompanyStampInfo->mst_user_id);
        // 利用者設定画面＆共通印割当共通ロジック
        // Business以上 + 氏名印または日付印すべて削除された ＋ 共通印割当なし場合
        // 利用者を無効に更新
        $updateFlg = false;
        if($assignedCompanyStampInfo->contract_edition == 1 || $assignedCompanyStampInfo->contract_edition == 2){
            // Business以上
            // 氏名印または日付印 ＋ 共通印　＋　部署印　＝　1
            // 削除後、ゼロ件
            if((count($stamps['stampMaster']) + count($stamps['stampCompany']) + count($stamps['stampDepartment'])) == 1){
                if($assignedCompanyStampInfo->stamp_flg == 2){
                    // 部署印の場合、
                    // 作成中の部署印を削除する場合、なにもしない
                    // バッチ後の部署印を削除する場合、判定要
                    if($assignedCompanyStampInfo->state_flg != 2){
                        if($itemUser->state_flg == 1){
                            $updateFlg = true;
                        }
                    }
                }else{
                    // 部署印以外の場合、
                    if($itemUser->state_flg == 1){
                        $updateFlg = true;
                    }
                }
            }
        }else{
            // Business以上 以外
            // 氏名印または日付印　＝　1 ＋　通常印を削除する場合（共通印数を０になる）
            if(count($stamps['stampMaster']) == 1 && $assignedCompanyStampInfo->stamp_flg == 0){
                if($itemUser->state_flg == 1){
                    $updateFlg = true;
                }
            }
        }

        if($updateFlg){
            // 有効の場合、無効に更新
            if($itemUser->password){
                // パスワード設定済み
                $itemUser->state_flg = 9;
            }else{
                // パスワード未設定
                $itemUser->state_flg = 0;
            }
        }

        if($updateFlg){
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client){
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $apiUser = [
                "user_email" => $itemUser->email,
                "email"=> strtolower($itemUser->email),
                "contract_app"=> config('app.pac_contract_app'),
                "app_env"=> config('app.pac_app_env'),
                "contract_server"=> config('app.pac_contract_server'),
                "user_auth"=> AppUtils::AUTH_FLG_USER,
                "user_first_name"=> $itemUser->given_name,
                "user_last_name"=> $itemUser->family_name,
                "company_name"=> $assignedCompanyStampInfo->company_name,
                "company_id"=> $assignedCompanyStampInfo->mst_company_id,
                "status"=> AppUtils::convertState($itemUser->state_flg),
                "system_name"=> $assignedCompanyStampInfo->system_name,
                "update_user_email"=> $user->email,
            ];

            $itemUser->save();

            Log::debug("Call ID App Api to create company user");
            $apiUser['create_user_email'] = $user->email;
            $result = $client->put("users",[
                RequestOptions::JSON => $apiUser
            ]);

            if($result->getStatusCode() != 200) {
                DB::rollBack();
                Log::warning("Call ID App Api to create company user failed. Response Body ".$result->getBody());
                $response = json_decode((string) $result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message],
                    'errors' => isset($response->errors)?$response->errors:[]
                ]);
            }
        }
    }

    /**
     * @param $stamp
     * @return string
     */
    public function convenientStampWithDate($stamp){
        $date = date("Y").'/01/01';
        $date_stamp_style = 'y.m.d';
        $date = \App\Http\Utils\DateJPUtils::convert($date, $date_stamp_style);
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
}
