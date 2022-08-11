<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateStampInfoAPIRequest;
use App\Http\Requests\API\UpdateStampInfoAPIRequest;
use App\Models\StampInfo;
use App\Repositories\StampInfoRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Response;

/**
 * Class StampInfoController
 * @package App\Http\Controllers\API
 */

class StampInfoAPIController extends AppBaseController
{
    /** @var  StampInfoRepository */
    private $stampInfoRepository;

    public function __construct(StampInfoRepository $stampInfoRepo)
    {
        $this->stampInfoRepository = $stampInfoRepo;
    }

    /**
     * Display the specified Stamp Info.
     * GET|HEAD /stamp_infos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            /** @var User $user */
            $stamp_info = DB::table('stamp_info')->where('info_id', $id)->first();

            if (empty($stamp_info)) {
                return $this->sendError('印面情報が見つかりません');
            }

            $time_stamp_info = DB::table('time_stamp_info')
                                ->where('circular_document_id', $stamp_info->circular_document_id)
                                ->orderByDesc('create_at')->first();
            if (isset($time_stamp_info)) {
                $stamp_info->time_stamp = $time_stamp_info->create_at;
            } else {
                $stamp_info->time_stamp = null;
            }
            if (isset($stamp_info->stamp_image)&& $stamp_info->stamp_image){
                $fontPath=public_path('fonts/arial.ttf');
                $arrImg = getimagesize("data:image/png;base64,".$stamp_info->stamp_image);
                $img = Image::canvas($arrImg[0]+40, $arrImg[1]+40);
                $img->insert("data:image/png;base64,".$stamp_info->stamp_image,'center', 10, 10);
                $img->resize(100,100);
                $color='#e1e1e1';
                $size=12;
                $angle=35;
                $x=0;
                $y=22;
                $text='shachihata shachihata shachihata';
                for ($i=0;$i<7;$i++){
                    $img->text($text, $x, $y,function ($font)use ($fontPath,$color,$size,$angle){
                        $font->file($fontPath);
                        $font->size($size);
                        $font->angle($angle);
                        $font->color($color);
                    });
                    
                    $y+=22;  
                }
                $jpg=(string)$img->encode('png',75);
                $stamp_info->stamp_image=base64_encode($jpg); 
            }
            return $this->sendResponse($stamp_info, '印面情報の取得処理に成功しました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findByCircularDocumentId($circular_id, Request $request) {
        try {
            $finishedDate = '';
            // usingHash
            if (isset($request['usingHash']) && $request['usingHash'] && $request['finishedDate']) {
                $finishedDateKey = $request->get('finishedDate');
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            } else {
                if (isset($request['finishedDate']) && $request['finishedDate']) {  // 完了一覧、今月以外
                    $finishedDateKey = $request->get('finishedDate');
                    $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
                }
            }

            $circular_document_id = $request['circular_document_id'];
            if(!$circular_document_id) {
                return $this->sendError('circular_documCheckCircularPermissionent_id 必須項目です。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            $circular_document= DB::table("circular_document$finishedDate")->where('id', $circular_document_id)->where('circular_id', $circular_id)->first();

            if(!$circular_document) {
                return $this->sendError('circular_document_id 必須項目です。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $stamp_infos = DB::table('stamp_info')
                ->where('circular_document_id', $circular_document_id)
                ->get();

            return $this->sendResponse($stamp_infos, '印面情報の取得処理に成功しました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function findTextByCircularDocumentId($circular_id, Request $request) {
        try {
            $finishedDate = '';
            // usingHash
            if (isset($request['usingHash']) && $request['usingHash'] && $request['finishedDate']) {
                $finishedDateKey = $request->get('finishedDate');
                $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
            } else {
                if (isset($request['finishedDate']) && $request['finishedDate']) {  // 完了一覧、今月以外
                    $finishedDateKey = $request->get('finishedDate');
                    $finishedDate = Carbon::now()->addMonthsNoOverflow(-$finishedDateKey)->format('Ym');
                }
            }

            $circular_document_id = $request['circular_document_id'];
            if(!$circular_document_id) {
                return $this->sendError('circular_documCheckCircularPermissionent_id 必須項目です。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }
            $circular_document= DB::table("circular_document$finishedDate")->where('id', $circular_document_id)->where('circular_id', $circular_id)->first();

            if(!$circular_document) {
                return $this->sendError('circular_document_id 必須項目です。', \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $stamp_infos = DB::table('stamp_info')
                ->where('circular_document_id', $circular_document_id)
                ->get();

            $arrTextinfos = DB::table('text_info')
                ->where('circular_document_id', $circular_document_id)
                ->get();
            foreach($arrTextinfos as $key=> $textInfo){
                $textInfo->text = str_replace(["\r\n", "\n", "\r"], "<br />", $textInfo->text);
                $arrTextinfos[$key] = $textInfo;
            }

            return $this->sendResponse(['text'=>$arrTextinfos,'stamp' =>$stamp_infos], '印面情報の取得処理に成功しました。');
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 印章使用記録追加
     * @param Request $request
     */
    public function addToAssignStampInfo(Request $request)
    {
        try {
            $assign_stamp_infos = $request['assign_stamp_infos'];
            foreach ($assign_stamp_infos as $key => $assign_stamp_info) {
                $circular_id = DB::table('circular')->select('id')->where('origin_circular_id',$assign_stamp_info['circular_id'])->value('id');
                $assign_stamp_infos[$key]['circular_id'] = $circular_id;
            }

            DB::table('assign_stamp_info')->insert($assign_stamp_infos);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
