<?php

namespace App\Http\Controllers;

use GuzzleHttp\RequestOptions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;


class loginSettingController extends AdminController
{


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Company
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
      $this->setMetaTitle("ログイン設定画面");
        
      return $this->render('LoginSetting.index');

    }

    /**
     * show2.pngの画像保存処理
     *
     */
    public function imageChange(Request $request)
    {

      $path = dirname( __FILE__ , 4) . '/public/images/login/';

      $path2 = dirname( __FILE__ , 5) . '/pac_user/public/images/login/';

      $storagepath = dirname( __FILE__ , 4) . '/storage/app/';

        $image = $request->file('upload_file');

        $s3path =  config('filesystems.prefix_path') . '/' .config('app.s3_login_root_folder');
        $isFolderExist = Storage::disk('s3')->exists($s3path);

        if (!$isFolderExist) {
            Storage::disk('s3')->makeDirectory($s3path);
            Storage::disk('s3')->makeDirectory($s3path.'/loginSetting');

            $s3path = $s3path.'/'.'loginSetting/';
            Storage::disk('s3')->makeDirectory($s3path);
        }else{
            $s3path = $s3path.'/'.'loginSetting/';
        }

        Storage::disk('s3')->putfileAs($s3path.'/', $image, 'show2.png', 'pub');

      $this->setMetaTitle("ログイン設定画面");
      return $this->render('LoginSetting.ChangeTextDone');

    }

    /**
     * login_r.txtの書き込み処理
     *
     */
    public function write(Request $request)
    {

      $file1 = dirname( __FILE__ , 4) . '/public/login_r.txt';

      $file2 = dirname( __FILE__ , 5) . '/pac_user/public/login_r.txt';


      $data1 = $request->textBox_contents;

          $s3path =  config('filesystems.prefix_path') . '/' .config('app.s3_login_root_folder');
          $isFolderExist = Storage::disk('s3')->exists($s3path);
            
          if (!$isFolderExist) {
              Storage::disk('s3')->makeDirectory($s3path);
              Storage::disk('s3')->makeDirectory($s3path.'/loginSetting');
  
              $s3path = $s3path.'/'.'loginSetting/';
              Storage::disk('s3')->makeDirectory($s3path);
          }else{
              $s3path = $s3path.'/'.'loginSetting/';
          }

          Storage::disk('s3')->put($s3path.'/'.'login_r.txt', $data1);
            
      $this->setMetaTitle("ログイン設定画面");
      return $this->render('LoginSetting.ChangeTextDone');

    }

    /**
     * top_link.txtの書き込み処理
     *
     */
    public function writeurl(Request $request)
    {

      $file1 = dirname( __FILE__ , 4) . '/public/top_link.txt';

      $file2 = dirname( __FILE__ , 5) . '/pac_user/public/top_link.txt';

      $data2 = $request->textBox_contentsurl;

        $text = $data2.'
        ';

          $s3path =  config('filesystems.prefix_path') . '/' .config('app.s3_login_root_folder');
          $isFolderExist = Storage::disk('s3')->exists($s3path);

          if (!$isFolderExist) {
            Storage::disk('s3')->makeDirectory($s3path);
            Storage::disk('s3')->makeDirectory($s3path.'/loginSetting');

            $s3path = $s3path.'/'.'loginSetting/';
            Storage::disk('s3')->makeDirectory($s3path);
        }else{
            $s3path = $s3path.'/'.'loginSetting/';
        }

        Storage::disk('s3')->put($s3path.'/'.'top_link.txt', $text);
           
        $this->setMetaTitle("ログイン設定画面");
        return $this->render('LoginSetting.ChangeTextDone');

    }
}