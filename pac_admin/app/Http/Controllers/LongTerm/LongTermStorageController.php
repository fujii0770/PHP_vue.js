<?php

namespace App\Http\Controllers\LongTerm;

use App\Http\Controllers\AdminController;
use App\Http\Utils\LongTermFolderUtils;
use App\Models\Company;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LongTermStorageController extends AdminController
{
    private $model;

    /**
     * LongTermStorageController constructor.
     * @param $model
     */
    public function __construct(Company $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * 初期化(画面表示)
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){
        try{
            $user = \Auth::user();
            $company = $this->model->find($user->mst_company_id);
            $itemsFolder = LongTermFolderUtils::getLongTermFolderTree($user->mst_company_id);
            $folder = [ 'parent_folder_id' => 0 , 'folder_id' => $company->long_term_default_folder_id];
            if ($company->long_term_folder_flg && $company->long_term_default_folder_id){
                $folder_item = DB::table('long_term_folder')->select('tree')->where('id',$company->long_term_default_folder_id)->first();
                $folder['parent_folder_id'] = array_filter(explode(',',$folder_item->tree));
            }

            $this->assign('company', $company);
            $this->assign('itemsFolder', $itemsFolder);
            $this->assign('folder', $folder);
            $this->setMetaTitle("長期保管設定");

            return $this->render('LongTerm.LongTermStorage');
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            throw new \Exception($ex);
        }
    }

    /**
     * 長期保存機能の自動保存 設定保存
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request){
        try{
            $user = \Auth::user();
            $upd_company = $request->all();
            $validator = Validator::make($upd_company,[
                'long_term_storage_delete_flg'=>'required',
                'long_term_storage_move_flg'=>'required',
                'auto_save_num'=>'required|numeric|max:500',
                'long_term_default_folder_id' => 'numeric'
            ]);
            if ($validator->fails())
            {
                $message = $validator->messages();
                $message_all = $message->all();
                return response()->json(['status' => false,'message' => $message_all]);
            }
            $company = $this->model->find($user->mst_company_id);
            if (!$upd_company['auto_save']){
                $upd_company['long_term_default_folder_id'] = 0;
            }
            $company->fill($upd_company);
            $company->save();

            return response()->json(['status' => true, 'message' => [__('message.success.long_term_auto_save')]]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.long_term_auto_save')]]);
        }

    }
}
