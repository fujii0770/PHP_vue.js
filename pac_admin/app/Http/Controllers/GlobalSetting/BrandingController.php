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
use App\Models\Branding;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;

class BrandingController extends AdminController
{

    private $model;

    private $model_type;

    private $modelPermission;

    public function __construct(Branding $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        $branding = $this->model->where('mst_company_id',$user->mst_company_id)->first();
        
        $this->assign('branding', $branding);
        $this->setMetaTitle("ブランディング設定");
        $this->addScript('jscolor', asset("/js/libs/jscolor.js"));
        return $this->render('GlobalSetting.Settings.branding');
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
        $branding = $this->model->select(['id','background_color','color'])->where('mst_company_id',$user->mst_company_id)->first();
        $isNew = false;
        if(!$branding){
            $branding                   = new $this->model;
            $branding->mst_company_id   = $user->mst_company_id;
        }

        $branding->background_color   = $request->get('background_color');
        $branding->color              = $request->get('color');
        $logoType                     = $request->get('logoType');
         
        if($logoType == 'default'){
            $branding->logo_file_data = "";
        }else  if($logoType == 'custome' AND $request->file('file_logo')){
            $filePath = $request->file('file_logo');
            try {
                $img = Image::make($filePath);
            }catch (NotReadableException $e){
                Log::warning($e->getMessage() . $e->getTraceAsString());
                return response()->json(['status' => false, 'message' => '画像ファイルはPNG/JPG/GIF形式をご利用ください。']);
            }catch (\Exception $e){
                Log::error($e->getMessage() . $e->getTraceAsString());
                return response()->json(['status' => false, 'message' => 'ブランディング更新処理失敗しました。']);
            }
            if ($img->getWidth() > 300 || $img->getHeight() > 50){
                $img->resize(300, 50, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save($filePath);
            }
            $branding->logo_file_data = \base64_encode(file_get_contents($filePath));
        }
 
        $branding->save();

        return response()->json(['status' => true, 'message' => [__('message.success.save_limit')]]);
    }

}