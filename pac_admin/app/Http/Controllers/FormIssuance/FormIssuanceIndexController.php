<?php

namespace App\Http\Controllers\FormIssuance;

use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Log;
use Auth;
use Carbon\Carbon;
use DB;

class FormIssuanceIndexController extends AdminController
{
    /**
     * 帳票項目設定情報を表示します。
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        $orderBy = $request->get('orderBy') ? $request->get('orderBy') : 'id';
        $orderDir = $request->get('orderDir') ? $request->get('orderDir'): 'desc';

        $frmIndex = DB::table('frm_index')->where('mst_company_id',$user->mst_company_id)
            ->orderBy($orderBy,$orderDir)
            ->paginate(3);

        $this->assign('frmIndex', $frmIndex);
        $this->setMetaTitle("明細項目設定");
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', $orderDir);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        return $this->render('FormIssuance.FrmIndex.index');
    }

    /**
     * 選択したの帳票項目設定を表示します
     * @param integer $id 帳票項目ID
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request){
        $user   = $request->user();

        if(!$id){
            return response()->json(['status' => false, 'message' => [__('message.false.get_frm_index_id')]]);
        }
        $item = DB::table('frm_index')->where('mst_company_id',$user->mst_company_id)->find($id);

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'item' => $item]);
    }

    /**
     * 帳票項目設定登録
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $info = $request->get('item');

        if(!$info || !isset($info['index_name']) || !isset($info['data_type'])) {
            return response()->json(['status' => false, 'message' => [__('message.false.frm_index_requisite')]]);
        }
        // 重複チェック
        $other_frm_index = DB::table('frm_index')
            ->where('mst_company_id', $user->mst_company_id)
            ->where('index_name', $info['index_name'])
            ->first();
        if($other_frm_index){
            return response()->json(['status' => false, 'message' => [__('message.false.frm_index_exist')]]);
        }

        //入力可能帳票項目の検査
        $FrmIndexNumber = DB::table('frm_index')
            ->where('mst_company_id',$user->mst_company_id)
            ->pluck('frm_index_number');
        if(!$FrmIndexNumber->contains(1)){
            $frm_index_number = 1;
        }else if(!$FrmIndexNumber->contains(2)){
            $frm_index_number = 2;
        }else if(!$FrmIndexNumber->contains(3)){
            $frm_index_number = 3;
        }else{
            return response()->json(['status' => false, 'message' => [__('message.false.frm_index_exceed')]]);
        }

        try{
            DB::table('frm_index')
                ->insert([
                    'mst_company_id' => $user->mst_company_id,
                    'index_name' => $info['index_name'],
                    'data_type' => $info['data_type'],
                    'frm_index_number' => $frm_index_number,
                    'create_at' => Carbon::now(),
                    'create_user' => $user->family_name . $user->given_name
                ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.create_frm_index')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.create_frm_index')]]);
    }

    /**
     * 選択したの帳票項目設定を更新
     * @param integer $id 帳票項目ID
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request){
        $user = \Auth::user();
        $info = $request->get('item');

        if(!$id){
            return response()->json(['status' => false, 'message' => [__('message.false.get_frm_index_id')]]);
        }

        if(!$info || !isset($info['index_name']) || !isset($info['data_type'])) {
            return response()->json(['status' => false, 'message' => [__('message.false.frm_index_requisite')]]);
        }

        $frm_index = DB::table('frm_index')->where('id', $id)->first();
        $frm_indexNo = 'frm_index'.$frm_index->frm_index_number;
        $frm_indexNo_col = $frm_indexNo.'_col';

        // 重複チェック
        if($frm_index->index_name != $info['index_name']){
            $other_frm_index = DB::table('frm_index')
                ->where('mst_company_id', $user->mst_company_id)
                ->where('index_name', $info['index_name'])
                ->first();
            if($other_frm_index){
                return response()->json(['status' => false, 'message' => [__('message.false.frm_index_exist')]]);
            }
        }

        DB::beginTransaction();
        try{

            DB::table('frm_index')
                ->where('id', $id)
                ->update([
                    'index_name' => $info['index_name'],
                    'data_type' => $info['data_type'],
                    'update_at' => Carbon::now(),
                    'update_user' => $user->family_name . $user->given_name
                ]);
            //データ型変更の場合、frm_others_colsとfrm_others_dataに対応するデータを空にする
            if($info['data_type'] != $frm_index->data_type){
                DB::table('frm_others_cols')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->update([
                        $frm_indexNo_col => '',
                        'update_at' => Carbon::now(),
                        'update_user' => $user->family_name . $user->given_name
                    ]);
                DB::table('frm_others_data')
                    ->where('mst_company_id', $user->mst_company_id)
                    ->update([
                        $frm_indexNo => '',
                        'update_at' => Carbon::now(),
                        'update_user' => $user->family_name . $user->given_name
                    ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.update_frm_index')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.update_frm_index')]]);
    }

    /**
     * 選択したの帳票項目設定を削除
     * @param integer $id 帳票項目ID
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request){
        $user   = $request->user();
        if(!$id){
            return response()->json(['status' => false, 'message' => [__('message.false.get_frm_index_id')]]);
        }
        $item = DB::table('frm_index')->where('mst_company_id',$user->mst_company_id)->find($id);
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        $frm_indexNo = 'frm_index'.$item->frm_index_number;
        $frm_indexNo_col = $frm_indexNo.'_col';

        DB::beginTransaction();
        try{
            // 帳票項目削除
            DB::table('frm_index')
                ->where('mst_company_id',$user->mst_company_id)
                ->where('id',$id)
                ->delete();
            //帳票項目削除の場合、frm_others_colsとfrm_others_dataに対応するデータを空にする
            DB::table('frm_others_cols')
                ->where('mst_company_id', $user->mst_company_id)
                ->update([
                    $frm_indexNo_col => '',
                    'update_at' => Carbon::now(),
                    'update_user' => $user->family_name . $user->given_name
                ]);
            DB::table('frm_others_data')
                ->where('mst_company_id', $user->mst_company_id)
                ->update([
                    $frm_indexNo => '',
                    'update_at' => Carbon::now(),
                    'update_user' => $user->family_name . $user->given_name
                ]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.delete_frm_index')] ]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.delete_frm_index')]]);
    }
}
