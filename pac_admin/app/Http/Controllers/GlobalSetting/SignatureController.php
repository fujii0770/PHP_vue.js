<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController; 
use App\Http\Utils\PermissionUtils;
use DB;
use Illuminate\Support\Facades\Log;
use Storage;

class SignatureController extends AdminController
{
    //
    public function index(Request $request){
        $user = \Auth::user();
        $company = DB::table('mst_company')->where('id',$user->mst_company_id)->first();
        $this->assign('company', $company);
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->assign('allow_create', $user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_CREATE));
        $this->assign('allow_update', $user->can(PermissionUtils::PERMISSION_ADMINISTRATOR_SETTINGS_UPDATE));
        $this->assign('disabled', !$company->signature_flg);
        
        $this->setMetaTitle("電子署名設定");        
        return $this->render('GlobalSetting.Signature.index');
    }

    public function show($id){
        $user = \Auth::user();
        $company = DB::table('mst_company')->where('id',$user->mst_company_id)->first();
        if(!isset($company)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'info' => $company]);
    }

    public function update(Request $request)
    {
        $user = \Auth::user();
        try{
            if(isset($request['create'])){
                if ($request->hasFile('file') && isset($request['password']) && $request['password']) {
                    $file = $request->file('file');
                    //delete folder old file
                    Storage::deleteDirectory('certificate/' .$user->mst_company_id);
                    $original_name = $file->getClientOriginalName();
                    $folderPath = 'certificate/' .$user->mst_company_id;
                    Storage::disk('local')->putFileAs($folderPath, $file,$original_name);
                    $filePath = storage_path('app/'.$folderPath.'/'.$original_name);
                    $certificateName = $original_name;
                    $certs = array();

                    if (openssl_pkcs12_read(file_get_contents($filePath), $certs, $request['password'])) {
                        if (isset($certs['cert'])){
                            $certInfos = openssl_x509_parse($certs['cert']);
                            if (isset($certInfos['subject']) && isset($certInfos['subject']['CN'])){
                                $certificateName = $certInfos['subject']['CN'];
                            }
                        }
                    } else {
                        return response()->json(['status' => false, 'message' => ['電子証明書登録処理に失敗しました。パスワードが間違っています。'] ]);
                    }

                    $settingCertificate = [
                        'certificate_name' => $certificateName,
                        'certificate_flg' => 0,
                        'certificate_destination' => $filePath,
                        'certificate_pwd' => $request['password'],
                    ];
                    $messageTrue = [__('message.success.create_setting_certificate')];
                    $messageFalse = [__('message.false.create_setting_certificate')];
                }else{
                    return response()->json(['status' => false, 'message' => [__('message.false.create_setting_certificate_without_pwd')] ]);
                }
            }else{
                $settingCertificate = [
                    'certificate_flg' => $request['certificate_flg'],
                ];
                $messageTrue = [__('message.success.save_setting_certificate')];
                $messageFalse = [__('message.false.save_setting_certificate')];
            }
            DB::table('mst_company')
                ->where('id',$user->mst_company_id)
                ->update($settingCertificate);
            return response()->json(['status' => true, 'message' => $messageTrue]);
        }catch(\Exception $e){
            if (!$messageFalse){
                $messageFalse = [__('message.false.create_setting_certificate')];
            }
            return response()->json(['status' => false, 'message' => $messageFalse ]);
        }
    }    

    public function delete(Request $request)
    {
        try{
            $user = \Auth::user();
            DB::table('mst_company')
                ->where('id',$user->mst_company_id)
                ->update([
                    'certificate_destination' => '',
                    'certificate_flg' => 0,
                    'certificate_name' => '',
                ]);
            return response()->json(['status' => true, 'message' => [__('message.success.delete_setting_certificate')]]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['status' => false, 'message' => [__('message.false.delete_setting_certificate')] ]);
        }
    }
}
