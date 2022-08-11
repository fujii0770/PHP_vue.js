<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Permission;
use App\Models\PasswordPolicy;
use Illuminate\Support\Facades\Storage;

class PasswordPolicyController extends AdminController
{

    private $model;

    private $permisstion;
  

    public function __construct(PasswordPolicy $model, Permission $permisstion)
    {
        parent::__construct();
        $this->model        = $model;
        $this->permisstion  = $permisstion;
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();

        $passwordPolicy   = $this->model->where('mst_company_id',$user->mst_company_id)->first();
        
        $this->assign('passwordPolicy', $passwordPolicy);

        $this->setMetaTitle("パスワードポリシー設定");
        return $this->render('GlobalSetting.Settings.PasswordPolicy');
    }
 
    /**
     * Store a DateStamp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
         $passwordPolicy = $request->all();
         $validator = Validator::make($passwordPolicy, $this->model->rules());
         if ($validator->fails())
         {
             $message = $validator->messages();
             $message_all = $message->all();
             return response()->json(['status' => false,'message' => $message_all]);
         }

         $item   = $this->model->where('mst_company_id',$user->mst_company_id)->first();

         if(!$item){
             $item = new $this->model;  
             $item->mst_company_id  = $user->mst_company_id;
             $item->create_user     = $user->getFullName();          
         } 
         $item->update_user = $user->getFullName();
         $item->fill($passwordPolicy);
         $item->save();

        return response()->json(['status' => true, 'message' => [__('message.success.save_setting_password_policy')]]);
    }

}