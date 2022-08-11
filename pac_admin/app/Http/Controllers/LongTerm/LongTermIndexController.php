<?php

namespace App\Http\Controllers\LongTerm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use DB;

class LongTermIndexController extends AdminController
{
    //
    public function show(Request $request)
    {
        $user = \Auth::user();

        $limit = $request->get('limit') ? $request->get('limit') : config('app.page_limit');
        if(!array_search($limit, config('app.page_list_limit'))){
            $limit = config('app.page_limit');
        }
        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $company = DB::table('mst_company')->where('id',$user->mst_company_id)->first();
        $longTermStorage = $company->long_term_storage_flg;

        $longTermIndex = DB::table('mst_longterm_index')->where('mst_company_id',$user->mst_company_id)
            ->where('template_flg',0)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit);

        $temp_longTermIndex = DB::table('mst_longterm_index')->where('mst_company_id',$user->mst_company_id)
            ->where('template_flg',1)
            ->where('template_valid_flg',1)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit);

        $longTermIndexName = DB::table('mst_longterm_index')->where('mst_company_id',$user->mst_company_id)
            ->select('index_name','id')
            ->where('template_flg',1)
            ->where('template_valid_flg',0)
            ->orderBy($orderBy,$orderDir)
            ->pluck('index_name','id')
            ->toArray();

        $frm_longTermIndex = DB::table('mst_longterm_index')->where('mst_company_id',$user->mst_company_id)
            ->where('template_flg',2)
            ->where('template_valid_flg',1)
            ->orderBy($orderBy,$orderDir)
            ->paginate($limit);

        $frmLongTermIndexName = DB::table('mst_longterm_index')->where('mst_company_id',$user->mst_company_id)
            ->select('index_name','id')
            ->where('template_flg',2)
            ->where('template_valid_flg',0)
            ->orderBy($orderBy,$orderDir)
            ->pluck('index_name','id')
            ->toArray();

        ksort($longTermIndexName);

        $template_flg = $company->template_flg;
        
        $this->assign('longTermIndex', $longTermIndex);
        $this->assign('temp_longTermStorage', $temp_longTermIndex);
        $this->assign('frm_longTermStorage', $frm_longTermIndex);
        $this->setMetaTitle("長期保管インデックス設定");
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->assign('longTermStorage', $longTermStorage);
        $this->assign('template_flg', $template_flg);
        $this->assign('longTermIndexName', $longTermIndexName);
        $this->assign('frmLongTermIndexName', $frmLongTermIndexName);
        $this->assign('templateFlg', $company->template_flg);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        return $this->render('LongTerm.longTermIndex.index');
    }

    public function showOne($id)
    {
        $user = \Auth::user();


        $longTermIndex = DB::table('mst_longterm_index')->where('id',$id)
            ->first();
        
        return response()->json(['status' => true, 'info' => $longTermIndex]);
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        $info = $request->only('id','index_name', 'data_type');
        Log::debug('start to store long term index');
        Log::debug($info);

        if(!$info['index_name'] || !isset($info['data_type'])) {
            Log::debug('index_name or data_type is null');
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        DB::beginTransaction();
        try{

            $info['id'] = DB::table('mst_longterm_index')
                ->insertGetId([
                    'mst_company_id' => $user->mst_company_id,
                    'mst_user_id' => $user->id,
                    'index_name' => $info['index_name'],
                    'data_type' => $info['data_type'],
                    'permission' => 1,
                    'create_at' => Carbon::now(),
                    'create_user' => $user->family_name . $user->given_name
                ]);
            Log::debug('store long term index');
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')],
            'id' => $info['id'], 'data_type' => $info['data_type']]);
    }

    public function update($id, Request $request){
        $user = \Auth::user();
        $info = $request->only('id','index_name', 'data_type');
        Log::debug('start to store long term index');
        Log::debug($info);

        if(!$info['index_name'] || !isset($info['data_type'])) {
            Log::debug('index_name or data_type is null');
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        DB::beginTransaction();
        try{

            DB::table('mst_longterm_index')
                ->where('id', $info['id'])
                ->update([
                    'index_name' => $info['index_name'],
                    'data_type' => $info['data_type'],
                    'update_at' => Carbon::now(),
                    'update_user' => $user->family_name . $user->given_name
                ]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')],
            'id' => $info['id'], 'data_type' => $info['data_type']]);
    }

    public function delete($id, Request $request){
        $user = \Auth::user();

        DB::beginTransaction();
        try{

            DB::table('mst_longterm_index')
                ->where('id', $id)
                ->delete();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')]]);
    }

    public function templateRelease($id, Request $request){
        $user = \Auth::user();

        DB::beginTransaction();
        try{
            DB::table('mst_longterm_index')
                ->where('id', $id)
                ->update(['template_valid_flg' => 0]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')]]);
    }

    public function templateValid($id, Request $request){
        $user = \Auth::user();


        DB::beginTransaction();
        try{
            Log::info('$id' . var_export($id, true));
            DB::table('mst_longterm_index')
                ->where('id', $id)
                ->update(['template_valid_flg' => TRUE]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')]]);
    }

    public function formIssuanceRelease($id, Request $request){
        $user = \Auth::user();

        DB::beginTransaction();
        try{
            DB::table('mst_longterm_index')
                ->where('id', $id)
                ->update(['template_valid_flg' => 0]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')]]);
    }

    public function formIssuanceValid($id, Request $request){
        $user = \Auth::user();

        DB::beginTransaction();
        try{
            Log::info('$id' . var_export($id, true));
            DB::table('mst_longterm_index')
                ->where('id', $id)
                ->update(['template_valid_flg' => TRUE]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')]]);
    }
}
