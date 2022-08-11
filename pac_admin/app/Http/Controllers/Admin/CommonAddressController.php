<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Http\Utils\AppUtils;
use App\Http\Utils\CommonUtils;
use App\Http\Utils\PermissionUtils;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Session;
use Auth;
use Carbon\Carbon;
use App\Models\Company;

class CommonAddressController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $action = $request->get('action','');
        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        // 無害化処理設定時はCSVダウンロード無効化するためのフラグ TODO 非同期化と無害化
        $sanitizing_flg = Company::where('id', $user->mst_company_id)
                            ->first()->sanitizing_flg;
        if(!array_search($limit, config('app.page_list_limit'))){
            $limit = config('app.page_limit');
        }

        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';
        $query = [];
 
        if($action != ""){
            // PAC_5-1978 共通アドレス帳のCSVを全件出力できるように修正する Start
            $query = DB::table('address');
            if($action == "export" && !empty($request->get('cid'))){
                $query = $query->whereIn('id', $request->get('cid'));
            }else{
                if($request->name){
                    $query = $query->where('name', 'like', '%' . $request->name . '%');
                }
                if($request->email){
                    $query = $query->where('email', 'like', '%' . $request->email . '%');
                }
                if($request->company_name){
                    $query = $query->where('company_name', 'like', '%' . $request->company_name . '%');
                }
                if($request->position){
                    $query = $query->where('position_name', 'like', '%' . $request->position . '%');
                }
                $query = $query->where('type','1')->where('mst_company_id',$user->mst_company_id)->orderBy($orderBy,$orderDir);
            } 
            if ($action == 'export') {
                $query = $query->get()->toArray();
            } else {
                $query = $query->paginate($limit)->appends(request()->input());
            }
            // PAC_5-1978 End
            $orderDir = strtolower($orderDir)=="asc"?"desc":"asc";
        }
        $total = count($query);
        $this->assign('query', $query);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('total', $total);
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_USER_SETTINGS_UPDATE));
        $this->assign('sanitizing_flg', $sanitizing_flg);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->setMetaTitle("共通アドレス帳");

        if($action == 'export'){
            return $this->render('SettingAddress.csv');
         }else{
            return $this->render('SettingAddress.index');
         }
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        $currentTime = Carbon::now();
        $item_address = $request->get('item');
        $validator = Validator::make($request->get('item'), [
            'email' => 'required|email|max:256',
            'name' => 'required|max:128',
            'company_name' => 'max:256',
            'position_name' => 'max:256',
        ]);
        
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }else{
            try{
                $id = DB::table('address')->insertGetId([
                    'email' => $item_address['email'], 
                    'name' => $item_address['name'], 
                    'company_name' => $item_address['company_name'], 
                    'position_name' => $item_address['position_name'],
                    'type'=>'1',
                    'create_user'=> $user->getFullName(),
                    'mst_company_id' =>  $user->mst_company_id,
                    'create_at' => $currentTime,

                ]);
                Session::flash('id', $id);
                return response()->json(['status' => true,'message' => [__('message.success.create_common_address')]
                    ]);
            }catch(\Exception $e){
                DB::rollBack();
                Log::error($e->getMessage().$e->getTraceAsString());
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
            }
        }   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \Auth::user();
        $item =  DB::table('address')
                ->where('id',$id)
                ->where('mst_company_id',$user->mst_company_id)
                ->first();
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'item' => $item]);
        //

        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = \Auth::user();
        $currentTime = Carbon::now();
        $item_address = $request->get('item');
        $item =  DB::table('address')
                ->where('id',$id)
                ->where('mst_company_id',$user->mst_company_id)
                ->first();
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $validator = Validator::make($request->get('item'), [
            'email' => 'required|email|max:256',
            'name' => 'required|max:128',
            'company_name' => 'max:256',
            'position_name' => 'max:256',
        ]);
        
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }else{
            try{
                DB::table('address')
                    ->where('id',$id)
                    ->update([
                        'email' => $item_address['email'], 
                        'name' => $item_address['name'], 
                        'company_name' => $item_address['company_name'], 
                        'position_name' => $item_address['position_name'],
                        'type'=>'1',
                        'update_user'=> $user->getFullName(),
                        'update_at' => $currentTime
                    ]);
                    return response()->json(['status' => true,'message' => [__('message.success.update_common_address')]
                ]);
            }catch(\Exception $e){
            
                DB::rollBack();
                Log::error($e->getMessage().$e->getTraceAsString());
                return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
            }
        }   

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        $item =  DB::table('address')
                ->where('id',$id)
                ->where('mst_company_id',$user->mst_company_id)
                ->first();
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        try{
            DB::table('address')
                ->where('id',$id)
                ->delete();  
                return response()->json(['status' => true,'message' => [__('message.success.delete_common_address')]
            ]);
        }catch(\Exception $e){
        
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }  
    
    public function import(Request $request){
        if ($request->hasFile('file')) {
            $user = \Auth::user();  
            
            $file = $request->file('file');

            $path = $file->getRealPath();
           
            $csv_data = array_map('str_getcsv', file($path));

            // code対応 start
            $str = file_get_contents($file);
            $code = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5', 'SJIS'));

            if ($code == 'CP936' || $code == 'SJIS' || $code == 'SJIS-win') {
                $csv_data = CommonUtils::convertCode('SJIS-win', 'UTF-8', $csv_data);
            }
            // code対応 end

            $total = count($csv_data);

            $num_insert = 0;
            $num_error = 0;
            $arrReason = [];
            $array_address = [];
            $array_error = [];

            if($total){
                foreach($csv_data as $i => $col){
                    $detail_error = [
                        'row'=>$i+1,
                        'col' =>'',
                        'name_error' =>'',
                        'error' => ''
                    ];

                    $data_address = [
                        'type'=>'1',
                        'create_user'=> Auth::user()->getFullName()
                    ];

                    // 列数取得
                    $column_count = sizeof($col);

                    // 列数チェック追加
                    if ($column_count < 3){
                        $detail_error['col'] = '';
                        $detail_error['name_error'] = '';
                        $detail_error['error'] = '行が正しくありません';
                        $arrReason[] = array('行が正しくありません');
                        array_push($array_error,$detail_error);
                        $num_error ++;
                        continue;
                    }

                    // メールアドレス,姓,名,会社名,役職
                    $data_address['email']=isset($col[0])?$col[0]:"";
                    $data_address['name']=isset($col[1])&&isset($col[2])?$col[1].' '.$col[2]:"";
                    $data_address['company_name']=isset($col[3])?$col[3]:"";
                    $data_address['position_name']=isset($col[4])?$col[4]:"";
                    $data_address['mst_company_id']=$user->mst_company_id;

                    // check用
                    $data_address_check = array();
                    $data_address_check['email']=isset($col[0])?$col[0]:"";
                    $data_address_check['family_name']=isset($col[1])?$col[1]:"";
                    $data_address_check['given_name']=isset($col[2])?$col[2]:"";
                    $data_address_check['company_name']=isset($col[3])?$col[3]:"";
                    $data_address_check['position_name']=isset($col[4])?$col[4]:"";

                    $validator = Validator::make($data_address_check, [
                        'email' => 'required|email|max:256',
                        'family_name' => 'required|max:60',
                        'given_name' => 'required|max:60',
                        'company_name' => 'max:256',
                        'position_name' => 'max:256',
                    ]);
                    if ($validator->fails())
                    {
                        $message = $validator->messages();
                        $message_all = $message->all();
                        $arrReason[] = $message_all;
                        $num_error ++;

                        if($message->has('email') == true){
                            $detail_error['col'] = 1;
                            $detail_error['name_error'] = 'メールアドレス';
                        }
                        if($message->has('family_name') == true){
                            $detail_error['col'] = 2;
                            $detail_error['name_error'] = '姓';
                        }
                        if($message->has('given_name') == true){
                            $detail_error['col'] = 3;
                            $detail_error['name_error'] = '名';
                        }
                        if($message->has('company_name') == true){
                            $detail_error['col'] = 4;
                            $detail_error['name_error'] = '会社名';
                        }
                        if($message->has('position_name') == true){
                            $detail_error['col'] = 5;
                            $detail_error['name_error'] = '役職';
                        }

                        $error_all = implode(",", $message_all);
                        $detail_error['error'] = $error_all;
                 
                        array_push($array_error,$detail_error);
                    }else{
                         array_push($array_address,$data_address);
                         $num_insert++;
                    }
                }
                if($array_address && $num_error == 0){
                    DB::beginTransaction();
                    try{
                                    // check if isset
                        $countAddress = DB::table('address')->where('mst_company_id',$user->mst_company_id)->count();
                        if($countAddress > 0){
                            DB::table('address')->where('mst_company_id',$user->mst_company_id)->delete();
                        }
                        DB::table('address')->insert($array_address);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollBack();
                        $num_error ++;
                        $arrReason[] = $e->getMessage();
                    } 
                }
               
            }
        }
        return response()->json(['status' => (count($arrReason) == 0), 'total'=>$total,  'num_insert' => $num_insert,
         'num_error' => $num_error, 'message' => $arrReason , 'error'=> $array_error,'Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function deleteAddress(Request $request){
        $user = \Auth::user();  

        $cids = $request->get('cids',[]);
        $items = [];
        if(count($cids)){
            $items = DB::table('address')            
            ->where('mst_company_id',$user->mst_company_id)
            ->whereIn('id', $cids)
            ->get();
        }
        if(!count($items)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $listID             = [];
        $listEmail          = [];
        $listName           = [];
        $listCompany_name   = [];
        $listPosition_name  = [];
        foreach($items as $item){
            $listID[]               = $item->id;
            $listEmail[]            = $item->email;
            $listName[]             = $item->name;
            $listCompany_name[]     = $item->company_name;
            $listPosition_name[]    = $item->position_name;
        }
        Session::flash('id', $listID);
        Session::flash('email', $listEmail);
        Session::flash('name', $listName);
        Session::flash('company_name', $listCompany_name);
        Session::flash('position_name', $listPosition_name);

        DB::beginTransaction();
        try{
            DB::table('address')
                ->where('mst_company_id',$user->mst_company_id)
                ->whereIn('id', $cids)
                ->delete();  
            DB::commit();
            return response()->json(['status' => true,'message' => [__('message.success.delete_select_common_address')]]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR]] ]);
        }
    }

}
