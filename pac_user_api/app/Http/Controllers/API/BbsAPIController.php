<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\AppBaseController;
use App\Http\Utils\ApplicationAuthUtils;
use App\Http\Utils\AppUtils;
use App\Models\User;
use App\Models\Bbs;
use App\Models\BbsCategory;
use App\Models\BbsCategoryUsers;
use Carbon\Carbon;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Self_;

/**
 * Class BbsAPIController
 * @package App\Http\Controllers\API
 */

class BbsAPIController extends AppBaseController
{
    var $table = '';
    var $model = null;

    const EVERYONEAVAILABLE='001'; //すべてのユーザが閲覧、返信可
    const REPRESTRICTION = '002'; //すべてのユーザが閲覧可、所属メンバーのみ返信可
    const VIEWANDREPRESTRICTION='003'; //所属メンバーのみ閲覧、返信可
    const ONLYSINGLEAVAILABLE='004'; //自分のみ閲覧、返信可

    const CONFIG_SERVER_ENV = 'app.server_env';
    const CONFIG_EDITION_FLG = 'app.edition_flg';
    const CONFIG_SERVER_FLG =  'app.server_flg';
    const CONFIG_ROOT_FOLDER ='app.s3_storage_root_folder_bbs';
    const BBS_DIRECTORY = '/Bbs';

    const FILEKBN_TOPIC = '0';
    const FILEKBN_COMMENT = '1';
    const BBS_LIST = '0';
    const BBS_DETAIL = '1';

    const FILE_TOPIC ='Topic';
    const FILE_COMMENT ='Comment';

    const FILENAME_TOPIC = 'Topic_?.json';
    const FILENAME_COMMENT = 'Comment_?_?.json';
    const DIR_MAKE = 0;
    const DIR_CHECKONLY = 1;
    const FILE_NAME_ATTACHMENT = 'BBS_';
    const BBS_NOTICE_READ_STATE_IS_READ = 1;
    const BBS_NOTICE_READ_STATE_NOT_READ = 0;
    const BBS_NOTICE_TYPE_USER = 0;
    const BBS_NOTICE_TYPE_SYSTEM = 1;
    const BBS_STATE_VALID = 1;
    const BBS_STATE_DRAFT = 0;

    private $bbsIdDirectory = '';

    public function __construct()
    {
        $this->bbsIdDirectory =  '/' . config(self::CONFIG_SERVER_ENV) . '/' . config(self::CONFIG_EDITION_FLG) 
            . '/' . config(self::CONFIG_SERVER_FLG);
    
    }

    public function getTopicList(Request $request){

        $procKbn = $request->procKbn;
        switch($procKbn){
            case self::BBS_LIST:
                $ret = $this->getTopicListAll($request, $data);
                break;
            case self::BBS_DETAIL:
                $ret = $this->getTopicListDetail($request, $data);
                break;    
        }
        if (!$ret) return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

        return $this->sendResponse($data, '掲示板トピック取得に成功しました。');
    }

    private function getTopicListAll(Request $request, &$datalist){

        $ret = false;
        $reqdata = $request->all();
        $limitPage = isset($reqdata['limit'])?$reqdata['limit']:10;
        $page = isset($reqdata['page'])?$reqdata['page']:1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        $categoryId = isset($reqdata['categoryId'])?$reqdata['categoryId']:'';
        $keyword = isset($reqdata['keyword'])?$reqdata['keyword']:'';
        $isExpired = isset($reqdata['isExpired']) ? $reqdata['isExpired']: 0;
        $state = isset($reqdata['state']) ? $reqdata['state'] : self::BBS_STATE_VALID;
        try 
        {
            $user = $request->user();
            $userId = $user->id;
            $companyId = $user->mst_company_id;

            $bbsTopicQuery = $this->getBbsTopicAllQuery($userId, $companyId, $categoryId, $keyword, $isExpired, $state);
        
            $datalist = $bbsTopicQuery
            ->paginate($limit)
            ->appends(request()->input());
          
            $ret = true;
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $ret;
        }
        return $ret;

    }
    private function getTopicListDetail(Request $request, &$datadetail){

        $ret = false;
        $reqdata = $request->all();
        $bbsId = isset($reqdata['bbsId'])?$reqdata['bbsId']:'';
        $recalcFlg = isset($reqdata['RecalcFlg'])?$reqdata['RecalcFlg']:0;
        $isExpired = isset($reqdata['isExpired']) ? $reqdata['isExpired']: 0;
        try 
        {
            $user = $request->user();
            $userId = $user->id;
            $companyId = $user->mst_company_id;

            // 無害化処理設定
            $sanitizing_flg = DB::table('mst_company')->where('id', $user->mst_company_id)
                ->first()->sanitizing_flg;
            $bbsTopicQuery = $this->getBbsTopicDetailQuery($userId, $companyId, $bbsId, $isExpired);
            $datadetail = $bbsTopicQuery
                ->first();
            if (!$datadetail) {
                return true;
            }
            
            $userinfos = $this->getUserInfo();  
            $userinfo = $userinfos->where('id', $datadetail->mst_user_id)->first();
            
            $s3path = $datadetail->s3path;

            $datadetail->sanitizing_flg = $sanitizing_flg;
            $datadetail->username = $userinfo->username;
            $datadetail->user_profile_data = $userinfo->user_profile_data;  

            $totalsize = 0;
            $ret = $this->getJsonData($s3path, $bbsId, $user, $userinfos, $recalcFlg, $topicdata, $commentdata, $totalsize);
            if (!$ret) return $ret;       
          
//            if ($recalcFlg == 1 && $datadetail->total_file_size != $totalsize) {
//                DB::beginTransaction();
//                try{
//                    DB::table('bbs')
//                    ->where('id', $bbsId)
//                    ->update(['total_file_size' => $totalsize]);   
//    
//                    DB::commit();
//    
//                }catch (\Exception $ex) {
//                    DB::rollBack();
//                    Log::error($ex->getMessage().$ex->getTraceAsString());
//                    return $ret;
//                }
//            }       

            $datadetail->content = $topicdata['content'];
            $datadetail->filename = $topicdata['filename'];
            $datadetail->attachments = $topicdata['files'];
            $datadetail->files_size = $topicdata['files_size'];
            $datadetail->com_cnt = count($commentdata);
            $datadetail = ['topic'=>$datadetail, 'commentList'=>$commentdata];
            $datadetail['attachmentsNames'] = [];
            $s3files=Storage::disk('s3')->allFiles($s3path);
            foreach ($s3files as $s3file){
                if (Str::startsWith($s3file,$s3path.'/'.self::FILE_NAME_ATTACHMENT)){
                    $datadetail['attachmentsNames'][] = Str::replaceFirst($s3path.'/'.self::FILE_NAME_ATTACHMENT,'',$s3file);
                }
            }
            $ret = true;
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $ret;
        }
        return $ret;

    }
    private function getUserInfo(){
        return DB::table('mst_user as mu')
        ->join('mst_user_info as mui', 'mu.id', 'mui.mst_user_id')
        ->select(DB::raw(
            'mu.id as id'
            .', mui.user_profile_data as user_profile_data'
            .', concat(mu.family_name, mu.given_name) as username')
            )
        ->get();            
    }
    private function getJsonData($s3path, $id, $user, $userinfos, $recalcFlg, &$topic, &$comment, &$totalsize){

        $ret = false;
        try{
            $userId = $user->id;
            $ret = false;
            $ret = $this->checkDirectory(self::DIR_CHECKONLY, $user, $id, $s3path);
            if (!$ret) return $ret;
            
            $files = Storage::disk('s3')->files($s3path);
            $topic = [];
            $comment = [];
            $totalsize = 0;
            foreach($files as $file){
                $name = pathinfo($file);

                if ($recalcFlg == 1) $totalsize +=  Storage::disk('s3')->size($file); 

                $filename = $name['basename'];          
                $file_content = Storage::disk('s3')->get($s3path.'/'.$filename);
                $jsonarr =  json_decode($file_content, true);
                if (strpos($filename, self::FILE_TOPIC) !== false) {
                    $jsonarr['filename'] = $filename;
                    $jsonarr['files'] = isset($jsonarr['files'])?$jsonarr['files'] :[] ;
                    $jsonarr['files_size'] = isset($jsonarr['files_size'])?$jsonarr['files_size'] : 0;
                    $topic = $jsonarr;

                }
                elseif(strpos($filename, self::FILE_COMMENT) !== false)
                {
                    $userinfo = $userinfos->where('id', $jsonarr['mst_user_id'])->first();
                   
                    $jsonarr['username'] = $userinfo->username;
                    $jsonarr['user_profile_data'] = $userinfo->user_profile_data;
                    $jsonarr['filename'] = $filename;
                    $jsonarr['isAuthEditAndDelete'] =0;
                    if ($jsonarr['mst_user_id'] == $userId) $jsonarr['isAuthEditAndDelete'] = 1;
                    $jsonarr['disp_created_at'] = date('Y年m月d日 H時i分',strtotime($jsonarr['created_at']));
                    $jsonarr['isUpdate'] =0;
                    if ($jsonarr['updated_at'] > $jsonarr['created_at']) $jsonarr['isUpdate'] = 1;
                    $comment[] = $jsonarr;
                }

            }   
                     
            if ($comment) {
                foreach ((array) $comment as $key => $value) {
                    $sort[$key] = $value['created_at'];
                }
                
                array_multisort($sort, SORT_ASC, $comment);
            }
            $ret = true;
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $ret;
        }
        return $ret;
    }

    public function getBbsCategories(Request $request)
    {
        $data = $request->all();
        $limitPage = isset($data['limit'])?$data['limit']:10;
        $page = isset($data['page'])?$data['page']:1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        $categoryId = isset($data['categoryId'])?$data['categoryId']:'';
        $editflg = isset($data['editflg'])?$data['editflg']:'';
        $allflg = isset($data['allflg'])?$data['allflg']:'';
        try 
        {
    
            $isCategoryId = 0;
            if (isset($categoryId) && $categoryId != '') $isCategoryId = 1;

            $user = $request->user();
            $userId = $user->id;
            $companyId = $user->mst_company_id;

            $selectQuery = 'bc_s.bbs_category_id as bbs_category_id'
                .', bc_s.name as name'
                .', concat(mu.family_name, mu.given_name) as username';
            if($editflg)
            {
                $selectQuery = 'bc_s.bbs_category_id as bbs_category_id'
                    .', bc_s.name as name'
                    .', bc_s.memo as memo'
                    .', bc_s.mst_user_id as mst_user_id'
                    .', bc_s.bbs_auth_id as bbs_auth_id'
                    .', DATE_FORMAT(bc_s.created_at, '."'%Y年%c月%e日 %H:%i'".') as created_at'
                    .', DATE_FORMAT(bc_s.updated_at, '."'%Y年%c月%e日 %H:%i'".') as updated_at'
                    .', concat(mu.family_name, mu.given_name) as username'
                    .', ba.auth_code as auth_code'
                    .', ba.auth_content as auth_content'
                    .', case bc_s.mst_user_id when '.$userId.' then 1 else 0 end as isAuthEditAndDelete'
                    .', bau.categoryUsers as categoryUsers';
            }
    
            $bbsCategoryQuery = $this->getBbsCategoryQuery($userId, $companyId, $categoryId);

            $bbsCategoryUserQuery = DB::table('bbs_category_users')
                ->select(DB::raw('bbs_category_id, GROUP_CONCAT(mst_user_id) as categoryUsers'))
                ->groupBy('bbs_category_id');

            $data_sub = DB::table('mst_user as mu')
                ->JoinSub($bbsCategoryQuery, 'bc_s', function ($join) {
                    $join->on('mu.id', 'bc_s.mst_user_id');
                })
                ->when($editflg, function ($query) use ($bbsCategoryUserQuery) {
                    return $query->join('bbs_auth as ba', 'bc_s.bbs_auth_id', 'ba.id')
                                ->JoinSub($bbsCategoryUserQuery, 'bau' , 'bau.bbs_category_id', 'bc_s.bbs_category_id');
                })
                ->where('mu.state_flg', '!=', AppUtils::STATE_DELETE)
                ->select(DB::raw($selectQuery))
                ->orderBy('bc_s.created_at', 'DESC');

            if($isCategoryId)
            {
                $data = $data_sub
                ->first();             
                $data = ['data'=>$data];
            }else if($allflg){            
                $data = $data_sub
                ->get();
                $data = ['data'=>$data];
            }else{          
                $data = $data_sub
                ->paginate($limit)
                ->appends(request()->input());
            }

        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse($data ,'掲示板カテゴリ取得に成功しました。');
    }
    public function getBbsAuth()
    {
        try 
        {
            $data = DB::table('bbs_auth')
                ->select('id','auth_code', 'auth_content')
                ->orderBy('id')
                ->get();
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['data' => $data] ,'掲示板掲示板の権限取得に成功しました。');
    }

    public function getBbsMember(Request $request)
    {
        $user = $request->user();
        $companyId = $user->mst_company_id;
        try 
        {
            $data = DB::table('mst_user')
            ->where('state_flg', '!=', AppUtils::STATE_DELETE)
            ->where('mst_company_id', $companyId)
            ->select(DB::raw("id, concat(family_name, given_name) as username, '0' as checked"))
            ->orderBy('id')
            ->get();
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['data' => $data] ,'掲示板掲示板の権限取得に成功しました。');
    }
    
    public function getBbsMemberForPage(Request $request)
    {
        $user = $request->user();
        $companyId = $user->mst_company_id;
        $limit = $request->get('limit', 10);
        $search = $request->get('search', '');
        try
        {
            $query = DB::table('mst_user')
                ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                ->where('mst_company_id', $companyId);
            if ($search != '') $query->whereRaw(DB::raw('CONCAT(family_name, given_name) LIKE \'%' . $search . '%\''));
                $query->select('id', 'family_name', 'given_name');
            $data = $query->paginate($limit)->appends(request()->input());
            if(!$data->isEmpty()) {
                foreach ($data as $item) {
                    $item->username = $item->family_name . $item->given_name;
                    $item->checked = 0;
                    unset($item->family_name);
                    unset($item->given_name);
                }
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['data' => $data] ,'掲示板掲示板の権限取得に成功しました。');
    }
    
    public function getBbsMemberByIds(Request $request)
    {
        $user = $request->user();
        $companyId = $user->mst_company_id;
        $ids = $request->get('ids', '0');
        try
        {
            $ids = explode(',', $ids);
            $query = DB::table('mst_user')
                ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                ->where('mst_company_id', $companyId)
                ->whereIn('id', $ids)
                ->select('id', 'family_name', 'given_name');
            $data = $query->get();
            if(!$data->isEmpty()) {
                foreach ($data as $item) {
                    $item->username = $item->family_name . $item->given_name;
                    $item->checked = 0;
                    unset($item->family_name);
                    unset($item->given_name);
                }
            }
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->sendResponse(['data' => $data] ,'掲示板掲示板の権限取得に成功しました。');
    }

    public function deleteTopic(Request $request)
    {
        $errmsg = '投稿削除処理で異常が発生しました。';
        $ids = $request->ids;
        $user = $request->user();        
        $userId = $user->id;
        DB::beginTransaction();
        try{
            DB::table('bbs')
            ->wherein('id', $ids)
            ->delete();

            foreach ($ids as $id) { 

                $ret = false;
                $s3path = '';
                //S3ディレクトリ存在確認と作成
                $ret = $this->checkDirectory(self::DIR_CHECKONLY, $user, $id, $s3path);
                if (!$ret) {
                    DB::rollBack();
                    return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                //S3ファイル削除
                //投稿、コメント共に削除  フォルダ毎削除      
                Storage::disk('s3')->deleteDirectory($s3path);
            }

            DB::commit();
            return $this->sendSuccess('topic delete successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deleteComment(Request $request)
    {
        $errmsg = 'コメント削除処理で異常が発生しました。';
        $s3path = $request->s3path;
        $value = $request->value;
        $user = $request->user();
        $filename = $value['filename'];
        $id = $value['bbs_id'];
        DB::beginTransaction();
        try{

            $ret = false;
            //S3ディレクトリ存在確認と作成
            $ret = $this->checkDirectory(self::DIR_CHECKONLY, $user, $id, $s3path);
            if (!$ret) {
                DB::rollBack();
                return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $filepath = $s3path.'/'.$filename;
            $commentFiles = json_decode(Storage::disk('s3')->get($filepath),true);
            if (isset($commentFiles['attachments'])){
                foreach ($commentFiles['attachments'] as $file){
                    Storage::disk('s3')->delete($s3path.'/'.self::FILE_NAME_ATTACHMENT.$file['name']);
                }
            }
            
            //S3ファイル削除  【バケットのrootディレクトリからの相対パス】      
            Storage::disk('s3')->delete($filepath);
            $file_count = 0;
            $total_file_size = 0;
            $s3files = Storage::disk('s3')->allFiles($s3path);
            foreach ($s3files as $s3file) {
                if (Str::startsWith($s3file,$s3path.'/'.self::FILE_NAME_ATTACHMENT)){
                    $file_count++;
                    $total_file_size+=Storage::disk('s3')->size($s3file);
                }
            }
            DB::table('bbs')
                ->where('id',$id)
                ->update([
                    'file_count'=>$file_count,
                    'total_file_size'=>$total_file_size
                ]);
           DB::commit();
            return $this->sendSuccess('comment delete successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deleteCategory(Request $request)
    {
        $errmsg = 'カテゴリ削除処理で異常が発生しました。';
        $user = $request->user();
        $id = $request->id;
        DB::beginTransaction();
        try{
            $bbsInfos = DB::table('bbs')
                ->where('bbs_category_id', $id)
                ->distinct()
                ->get(['id', 's3path'])
                ->keyBy('id');
                //->toArray();
                // $bbsIds = $bbsInfos->keys();

            // DB::table('bbs')
            // ->wherein('id', $bbsIds)
            // ->delete();

            // DB::table('bbs_category_users')
            //     ->where('bbs_category_id' , $id)
            //     ->delete();
            DB::table('bbs_category')
                ->where('id', $id)
                ->delete();

            foreach($bbsInfos as $key => $val) {

                $ret = false;
                $id = $key;
                $s3path = $val->s3path;

                //S3ディレクトリ存在確認と作成
                $ret = $this->checkDirectory(self::DIR_CHECKONLY, $user, $id, $s3path);

                if (!$ret) return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

                //S3　ファイル削除
                // 投稿、コメントファイル削除　ディレクトリ毎削除　$bbsIdsに含まれるIDすべて
                Storage::disk('s3')->deleteDirectory($s3path);

            }
            DB::commit();
            return $this->sendSuccess('category delete successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function addTopic(Request $request)
    {
        $errmsg = '投稿追加処理で異常が発生しました。';
        $validator = Validator::make($request->all(), [
            'value.bbs_category_id' => 'required|numeric',
            'value.title' => 'required|string',
            'value.content' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $value = $request->value;
        $user = $request->user();
        $userId = $user->id;
        $files = $request->get('attachment',[]);
        $value['mst_user_id'] = $userId;
        $jsonValue = $value;
        unset($value['content']);
        unset($jsonValue['title']);
        unset($jsonValue['start_date']);
        unset($jsonValue['end_date']);

        DB::beginTransaction();
        try{
            $id = DB::table('bbs')
                ->insertGetId($value);
            //ファイル名
            $filename = $this->getFileName(self::FILEKBN_TOPIC, $userId);        $ret = false;
            $s3path = '';
            //S3ディレクトリ存在確認と作成
            $ret = $this->checkDirectory(self::DIR_MAKE, $user, $id, $s3path);
            if (!$ret) {
                DB::rollBack();
                return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $validateAttachment =$this->validateAttachment($user->mst_company_id,$files,$s3path);
            if ($validateAttachment){
                DB::rollBack();
                return $this->sendError($validateAttachment, \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            
            $filepath = $s3path.'/'.$filename;

            $jsonValue['files_size'] = 0;
            $jsonValue ['bbs_id'] = $id;
            foreach ($files as $file) {
                $filename = $file['name'];
                $jsonValue['files_size'] += $file['size'];
                $jsonValue['files'][] = [
                    'name'=>$filename,
                    'size'=> $file['size'],
                    'createAt' =>Carbon::now()->toDateTimeString()
                ];
                Storage::disk('s3')->put($s3path . '/'.self::FILE_NAME_ATTACHMENT.$filename, base64_decode($file['file']));
            }
            //JSON形式に変更
            $json = json_encode($jsonValue);
            //S3ファイルアップロード
            Storage::disk('s3')->put($filepath, $json);
            DB::table('bbs')
                ->where('id', $id)
                ->update(['s3path'=> $s3path, 'total_file_size' => $jsonValue['files_size'] ,'file_count'=>count($files), 'updated_at' => null]);

            if (!isset($value['state']) || $value['state'] == self::BBS_STATE_VALID) {
                $subject=sprintf("掲示板「%s」を追加しました",$value['title']);
                $this->addNotice($value['bbs_category_id'],$user->mst_company_id,self::BBS_NOTICE_TYPE_USER,$userId,$subject,"","");
            }

            DB::commit();
            return $this->sendSuccess('topic add successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function addComment(Request $request)
    {
        $errmsg = 'コメント追加処理で異常が発生しました。';
        $validator = Validator::make($request->all(), [
            'value.bbs_id' => 'required|numeric',
            'value.comment' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $s3path = $request->s3path;      
        $value = $request->value;
        $user = $request->user();
        $userId = $user->id;
        $files = $request->get('attachment',[]);
        DB::beginTransaction();
        try{
            $value['mst_user_id'] = $userId;
            $value['created_at'] = Carbon::now()->format('Y-m-d H:i:s.u');
            $value['updated_at'] = '';
            $id = $value['bbs_id'];
            
            //ファイル名
            $filename = $this->getFileName(self::FILEKBN_COMMENT, $userId);
            $ret = false;
            //S3ディレクトリ存在確認と作成
            $ret = $this->checkDirectory(self::DIR_MAKE, $user, $id, $s3path);
            if (!$ret) {
                DB::rollBack();
                return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            };
            $validateAttachment =$this->validateAttachment($user->mst_company_id,$files,$s3path);
            if ($validateAttachment){
                DB::rollBack();
                return $this->sendError($validateAttachment, \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            $filepath = $s3path.'/'.$filename;
            $value['files_size'] = 0;
            foreach ($files as $file) {
                $file_path=$s3path.'/'.self::FILE_NAME_ATTACHMENT.$file['name'];
                $filename = $file['name'];
                $value['files_size'] += $file['size'];
                $value['attachments'][] = [
                    'name'=>$filename,
                    'size'=> $file['size'],
                    'createAt' =>Carbon::now()->toDateTimeString()
                ];
                Storage::disk('s3')->put($file_path, base64_decode($file['file']));
            }
            //JSON形式に変更
            $json = json_encode($value);
            //S3ファイルアップロード
            Storage::disk('s3')->put($filepath, $json, 'pub');
            $file_count = 0;
            $total_file_size = 0;
            $s3files = Storage::disk('s3')->allFiles($s3path);
            foreach ($s3files as $s3file) {
                if (Str::startsWith($s3file,$s3path.'/'.self::FILE_NAME_ATTACHMENT)){
                    $file_count++;
                    $total_file_size+=Storage::disk('s3')->size($s3file);
                }
            }
            DB::table('bbs')
                ->where('id',$id)
                ->update([
                    'file_count'=>$file_count,
                    'total_file_size'=>$total_file_size
                ]);
            Storage::disk('s3')->put($filepath, $json, 'pub');
            $bbs=DB::table('bbs')
                ->where('id',$id)
                ->first();
            if ($bbs){
                $subject=sprintf("掲示板「%s」に返信しました",$bbs->title);
                $this->addNotice($bbs->bbs_category_id,$user->mst_company_id,self::BBS_NOTICE_TYPE_USER,$userId,$subject,"","");
            }
            DB::commit();
            return $this->sendSuccess('comment add successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function addCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'value.name' => 'required|string',
            'value.memo' => 'nullable|string',
            'value.bbs_auth_id' => 'required|numeric',
            'valueuser.*.mst_user_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        $value = $request->value;
        $valueuser = $request->valueuser;
        $user = $request->user();
        $userId = $user->id;
        $value['mst_user_id'] = $userId;
        DB::beginTransaction();
        try{
         
            $id = DB::table('bbs_category')
                ->insertGetId($value);
            if ($value['bbs_auth_id'] == 1) {
                $user_list = DB::table('mst_user')
                    ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->pluck('id')
                    ->toArray();
                $valueuser = array_map(function ($item) {
                    return ['mst_user_id' => $item];
                }, $user_list);
            }

            foreach ($valueuser as &$value) { 
                $value['bbs_category_id']=$id;
            }
 
            DB::table('bbs_category_users')
                ->insert($valueuser);

                DB::commit();
            return $this->sendSuccess('category add successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateTopic(Request $request)
    {
        $errmsg = '投稿更新処理で異常が発生しました。';
        $validator = Validator::make($request->all(), [
            'value.id' => 'required|numeric',
            'value.bbs_category_id' => 'required|numeric',
            'value.title' => 'required|string',
            'value.content' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
 
        $userId = $user->id;

        $value = $request->value;
        $id = $value['id'];
        $files = $request->get('attachment',[]);
        $dbvalue = array(
            'bbs_category_id' => $value['bbs_category_id'],
            'title' => $value['title'],
            'start_date' => $value['start_date'],
            'end_date' => $value['end_date'],
        );

        $jsonValue = array(
            'bbs_id' => $value['id'],
            'bbs_category_id' => $value['bbs_category_id'],
            'mst_user_id' => $value['mst_user_id'],
            'content' => $value['content']
        );
        $isjson = true;
        //if ($value['content'] === $value['contentbf']) $isjson =false;
        unset($value['attachment']);
        DB::beginTransaction();
        try{
            $old_bbs = DB::table('bbs')->where('id', $id)->first();
            $old_state = null;
            $current_state = isset($value['state']) ? $value['state'] : null;
            if ($old_bbs && $old_bbs->state) $old_state = $old_bbs->state;
            if ($old_state == self::BBS_STATE_DRAFT && $current_state == self::BBS_STATE_VALID) {
                $dbvalue['created_at'] = Carbon::now();
                $dbvalue['updated_at'] = null;
            } else {
                $dbvalue['updated_at'] = Carbon::now();
            }
            if (in_array($current_state, [self::BBS_STATE_DRAFT, self::BBS_STATE_VALID])) {
                $dbvalue['state'] = $current_state;
            }
             DB::table('bbs')
                ->where('id', $id)
                ->update($dbvalue);
             if ($isjson){
                //ファイル名
                $filename = $value['filename'];

                $ret = false;
                //S3ディレクトリ存在確認と作成
                $ret = $this->checkDirectory(self::DIR_MAKE, $user, $id, $s3path);
                if (!$ret) {
                    return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                 $validateAttachment =$this->validateAttachment($user->mst_company_id,$files,$s3path);
                 if ($validateAttachment){
                     DB::rollBack();
                     return $this->sendError($validateAttachment, \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                 }
                $filepath = $s3path.'/'.$filename;
                 $value['files_size'] = 0;
                 foreach ($files as $file){
                     $file_path=$s3path.'/'.self::FILE_NAME_ATTACHMENT.$file['name'];
                    
                     if ($file['type'] == 'del') {
                         if (!Storage::disk('s3')->exists($file_path)){
                             break;
                         }
                         Storage::disk('s3')->delete($file_path);
                     }
                     if ($file['type'] == 'history'){
                         if (!Storage::disk('s3')->exists($file_path)){
                             break;
                         }
                         $size = Storage::disk('s3')->size($file_path);
                         $value['files_size'] += $size;
                         $value['files'][] = [
                             'name'=>$file['name'],
                             'size'=> $size,
                             'createAt' =>$file['createAt']
                         ];
                     }
                     if ($file['type'] == 'add'){
                         $value['files'][] = [
                             'name'=>$file['name'],
                             'size'=> $file['size'],
                             'createAt' =>Carbon::now()->toDateTimeString()
                         ];
                         Storage::disk('s3')->put($file_path,base64_decode($file['file']),'pub');
                     }
                 }
                //JSON形式に変更
                $json = json_encode($value);

                //S3ファイルアップロード        
                Storage::disk('s3')->put($filepath, $json, 'pub');
                 $file_count = 0;
                 $total_file_size = 0;
                 $s3files = Storage::disk('s3')->allFiles($s3path);
                 foreach ($s3files as $s3file) {
                    if (Str::startsWith($s3file,$s3path.'/'.self::FILE_NAME_ATTACHMENT)){
                        $file_count++;
                        $total_file_size+=Storage::disk('s3')->size($s3file);
                    }
                 }
                 DB::table('bbs')
                     ->where('id',$id)
                     ->update([
                         'file_count'=>$file_count,
                         'total_file_size'=>$total_file_size
                     ]);

                 if ($old_state == self::BBS_STATE_DRAFT && $current_state == self::BBS_STATE_VALID) {
                     $subject=sprintf("掲示板「%s」を追加しました",$value['title']);
                     $this->addNotice($value['bbs_category_id'],$user->mst_company_id,self::BBS_NOTICE_TYPE_USER,$userId,$subject,"","");
                     DB::table('bbs')->where('id',$id)->update(['updated_at'=>null]);
                 }
            }
            DB::commit();
            return $this->sendSuccess('topic update successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateComment(Request $request)
    {
        $errmsg = 'コメント更新処理で異常が発生しました。';
        $validator = Validator::make($request->all(), [
            'value.bbs_id' => 'required|numeric',
            'value.comment' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $userId = $user->id;
        $files = $request->get('attachment',[]);
        DB::beginTransaction();
        try{
            $value = $request->value;
            $s3path = $request->s3path;

            $value['updated_at'] = Carbon::now()->format('Y-m-d H:i:s.u');
            $filename = $value['filename'];
            $id = $value['bbs_id'];
            unset($value['username']);
            unset($value['user_profile_data']);
            unset($value['filename']);
            unset($value['isAuthEditAndDelete']);
            unset($value['attachments']);
            
           

            $ret = false;
            //S3ディレクトリ存在確認と作成
            $ret = $this->checkDirectory(self::DIR_MAKE, $user, $id, $s3path);
            if (!$ret) {
                DB::rollBack();
                return $this->sendError($errmsg, \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $validateAttachment =$this->validateAttachment($user->mst_company_id,$files,$s3path);
            if ($validateAttachment){
                DB::rollBack();
                return $this->sendError($validateAttachment, \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            $filepath = $s3path.'/'.$filename;

            $value['files_size'] = 0;
            foreach ($files as $file){
                $file_path=$s3path.'/'.self::FILE_NAME_ATTACHMENT.$file['name'];

                if ($file['type'] == 'del') {
                    if (!Storage::disk('s3')->exists($file_path)){
                        break;
                    }
                    Storage::disk('s3')->delete($file_path);
                }
                if ($file['type'] == 'history'){
                    if (!Storage::disk('s3')->exists($file_path)){
                        break;
                    }
                    $size = Storage::disk('s3')->size($file_path);
                    $value['files_size'] += $size;
                    $value['attachments'][] = [
                        'name'=>$file['name'],
                        'size'=> $size,
                        'createAt' =>$file['createAt']
                    ];
                }
                if ($file['type'] == 'add'){
                    $value['attachments'][] = [
                        'name'=>$file['name'],
                        'size'=> $file['size'],
                        'createAt' =>Carbon::now()->toDateTimeString()
                    ];
                    Storage::disk('s3')->put($file_path,base64_decode($file['file']),'pub');
                }
            }


            //JSON形式に変更
            $json = json_encode($value);
            //S3ファイルアップロード        
            Storage::disk('s3')->put($filepath, $json, 'pub');
            $file_count = 0;
            $total_file_size = 0;
            $s3files = Storage::disk('s3')->allFiles($s3path);
            foreach ($s3files as $s3file) {
                if (Str::startsWith($s3file,$s3path.'/'.self::FILE_NAME_ATTACHMENT)){
                    $file_count++;
                    $total_file_size+=Storage::disk('s3')->size($s3file);
                }
            }
            DB::table('bbs')
                ->where('id',$id)
                ->update([
                    'file_count'=>$file_count,
                    'total_file_size'=>$total_file_size
                ]);
            DB::commit();
            return $this->sendSuccess('comment update successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'value.name' => 'required|string',
            'value.memo' => 'nullable|string',
            'value.bbs_auth_id' => 'required|numeric',
            'valueuser.*.bbs_category_id' => 'required|numeric',
            'valueuser.*.mst_user_id' => 'required|numeric',
        ]);
       
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        $id = $request->id;
        $value = $request->value;
        $valueuser = $request->valueuser;
        $user = $request->user();
        DB::beginTransaction();
        try{

            DB::table('bbs_category_users')
                ->where('bbs_category_id' , $id)
                ->delete();

            DB::table('bbs_category')
                ->where('id', $id)
                ->update($value);
            if ($value['bbs_auth_id'] == 1) {
                $user_list = DB::table('mst_user')
                    ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                    ->where('mst_company_id', $user->mst_company_id)
                    ->pluck('id')
                    ->toArray();
                $valueuser = array_map(function ($item) use ($id) {
                    return [
                        'bbs_category_id' => $id,
                        'mst_user_id' => $item
                    ];
                }, $user_list);
            }
            DB::table('bbs_category_users')
                ->insert($valueuser);

            DB::commit();
            return $this->sendSuccess('category update successfully');
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function getFileName($kbn, $userId){

        switch($kbn)
        {
            case self::FILEKBN_TOPIC:
                $filename = Str::replaceArray('?', [$userId], self::FILENAME_TOPIC);
                break;
            case self::FILEKBN_COMMENT:
                $filename = Str::replaceArray('?', [$userId, Carbon::now()->format('YmdHisu')], self::FILENAME_COMMENT);
                break;
        }

        return $filename;
    }
    private function getFolderName($user, $bbsIdFolder){
        return config('filesystems.prefix_path') . '/' . config(self::CONFIG_ROOT_FOLDER). self::BBS_DIRECTORY. $this->bbsIdDirectory.'/'.$user->mst_company_id.'/'.$bbsIdFolder.'_'.$user->id;
    }
    private function checkDirectory($kbn, $user, $bbsIdFolder, &$s3path){
        $ret = false;
        try{
            if (!$s3path){
                $s3path = $this->getFolderName($user, $bbsIdFolder); 
            }
            $isDirectory = Storage::disk('s3')->exists($s3path);

            switch($kbn)
            {
                case self::DIR_MAKE:
                    if (!$isDirectory){
                        Storage::disk('s3')->makeDirectory($s3path);
                    }
                    break;
                case self::DIR_CHECKONLY:
                    return $isDirectory;
                    break;
            }
            $ret = true;
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $ret;
        }
        return $ret;
    }

    private function getBbsCommentAuthQuery($userId, $companyId, $categoryId)
    {
        $isCategoryId = 0;
        if (isset($categoryId) && $categoryId != '') $isCategoryId = 1;

        $selectQuery1 = 'id as bbs_category_id';
        $selectQuery2 = 'bc.id as bbs_category_id';

        $query_category_sub = DB::table('bbs_category as bc')
            ->join('bbs_category_users as bcu', 'bc.id', 'bcu.bbs_category_id')
            ->join('mst_user as mu', 'mu.id', 'bcu.mst_user_id')
            ->join('mst_company as mc', 'mc.id', 'mu.mst_company_id')
            ->where('mu.state_flg', '!=', AppUtils::STATE_DELETE)
            ->when($isCategoryId, function($query) use ($categoryId) {
                return $query->where('bc.id', $categoryId);
            })
            ->where(function($query_w1) use($userId, $companyId){
                $query_w1->where(function($query_w1) use($userId, $companyId){
                    return $query_w1
                        ->where(function($query_w1){
                            return $query_w1->where('bbs_auth_id', 2)
                            ->orwhere('bbs_auth_id', 3);
                            })
                        ->Where('bcu.mst_user_id', $userId)
                        ->Where('mc.id', '=', $companyId);
                 })
                 ->orwhere(function($query_w1) use($userId, $companyId){
                    return $query_w1->where('bc.bbs_auth_id', 4)
                            ->Where('bc.mst_user_id', $userId)
                            ->Where('mc.id', '=', $companyId);
                });
            })
            ->select(DB::raw($selectQuery2))
            ->distinct();

        $query_category = DB::table('bbs_category')
            ->where('bbs_auth_id', 1)
            ->select(DB::raw($selectQuery1))
            ->distinct()
            ->unionall($query_category_sub);
        return $query_category;
    }  
    private function getBbsCategoryQuery($userId, $companyId, $categoryId)
    {
        $selectQuery = 'bc.id as bbs_category_id'.', bc.name as name'
            .', bc.memo as memo'
            .', bc.mst_user_id as mst_user_id'
            .', bc.bbs_auth_id as bbs_auth_id'
            .', bc.created_at as created_at'
            .', bc.updated_at as updated_at';
        $isCategoryId = 0;
        if (isset($categoryId) && $categoryId != '') $isCategoryId = 1;

        $query_category_sub = DB::table('bbs_category as bc')
            ->join('bbs_category_users as bcu', 'bc.id', 'bcu.bbs_category_id')
            ->join('mst_user as mu', 'mu.id', 'bcu.mst_user_id')
            ->join('mst_company as mc', 'mc.id', 'mu.mst_company_id')
            ->where('mu.state_flg', '!=', AppUtils::STATE_DELETE)
            ->when($isCategoryId, function($query) use ($categoryId) {
                return $query->where('bc.id', $categoryId);
            })
            ->where(function($query_w1) use($userId, $companyId){
                $query_w1->where(function($query_w1) use($userId, $companyId){
                    return $query_w1->where('bc.bbs_auth_id', 3)
                            ->Where('bcu.mst_user_id', $userId)
                            ->Where('mc.id', '=', $companyId);
                 })
                 ->orwhere(function($query_w1) use($userId, $companyId){
                    return $query_w1->where('bc.bbs_auth_id', 4)
                            ->Where('bc.mst_user_id', $userId)
                            ->Where('mc.id', '=', $companyId);
                });
            })

            ->select(DB::raw($selectQuery))
            ->distinct();
            // DB::table('bbs_category')
            // ->when($isCategoryId, function($query) use ($categoryId) {
            //      return $query->where('id', $categoryId);
            //  })
            //  ->where(function($query_w1){
            //      return $query_w1->where('bbs_auth_id', 1)
            //      ->orwhere('bbs_auth_id', 2);
            //  })
 
        $query_category = DB::table('bbs_category as bc')
            ->join('mst_user as mu', 'mu.id', 'bc.mst_user_id')
            ->join('mst_company as mc', 'mc.id', 'mu.mst_company_id')
            ->where('mu.state_flg', '!=', AppUtils::STATE_DELETE)
            ->when($isCategoryId, function($query) use ($categoryId) {
                return $query->where('bc.id', $categoryId);
            })
            ->where(function($query_w1){
                return $query_w1->where('bc.bbs_auth_id', 1)
                ->orwhere('bc.bbs_auth_id', 2);
            })
            ->Where('mc.id', '=', $companyId)

            ->select(DB::raw($selectQuery))
            ->distinct()
            ->unionall($query_category_sub);
        return $query_category;
    }    
    private function getBbsTopicAllQuery($userId, $companyId, $categoryId, $keyword, $isExpired = 0, $state = self::BBS_STATE_VALID)
    {
    
        $selectitem = 'b.id'
            .', b.bbs_category_id'
            .', bc_s.name'
            .', b.mst_user_id'
            .', b.title'
            .', b.state'
            .", DATE_FORMAT(b.start_date, '%Y-%m-%d %H:%i:%s') as disp_start_date"
            .", DATE_FORMAT(b.end_date, '%Y-%m-%d %H:%i:%s') as disp_end_date"
            .', case b.mst_user_id when '.$userId.' then 1 else 0 end as isAuthEditAndDelete';
        $query_category = $this->getBbsCategoryQuery($userId, $companyId, $categoryId );
    
        if ($state == self::BBS_STATE_DRAFT) {
            $selectitem .= ', b.created_at, b.updated_at';
        }
       
        $bbslistQuery = DB::table('bbs as b')
            ->select(
                DB::raw($selectitem))
            ->JoinSub($query_category, 'bc_s', function ($join) {
                 $join->on('b.bbs_category_id', 'bc_s.bbs_category_id');
            })
            ->where('b.state', $state);
        if ($state == self::BBS_STATE_DRAFT) {
            $bbslistQuery->where('b.mst_user_id', $userId);
        }
        if ($state == self::BBS_STATE_VALID) {
            if ($isExpired == 1) {
                $bbslistQuery->whereRaw("DATE_FORMAT(b.end_date, '%Y-%m-%d %H:%i:%s') < DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')");
            } else {
                $bbslistQuery->whereRaw("DATE_FORMAT(b.start_date, '%Y-%m-%d %H:%i:%s') <= DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')")
                    ->where(function($query_w1){
                        return $query_w1->whereRaw("DATE_FORMAT(b.end_date, '%Y-%m-%d %H:%i:%s') >= DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')")
                            ->orWhereNull('b.end_date');
                    });
            }
        }
        
        $bbslistQuery->when(!empty($keyword), function($query) use ($keyword) {
                return $query->where('b.title', 'like', '%' . $keyword . '%');
            })
            ->orderBy('b.created_at', 'DESC');
   
        return $bbslistQuery;    
    }
    private function getBbsTopicDetailQuery($userId, $companyId, $bbsId, $isExpired = 0)    {
    
        $selectitem = 'b.id'
            .', b.bbs_category_id'
            .', bc_s.name'
            .', b.mst_user_id'
            .', b.title'
            .', b.s3path'
            .', b.total_file_size'
            .', b.start_date'
            .', b.end_date'
            .', b.state'
            .", DATE_FORMAT(b.start_date, '%Y-%m-%d %H:%i:%s') as disp_start_date"
            .", DATE_FORMAT(b.end_date, '%Y-%m-%d %H:%i:%s') as disp_end_date"
            .', DATE_FORMAT(b.created_at, '."'%Y年%c月%e日 %H:%i'".') as created_at'
            .', DATE_FORMAT(b.updated_at, '."'%Y年%c月%e日 %H:%i'".') as updated_at'
            .', DATE_FORMAT(b.created_at, '."'%Y-%c-%e %H:%i'".') as created_time'
            .', DATE_FORMAT(b.updated_at, '."'%Y-%c-%e %H:%i'".') as updated_time'
            .', case b.mst_user_id when '.$userId.' then 1 else 0 end as isAuthEditAndDelete'
            .', case when b.updated_at > b.created_at then 1 else 0 end as isUpdate'
            .", case bc_a.bbs_category_id is null when 1 then '0' else '1' end isCommentAuth";
        $bbsCommentAuthQuery = $this->getBbsCommentAuthQuery($userId, $companyId, '');
        $query_category = $this->getBbsCategoryQuery($userId, $companyId, '' );

        $bbslistQuery = DB::table('bbs as b')
            ->select(
                DB::raw($selectitem))
            ->JoinSub($query_category, 'bc_s', function ($join) {
                 $join->on('b.bbs_category_id', 'bc_s.bbs_category_id');
            })
            ->leftJoinSub($bbsCommentAuthQuery, 'bc_a', function ($join) {
                $join->on('b.bbs_category_id', 'bc_a.bbs_category_id');
            })
            ->where('b.id', $bbsId);

        if ($isExpired == 1) {
            $bbslistQuery->whereRaw("DATE_FORMAT(b.end_date, '%Y-%m-%d %H:%i:%s') < DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')");
        } else {
            $bbslistQuery->where(function($query) {
                return $query->where(function ($query_1) {
                    return $query_1->whereRaw("DATE_FORMAT(b.start_date, '%Y-%m-%d') <= DATE_FORMAT(NOW(), '%Y-%m-%d')")
                        ->where(function ($query_w1) {
                            return $query_w1->whereRaw("DATE_FORMAT(b.end_date, '%Y-%m-%d') >= DATE_FORMAT(NOW(), '%Y-%m-%d')")
                                ->orWhereNull('b.end_date');
                        })
                        ->where('state', self::BBS_STATE_VALID);
                })
                ->orWhere('state', self::BBS_STATE_DRAFT);
            });
        }
        $bbslistQuery->orderBy('b.created_at', 'DESC');
   
        return $bbslistQuery;    
    }
    public function getFile(Request  $request){
      
        $bbs=DB::table('bbs')
            ->where('id', $request->get('id','0'))
            ->first();
        $fileName = $request->get('filename','');
        if ($bbs){
            $validateFile = Storage::disk('s3')->exists($bbs->s3path.'/'.self::FILE_NAME_ATTACHMENT.$fileName);
            if ($validateFile){
                return ['status'=>Response::HTTP_OK,'file_name'=>$fileName,'file'=>base64_encode(Storage::disk('s3')->get($bbs->s3path.'/'.self::FILE_NAME_ATTACHMENT.$fileName))];
            }
        }
        return ['status'=>Response::HTTP_NOT_FOUND,$bbs];
    }

    public function getBbsSetting(Request $request)
    {
        $user = $request->user();
        $companySetting = DB::table('mst_constraints')
            ->where('mst_company_id', $user->mst_company_id)
            ->select(['bbs_max_attachment_size', 'bbs_max_total_attachment_size', 'bbs_max_attachment_count'])
            ->first();
        $countAttachmentSize = DB::table('bbs')
            ->leftJoin('mst_user', 'mst_user.id', '=', 'bbs.mst_user_id')
            ->where('mst_user.mst_company_id', $user->mst_company_id)
            ->sum('bbs.total_file_size');
        return ['status' => Response::HTTP_OK, 'info'=>['companySetting' => $companySetting, 'countAttachmentSize' => $countAttachmentSize]];
    }
    public function getNoticeList(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $res = [];
        DB::table('bbs_notice_management')
            ->leftJoin('bbs_notice', 'bbs_notice.id', '=', 'bbs_notice_management.notice_id')
            ->leftJoin('mst_user', 'bbs_notice.from_user_id', '=', 'mst_user.id')
            ->where('mst_user_id', $userId)
            ->select(DB::raw('bbs_notice_management.*,bbs_notice.*,bbs_notice.id as id,CONCAT(mst_user.family_name,mst_user.given_name) AS user_name,bbs_notice_management.id as mid,bbs_notice_management.created_at as mcreated_at,bbs_notice_management.updated_at as mupdated_at'))
            ->get()
            ->each(function ($item) use (&$res) {
                $res[] = [
                    'id' => $item->mid,
                    'createdAt' => $item->mcreated_at,
                    'updatedAt' => $item->mupdated_at,
                    'isRead' => $item->is_read,
                    'notice' => [
                        'contents' => $item->contents,
                        'createdAt' => $item->created_at,
                        'id' => $item->id,
                        'link' => $item->link,
                        'subject' => $item->subject,
                        'type' => $item->type,
                        'updatedAt' => $item->updated_at,
                        'mstUser' => [
                            'id' => $item->from_user_id,
                            'name' => $item->user_name,
                            'userProfileData' => null,
                        ]
                    ],
                ];
            });
        return $this->sendResponse($res, '');
    }

    private function validateAttachment($mst_company_id, $files, $s3path)
    {
        if (count($files) == 0 ){
            return false;
        }
        $companySetting = DB::table('mst_constraints')
            ->where('mst_company_id', $mst_company_id)
            ->select(['bbs_max_attachment_size', 'bbs_max_total_attachment_size', 'bbs_max_attachment_count'])
            ->first();
        $countAttachmentSize = DB::table('bbs')
            ->leftJoin('mst_user', 'mst_user.id', '=', 'bbs.mst_user_id')
            ->where('mst_user.mst_company_id', $mst_company_id)
            ->sum('bbs.total_file_size');
        $s3files = Storage::disk('s3')->allFiles($s3path);
        $files_name = [];
        $files_name_del = [];
        $files_name_add = [];
        $files_size = $countAttachmentSize;
        foreach ($files as $file) {
            if ($file['type'] == 'add') {
                if ($file['size'] > $companySetting->bbs_max_attachment_size*1024*1024) {
                    return 'アップロードできるファイルサイズは'.$companySetting->bbs_max_attachment_size.'MB以下です';
                }
                $files_size += $file['size'];
                $files_name_add[] = $file['name'];
            }
            if ($file['type'] == 'del') {
                $files_size -= $file['size'];
                $files_name_del[] = $file['name'];
            }
        }
        
        foreach ($s3files as $s3file) {
            if (Str::startsWith($s3file, $s3path . '/' . self::FILE_NAME_ATTACHMENT)) {
                $file_name = Str::replaceFirst($s3path . '/' . self::FILE_NAME_ATTACHMENT,'' , $s3file);
                if (!in_array($file_name, $files_name_del)) {
                    $files_name[] = $file_name;
                }
                
            }
        }
        if ($files_size > $companySetting->bbs_max_total_attachment_size*1024*1024*1024) {
            return '指定した添付ファイル合計容量を超えることはできません。';
        }
        
        if (count(array_intersect($files_name, $files_name_add))>0) {
            return '同一ファイル名でアップロードできません';
        }

        if ((count($files_name)+count($files_name_add)) > $companySetting->bbs_max_attachment_count) {
            return '指定した添付ファイル数を超えることはできません';
        }

        return false;
    }

    private function addNotice($category_id,$company_id, $type, $from_user_id, $subject, $contents, $link)
    {
        $category_info = DB::table('bbs_category')
            ->where('id', $category_id)
            ->first();
        $category_users = [];
        switch ($category_info->bbs_auth_id) {
            case 1:
                $category_users = DB::table('mst_user')
                    ->where('mst_company_id', '=',$company_id)
                    ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                    ->pluck('id');
                break;
            case 2:
                $category_users = DB::table('mst_user')
                    ->where('mst_company_id', '=',$company_id)
                    ->where('state_flg', '!=', AppUtils::STATE_DELETE)
                    ->pluck('id');
                break;
            case 3:
                $category_users = DB::table('bbs_category_users')
                    ->leftJoin('mst_user','mst_user.id','=','bbs_category_users.mst_user_id')
                    ->where('bbs_category_users.bbs_category_id', '=', $category_id)
                    ->where('mst_user.state_flg', '!=', AppUtils::STATE_DELETE)
                    ->select('mst_user.id')
                    ->pluck('id');
                break;
            case 4:
                return;
        }
        $notice_id=DB::table('bbs_notice')
            ->insertGetId([
                'type' => $type,
                'subject' => $subject,
                'contents' => $contents,
                'from_user_id' => $from_user_id,
                'link' => $link
            ]);
        $notice_management_arr=[];
        foreach ($category_users as $user_id) {
            $notice_management_arr[] = [
                'notice_id' => $notice_id,
                'is_read' => self::BBS_NOTICE_READ_STATE_NOT_READ,
                'mst_user_id' => $user_id
            ];
        }
        DB::table('bbs_notice_management')
            ->insert($notice_management_arr);
    }

    public  function unReadCnt(Request  $request){
        return DB::table('bbs_notice_management')
            ->where('is_read',self::BBS_NOTICE_READ_STATE_NOT_READ)
            ->where('mst_user_id',$request->user()->id)
            ->count();
    }
    public function makeNoticeRead(Request $request,$notice_id){
        DB::table('bbs_notice_management')
            ->where('is_read',self::BBS_NOTICE_READ_STATE_NOT_READ)
            ->where('mst_user_id',$request->user()->id)
            ->where('id',$notice_id)
            ->update([
                'is_read'=>self::BBS_NOTICE_READ_STATE_IS_READ
            ]);
        return $this->sendResponse([], '');
    }
    public function makeAllNoticeRead(Request $request){
        DB::table('bbs_notice_management')
            ->where('is_read',self::BBS_NOTICE_READ_STATE_NOT_READ)
            ->where('mst_user_id',$request->user()->id)
            ->update([
                'is_read'=>self::BBS_NOTICE_READ_STATE_IS_READ
            ]);
        return $this->sendResponse([], '');
    }

    public function getTopicLikes(Request $request)
    {
        $bbsId = $request->get('bbs_id', '');
        $limit = $request->get('limit', '10');
        $limit = $limit === 'all' ? $limit : AppUtils::normalizeLimit($limit, 10);
        try
        {
            $user = $request->user();
            $userId = $user->id;
            $likesListCount = DB::table('bbs_likes_history AS blh')
                ->join('mst_user as mu', 'blh.mst_user_id', 'mu.id')
                ->join('mst_user_info as mui', 'mu.id', 'mui.mst_user_id')
                ->where('blh.bbs_id', $bbsId)
                ->count();
            $likesList = DB::table('bbs_likes_history AS blh')
                ->join('mst_user as mu', 'blh.mst_user_id', 'mu.id')
                ->join('mst_user_info as mui', 'mu.id', 'mui.mst_user_id')
                ->where('blh.bbs_id', $bbsId)
                ->select(DB::raw(
                    'blh.mst_user_id'
                    .', mui.user_profile_data as user_profile_data'
                    .', concat(mu.family_name, mu.given_name) as username')
                );
                if ($limit == 'all') {
                    $likesList = $likesList->get();
                } else {
                    $likesList = $likesList->limit($limit)->get();
                }
            $hasLikedCount = DB::table('bbs_likes_history')
                ->where('bbs_id', $bbsId)
                ->where('mst_user_id', $userId)
                ->count();
            $hasLiked = $hasLikedCount > 0 ? true : false;
            return $this->sendResponse(['likesList' => $likesList, 'hasLiked' => $hasLiked, 'likesListCount' => $likesListCount], '掲示板投稿のいいねリストが正常に取得されました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function addTopicLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bbs_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $bbsId = $request->get('bbs_id');
            $user = $request->user();
            $userId = $user->id;
            $hasLikedCount = DB::table('bbs_likes_history')
                ->where('bbs_id', $bbsId)
                ->where('mst_user_id', $userId)
                ->count();
            if ($hasLikedCount === 0) {
                DB::table('bbs_likes_history')
                    ->insert([
                        'bbs_id' => $bbsId,
                        'mst_user_id' => $userId,
                        'mst_company_id' => $user->mst_company_id,
                        'created_at' => Carbon::now()
                    ]);
            }
            DB::commit();
            return $this->sendSuccess('投稿のいいねレコードの追加に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function deleteLikeTopic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bbs_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $bbsId = $request->get('bbs_id');
            $user = $request->user();
            $userId = $user->id;
            DB::table('bbs_likes_history')
                ->where('bbs_id', $bbsId)
                ->where('mst_user_id', $userId)
                ->delete();
            DB::commit();
            return $this->sendSuccess('投稿のいいねレコードの削除処理に成功しました。');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function addDraftTopic(Request $request)
    {
        return $this->addTopic($request);
    }
    
    public function updateDraftTopic(Request $request)
    {
        return $this->updateTopic($request);
    }
    
    public function deleteDraftTopic(Request $request)
    {
        return $this->deleteTopic($request);
    }

}