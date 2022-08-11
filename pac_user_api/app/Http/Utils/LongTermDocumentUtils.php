<?php

namespace App\Http\Utils;

use App\Utils\OfficeConvertApiUtils;
use App\Utils\PDFUtils;
use GuzzleHttp\Exception\ServerException;
use Howtomakeaturn\PDFInfo\PDFInfo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Knox\PopplerPhp\Constants;
use Knox\PopplerPhp\PdfToCairo;

class LongTermDocumentUtils
{

    public static  function ToPdfAndImg($user,$file,$name)
    {
        $realFileExtension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getPathName();
        $uniquePath=(new \DateTime())->format('Y/m/d/').config('app.edition_flg').config('app.app_server_env').config('app.pac_contract_server').'/'.$user->mst_company_id;
        if ($realFileExtension != 'pdf'){
            // 変換前にフォルダが存在している必要がある
            if (!File::exists(storage_path("app/uploads/$uniquePath"))){
                File::makeDirectory(storage_path("app/uploads/$uniquePath"), 0777, true);
            }
            $stored_path = 'uploads/'.$uniquePath;
            rename($filePath, "$filePath.$realFileExtension");
            $filePath = "$filePath.$realFileExtension";
            $errorResponse = self::tryConvertOfficeToPdf($filePath, storage_path("app/$stored_path/$name"));
            if ($errorResponse) {
                // 変換失敗
                return $errorResponse;
            }
//                     ファイル変換成功したので、元のword,excelファイルをpdfと同じディレクトリに保存する
//                    $savedOfficeFileName = substr($stored_basename, 0, -1 * strlen("pdf")) . $realFileExtension;
//                    File::copy($filePath, storage_path("app/$stored_path") . "/" . $savedOfficeFileName);
                    $stored_path = "$stored_path/$name";


        }else{

            $stored_path = 'uploads/'.$uniquePath;
            Storage::putfileAs($stored_path, $file, $name);
            $stored_path = "$stored_path/$name";
//                    $errorMessage = self::checkAcceptablePdf(storage_path('app/'.$stored_path));
//                    if ($errorMessage) {
//                        return Response::json(['status'=>false, 'message'=>$errorMessage, 'data'=> null], 500);
//                    }
        }
    self::processGetPage(1,$stored_path,false);
    self::processGetPage(1,$stored_path,true);
    return $stored_path;

    }
    /**
     * Office文書からPDFへの変換を試みる
     * 成功した場合 null を、そうでなければクライアントへ返すためのエラーレスポンスを返す
     *
     * @param string $officeFilePath 入力ファイルパス (Word, Excel)
     * @param string $outputFilePath 出力ファイルパス (PDF)
     */
    private static function tryConvertOfficeToPdf(string $officeFilePath, string $outputFilePath): ?\Illuminate\Http\JsonResponse {
        // アップロード対応しない拡張子は弾く
        $extension = pathinfo($officeFilePath, PATHINFO_EXTENSION);

        $supportedOfficeExtensions = ["doc", "docx", "xls", "xlsx"];
        $isSupportedOfficeExtension = in_array($extension, $supportedOfficeExtensions, true);
        if (!$isSupportedOfficeExtension) {
            Log::debug("file extension not supported. ($extension)");

            return Response::json([
                'status' => false,
                'message' => "対応していない拡張子のファイルです。",
                'data' => null
            ], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }

        Log::debug("OfficeConverter start: $officeFilePath");

        try {
            OfficeConvertApiUtils::convertInstantly($officeFilePath, $outputFilePath);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        Log::debug("OfficeConverter success: $officeFilePath");
        return null;
    }
    /**
     * 受け付けられるPDFか判定する
     * 受け付けられる場合 null を、そうでなければエラーメッセージを返す
     */
    private static function checkAcceptablePdf(string $pdfPath) {
        Log::debug('Checking the file is readable。path：'.$pdfPath);

        $pdf = new PDFInfo($pdfPath);
        // ファイルが暗号化されているかチェック
        if ($pdf->encrypted != "no"){
            Log::debug("pdf file encrypted.");
            return "保護されたPDFファイルです。";
        }

        return null;
    }
    public static function charactersReplace($fileName)
    {
        $standardCharacter = array("が","ぎ","ぐ","げ","ご","ざ","じ","ず","ぜ","ぞ","だ","ぢ","づ","で","ど","ば","び","ぶ","べ","ぼ","ぱ","ぴ","ぷ","ぺ","ぽ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ");
        $realCharacter = array("が","ぎ","ぐ","げ","ご","ざ","じ","ず","ぜ","ぞ","だ","ぢ","づ","で","ど","ば","び","ぶ","べ","ぼ","ぱ","ぴ","ぷ","ぺ","ぽ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ");
        $realFileName =  str_replace($realCharacter, $standardCharacter, $fileName);

        return $realFileName;
    }
    private static function processGetPage( $page=1, $folderPath,$isThumbnail=false){
        ini_set("max_execution_time", 3600);
//        $userPath =  storage_path("app/$folderPath");
//        $pdfFilePath = $userPath.'/'.$file_name;
        $pdfFilePath=storage_path("app/$folderPath");
        $imageExists = false;
        if (!file_exists($pdfFilePath)) {
            Log::debug('PDF file is not exist');
        } else {
            $imgFolderPath = pathinfo($pdfFilePath)['filename'];
            $imgFilename = $isThumbnail ? "$page" : "$page-thumbnail";
            $imgFilePath = "$imgFolderPath/$imgFilename.jpg";

            try{
                $imageExists = file_exists($imgFilePath);
                if (!$imageExists){
                    $imageExists = self::generatePageImageWithRetry($pdfFilePath, (int)$page, $imgFilename, $isThumbnail, $imgFilePath);

                    if (!$imageExists) {
                        Log::debug('Cannot convert to image for page '.$page);
                    }
                }
            }catch (\Exception $e){
                Log::error($e->getMessage().$e->getTraceAsString());
            }
        }

        if ($imageExists) {
            $data = [
                'image' => "data:image/jpeg;base64," . base64_encode(file_get_contents($imgFilePath))
            ];
//            $data=base64_encode(file_get_contents($imgFilePath));
            $isOldClient = !$isThumbnail;
            if ($isOldClient) {
                $pdf = new PDFUtils($pdfFilePath);
                $page = $pdf->pagesInfo[$page - 1];

                preg_match('/([0-9\.]+) x ([0-9\.]+)/', $page["size"], $matches);
                $data['width'] = round($matches[1]*0.3527777778);
                $data['height'] = round($matches[2]*0.3527777778);
                $data['isPortraitPage'] = ($page["rot"] == 0 && $data['width'] < $data['height']);

            }
        } else {
//            $data = [
//                'image' => "data:image/png;base64," . base64_encode(file_get_contents(public_path('images/no-preview.png')))
//            ];
//            $data=base64_encode(file_get_contents(public_path('images/no-preview.png')));
            $data=false;
        }
        return $data;
    }
    private static function generatePageImageWithRetry(string $pdfFilePath, int $page, string $filePrefix, bool $isThumb, string $outPath) {
        // $outPath は チェック先ファイル名としてのみ利用、変更しても出力先にはならない

        $MAX_RETRY = 3;
        for ($i = 0; $i < $MAX_RETRY; $i++) {
            $isOk = self::generatePageImage($pdfFilePath, $page, $filePrefix, $isThumb);
            // OKの場合でもファイルがないことがある
            // その場合リトライする
            // PDFにより起こるのか、それ以外の要因で起こるのか不明
            $doRetry = $isOk && !file_exists($outPath);
            if (!$doRetry) {
                break;
            }
        }
        if ($i == 0) {
            // リトライなしの場合ログ出力しない
            return $isOk;
        } else {
            if ($doRetry) {
                // リトライが必要だが上限に達した
                Log::info("page image generation failed.");
                return false;
            } else {
                // リトライしてリトライがいらない状況になった
                Log::debug("page image generation with retry (retry=$i, isOk=".(int)$isOk.")");
                return $isOk;
            }
        }
    }
    /**
     * PDF指定ページの画像を生成する
     * 成功した場合は true 、失敗した場合は false を返す
     */
    private static function generatePageImage(string $pdfFilePath, int $page, string $filePrefix, bool $isThumb) {
        $cairo = new PdfToCairo($pdfFilePath);
        $cairo->startFromPage($page)->stopAtPage($page);
        $cairo->setRequireOutputDir(true);
        $cairo->setSubDirRequired(true);
        $cairo->setFlag(Constants::_SINGLE_FILE);
        $cairo->setOutputSubDir(pathinfo($pdfFilePath)['filename']);
        $cairo->setOutputFilenamePrefix($filePrefix);
        if ($isThumb) {
            $cairo->scalePagesTo(200);
        }

        $shellOutput = $cairo->generateJPG();
        return !$shellOutput;
    }
    public static function getFolderToBase(string $path)
    {
        $file=chunk_split(base64_encode(file_get_contents($path)));;
        return AppUtils::encrypt($file);
    }
    public  static function getFileToBase(string $path) :string
    {
        $file=File::get($path);
        return base64_encode($file);
    }
}