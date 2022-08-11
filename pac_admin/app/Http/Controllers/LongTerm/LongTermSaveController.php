<?php

namespace App\Http\Controllers\LongTerm;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use DB;

class LongTermSaveController extends AdminController
{
    //
    public function show(Request $request)
    {
        $user = \Auth::user();

        $longTermStorage = DB::table('mst_long_term_save')->where('mst_company_id',$user->mst_company_id)->first();
        $this->assign('longTermStorage', $longTermStorage);
        $this->setMetaTitle("長期保管設定");

        return $this->render('LongTerm.LongTermStorage');
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        $input = $request->all();
        try{
            $longTermStorage = DB::table('mst_long_term_save')->where('mst_company_id',$user->mst_company_id)->first();
            $validator = Validator::make($input,[
                'auto_save_days' => 'required|numeric|min:1',
                'auto_save' => 'required|numeric',
                // PAC_5-2318  add field long_term_storage_delete_flg 文書の削除
                'long_term_storage_delete_flg'=>'required']);
            if ($validator->fails())
            {
                $message = $validator->messages();
                $message_all = $message->all();
                return response()->json(['status' => false,'message' => $message_all]);
            }

            if(!$longTermStorage){
                DB::table('mst_long_term_save')
                ->insert([
                    'mst_company_id' => $user->mst_company_id,
                    'auto_save' => $input['auto_save'],
                    'auto_save_days' => $input['auto_save_days'],
                    'create_user' => $user->getFullName(),
                    'create_at' => Carbon::now()
                ]);
            }else{
                DB::table('mst_long_term_save')
                    ->where('mst_company_id',$user->mst_company_id)
                    ->update([
                        'auto_save' => $input['auto_save'],
                        'auto_save_days' => $input['auto_save_days'],
                        'update_user' => $user->getFullName(),
                        'update_at' => Carbon::now()
                ]);
            }

        }catch(\Exception $e){

            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.save_mst_long_term_save')]]);
        }

        return response()->json(['status' => true, 'message' => [__('message.success.save_mst_long_term_save')]]);
    }
}
