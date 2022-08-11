<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptUploadRequest;
use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\DeleteCircularDocRequest;
use App\Http\Requests\DownloadFileRequest;
use App\Http\Requests\GetPageRequest;
use App\Http\Requests\LoadCircularByHashRequest;
use App\Http\Requests\LoadCircularRequest;
use App\Http\Requests\RejectUploadRequest;
use App\Http\Requests\RenameCircularDocRequest;
use App\Http\Requests\SaveFileRequest;
use App\Http\Requests\UploadFileRequest;
use App\Http\Requests\AcceptUploadUserImageRequest;
use App\Oauth\DropboxProvider;
use App\Utils\AppUtils;
use App\Utils\BoxUtils;
use App\Utils\CircularAttachmentUtils;
use App\Utils\CircularDocumentUtils;
use App\Utils\DropboxUtils;
use App\Utils\UploadFileUtils;
use App\Utils\GoogleDriveUtils;
use App\Utils\OfficeConvertApiUtils;
use App\Utils\OneDriveUtils;
use App\Utils\PDFUtils;
use App\Utils\UserApiUtils;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\ServerException;
use Howtomakeaturn\PDFInfo\PDFInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Knox\PopplerPhp\Constants;
use Knox\PopplerPhp\PdfToCairo;
use Knox\PopplerPhp\PdfUnite;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Response;
use Session;
use Symfony\Component\Process\Process;
use App\Utils\StatusCodeUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use setasign\Fpdi\Fpdi;
use Intervention\Image\Facades\Image;

class ApplicationController extends Controller
{
    public function __invoke()
    {
        return view('application');
    }

    private function getBoxProvider(){
        return new GenericProvider(array_merge(config("oauth.box"), ['redirectUri' => config('oauth.return_url')]));
    }

    private function getOneDriveProvider(){
        return new GenericProvider(array_merge(config("oauth.onedrive"), ['redirectUri' => config('oauth.return_url'), 'scopes' => ['Files.ReadWrite.All']]));
    }

    private function getGoogleProvider(){
        return new GenericProvider(array_merge(config("oauth.google"), ['redirectUri' => config('oauth.return_url'), 'scopes' => ['https://www.googleapis.com/auth/drive']]));
    }

    private function getDropBoxProvider(){
        return new DropboxProvider(array_merge(config("oauth.dropbox"), ['redirectUri' => config('oauth.return_url')]));
    }

    private function getProvider($drive){
        if ($drive == 'box'){
            return $this->getBoxProvider();
        }else if ($drive == 'onedrive'){
            return $this->getOneDriveProvider();
        }else if ($drive == 'google'){
            return $this->getGoogleProvider();
        }else if ($drive == 'dropbox'){
            return $this->getDropBoxProvider();
        }
        return null;
    }

    public function uploadExternal(\Illuminate\Http\Request $request){
        $drive = $request->input('drive');
        $provider = $this->getProvider($drive);

        if ($provider){
            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            $authorizationUrl = $provider->getAuthorizationUrl();

            // Get the state generated for you and store it to the session.
            Session::put('oauth2state', $provider->getState());
            Session::put('drive', $drive);

            // Redirect the user to the authorization URL.
            return Redirect::to($authorizationUrl);
        }else{
            abort(404);
        }
    }

    public function externalDriveCallback(\Illuminate\Http\Request $request){
        if (empty($request->input('state')) || ($request->input('state') !== Session::get('oauth2state'))) {
            Session::remove('oauth2state');
            Session::remove('drive');
        } else {
            try {
                $drive = Session::get('drive');
                $provider = $this->getProvider($drive);

                if ($provider && !empty($request->input('code'))){
                    // Try to get an access token using the authorization code grant.
                    $accessToken = $provider->getAccessToken('authorization_code', [
                        'code' => $request->input('code')
                    ]);

                    $token = $accessToken->getToken();

                    if ($drive == 'box'){
                        Session::put(BoxUtils::BOX_API_TOKEN, $token);
                    }else if ($drive == 'onedrive'){
                        Session::put(OneDriveUtils::ONEDRIVE_API_TOKEN, $token);
                        $client = OneDriveUtils::getAuthorizedApiClient(false, ['headers' => [ 'Content-Type' => 'application/json' ]]);
                        if(!$client){
                            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
                        }
                        $uri = 'me/drive/root';
                        $result = $client->get($uri, []);
                        $resData = json_decode((string)$result->getBody());
                        $statusCode = $result->getStatusCode();
                        if($statusCode == 200){
                            if($resData->parentReference->driveType == 'business'){
                                return view('login-to-cloud-done', ['drive' => $drive,'message' => 'ご利用いただけません。']);
                            }
                        }else{
                            Log::warning("Get Root Drive ID response body: ".$result->getBody());
                            return Response::json(['status' => false, 'message' => $resData ? $resData->error->message: '', 'data' => null], $statusCode);
                        }
                    }else if ($drive == 'google'){
                        Session::put(GoogleDriveUtils::GOOGLE_API_TOKEN, $token);
                    }else if ($drive == 'dropbox'){
                        Session::put(DropboxUtils::DROPBOX_API_TOKEN, $token);
                    }

                    // We have an access token, which we may use in authenticated
                    // requests against the service provider's API.
                    /* echo 'Access Token: ' . $accessToken->getToken() . "<br>";
                    echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
                    echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
                    echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>"; */

                    // Using the access token, we may look up details about the
                    // resource owner.
                    //  $resourceOwner = $provider->getResourceOwner($accessToken);

                    //   var_export($resourceOwner->toArray());

                    return view('login-to-cloud-done', ['drive' => $drive,'message' => null]);
                }else{
                    Log::debug('Failed to get the access token');
                    Log::debug('$Provider or \'code\' is null');
                    //return Response::json(['status'=>false, 'message'=> '', 'data'=> null], 404);
                    return view('login-to-cloud-done', ['drive' => $drive,'message' => 'クラウドストレージの取得に失敗しました']);
                }
            } catch (IdentityProviderException $e) {
                Log::error('Failed to get the access token');
                Log::error($e->getMessage().$e->getTraceAsString());
                return Response::json(['status'=>false, 'message'=> $e->getMessage(), 'data'=> null], 500);
            }
        }
    }

    public function getCloudItems(\Illuminate\Http\Request $request){
        try {
            $folderId = $request->get('folder_id');

            $drive = $request->get('drive');

            if ($drive == 'box'){
                return $this->getBoxItems($folderId);
            }else if ($drive == 'onedrive'){
                return $this->getOneDriveItems($folderId);
            }else if ($drive == 'google'){
                return $this->getGoogleDriveItems($folderId);
            }else if ($drive == 'dropbox'){
                return $this->getDropboxItems($folderId);
            }
            return null;
        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> $ex->getMessage(), 'data'=> null], 500);
        }
    }

    public function uploadToCloud(\Illuminate\Http\Request $request){
        try {
            $params = $request->all();
            $file = $request->file;

            $drive = $request['drive'];

            if ($drive == 'box'){
                return $this->uploadToBox($params,$file);
            }else if ($drive == 'onedrive'){
                return $this->uploadToOneDrive($params,$file);
            }else if ($drive == 'google'){
                return $this->uploadToGoogleDrive($params,$file);
            }else if ($drive == 'dropbox'){
                return $this->uploadToDropbox($params,$file);
            }
            return null;
        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> $ex->getMessage(), 'data'=> null], 500);
        }
    }

    private function getBoxItems($folder_id = 0)
    {
        $client = BoxUtils::getAuthorizedApiClient();
        if (!$client) {
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $max_limit = config('oauth.box.item_max_limit');
        $uri = "folders/$folder_id?limit=$max_limit";
        $result = $client->get($uri);

        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200) {
            $total = $resData->item_collection->total_count;
            if ($total > $max_limit) {
                $uri = "folders/$folder_id?limit=$total";
                $result = $client->get($uri);
                if ($result && $result->getStatusCode() == 200) {
                    $resData = json_decode((string)$result->getBody());
                } else {
                    $this->printAPICallErrorLog($uri, $result);
                    return Response::json(['status' => false, 'message' => 'BOX API呼出失敗しました。', 'data' => null], $statusCode);
                }
            }
            return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $resData]);
        } else {
            $this->printAPICallErrorLog($uri, $result);
            return Response::json(['status' => false, 'message' => 'BOX API呼出失敗しました。', 'data' => null], $statusCode);
        }
    }

    private function getDropboxItems($folder_id = "") {
        $client = DropboxUtils::getAuthorizedApiClient(false, ['headers' => [ 'Content-Type' => 'application/json' ]]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        if (!$folder_id){
            $folder_id = "";
        }
        $uri = 'files/list_folder';
        $param = ['path' => $folder_id];
        $result = $client->post($uri,  [
            RequestOptions::JSON => $param
        ]);

        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            // Log::info($result->getBody());
            $responseRes = new \stdClass();
            $responseRes->type = 'folder';
            $responseRes->path_collection = new \stdClass();
            $responseRes->path_collection->entries = array();
            if ($folder_id){
                $responseRes->path_collection->entries[] = ['id' =>"0", 'name' => 'All Files'];

                $arrPath = explode('/', $folder_id);
                $previousPath = '';
                for($i = 0 ; $i < count($arrPath) - 1; $i++){
                    if ($arrPath[$i]){
                        $previousPath .= ('/'.$arrPath[$i]);
                        $responseRes->path_collection->entries[] = ['id' => $previousPath, 'name' => $arrPath[$i]];
                    }
                }
                $responseRes->id = $folder_id;
                $responseRes->name = $arrPath[count($arrPath) - 1];
            }else{
                $responseRes->id = "";
                $responseRes->name = "All Files";
            }

            $responseRes->item_collection = new \stdClass();
            $responseRes->item_collection->entries = array();
            foreach($resData->entries as $entry){
                $value = get_object_vars($entry);
                $responseRes->item_collection->entries[] = ['type' => $value['.tag'], 'name' => $value['name'], 'id' => $value['path_display']];
            }
            return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $responseRes]);
        }else {
            $this->printAPICallErrorLog($uri, $result, $param);
            return Response::json(['status' => false, 'message' => $resData ? $resData->message: '', 'data' => null], $statusCode);
        }
    }

    private function getGoogleDriveItems($folder_id = "") {
        $client = GoogleDriveUtils::getAuthorizedApiClient(false, ['headers' => [ 'Content-Type' => 'application/json' ]]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $responseRes = new \stdClass();
        $responseRes->type = 'folder';
        $responseRes->path_collection = new \stdClass();
        $responseRes->path_collection->entries = array();
        if ($folder_id) {
            // get breadcrumb
            $result = $client->get('files', [
                'query' => ['q' => "trashed = false and (mimeType = 'application/vnd.google-apps.folder')"]
            ]);
            $resData = json_decode((string)$result->getBody());
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200) {
                // Log::info($result->getBody());
                $allFolder = array();
                $currentFolder = null;
                foreach ($resData->items as $item) {
                    $allFolder[$item->id] = $item;
                    if ($item->id == $folder_id){
                        $currentFolder = $item;
                    }
                }
                if ($currentFolder){
                    $responseRes->id = $folder_id;
                    $responseRes->name = $currentFolder->title;
                    while($currentFolder->parents && count($currentFolder->parents) > 0){
                        if ($currentFolder->parents[0]->isRoot){
                            $responseRes->path_collection->entries[] = ['id' =>"0", 'name' => "All Files"];
                            break;
                        }else{
                            $currentFolder = $allFolder[$currentFolder->parents[0]->id];
                            $responseRes->path_collection->entries[] = ['id' =>$currentFolder->id, 'name' => $currentFolder->title];
                        }
                    }
                    $responseRes->path_collection->entries = array_reverse($responseRes->path_collection->entries);
                }
            } else {
                $this->printAPICallErrorLog('files', $result);
                return Response::json(['status' => false, 'message' => 'GoogleDrive処理失敗しました。', 'data' => null], $statusCode);
            }
        }else{
            $folder_id = 'root';
            $responseRes->id = "0";
            $responseRes->name = "All Files";
        }
        $result = $client->get('files',  [
            'query' => ['q' =>"trashed = false and '$folder_id' in parents"]
        ]);

        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            $responseRes->item_collection = new \stdClass();
            $responseRes->item_collection->entries = array();
            foreach($resData->items as $entry){
                $value = get_object_vars($entry);
                $responseRes->item_collection->entries[] = ['type' => ($value['mimeType'] == 'application/vnd.google-apps.folder')?'folder':'file', 'name' => $value['title'], 'id' => $value['id']];
            }

            return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $responseRes]);
        }else {
            $this->printAPICallErrorLog('files', $result, ['folder_id' => $folder_id]);
            return Response::json(['status' => false, 'message' => 'アップロード処理に失敗しました。', 'data' => null], $statusCode);
        }
    }

    private function getOneDriveItems($folder_id = "") {
        $client = OneDriveUtils::getAuthorizedApiClient(false, ['headers' => [ 'Content-Type' => 'application/json' ]]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $uri = '';
        if ($folder_id){
            $uri = 'me/drive/root';
            $result = $client->get($uri, []);
            $resData = json_decode((string)$result->getBody());
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200){
                $driveId = $resData->parentReference->driveId;
                Log::debug("Get Root Drive ID response body: ".$driveId);
                $folder_id = rawurldecode($folder_id);
                $folder_id = ltrim($folder_id, '/');
                $folder_id = rawurlencode($folder_id);
                $uri = "drives/$driveId/root:/$folder_id:/children";
                $result = $client->get(($uri), []);
            }else {
                Log::error("Get Root Drive ID response body: ".$result->getBody());
                return Response::json(['status' => false, 'message' => $resData ? $resData->error->message: '', 'data' => null], $statusCode);
            }
        }else{
            $uri = 'me/drive/root/children';
            $result = $client->get($uri, []);
        }
        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            $responseRes = new \stdClass();
            $responseRes->type = 'folder';
            $responseRes->path_collection = new \stdClass();
            $responseRes->path_collection->entries = array();
            if ($folder_id && $folder_id != 'root'){
                $responseRes->path_collection->entries[] = ['id' =>"0", 'name' => 'All Files'];

                $arrPath = explode('/', $folder_id);
                $previousPath = '';
                for($i = 0 ; $i < count($arrPath) - 1; $i++){
                    if ($arrPath[$i]){
                        $previousPath .= ('/'.$arrPath[$i]);
                        $responseRes->path_collection->entries[] = ['id' => $previousPath, 'name' => $arrPath[$i]];
                    }
                }
                $responseRes->id = $folder_id;
                $responseRes->name = $arrPath[count($arrPath) - 1];
            }else{
                $responseRes->id = "0";
                $responseRes->name = "All Files";
            }

            $responseRes->item_collection = new \stdClass();
            $responseRes->item_collection->entries = array();
            foreach($resData->value as $entry){
                $responseRes->item_collection->entries[] = ['type' => isset($entry->folder)?'folder':"file", 'name' => $entry->name,
                    'id' => str_replace('/drive/root:', '', $entry->parentReference->path).'/'.$entry->name];
            }

            return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $responseRes]);
        }else {
            $this->printAPICallErrorLog($uri, $result);
            return Response::json(['status' => false, 'message' => $resData ? $resData->error->message: '', 'data' => null], $statusCode);
        }
    }

    private function downloadBoxItems($file_id = 0) {
        $client = BoxUtils::getAuthorizedApiClient();
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $result = $client->get('files/'.$file_id.'/content');

        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            return $result->getBody();
        }else {
            Log::warning($result->getBody());
            return null;
        }
    }

    private function downloadOnedriveItems($fileId = 0) {
        $client = OneDriveUtils::getAuthorizedApiClient(true,['Content-Type'=> 'application/json']);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $result = $client->get('me/drive/root:'.$fileId.':/content');
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            return $result->getBody();
        }else {
            Log::warning($result->getBody());
            return null;
        }
    }

    private function downloadGoogleDriveItems($fileId = 0) {
        $client = GoogleDriveUtils::getAuthorizedApiClient(false, ['headers' => [ 'Content-Type' => 'application/json' ]]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $result = $client->get('files/'.$fileId.'?alt=media');
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            return $result->getBody();
        }else {
            Log::warning($result->getBody());
            return null;
        }

    }

    private function downloadDropboxItems($fileId = '') {
        $params = [
            "path" => $fileId
        ];
        $client = DropboxUtils::getAuthorizedApiClient(true,['Dropbox-API-Arg' => json_encode($params)]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $result = $client->get('files/download');

        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            return $result->getBody();
        }else {
            Log::warning($result->getBody());
            return null;
        }
    }

    private function uploadToBox($params, $file) {
        if($params['file_id'] == 'undefined'){
            $path = 'files/content';
        }else{
            $path = 'files/'.$params['file_id'].'/content';
        }
        $client = BoxUtils::getAuthorizedApiClient(true,['Content-Type'=> 'multipart/form-data']);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $result = $client->post($path, [
                'multipart' => [
                    [
                        'name'     => 'attributes',
                        'contents' => json_encode([ 'name' => $params['filename'],
                            'parent'=> ['id'=> $params['folder_id']] ]),
                    ],
                    [
                        'name'     => 'contents',
                        'contents' => file_get_contents($file),
                        'filename'=> $file
                    ],
                ],
            ]
        );
        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200 OR $statusCode == 201){
            return Response::json(['status' => true, 'message' => 'ファイルの保存に成功しました', 'data' => $resData]);
        }else {
            $this->printAPICallErrorLog($path, $result);
            return Response::json(['status' => false, 'message' => $resData ? $resData->message: 'ファイルの保存に失敗しました', 'data' => $resData],$statusCode);
        }
    }

    private function uploadToDropbox($params, $file) {
        $parameter = [
            "path" => $params['folder_id'].'/'.$params['filename'],
            "mode"=> "add",
            "autorename"=> true,
            "mute"=> false,
            "strict_conflict"=> false
        ];
        $client = DropboxUtils::getAuthorizedApiClient(true,['Content-Type'=> 'application/octet-stream', 'Dropbox-API-Arg' => json_encode($parameter)]);
        if(!$client){
            return Response::json(['status' => false, 'message' => 'アップロードに失敗しました', 'data' => null], 401);
        }
        $url ='files/upload';
        $request = new Psr7\Request(
            'POST',
            $url,
            ['Content-Type'=> 'application/octet-stream', 'Dropbox-API-Arg' => json_encode($parameter), 'Content-Length' => filesize($file)],
            new Psr7\LazyOpenStream( $file, 'r')
        );
        $result = $client->send($request);
        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200 OR $statusCode == 201){
            return Response::json(['status' => true, 'message' => 'アップロードに成功しました', 'data' => $resData]);
        }else {
            $this->printAPICallErrorLog($url, $result);
            $message = (string)$result->getBody();
            return Response::json(['status' => false, 'message' => $message?$message:'アップロードに失敗しました', 'data' => $resData],$statusCode);
        }
    }

    private function uploadToGoogleDrive($params, $file) {
        $client = GoogleDriveUtils::getAuthorizedApiClient(true,['Content-Type'=> 'application/json', 'Content-Length' => filesize($file)]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }

        if ($params['folder_id']){
        $result = $client->post('files?uploadType=resumable', [
                RequestOptions::JSON => [ 'title' => $params['filename'],
                    "parents"=> [[
                        "kind" => "drive#file",
                        "id"=> $params['folder_id']
                    ]]
                ]]
        );
        }else{
            $result = $client->post('files?uploadType=resumable', [
                    RequestOptions::JSON => [ 'title' => $params['filename']]]
            );
        }
        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            $url = $result->getHeader('Location')[0];
            $url = str_replace(config('oauth.google.upload_url'),"",$url);
            $client = GoogleDriveUtils::getAuthorizedApiClient(true,['Content-Type'=> 'application/octet-stream', 'Content-Length' => filesize($file)]);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $request = new Psr7\Request(
                'PUT',
                $url,
                ['Content-Type'=> 'application/pdf', 'Content-Length' => filesize($file)],
                new Psr7\LazyOpenStream( $file, 'r')
            );
            $result = $client->send($request);
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200 OR $statusCode == 201){
                return Response::json(['status' => true, 'message' => 'アップロードに成功しました', 'data' => $resData]);
            }else {
                $this->printAPICallErrorLog($url, $result);
                $message = (string)$result->getBody();
                return Response::json(['status' => false, 'message' => $message?$message:'アップロードに失敗しました', 'data' => $resData],$statusCode);
            }
        }else {
            $this->printAPICallErrorLog('files?uploadType=resumable', $result);
            $message = $result->getBody();
            return Response::json(['status' => false, 'message' => $message, 'data' => $resData],$statusCode);
        }
    }

    private function uploadToOneDrive($params, $file) {
        $client = OneDriveUtils::getAuthorizedApiClient(true,['Content-Type'=> 'application/json', 'Content-Length' => filesize($file)]);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        if ($params['folder_id']){
            $folderId = '/'.$params['folder_id'].'/'.$params['filename'];
        }else{
            $folderId = '/'.$params['filename'];
        }
        $uploadParam = ['item' => ["@microsoft.graph.conflictBehavior"=> "rename",
            "fileSystemInfo"=> [ "@odata.type"=> "microsoft.graph.fileSystemInfo" ]]];
        $result = $client->post("me/drive/root:$folderId:/createUploadSession", [
            RequestOptions::JSON => $uploadParam
        ]);

        $resData = json_decode((string)$result->getBody());
        $statusCode = $result->getStatusCode();
        if ($statusCode == 200){
            $url = $resData->uploadUrl;
            $length = filesize($file);
            $client = OneDriveUtils::getSimpleClient(['Content-Length' => $length]);
            $request = new Psr7\Request(
                'PUT',
                $url,
                ['Content-Type'=> 'application/pdf', 'Content-Length' => $length,'Content-Range' => ('bytes 0-'.($length - 1).'/'.$length)],
                new Psr7\LazyOpenStream( $file, 'r')
            );
            $result = $client->send($request);
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200 OR $statusCode == 201){
                return Response::json(['status' => true, 'message' => 'アップロードに成功しました', 'data' => $resData]);
            }else {
                $this->printAPICallErrorLog($url, $result);
                $message = (string)$result->getBody();
                return Response::json(['status' => false, 'message' => $message?$message:'アップロードに失敗しました', 'data' => $resData],$statusCode);
            }
        }else {
            $this->printAPICallErrorLog("me/drive/root:$folderId:/createUploadSession", $result);
            $message = $result->getBody();
            return Response::json(['status' => false, 'message' => $message?$message:'アップロードに失敗しました', 'data' => $resData],$statusCode);
        }
    }

    public function uploadFromCloud(\Illuminate\Http\Request $request)
    {
        try {
            $fileId = $request->get('file_id');
            $filename = $request->get('filename');
            $drive = $request->get('drive');
            $file_content = null;
            $max_document_size = $request->get('file_max_document_size') ? $request->get('file_max_document_size') : 8;

            if ($drive == 'box'){
                $file_content = $this->downloadBoxItems($fileId);
            }else if ($drive == 'onedrive'){
                $fileId = rawurldecode($fileId);
                $fileId = rawurlencode($fileId);
                $file_content =  $this->downloadOnedriveItems($fileId);
            }else if ($drive == 'google'){
                $file_content =  $this->downloadGoogleDriveItems($fileId);
            }else if ($drive == 'dropbox'){
                $file_content = $this->downloadDropboxItems($fileId);
            }

            $uniquePath = $this->getUniquePath($request);

            if (!File::exists(storage_path("app/uploads/$uniquePath"))){
                File::makeDirectory(storage_path("app/uploads/$uniquePath"), 0777, true);
            }

            $realFileNamePdf = preg_replace('#[/*\"|\'\`]#', '', $filename);
            preg_match('/[^.]*$/', $filename, $matches);
            $realFileExtension = strtolower($matches[0]);
//            $stored_path = 'uploads/'.$uniquePath. "/filecloud-" . time() . '.'.$realFileExtension;
            $stored_path = 'uploads/'.$uniquePath. "/filecloud-" . AppUtils::getUnique() . '.'.$realFileExtension;
            $file_path = storage_path('app/'.$stored_path);
            file_put_contents($file_path, $file_content);

            // ファイルサイズチェック
            $fileSize = filesize($file_path);
            if($fileSize > $max_document_size * 1024 * 1024){
                $intFileSizeMB = round($fileSize / 1024 /1024,1) == intval($fileSize / 1024 /1024) ?intval($fileSize / 1024 /1024) : round($fileSize / 1024 /1024,1);
                return Response::json(['status'=>false, 'message'=> "アップロードできる合計のファイルサイズは {$max_document_size}MB　以内です （現在ファイルは：　{$intFileSizeMB}MB　）", 'data'=> null], 500);
            }

            if ($realFileExtension != 'pdf'){
                Log::debug("Convert file $filename to pdf");
                $realFileNamePdf = preg_replace('/.[^.]*$/', '', $filename) . '.pdf';
//                $stored_basename = now()->timestamp. '.pdf';
                $stored_basename = hash('SHA256', $fileId.rand().AppUtils::getUnique()). '.pdf';
                $stored_path = 'uploads/'.$uniquePath;

                Log::debug("Convert from $file_path to $stored_basename in folder storage/app/$stored_path");
                try {
                    $errorResponse = self::tryConvertOfficeToPdf($file_path, storage_path("app/$stored_path/$stored_basename"));
                    if ($errorResponse) {
                        // 変換失敗
                        File::delete($file_path);
                        return $errorResponse;
                    }
                } catch (\Exception $ex) {
                    File::delete($file_path);
                    throw $ex;
                }
                $stored_path = "$stored_path/$stored_basename";
            }else{
                $errorMessage = self::checkAcceptablePdf(storage_path('app/'.$stored_path));
                if ($errorMessage) {
                    return Response::json(['status'=>false, 'message'=>$errorMessage, 'data'=> null], 500);
            }
            }

            $stored_basename = pathinfo($stored_path)['basename'];

            $fileInfo = [
                'name' => $realFileNamePdf,
                'server_file_name' => $stored_basename,
                'server_file_path' => $stored_path,
                'circular_document_id'=> null,
                'confidential_flg'=> null,
                'mst_company_id'=> null,
                'create_user_id'=> null,
                'origin_edition_flg'=> null,
                'origin_env_flg'=> null,
                'origin_server_flg'=> null,
            ];

            return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $fileInfo]);

        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $message = $ex->getMessage();
            return Response::json(['status'=>false, 'message'=>$message, 'data'=> null], 500);
        }
    }

    // fix PAC_5-1012 【セキュリティ強化】他社情報漏洩防止プログラム改善: ディレクトリ
    private function getUniquePath($request){
        $today = new \DateTime();
        $uniquePath = $today->format('Y/m/d/');

        $token = trim($request->bearerToken());
        if (!$token){
            $token = Session::get('accessToken', '');
        }
        if ($token && Session::has($token)){
            $user = Session::get($token);
            $uniquePath .= config('app.edition_flg').config('app.app_server_env').config('app.pac_contract_server').'/'.$user->mst_company_id;
        }else if (Session::has('hashUser')){
            $user = Session::get('hashUser');//is_external
            if ($user->is_external){
                $uniquePath .= $user->current_edition_flg.$user->current_env_flg.$user->current_server_flg.'/guest';
            }else{
                $uniquePath .= $user->current_edition_flg.$user->current_env_flg.$user->current_server_flg.'/'.$user->mst_company_id;
            }
        }else{
            $uniquePath .= config('app.edition_flg').config('app.app_server_env').config('app.pac_contract_server').'/guest';
        }

        Log::debug('Unique path folder: '.$uniquePath);
        return $uniquePath;
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

        $handle = fopen($pdfPath, "r");
        $file_info = finfo_open(FILEINFO_MIME_TYPE);// MIME型を返す
        if (filesize($pdfPath) && finfo_file($file_info, $pdfPath) == 'application/pdf'){
            $contents = fread($handle, filesize($pdfPath));
            fclose($handle);
            // ファイルが暗号化されているかチェック
            if (stristr($contents, "/Encrypt")){
                Log::debug("pdf file encrypted.");
                return "保護されたPDFファイルです。";
            }
        }else{
            return '対応していないファイルです。';
        }

        return null;
    }

    /**
     * クラウドからファイルをダウンロードして添付ファイルとして、serverに保存します。
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function uploadAttachmentFromCloud(\Illuminate\Http\Request $request){
        try{
            $fileId = $request->get('file_id');
            $drive = $request->get('drive');
            $filename = $request->get('filename');
            $circular_id = $request->get('circular_id');
            $file_max_attachment_size = $request->get('file_max_attachment_size') ? $request->get('file_max_attachment_size') : 500;
            $file_content = null;

            if ($drive == 'box'){
                $file_content = $this->downloadBoxItems($fileId);
            }else if ($drive == 'onedrive'){
                $file_content =  $this->downloadOnedriveItems($fileId);
            }else if ($drive == 'google'){
                $file_content =  $this->downloadGoogleDriveItems($fileId);
            }else if ($drive == 'dropbox'){
                $file_content = $this->downloadDropboxItems($fileId);
            }

            $uniquePath = $this->getUniquePath($request);

            if (!File::exists(storage_path("app/attachmentUploads/$uniquePath"))){
                File::makeDirectory(storage_path("app/attachmentUploads/$uniquePath"), 0777, true);
            }
            preg_match('/[^.]*$/', $filename, $matches);
            $realFileExtension = strtolower($matches[0]);
            $stored_path = 'attachmentUploads/'.$uniquePath.'/'.AppUtils::getUnique().'.'.$realFileExtension;

            $file_path = storage_path('app/'.$stored_path);
            file_put_contents($file_path,$file_content);

            $fileSize = filesize($file_path);
            if($fileSize > $file_max_attachment_size * 1024 * 1024){
                return Response::json(['status'=>false, 'message'=>  __('message.warning.attachment_request.upload_attachment_size_max',['file_max_attachment_size' => $file_max_attachment_size]), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $server_url = storage_path().'/app/'.$stored_path;

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }

            $result = $client->post("attachment", [
                RequestOptions::JSON => ['circular_id'=>$circular_id,'file_name' => $filename, 'server_url' => $server_url,'file_size' => $fileSize]
            ]);
            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::debug("response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $circular_attachment_id = $resultBody->data->circular_attachment_id;
            $circular_attachment = $resultBody->data->circular_attachment;
            return Response::json(['status' => true, 'message' => $resultBody->message, 'data' => ["circular_attachment_id" => $circular_attachment_id,'circular_attachment' => $circular_attachment]]);

        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> __('message.false.attachment_request.file_upload'), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 添付ファイルをアップロードして保存します。
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function attachmentUpload(\Illuminate\Http\Request $request){
        ini_set("max_execution_time", 3600);
        try{
            $file = $request->file;
            $circular_id = intval($request['circular_id']);
            $file_max_attachment_size = $request->get('file_max_attachment_size') ? $request->get('file_max_attachment_size') : 500;
            if (!$file->getClientMimeType()) {
                return Response::json(['status'=>false, 'message'=> __('message.false.attachment_request.file_attribute'), 'data'=> null], \Illuminate\Http\Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
            }

            $realFileName = $request->file->getClientOriginalName();
            $realFileName =  CircularDocumentUtils::charactersReplace($realFileName);
            $uniquePath = $this->getUniquePath($request);
            $fileSize = filesize($request->file->getPathName());

            if($fileSize > $file_max_attachment_size * 1024 * 1024){
                return Response::json(['status'=>false, 'message'=>  __('message.warning.attachment_request.upload_attachment_size_max',['file_max_attachment_size' => $file_max_attachment_size]), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $stored_path = $request->file->store('attachmentUploads/'.$uniquePath);
            $server_url = storage_path().'/app/'.$stored_path;

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            $result = $client->post("attachment", [
                RequestOptions::JSON => ['circular_id'=>$circular_id,'file_name' => $realFileName, 'server_url' => $server_url,'file_size' => $fileSize]
            ]);
            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::debug("response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $circular_attachment_id = $resultBody->data->circular_attachment_id;
            $circular_attachment = $resultBody->data->circular_attachment;

            return Response::json(['status' => true, 'message' => $resultBody->message, 'data' => ["circular_attachment_id" => $circular_attachment_id,'circular_attachment' => $circular_attachment]]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> __('message.false.attachment_request.file_upload'), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 添付ファイルを削除
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function deleteAttachment(\Illuminate\Http\Request $request){
        try{

            if (!isset($request['circular_attachment_id'])){
                return Response::json(['status' => false, 'message' => 'Request is invalid!', 'data' => null], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

            $attachment_id = $request->get('circular_attachment_id');

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if (!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null],  \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            $result = $client->get("attachmentDelete",[
                RequestOptions::JSON => [ 'circular_attachment_id' => $attachment_id]
            ]);
            $resultBody = json_decode((string)$result->getBody());

            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::debug("response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Response::json(['status' => true, 'message' => $resultBody->message, 'data'=> null]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=>  __('message.false.attachment_request.delete_attachment'), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 添付ファイルをダウンロード
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function downloadAttachment(\Illuminate\Http\Request $request){

        try{
           if (!isset($request['circular_attachment_id'])){
               return Response::json(['status' => false, 'message' => 'Request is invalid!', 'data' => null], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
           }

            $circular_attachment_id =$request['circular_attachment_id'];

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }

            $result = $client->get("attachmentDownload", [
                RequestOptions::JSON => ['circular_attachment_id' => $circular_attachment_id]
            ]);

            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::debug("response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $attachment = $resultBody->data;
            $file_data = null;

            if (config('app.app_server_env') == CircularAttachmentUtils::ENV_FLG_AWS){
                $file_data = chunk_split(base64_encode(Storage::disk('s3')->get($attachment->server_url)));
            }elseif (config('app.app_server_env') == CircularAttachmentUtils::ENV_FLG_K5){
                $file_data = chunk_split(base64_encode(Storage::disk('k5')->get($attachment->server_url)));
            }

            $circular_attachment = [
                'file_data' => $file_data,
                'circular_attachment_id' => $request['circular_attachment_id'],
                'file_name' => $attachment->file_name
            ];

            return Response::json(['status' => true, 'message' => $resultBody->message, 'data'=> $circular_attachment]);
        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> __('message.false.attachment_request.get_data'), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ファイルメール便をアップロード
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function mailFileUpload(\Illuminate\Http\Request $request)
    {
        ini_set("max_execution_time", 3600);
        try {
            $file = $request->file;
            $file_mail_size_single = $request->get('file_mail_size_single') ?: 500;
            $disk_mail_id = $request->get('disk_mail_id');
            if (!$file->getClientMimeType()) {
                return Response::json(['status' => false, 'message' => __('message.false.attachment_request.file_attribute'), 'data' => null], \Illuminate\Http\Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
            }

            $realFileName = $request->file->getClientOriginalName();
            $realFileName = CircularDocumentUtils::charactersReplace($realFileName);
            $uniquePath = $this->getUniquePath($request);
            $fileSize = filesize($request->file->getPathName());

            if ($fileSize > $file_mail_size_single * 1024 * 1024) {
                return Response::json(['status' => false, 'message' => __('message.warning.disk_mail_file.upload_size_max', ['max_size' => $file_mail_size_single]), 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $stored_path = $request->file->store('fileMailUploads/' . $uniquePath);
            $server_url = storage_path() . '/app/' . $stored_path;

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if (!$client) {
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            $result = $client->post("storeMailFile", [
                RequestOptions::JSON => [
                    'disk_mail_id' => $disk_mail_id,
                    'file_name' => $realFileName,
                    'server_url' => $server_url,
                    'file_size' => $fileSize
                ]
            ]);
            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::debug("response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $disk_mail_id = $resultBody->data->disk_mail_id;
            $disk_mail_file = $resultBody->data->disk_mail_file;

            return Response::json(['status' => true, 'message' => $resultBody->message, 'data' => ["disk_mail_id" => $disk_mail_id, "disk_mail_file" => $disk_mail_file]]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . $ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => __('message.false.attachment_request.file_upload'), 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * クラウドストレージからファイルを選択
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function downloadCloudMailFile(\Illuminate\Http\Request $request){
        try{
            $fileId = $request->get('file_id');
            $drive = $request->get('drive');
            $filename = $request->get('filename');
            $disk_mail_id = $request->get('disk_mail_id');
            $file_mail_size_single = $request->get('file_mail_size_single') ? $request->get('file_mail_size_single') : 500;
            $file_content = null;

            if ($drive == 'box'){
                $file_content = $this->downloadBoxItems($fileId);
            }else if ($drive == 'onedrive'){
                $file_content =  $this->downloadOnedriveItems($fileId);
            }else if ($drive == 'google'){
                $file_content =  $this->downloadGoogleDriveItems($fileId);
            }else if ($drive == 'dropbox'){
                $file_content = $this->downloadDropboxItems($fileId);
            }

            $uniquePath = $this->getUniquePath($request);

            if (!File::exists(storage_path("app/fileMailUploads/$uniquePath"))){
                File::makeDirectory(storage_path("app/fileMailUploads/$uniquePath"), 0777, true);
            }
            preg_match('/[^.]*$/', $filename, $matches);
            $realFileExtension = strtolower($matches[0]);
            $stored_path = 'fileMailUploads/'.$uniquePath.'/'.AppUtils::getUnique().'.'.$realFileExtension;

            $file_path = storage_path('app/'.$stored_path);
            file_put_contents($file_path,$file_content);

            $fileSize = filesize($file_path);
            if($fileSize > $file_mail_size_single * 1024 * 1024){
                return Response::json(['status'=>false, 'message'=>  __('message.warning.attachment_request.upload_attachment_size_max',['file_max_attachment_size' => $file_mail_size_single]), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $server_url = storage_path().'/app/'.$stored_path;

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }

            $result = $client->post("storeMailFile", [
                RequestOptions::JSON => [
                    'disk_mail_id' => $disk_mail_id,
                    'file_name' => $filename,
                    'server_url' => $server_url,
                    'file_size' => $fileSize
                ]
            ]);
            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() != \Illuminate\Http\Response::HTTP_OK) {
                Log::debug("response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $disk_mail_id = $resultBody->data->disk_mail_id;
            $disk_mail_file = $resultBody->data->disk_mail_file;

            return Response::json(['status' => true, 'message' => $resultBody->message, 'data' => ["disk_mail_id" => $disk_mail_id, "disk_mail_file" => $disk_mail_file]]);

        }catch (\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> __('message.false.attachment_request.file_upload'), 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ファイルメール便ダウンロード画面表示
     * @param $token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mailFileDownloadInit($token)
    {
        //パラメーター取得
        $decryptedHashing = AppUtils::decrypt($token, true);
        $params = explode('#', $decryptedHashing);
        $email = count($params) > 0 ? $params[0] : '';
        $disk_mail_id = count($params) > 1 ? $params[1] : '';
        $hash = count($params) > 2 ? $params[2] : '';
        Session::put('email', $email);
        Session::put('token', $hash);
        Session::put('disk_mail_id', $disk_mail_id);
        return $this->render('file_mail.download');
    }

    /**
     * ファイルメール便ダウンロード
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function mailFileDownload(\Illuminate\Http\Request $request)
    {
        //パラメーター取得
        $client = UserApiUtils::getApiClient();
        $code = $request->get('code');
        $result = $client->post("mailFileDownload", [
            RequestOptions::JSON => [
                'token' => Session::get('token'),
                'email' => Session::get('email'),
                'code' => $code,
                'disk_mail_id' => Session::get('disk_mail_id')
            ]
        ]);
        $resultCheck = \json_decode((string)$result->getBody());

        if ($result->getStatusCode() == 200) {
            return Response::json(['status_code' => 200, 'message' => '', 'data' => $resultCheck->data]);
        } elseif ($result->getStatusCode() == 401 || $result->getStatusCode() == 403) {
            return Response::json(['status_code' => $result->getStatusCode(), 'message' => $resultCheck->message, 'data' => []]);
        } else {
            return Response::json(['message' => $resultCheck->message ?: '予期せぬエラーが発生しました。 時間をおいてお試しください。'], $result->getStatusCode() ?: 500);
        }
    }

    /**
     * 文書アップロード
     *
     * @param UploadFileRequest $request
     * @return mixed
     */
    public function upload(UploadFileRequest $request)
    {
        try {
            // 文書種類チェック
            $file = $request->file;
            if (!$file->getClientMimeType()) {
                return Response::json(['status'=>false, 'message'=> "ファイル属性の取得に失敗しました。も一度やり直してください", 'data'=> null], 500);
            }
            $fileMimeType = $file->getClientMimeType();
            if (!in_array($fileMimeType, UploadFileUtils::FILE_TYPES)) {
                return Response::json(['status'=>false, 'message'=>'対応していないファイルです。', 'data'=> null], 422);
            }

            $max_document_size = $request->get('max_document_size')?$request->get('max_document_size'):8;

            $realFileName = $request->file->getClientOriginalName();
            $realFileName =  CircularDocumentUtils::charactersReplace($realFileName);
            $realFileName = preg_replace('#[/*\"|\'\`]#', '', $realFileName);

            $realFileExtension = strtolower($request->file->getClientOriginalExtension());

            if ($request->input('filename_url_encoded_flg', 0)){
                $realFileName = urldecode($realFileName);
            }

            $filePath = $request->file->getPathName();
            // アップロードファイル サイズチェック
            $fileSize = filesize($filePath);
            if($fileSize > $max_document_size * 1024 * 1024){
                $intFileSizeMB = round($fileSize / 1024 /1024,1) == intval($fileSize / 1024 /1024) ?intval($fileSize / 1024 /1024) : round($fileSize / 1024 /1024,1);
                return Response::json(['status'=>false, 'message'=> "アップロードできる合計のファイルサイズは {$max_document_size}MB　以内です （現在ファイルは：　{$intFileSizeMB}MB　）", 'data'=> null], 500);
            }

            $uniquePath = $this->getUniquePath($request);

            if ($realFileExtension != 'pdf'){
                Log::debug("upload Convert file $realFileName to pdf");

                // 変換前にフォルダが存在している必要がある
                if (!File::exists(storage_path("app/uploads/$uniquePath"))){
                    File::makeDirectory(storage_path("app/uploads/$uniquePath"), 0777, true);
                }

                // rename
                $fileId=$request->circular_id?$request->circular_id:0;

                $stored_basename = hash('SHA256', $fileId.rand().AppUtils::getUnique()). '.pdf';
                $stored_path = 'uploads/'.$uniquePath;

                $realFileNamePdf = substr_replace($realFileName, '_' , strripos($realFileName, '.'), 1).'.pdf';
                rename($filePath, "$filePath.$realFileExtension");
                $filePath = "$filePath.$realFileExtension";
                Log::debug("upload real file from $realFileName to tmp file $filePath");

                Log::debug("upload Convert from $filePath to $stored_basename in folder storage/app/$stored_path");
                $errorResponse = self::tryConvertOfficeToPdf($filePath, storage_path("app/$stored_path/$stored_basename"));
                if ($errorResponse) {
                    // 変換失敗
                    return $errorResponse;
                }

                // ファイル変換成功したので、元のword,excelファイルをpdfと同じディレクトリに保存する
                $savedOfficeFileName = substr($stored_basename, 0, -1 * strlen("pdf")) . $realFileExtension;
                File::copy($filePath, storage_path("app/$stored_path") . "/" . $savedOfficeFileName);

                $stored_path = "$stored_path/$stored_basename";
            }else{
                $stored_path = $request->file->store('uploads/'.$uniquePath);
                $realFileNamePdf = $realFileName;

                $errorMessage = self::checkAcceptablePdf(storage_path('app/'.$stored_path));
                if ($errorMessage) {
                    return Response::json(['status'=>false, 'message'=>$errorMessage, 'data'=> null], 500);
                }
            }

            $stored_basename = pathinfo($stored_path)['basename'];

            Log::debug("upload pdf path: $stored_path");
            $fileInfo = [
                'name' => $realFileNamePdf,
                'server_file_name' => $stored_basename,
                'origin_file_name_for_office_soft' => isset($savedOfficeFileName) ? $realFileName : null,
                'server_file_name_for_office_soft' => isset($savedOfficeFileName) ? $savedOfficeFileName : null,
                'server_file_path' => $stored_path,
                'circular_document_id'=> null,
                'confidential_flg'=> null,
                'mst_company_id'=> null,
                'create_user_id'=> null,
                'origin_edition_flg'=> null,
                'origin_env_flg'=> null,
                'origin_server_flg'=> null,
                'max_document_size' => $max_document_size,
            ];

            return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $fileInfo]);
        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $message = $ex->getMessage();
            return Response::json(['status'=>false, 'message'=>$message, 'data'=> null], 500);
        }
    }

    public function uploadFilesForPageBreak(\Illuminate\Http\Request $request){
        // ファイルをPythonサーバーにアップし、ファイル名とファイルタイプのリストを返す
        $fileInfoList = $request->input('convertedFiles');
        if (count($fileInfoList) != 1) {
            // 現状、複数ファイルは受け付けない
            // 処理したい場合は処理できるように変更する必要がある
            throw new \Exception("複数ファイルは処理できません");
        }

        $uniquePath = $this->getUniquePath($request);
        $body = [];
        foreach ($fileInfoList as $fileInfo) {
            // word,excelファイルの保存場所
            $savedFilepath = "/app/uploads/".$uniquePath.'/'.$fileInfo["serverFilename"];
            $body[] = [
                "name" => "files",
                "contents" => fopen(storage_path($savedFilepath), 'r'),
            ];
            $body[] = [
                "name" => "unique_names",
                "contents" => "/".$uniquePath."/".$fileInfo["serverFilename"]
            ];
        }

        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        try {
            $result = $client->post('upload_files', [
                'multipart' => $body,
            ]);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        $resData = json_decode((string)$result->getBody());
        // キーを保持
        $this->pushOfficeConvertFileKey($fileInfoList[0]["serverFilename"], $resData->file_key);

        $responseData = [["type" => $resData->type]];
        return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => $responseData]);
    }

    /**
     * 本サーバー上のファイル名と、OfficeConvertApi の fileKey を紐づけて保持
     * セッションに保持
     */
    private function pushOfficeConvertFileKey(string $serverFilename, string $office_convert_file_key): void {
        $uploadFiles = Session::get('uploadFiles', []);
        $uploadFiles[$serverFilename] = $office_convert_file_key;

        // 残す個数に上限を設ける
        $uploadFiles = array_slice($uploadFiles, -30, null, true);

        Session::put('uploadFiles', $uploadFiles);
    }

    /**
     * 本サーバー上のファイル名から、OfficeConvertApi の fileKey を取得
     * セッションから取得
     *
     * 見つからなければ例外を投げる
     */
    private function getOfficeConvertFileKey(string $filename): string {
        $uploadFiles = Session::get('uploadFiles', []);

        $office_convert_file_key = $uploadFiles[$filename] ?? null;
        if ($office_convert_file_key === null) {
            throw new \Exception("not in uploadFiles: " . $filename);
        }

        return $office_convert_file_key;
    }

    public function odsPreview(\Illuminate\Http\Request $request){
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        try {
            $result = $client->get("ods_preview", [
                RequestOptions::HEADERS => ['File-Key' => $apiFileKey]
            ]);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        $resData = json_decode((string)$result->getBody());
        return Response::json(['status' => true, 'message' => 'get ods preview data', 'data' => $resData]);
    }

    public function odtPreview(\Illuminate\Http\Request $request){
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        try {
            $result = $client->get("odt_preview", [
                RequestOptions::HEADERS => ['File-Key' => $apiFileKey]
            ]);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        $resData = json_decode((string)$result->getBody());
        return Response::json(['status' => true, 'message' => 'get odt preview data', 'data' => $resData]);
    }

    public function odtUpdate(\Illuminate\Http\Request $request){
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        $operation = $request->input('operation');
        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        try {
            $result = $client->post("odt_update", [
                RequestOptions::HEADERS => ['File-Key' => $apiFileKey],
                RequestOptions::JSON => ['operation' => $operation],
            ]);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        $resData = json_decode((string)$result->getBody());
        return Response::json(['status' => true, 'message' => 'update odt page breaks', 'data' => $resData]);
    }

    public function odtReset(\Illuminate\Http\Request $request){
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        try {
            $client->post("odt_reset", [
                RequestOptions::HEADERS => ['File-Key' => $apiFileKey]
            ]);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        return Response::json(['status' => true, 'message' => 'reset odt page breaks', 'data' => null]);
    }

    public function rejectPageBreaks(RejectUploadRequest $request){
        // 改ページ調整APIのファイルを削除後、捺印画面のアップロード中止処理を行う
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        try {
            $client->post("delete_files", [
                RequestOptions::JSON => ['file_keys' => [$apiFileKey]]
            ]);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        return $this->rejectUpload($request);
    }

    /**
     * 改ページ調整画面の「決定」ボタン処理、「プレビュー・捺印へ」ボタンクリック前
     * 改ページ位置を変更したPDFを取得し、「プレビュー・捺印へ」ボタン処理を行う
     */
    public function decidePageBreaksBeforeAcceptUpload(AcceptUploadRequest $request){
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        // pdfのファイルパス取得方法をacceptUpload関数と合わせる
        $uniquePath = $this->getUniquePath($request);
        $pdfFilepath = storage_path("/app/uploads/".$uniquePath.'/'.$request->input('pdfFilename'));
        $breaks = $request->input('breaks');

        try {
            self::decidePageBreaks($apiFileKey, $pdfFilepath, $breaks);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        // プレビュー・捺印へボタンと処理を合わせる
        return $this->acceptUpload($request);
    }

    public function acceptUpload(AcceptUploadRequest $request) {
        try {
            $uniquePath = $this->getUniquePath($request);
            $circular_id = intval($request['circular_id']);
            $templateId = intval($request['templateId']);

            $files = $request['files'];
            $intFileMaxSizeSet = 0;
            if(count($files) <= 0) {
                return Response::json(['status' => false, 'message' => 'Files is invalid!', 'data' => null], \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

                $filePaths = [];
                foreach ($files as $file) {
//                    $intFileMaxSizeSet = $file['max_document_size'];
                    $filePaths[] = storage_path() . "/app/" . $file['server_file_path'];
                }

            // アップロードファイルの合計サイズ
                $totalFileSize = 0;
                foreach ($filePaths as $filePath){
                    $totalFileSize += filesize($filePath);
                }
				// PAC_5-1971   メールの「回覧文書をみる」から表示した文書へAddFileでファイル追加ができない
                $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
                if(!$client){
                    return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
                }
                $objUser = $this->getUser();
                $intCompanyID = !empty($objUser) && is_object($objUser) &&  isset($objUser->mst_company_id) && !empty($objUser->mst_company_id) ? $objUser->mst_company_id : 0;
                $result = $client->get("setting/getMyCompanyConstraintsMaxDocumentSize/$intCompanyID/$circular_id", []);
                $resultBody = json_decode((string)$result->getBody());
                $max_document_size = 0;
                // PAC_5-1989
                if($result->getStatusCode() != 200 || !isset($resultBody->data) || !isset($resultBody->data->max_document_size)){
                    $request['usingHash'] = true;
                    $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
                    if(!$client){
                        return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
                    }
                    $result = $client->get("userByHashing");
                    if ($result->getStatusCode() == 200 && $resultBody && isset($resultBody->user) && isset($resultBody->user->max_document_size) &&  !empty($resultBody->user->max_document_size)) {
                        $max_document_size = $resultBody->user->max_document_size;
                    }else {
                        return Response::json(['status'=>false, 'message'=> 'ファイルを読み取れませんでした。', 'data'=> null], 500);
                    }
                }else{
                    $max_document_size = $resultBody->data->max_document_size;
                }
                // アップロードファイルの合計サイズが8MBを超えた場合
                if ($totalFileSize > $max_document_size * 1024 * 1024){
                    $intTotalFileSizeMB = round($totalFileSize / 1024 /1024,1) == intval($totalFileSize / 1024 /1024) ?intval($totalFileSize / 1024 /1024) : round($totalFileSize / 1024 /1024,1);
                    return Response::json(['status'=>false, 'message'=> "アップロードできる合計のファイルサイズは　{$max_document_size}MB　以内です （現在ファイルは：　{$intTotalFileSizeMB}MB　）", 'data'=> null], 413);
                }

            $first_file = $files[0];
            $server_file_name = hash('SHA256', $first_file['name'].rand().AppUtils::getUnique());

            $server_file_path = storage_path() . "/app/uploads/".$uniquePath.'/';

            // 元ファイルはまだここでは消さない
            // （失敗時再試行ができるように）
            if (count($files) > 1){
                $pdfUnite = new PdfUnite($filePaths);
                $pdfUnite->setRequireOutputDir(true);
                $pdfUnite->setSubDirRequired(true);
                $pdfUnite->setOutputSubDir('tmp');

                $pdfUnite->setOutputFilenamePrefix($server_file_name);
                $pdfUnite->generate();

                $merged_file_path = $pdfUnite->getOutputPath().'/';

                Log::debug("acceptUpload file copy: ".$merged_file_path.$server_file_name.".pdf to ".$server_file_path.$server_file_name.".pdf");
                File::move($merged_file_path.$server_file_name.'.pdf',  $server_file_path.$server_file_name.'.pdf');
            }else{
                $first_pdf_path = $filePaths[0];
                Log::debug("acceptUpload file copy: ".$first_pdf_path." to ".$server_file_path.$server_file_name.".pdf");
                File::copy($first_pdf_path, $server_file_path.$server_file_name.'.pdf');
            }
            //ファイルのパス
            $file_path = $server_file_path.$server_file_name.'.pdf';
            $stampClient = UserApiUtils::getPdfApiClient();
            //ファイルの注釈を全削除する、黒塗りは解消する
            $test_result = $stampClient->post("delPdfAnnotation",[
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($file_path,'r')
                    ]
                ]
            ]);
            if ($test_result->getStatusCode() == \Illuminate\Http\Response::HTTP_OK){
                File::delete($file_path);
                file_put_contents($file_path,$test_result->getBody());
            }else{
                Log::warning($test_result->getBody());
                return Response::json(['status' => false, 'message' => 'ファイルを読み取れませんでした。', 'data' => null], 500);
            }

            $imgPath = storage_path() . "/app/uploads/".$uniquePath.'/' .$server_file_name;
            File::makeDirectory($imgPath);

            $pdfBase64 = chunk_split(base64_encode(file_get_contents($server_file_path. $server_file_name.'.pdf')));

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $result = $client->post("circulars", [
                RequestOptions::JSON => [
                    'circular_id'=>$circular_id,
                    'file_name' => $first_file['name'],
                    'pdf_data' => $pdfBase64,
                    'usingHash'=> $request['usingHash'] ,
                    'isSpecialSiteFlg' => $request->get('special_sit_flg'),
                    'templateId'=>$templateId,
                ]
            ]);
            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() == 200) {
                $circular = $resultBody->data->circular;
                $circular_document_id = $resultBody->data->circular_document_id;

                $created_file = $first_file;
                unset($created_file['server_file_path']);
                $created_file['server_file_name'] = $server_file_name.'.pdf';
                $created_file['path'] = "uploads/".$uniquePath.'/'. $server_file_name.'.pdf';
                $created_file['circular_document_id'] = $circular_document_id;
                $created_file['confidential_flg'] = $resultBody->data->circular_document->confidential_flg;
                $created_file['mst_company_id'] = $resultBody->data->circular_document->create_company_id;
                $created_file['create_user_id'] = $resultBody->data->circular_document->create_user_id;
                $created_file['origin_edition_flg'] = $resultBody->data->circular_document->origin_edition_flg;
                $created_file['origin_env_flg'] = $resultBody->data->circular_document->origin_env_flg;
                $created_file['origin_server_flg'] = $resultBody->data->circular_document->origin_server_flg;
                $created_file['parent_send_order'] = $resultBody->data->circular_document->parent_send_order;

                $create_at_date = new DateTime($resultBody->data->circular_document->create_at, new DateTimeZone('UTC'));
                $create_at_date->setTimezone(new DateTimeZone('Asia/Tokyo'));
                $created_file['create_at'] = date_format($create_at_date,"Y-m-d H:i:s");

                $fileInfo = $this->previewFile($created_file);
                // PDF変換後の改ページ調整で楽観ロック用更新日時をセット
                $fileInfo['document_data_update_at'] = $resultBody->data->document_data_update_at;
                // 成功時のみ削除
                foreach ($filePaths as $filePath) {
                    File::delete($filePath);
                }
                return Response::json(['status' => true, 'message' => 'アップロード処理に成功しました。', 'data' => ["fileInfo" => $fileInfo, 'circular'=> $circular]]);
            } else {
                Log::debug("Login response body " . $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
            }

        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            $message = 'ファイルを読み取れませんでした。';
            return Response::json(['status'=>false, 'message'=> $message, 'data'=> null], 500);
        }

    }

    public function rejectUpload(RejectUploadRequest $request) {
        try {
            $files = $request['files'];
            if($files) {
                foreach ($files as $file) {
                    if(array_key_exists('status',$file) && $file['status'] == false) {
                        return Response::json(['status' => true, 'message' => 'Reject upload successful!', 'data' => null]);
                    }
                    $path = storage_path()."/app/";
                    $pdfFilePath = $path.$file['server_file_path'];
                    //$imgFolderPath = $path.$file['img_path'];
                    // File::delete($pdfFilePath);
                    File::delete(File::glob(substr($pdfFilePath, 0, -1 * strlen("pdf"))."*"));
                    //File::deleteDirectory($imgFolderPath);
                }
            }
            return Response::json(['status' => true, 'message' => 'Reject upload successful!', 'data' => null]);

        }catch(\Exception $ex){
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> $ex->getMessage(), 'data'=> null], 500);
        }
    }

    private function previewFile($file, $del_flg = false, $delete_email = null,$delete_at = null){
        ini_set("max_execution_time", 3600);
        $fileInfo = array();
        if ($file){
            // デフォルト値
            $delete_watermark_width_px = 592;
            $delete_watermark_height_px = 842;

            if ($file['path']){
                try{
                    $pdfFilePath = storage_path()."/app/".$file['path'];
                    if (file_exists($pdfFilePath)){
                        File::chmod($pdfFilePath,0755);

                        $pdf = new PDFUtils($pdfFilePath);
                        $pages = $pdf->getShownPagesInfo();

                        $delete_watermark_width_px = round($pages[0]['width_pt']*1.3333333333333333);
                        $delete_watermark_height_px = round($pages[0]['height_pt']*1.3333333333333333);

                        preg_match('/([0-9\.]+) x ([0-9\.]+)/', $pdf->pagesInfo[0]["size"] , $matches);

                        $fileInfo = array(
                            'circular_document_id'=>$file['circular_document_id'],
                            'origin_env_flg'=>$file['origin_env_flg'],
                            'origin_edition_flg'=>$file['origin_edition_flg'],
                            'origin_server_flg'=>$file['origin_server_flg'],
                            'confidential_flg'=>$file['confidential_flg'],
                            'mst_company_id'=>$file['mst_company_id'],
                            'create_user_id'=>$file['create_user_id'],
                            'name' => $file['name'],
                            'server_file_name'=> $file['server_file_name'],
                            'server_file_path'=>$file['path'],
                            'parent_send_order'=>$file['parent_send_order'],
                            'total_timestamp'=>isset($file['total_timestamp'])?$file['total_timestamp']:0,
                            'pages' => $pages,
                            'width_px' => round($matches[1]*1.3333333333333333),
                            'height_px' => round($matches[2]*1.3333333333333333),
							'comments' => isset($file['comments'])?$file['comments']:[],
                            'create_at' => $file['create_at'],
                            'sticky_notes' => isset($file['sticky_notes'])?$file['sticky_notes']:[],
                        );
                    }else{
                        // $fileList[] = array('id' => $fileId, 'name' => $fileId.'_name', 'maxpages' => $pdf->getNumberOfPages());
                    }
                }catch (\Exception $e){
                    Log::error($e->getMessage().$e->getTraceAsString());
                }
            }else{
                $fileInfo = array(
                    'circular_document_id'=>$file['circular_document_id'],
                    'confidential_flg'=>$file['confidential_flg'],
                    'origin_env_flg'=>$file['origin_env_flg'],
                    'origin_edition_flg'=>$file['origin_edition_flg'],
                    'origin_server_flg'=>$file['origin_server_flg'],
                    'mst_company_id'=>$file['mst_company_id'],
                    'create_user_id'=>$file['create_user_id'],
                    'name' => $file['name'],
                    'total_timestamp'=>$file['total_timestamp'],
                    'server_file_name'=> $file['server_file_name'],
                    'server_file_path'=>$file['path'],
                    'parent_send_order'=>$file['parent_send_order'],
                    'create_at' => $file['create_at'],
					'comments' => $file['comments'],
                );
            }
            $fileInfo['del_flg'] = false;
            $fileInfo['delete_watermark'] = null;

            if($del_flg && $delete_email && $delete_at) {
                $fileInfo['del_flg'] = true;
                $update_at = new \DateTime($delete_at);
                $fileInfo['delete_watermark'] = $this->generateDeleteWatermark($delete_watermark_width_px, $delete_watermark_height_px,
                                                                               $delete_email, $update_at->format('Y/m/d H:i:s'));
            }
        }
        return $fileInfo;
    }

    private function generateDeleteWatermark($watermark_w,$watermark_h, $email, $datetime) {
        try {

            $image = new \Imagick();
            $image->newImage($watermark_w, $watermark_h, new \ImagickPixel('none'));
            $watermark = new \Imagick();

            $text1 = $email;
            $text2 = $datetime;

            $draw = new \ImagickDraw();
            $watermark->newImage(150, 300, new \ImagickPixel('none'));

            // Set font properties
            $draw->setFont(public_path('/fonts/MS-Gothic.ttf'));
            $draw->setFillColor('#626262');
            $draw->setFillOpacity(1);

            // Position text at the top left of the watermark
            $draw->setGravity(\Imagick::GRAVITY_NORTHWEST);

            // Draw text on the watermark
            $watermark->annotateImage($draw, 5, 75, -30, $text1);

            // Position text at the bottom right of the watermark
            $draw->setGravity(\Imagick::GRAVITY_NORTHWEST);

            // Draw text on the watermark
            $watermark->annotateImage($draw, 5, 165, -30, $text2);


            // Repeatedly overlay watermark on image
            for ($w = 0; $w < $image->getImageWidth(); $w += 150) {
                for ($h = 0; $h < $image->getImageHeight(); $h += 200) {
                    $image->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $w, $h);
                }
            }

            // Set output image format
            $image->setImageFormat('png');

            $imgBuff = $image->getimageblob();

            // Output the new image
            return base64_encode($imgBuff);
        }catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return null;
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

    public function getPage(GetPageRequest $request){
        $uniquePath = $this->getUniquePath($request);
        return $this->processGetPage($request, "/app/uploads/".$uniquePath."/");
    }

    public function deleteCircularDocument(DeleteCircularDocRequest $request){
        $uniquePath = $this->getUniquePath($request);
        $file_path = request()->get('file_path');
        $circular_id = request()->get('circular_id');
        $circular_document_id = request()->get('circular_document_id');
        if ($file_path){
            try {
                if($circular_id) {
                    $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
                    if(!$client){
                        return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
                    }
                    $result = $client->delete("circulars/".$circular_id."/documents/".$circular_document_id, [
                        RequestOptions::JSON => ['circular_id' => $circular_id]
                    ]);
                    $resultBody = json_decode((string)$result->getBody());
                    if ($result->getStatusCode() != 200) {
                        Log::debug("Login response body " . $result->getBody());
                        return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
                    }
                }
                $path = storage_path()."/app/";
                $pdfFilePath = $path.$file_path;

                $imgFolderPath = $path.'uploads/'.$uniquePath.'/'.pathinfo($pdfFilePath)['filename'];

                File::delete($pdfFilePath);
                File::deleteDirectory($imgFolderPath);
                return Response::json(['status'=>true, 'message'=> '文書削除処理に成功しました。', 'data'=> null]);
            }catch (\Exception $e){
                Log::error($e->getMessage().$e->getTraceAsString());
                return Response::json(['status'=>false, 'message'=> '文書削除処理に失敗しました。', 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

            }
        }
    }

	/**
	 * PDFファイル名変更
	 * @param RenameCircularDocRequest $request
	 * @return mixed
	 */
	public function renameCircularDocument(RenameCircularDocRequest $request)
	{
		$circular_document_id = request()->get('circular_document_id');
		$file_name = request()->get('file_name');
		$circular_id = request()->get('circular_id');
		try {
			$client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
			if (!$client) {
				return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
			}
			$result = $client->put("circulars/".$circular_id."/documents/".$circular_document_id, [
				RequestOptions::JSON => ['file_name' => $file_name]
			]);
			$resultBody = json_decode((string)$result->getBody());
			if ($result->getStatusCode() != 200) {
				Log::debug("Login response body " . $result->getBody());
				return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
			}

			return Response::json(['status' => true, 'message' => '文書名変更処理に成功しました。', 'data' => null]);
		} catch (\Exception $e) {
			Log::error($e->getMessage() . $e->getTraceAsString());
			return Response::json(['status' => false, 'message' => '文書名変更処理に失敗しました。', 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);

		}
	}

    public function deleteStoredFiles(BaseFormRequest $request){
        try {
            //$userId = $this->getUser()->id;
            $files = request()->get('filepaths');

            if ($files && count($files) > 0 ){
                $path = storage_path()."/app/";
                foreach($files as $file_path) {
                    $pdfFilePath = $path.$file_path;
                    $pathInfos = pathinfo($pdfFilePath);
                    $imgFolderPath = $path.$pathInfos['dirname'].'/'.$pathInfos['filename'];
                    File::delete($pdfFilePath);
                    File::deleteDirectory($imgFolderPath);
                }
                return Response::json(['status'=>true, 'message'=> '文書削除処理に成功しました。', 'data'=> null]);
            }
        }catch (\Exception $e){
            Log::warning($e->getMessage().$e->getTraceAsString());
            return Response::json(['status'=>true, 'message'=> '文書削除処理に成功しました。', 'data'=> null]);

        }
    }

    /**
     * 改ページプレビューを確定する
     * 外部サーバーに PDF を出力させた後、終了したことを伝える
     * 改ページの更新も行う (ods のみ)
     *
     * 出力パスにファイルが存在する場合は上書きする
     * 処理失敗時は、出力パスにあるファイルを削除し、例外を投げる
     *
     * 特に、ServerException の処理は呼び出し側で行うこと
     */
    private static function decidePageBreaks(string $apiFileKey, string $pdfFilePath, $odsBreaks) {
        $client = OfficeConvertApiUtils::getApiClientForPageBreak();

        if (is_array($odsBreaks)) {
            // $odsBreaksが配列に勝手に変換されていたらオブジェクトに変換する
            // client(js)、api(python)ともに配列ではなく、オブジェクトを想定している
            $odsBreaks = (object) $odsBreaks;
        }

        // odsファイルのみ改ページを更新
        if ($odsBreaks !== null) {
            $client->post("ods_update", [
                RequestOptions::HEADERS => ['File-Key' => $apiFileKey],
                RequestOptions::JSON => ['breaks' => $odsBreaks],
            ]);
        }

        // pdfを取得し、置き換える(DB上ではない)
        $isSucceeded = false; // 失敗時の削除用
        $outResource = fopen($pdfFilePath, 'w+'); // finally を通るときにファイルが存在している必要があるため、この時点で作成
        try {
            $client->post("export", [
                RequestOptions::HEADERS => ['File-Key' => $apiFileKey],
                RequestOptions::SINK => $outResource,
            ]);

            $isSucceeded = true;
        } finally {
            if (!$isSucceeded) {
                // 中身はエラーレスポンスであり不要なため、削除する
                unlink($pdfFilePath);
            }
        }

        // python側のファイルを削除
        $client->post("delete_files", [
            RequestOptions::JSON => ['file_keys' => [$apiFileKey]]
        ]);
    }

    /**
     * 改ページ調整画面の「決定」ボタン処理、「プレビュー・捺印へ」ボタンクリック後
     * 改ページ位置を変更したPDFを取得し、DB上のPDFデータを置き換える
     */
    public function decidePageBreaksAfterAcceptUpload(BaseFormRequest $request){
        $filename = $request->input('filename');
        $apiFileKey = $this->getOfficeConvertFileKey($filename);

        // pdfのファイルパス取得方法をacceptUpload関数と合わせる
        $uniquePath = $this->getUniquePath($request);
        $pdfFilepath = storage_path("/app/uploads/".$uniquePath.'/'.$request->input('pdfFilename'));
        $breaks = $request->input('breaks');

        try {
            self::decidePageBreaks($apiFileKey, $pdfFilepath, $breaks);
        } catch (ServerException $e) {
            return OfficeConvertApiUtils::logAndGenerateErrorResponse($e);
        }

        // ローカルに保存したPDFでDB上のファイルを置き換える
		$circular_document_id = $request['circular_document_id'];
        $circular_id = $request['circular_id'];
        $document_data_update_at = $request['document_data_update_at'];
        // PAC_5-2242 Start
        if($request['usingHash']) {
            $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$userApiClient){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $getUserResult = $userApiClient->get("userByHashing");
            $resultBody = json_decode((string)$getUserResult->getBody());
            if ($getUserResult->getStatusCode() == 200) {
                if($resultBody && $resultBody->user) {
                    $user = $resultBody->user;
                }
            }else {
                Log::warning("userByHashing response body: " . $getUserResult->getBody());
                return Response::json(['status' => false, 'message' => '権限がありません。', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
        } else {
        $user = $this->getUser();
        }
        // PAC_5-2242 End
        $user_email = $user->email;
        $pdfBase64 = chunk_split(base64_encode(file_get_contents($pdfFilepath)));
        $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
        if(!$client){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $result = $client->put("circulars/".$circular_id."/documents/".$circular_document_id."/replacePdf", [
            RequestOptions::JSON => ['pdf_data' => $pdfBase64, 'user_email' => $user_email, 'document_data_update_at' => $document_data_update_at]
        ]);
        $resultBody = json_decode((string)$result->getBody());
        $httpStatusCode = $result->getStatusCode();
        if ($httpStatusCode != 200) {
            Log::debug("replacePdf response body " . $result->getBody());
            if ($httpStatusCode === \Illuminate\Http\Response::HTTP_PRECONDITION_FAILED) {
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], $httpStatusCode);
            } else {
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
            }
        }

        return Response::json(['status' => true, 'message' => 'PDF変更処理に成功しました。', 'data' => ["document_data_update_at" => $resultBody->data->document_data_update_at]]);
    }

    public function saveFile(SaveFileRequest $request) {
        try {
            $user = $this->getUser();
            $appEnv = null;
            $appServer = null;
            $appEdition = null;
            $username = '';

            $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$userApiClient){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            if($request['usingHash']) {
                $result = $userApiClient->get("userByHashing");
                $resultBody = json_decode((string)$result->getBody());
                if ($result->getStatusCode() == 200) {
                    if($resultBody && $resultBody->user) {
                        $user = $resultBody->user;
                        $appEnv = $user->current_env_flg;
                        $appServer = $user->current_server_flg;
                        $appEdition = $user->current_edition_flg;
                        $username = $user->name;
                    }
                }else {
                    Log::warning("userByHashing response body: " . $result->getBody());
                    return Response::json(['status' => false, 'message' => '権限がありません。', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
                }
            }else {
                $username = $user->family_name.' '. $user->given_name;
            }

            // 名刺機能が有効なら自分の名刺IDを取得
            $bizcard_id = null;
            $getBizcardFlgResult = $userApiClient->get("setting/getBizcardFlg");
            $bizcardFlgData= json_decode((string)$getBizcardFlgResult->getBody());
            if ($getBizcardFlgResult->getStatusCode() == 200) {
                if($bizcardFlgData->data->bizcard_flg) {
                    Log::debug('ApplicationController.saveFile: Get my bizcard ID');
                    // emailはURLエンコードして渡す
                    $encodedEmail = rawurlencode($user->email);
                    Log::debug('encodedEmail: ' . $encodedEmail);
                    $getBizcardIDResult = $userApiClient->get("user/getBizcardId?env_flg=$appEnv&server_flg=$appServer"
                                                            . "&edition_flg=$appEdition&email=$encodedEmail");
                    Log::debug('getBizcardID response: ' . $getBizcardIDResult->getBody());
                    if ($getBizcardIDResult->getStatusCode() == 200) {
                        $bizcardIdData = json_decode($getBizcardIDResult->getBody());
                        $bizcard_id = $bizcardIdData->data->bizcard_id;
                        Log::debug('My bizcard ID: ' . $bizcard_id);
                    }
                }
            }

            $uniquePath = $this->getUniquePath($request);

            $files = $request['files'];

            $stamp_infos = [];
            $text_infos = [];
			$comment_infos = [];
            $sticky_note_infos = [];

            $active_circular_document_id = $request['active_id'];
            $arrCircularDocumentId = [];
            foreach ($files as $index=>$file){
                $arrCircularDocumentId[$file['circular_document_id']] = $file['circular_document_id'];
            }

            $result = $userApiClient->get("setting/getMyCompany");
            $company= json_decode((string)$result->getBody());
            if ($result->getStatusCode() == 200) {
                $addSignature = $company->data->esigned_flg;
            }else{
                Log::error('Log getMyCompany: '. $result->getBody());
                return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
            }
            $hasSignature = (isset($addSignature) && $addSignature == 1 ) ? ((isset($request['downloadable']) && $request['downloadable'])?0:$request['signature']) : 0;
            $circularHasSignature = $hasSignature;
            if (!$hasSignature){
                $result = $userApiClient->get("circulars/".$request['circular_id']."/checkHasSignatureSaveFile", []);
                if ($result->getStatusCode() != 200) {
                    log::debug('[checkHasSignatureSaveFile]調用失敗！statusCode:'.$result->getStatusCode().', circular_id:'.$request['circular_id']);
                    Log::error('Log checkHasSignatureSaveFile: '. $result->getBody());
                    return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
                }
                $checkHasSignatureSaveFileRes = json_decode((string)$result->getBody())->data;
                if ($checkHasSignatureSaveFileRes->circularStatus != CircularDocumentUtils::SAVING_STATUS) {
                    $circularHasSignature = $checkHasSignatureSaveFileRes->hasSignature;
                    if ((!isset($request['downloadable']) || !$request['downloadable'])){
                        $hasSignature = $checkHasSignatureSaveFileRes->hasSignature;
                        if (!$hasSignature){
                            $hasSignature = $addSignature;
                        }
                    }
                }
            }
            if ($hasSignature && (!isset($request['no_timestamp']) || !$request['no_timestamp']) && (!isset($request['isSendBack']) || !$request['isSendBack'])){
                $result = $userApiClient->get("circulars/".$request['circular_id']."/checkUsingTasSave", [
                    RequestOptions::JSON => ['circular_document_ids' => $arrCircularDocumentId]
                ]);
                if ($result->getStatusCode() != 200) {
                    // PAC_5-1383 log追加
                    log::debug('[checkUsingTasSave]調用失敗！statusCode:'.$result->getStatusCode().', circular_id:'.$request['circular_id']);
                    Log::error('Log checkUsingTasSave: '. $result->getBody());
                    return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
                }
                $usingTas = json_decode((string)$result->getBody())->data;
            }else{
                $usingTas = null;
            }
            $arrCircularDocument = [];

            //実行時間を取得
            $run_time = date('Y-m-d H:i:s');

            foreach ($files as $index=>$file) {
                $stamps = $file['stamps'];

                if ($usingTas){
                    $checkPermission = (array)($usingTas->circular_documents);
                    if(isset($checkPermission[$file['circular_document_id']]->usingtas)){
                        $checkUsingTas = $checkPermission[$file['circular_document_id']]->usingtas;
                    }else{
                        $checkUsingTas = false;
                    }
                }else{
                    $checkUsingTas = false;
                }


                if(isset($checkPermission[$file['circular_document_id']]->user_add_stamp)){
                    $user_add_stamp = $checkPermission[$file['circular_document_id']]->user_add_stamp;
                }else{
                    $user_add_stamp = '';
                }

                //check add stamp to final_approval
                if($usingTas && $usingTas->final_approval && !$checkUsingTas && $stamps){
                    foreach($stamps as $stamp){
                        if(isset($stamp['time_stamp_permission']) && $stamp['time_stamp_permission']){
                            $checkUsingTas = true;
                            $user_add_stamp = new \stdClass();
                            $user_add_stamp->mst_company_id = $usingTas->issuing_count?$usingTas->issuing_count->mst_company_id:$user->mst_company_id;
                            $user_add_stamp->mst_user_id = $usingTas->issuing_count?$usingTas->issuing_count->mst_user_id:$user->id;
                            continue;
                        }
                    }
                }

                $files[$index]['usingTas'] = false;
                $files[$index]['usingDTS'] = $checkUsingTas;

                if($checkUsingTas){
                    $arrCircularDocument[$index]['circular_document_id'] = $file['circular_document_id'];
                    $arrCircularDocument[$index]['usingTas'] = $checkUsingTas;
                    $arrCircularDocument[$index]['usingDTS'] = $checkUsingTas;
                    if($user_add_stamp){
                        $arrCircularDocument[$index]['user_add_stamp'] = $user_add_stamp;
                    }else{
                        $arrCircularDocument[$index]['user_add_stamp'] = '';
                    }
                }
                $texts = $file['texts'];
				$comments = isset($file['comments']) ? $file['comments']:'';
				$sticky_notes = isset($file['sticky_notes']) ? $file['sticky_notes']:'';
                $server_file_name = $file['server_file_name'];
                if (!$server_file_name){
                    continue;
                }
                $userPath = storage_path() . "/app/uploads/" . $uniquePath . "/";
                $pdfFilePath = $userPath . $server_file_name;

                Log::debug("save file: $pdfFilePath");
                $pdfBase64 = chunk_split(base64_encode(file_get_contents($pdfFilePath)));
                $files[$index]['pdf_data'] = $pdfBase64;

                $files[$index]['append_pdf_data'] = null;

                unset($files[$index]['file_name']);
                unset($files[$index]['server_file_name']);
                if ($texts){
                    foreach ($texts as $text) {
                        if ($text['text'] != null){
                            array_push($text_infos,[
                                'circular_document_id' => $file['circular_document_id'],
                                'text' => $text['text'],
                                'name' => $username,
                                'email' => $user->email
                            ]);
                        }
                    }
                }
				if ($comments){
					foreach ($comments as $comment) {
						array_push($comment_infos,[
							'circular_document_id' => $file['circular_document_id'],
							'private_flg' => $comment['private_flg'],
							'text' => $comment['text'],
							'parent_send_order' => $comment['parent_send_order'],
							'name' => $username,
							'email' => $user->email
						]);
					}
				}
                if ($sticky_notes){
                    foreach ($sticky_notes as $sticky_note) {
                        array_push($sticky_note_infos,[
                            'id' => $sticky_note['id'],
                            'circular_id' => $request['circular_id'],
                            'document_id' => $file['circular_document_id'],
                            'top' => $sticky_note['top'],
                            'left' => $sticky_note['left'],
                            'note_format' => $sticky_note['note_format'],
                            'note_text' => $sticky_note['note_text'],
                            'page_num' => $sticky_note['page_num'],
                            'edition_flg' => isset($appEdition) ? $appEdition : config('app.edition_flg'),
                            'removed_flg' => $sticky_note['removed_flg'],
                            'deleted_flg' => $sticky_note['deleted_flg'],
                            'env_flg' =>isset($appEnv) ? $appEnv : config('app.app_server_env'),
                            'server_flg' => isset($appServer) ? $appServer : config('app.pac_contract_server'),
                            'operator_name' => $username,
                            'operator_email' => $user->email,
                        ]);
                    }
                }
                if(!$stamps) continue;
                foreach ($stamps as $stampIndex=>$stamp) {
                    $info_id = hash('SHA256', $appEnv.$appServer.$user->email.$file['file_name'].rand().time());
                    $files[$index]['stamps'][$stampIndex]['stamp_url'] = AppUtils::generateStampUrl() . '/' . $info_id;
                    if($hasSignature && isset($stamp['rotateAngle']) && $stamp['rotateAngle']){
                        if (($stamp['width'] != $stamp['height'])){
                            // rotate the image in PHP if the image is not square
                            $image = Image::make($stamp['stamp_data']);
                            $image->rotate($stamp['rotateAngle']);
                            $imageBase64 = (string) $image->encode('data-url');
                            $imageBase64 = explode(',', $imageBase64);
                            $imageBase64 = $imageBase64[1];
                            $files[$index]['stamps'][$stampIndex]['stamp_data'] = $imageBase64;
                            $files[$index]['stamps'][$stampIndex]['stamp_data_rotated'] = true;
                        }

                        // calculate image dimension after rotated
                        $fltRadians = deg2rad( $stamp['rotateAngle'] );
                        $intWidthRotated = $stamp['height'] * abs( sin( $fltRadians) ) + $stamp['width'] * abs( cos( $fltRadians ) );
                        $intHeightRotated = $stamp['height'] * abs( cos( $fltRadians ) ) + $stamp['width'] * abs( sin( $fltRadians ) );
                        $files[$index]['stamps'][$stampIndex]['width'] = $intWidthRotated;
                        $files[$index]['stamps'][$stampIndex]['height'] = $intHeightRotated;
                        $files[$index]['stamps'][$stampIndex]['x_axis'] -= ($intWidthRotated - $stamp['width'])/2;
                        $files[$index]['stamps'][$stampIndex]['y_axis'] += ($intHeightRotated - $stamp['height'])/2;
                    }
                    if(isset($stamp['opacity']) && $stamp['opacity'] && $stamp['opacity'] < 1){
                        $stamp_data = $stamp['stamp_data'];
                        if(array_key_exists('stamp_data_rotated',$files[$index]['stamps'][$stampIndex])){
                            $stamp_data = $files[$index]['stamps'][$stampIndex]['stamp_data'];
                        }
                        $im = Image::make(base64_decode($stamp_data));
                        $im->opacity($stamp['opacity'] * 100);
                        $im->encode('png');
                        $files[$index]['stamps'][$stampIndex]['stamp_data'] = base64_encode($im->encoded);;
                    }
                    $result = $userApiClient->get("setting/getMyCompany");
                    $company= json_decode((string)$result->getBody());
                    if ($company->data->pdf_annotation_flg == 1) {
                        $files[$index]['stamps'][$stampIndex]['email'] = $user->email;
                        $files[$index]['stamps'][$stampIndex]['stamp_time'] = $run_time;
                        $files[$index]['stamps'][$stampIndex]['file_name'] = $file['file_name'];
                        $files[$index]['stamps'][$stampIndex]['serial'] = (isset($stamp['serial']) && $stamp['serial'])?$stamp['serial']:$this->generateUUID();
                        $files[$index]['stamps'][$stampIndex]['pdf_annotation_flg'] = $company->data->pdf_annotation_flg;
                   }
                    //  PAC_5-2232  捺印時にダウンロードして回覧をすると捺印プロパティが404エラーになる
                    //if (!isset($stamp['repeated']) || !$stamp['repeated']){
                        array_push($stamp_infos,[
                            'circular_document_id' => $file['circular_document_id'],
                            'repeated' => !isset($stamp['repeated']) || !$stamp['repeated'],
                            'stamp_image' => $stamp['stamp_data'],
                            'id' => isset($stamp['id'])?$stamp['id']:0,
                            'sid' => isset($stamp['sid'])?$stamp['sid']:0,
                            'stamp_flg' => isset($stamp['stamp_flg'])?$stamp['stamp_flg']:0,
                            'name' => $username,
                            'email' => $user->email,
                            'bizcard_id' => $bizcard_id,
                            'env_flg' => $appEnv != null ? $appEnv : config('app.app_server_env'),
                            'server_flg' => $appServer != null ? $appServer : config('app.pac_contract_server'),
                            'edition_flg' => $appEdition != null ? $appEdition : config('app.edition_flg'),
                            'info_id' => $info_id,
                            'serial' => (isset($stamp['serial']) && $stamp['serial'])?$stamp['serial']:$this->generateUUID(),
                            'file_name' => $file['file_name'],
                            'time_stamp_permission' => isset($stamp['time_stamp_permission'])?$stamp['time_stamp_permission']:0,
                            'parent_send_order' => $file['parent_send_order'],
                            // PAC_5-2232 捺印時にダウンロードして回覧をすると捺印プロパティが404エラーになる
                            // get unique key
                            'file_stampMd5' => md5(implode("_",$stamp).$index),
                        ]);
                    //}

                }
            }

            // TODO review signatureAndImpress 2 times
            $stampClient = UserApiUtils::getStampApiClient();

            $signatureKeyFile = $company->data->certificate_flg?$company->data->certificate_destination:null;
            $signatureKeyPassword = $company->data->certificate_flg?$company->data->certificate_pwd:null;
            // PAC_5-1327 add signature reason
            $signatureReason = $this->getSignatureReason($username, $user->email);
            $result = $stampClient->post("signatureAndImpress", [
                RequestOptions::JSON => [
                    'signature' => $hasSignature,
                    'data' => $files,
                    'signatureKeyFile' => $signatureKeyFile,
                    'signatureReason' => $signatureReason,
                    'signatureKeyPassword' => $signatureKeyPassword,
                    'usingNewSignatureForTas' => ($usingTas && $usingTas->final_approval),
                    'timestampSignatureReason' => $this->getShachihataSignatureReason(),
                    'documentTimestampSignatureReason' => $this->getDTSSignatureReason()
                ]
            ]);
            $resData = json_decode((string)$result->getBody());

            if ($result->getStatusCode() == 200) {
                if ($resData && $resData->data) {
                    $new_documents = [];
                    $first_document = null;
                    foreach ($resData->data as $index=>$item) {
                        $item->confidential_flg = $files[$index]['confidential_flg'];
                        $new_documents[] = $item;

                        if($index == 0) {
                            $first_document = $item;
                            continue;
                        }

                        if($first_document && $first_document->circular_document_id > $item->circular_document_id) {
                            $first_document = $item;
                        }
                    }
                    $first_page_image = '';
                    if($first_document && !$request['downloadable']) {
                        $first_page_image = $this->getFirstPageImage($first_document->pdf_data);
                    }
                    $result = $userApiClient->put("circulars/".$request['circular_id']."/documents/updateList", [
                        RequestOptions::JSON => [
                            'documents' => $new_documents,
                            'stamp_infos'=>$stamp_infos,
                            'text_infos'=>$text_infos,
                            'sticky_notes'=>$sticky_note_infos,
							'comment_infos'=>$comment_infos,
                            'first_page_data' => $first_page_image,
                            'active_id'=> $active_circular_document_id,
                            'hasSignature' => $circularHasSignature,
                            'downloadable'=> $request['downloadable'],
                            'check_add_text_history'=> $request['check_add_text_history'],
                            'check_add_stamp_history'=> $request['check_add_stamp_history'],
                            'run_time' => $run_time,
                            'update_at' => $request['update_at'],
                        ]
                    ]);
                    $this->storeTimestampInfo($userApiClient, $user, $arrCircularDocument, $appEnv, $appServer);
                    $resultBody = json_decode((string)$result->getBody());
                    $isReserveDownload = $request->get('isDownloadReserve',false); //ダウンロード予約
                    $reserveFileName = $request->get('reserveFileName',''); //ファイルの名前
                    if ($result->getStatusCode() == 200) {
                        if (isset($user->sanitizing_flg) && $user->sanitizing_flg && $isReserveDownload){
                            $reserve_result = $userApiClient->post('previewFile/reserve', [
                                RequestOptions::JSON => [
                                    'reserve_file_name' => $reserveFileName,
                                    'document_id' => $active_circular_document_id,
                                    'stampHistory' => $request->get('check_add_stamp_history',false),
                                    'addTextHistory' => $request->get('check_add_text_history',false),
                                    'finishedDate' => '',
                                ]
                            ]);
                            if ($reserve_result->getStatusCode() == StatusCodeUtils::HTTP_OK){
                                $reserveResultBody = json_decode((string)$reserve_result->getBody());
                                $reserveResultBody->data['update_at'] = $resultBody->data->update_at;
                                return Response::json(['status' => true, 'message' => 'ダウンロードを予約しました。', 'data' => $reserveResultBody->data]);
                            }
                        }else{
                            if($resultBody && $resultBody->data && $request['downloadable']) {
                                if(($request['check_add_stamp_history'] && $request['check_add_stamp_history'] == 1) || ($request['check_add_text_history'])){
                                    $hasSignature = (isset($addSignature) && $addSignature == 1 ) ? $request['signature'] : 0;
                                    // PAC_5-1327 add signature reason
                                    $signatureReason = $this->getSignatureReason($username, $user->email);
                                    $result = $stampClient->post("signatureAndImpress", [
                                        RequestOptions::JSON => [
                                            'signature' =>$hasSignature,
                                            'signatureKeyFile' => $signatureKeyFile,
                                            'signatureKeyPassword' => $signatureKeyPassword,
                                            'signatureReason' => $signatureReason,
                                            'data' => [
                                                [
                                                    'circular_document_id'=> $active_circular_document_id,
                                                    'pdf_data'=> $resultBody->data->pdf_data,
                                                    'append_pdf_data'=> $resultBody->data->append_data,
                                                    'stamps'=> [],
                                                    'texts'=> [],
                                                    'usingTas'=>0,
                                                ]
                                            ],
                                        ]
                                    ]);
                                    $resData = json_decode((string)$result->getBody());
                                    if ($result->getStatusCode() == 200) {
                                        $this->storeTimestampInfo($userApiClient, $user, $files, $appEnv, $appServer);
                                        return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。', 'data' => $resData->data]);
                                    }else {
                                        Log::error('Log signatureAndImpress: '. $result->getBody());
                                        return Response::json(['status' => false, 'message' => $resData->message, 'data' => null], 500);
                                    }
                                }else{
                                    $resData=[];
                                    $resData[0] = [
                                        'circular_document_id' => $active_circular_document_id,
                                        'pdf_data'=> $resultBody->data->pdf_data,
                                        'update_at' => $resultBody->data->update_at,
                                    ];
                                    return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。', 'data' => $resData]);
                                }
                            }
                            $resData->data['update_at'] = $resultBody->data->update_at;
                            return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。', 'data' => $resData->data]);
                        }
                    } else if($result->getStatusCode() == StatusCodeUtils::HTTP_NOT_ACCEPTABLE) {
                        Log::debug("Update circulars response body(HTTP_NOT_ACCEPTABLE): " . $result->getBody());
                        return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], StatusCodeUtils::HTTP_NOT_ACCEPTABLE);
                    } else {
                        Log::debug("Update circulars response body: " . $result->getBody());
                        return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
                    }
                }else {
                    Log::error('Log signatureAndImpress: '. $result->getBody());
                    return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
                }
            } else {
                if ($files){
                    foreach ($files as $file_key => $file){
                        unset($files[$file_key]['pdf_data']);
                        unset($files[$file_key]['append_pdf_data']);
                        if(isset($file['stamps'])){
                            foreach ($file['stamps'] as $stamp_key => $stamp){
                                unset($files[$file_key]['stamps'][$stamp_key]['stamp_data']);
                            }
                        }
                    }
                    Log::warning(json_encode($files));
                }
                Log::error('Log signatureAndImpress: '. $result->getBody());
                return Response::json(['status' => false, 'message' => $resData->message, 'data' => null], 500);
            }
        }catch(\Exception $ex) {
            if ($request->get('files')){
                $upload_info = $request->get('files');
                foreach ($upload_info as $file_key => $file){
                    if(isset($file['stamps'])){
                        foreach ($file['stamps'] as $stamp_key => $stamp){
                            unset($upload_info[$file_key]['stamps'][$stamp_key]['stamp_data']);
                        }
                    }
                }
                Log::warning(json_encode($upload_info));
            }
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => '保存処理に失敗しました。', 'data' => null], 500);
        }
    }

    private function storeTimestampInfo($userApiClient, $user, $circularDocuments, $appEnv, $appServer){
        Log::debug("storeTimestampInfo usingTas");
        if(count($circularDocuments)){
            $timestamps = [];
            foreach($circularDocuments as $index => $circularDocument) {
                Log::debug("storeTimestampInfo usingTas value: ".$circularDocument['usingTas']);
                if($circularDocument['usingDTS']){
                    $timestamps[] = [
                        'circular_document_id' => $circularDocument['circular_document_id'],
                        'mst_company_id' => (isset($circularDocument['user_add_stamp']) && $circularDocument['user_add_stamp'])?$circularDocument['user_add_stamp']->mst_company_id:$user->mst_company_id,
                        'mst_user_id' =>(isset($circularDocument['user_add_stamp']) && $circularDocument['user_add_stamp'])?$circularDocument['user_add_stamp']->mst_user_id:$user->id,
                        'app_env' => $appEnv,
                        'contract_server' => $appServer
                    ];
                }
            }

            if($timestamps){
                $result = $userApiClient->post("timestampinfo", [RequestOptions::JSON => ['timestamps' => $timestamps] ]);
                if ($result->getStatusCode() == 200) {
                    Log::debug('storeTimestampInfo success');
                }else{
                    Log::warning('storeTimestampInfo: '. $result->getBody());
                }
            }

        }
    }

    public function signatureCircular(SaveFileRequest $request, $id) {
        $user = $this->getUser();
        $appEnv = null;
        $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
        if(!$userApiClient){
            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
        }
        $settingCompanyResult = $userApiClient->get("setting/getMyCompany");
        $company= json_decode((string)$settingCompanyResult->getBody());
        if ($settingCompanyResult->getStatusCode() == 200) {
            $addSignature = $company->data->esigned_flg;
        }else{
            Log::error('Log signatureCircular: '. $settingCompanyResult->getBody());
            return Response::json(['status' => false, 'message' => $settingCompanyResult->message, 'data' => null], 500);
        }

        if(isset($addSignature) && $addSignature){
            $result = $userApiClient->get("circulars/".$id."/documentsAll");
            $circularDocuments = json_decode((string)$result->getBody());
            if ($result->getStatusCode() == 200) {
                $files = [];

                foreach($circularDocuments->data as $document){
                    $files[] = ['circular_document_id' => $document->id,
                        'pdf_data' => $document->file_data,
                        'append_pdf_data' => null,
                        'confidential_flg' =>$document->confidential_flg,
                        'file_name' => $document->file_name,
                        'stamps' => [],
                        'texts' => [],
                        'usingTas'=>0
                    ];
                }

                $stampApiClient = UserApiUtils::getStampApiClient();
                // PAC_5-1327 add signature reason
                // PAC_5-1327 only logged user is using this function (sendNotifyFirst)
                $username = $user->family_name.' '. $user->given_name;
                $signatureReason = $this->getSignatureReason($username, $user->email);
                $result = $stampApiClient->post("signatureAndImpress", [
                    RequestOptions::JSON => [
                        'signature' => 1,
                        'data' => $files,
                        'signatureReason' => $signatureReason,
                        'signatureKeyFile' => $company->data->certificate_flg?$company->data->certificate_destination:null,
                        'signatureKeyPassword' => $company->data->certificate_flg?$company->data->certificate_pwd:null,
                    ]
                ]);

                $resData = json_decode((string)$result->getBody());
                if ($result->getStatusCode() == 200) {
                    if ($resData && $resData->data) {
                        $new_documents = [];
                        foreach ($resData->data as $index=>$item) {
                            $item->confidential_flg = $files[$index]['confidential_flg'];
                            $new_documents[] = $item;
                        }
                        $result = $userApiClient->put("circulars/".$id."/documents/updateList", [
                            RequestOptions::JSON => [
                                'documents' => $new_documents,
                                'stamp_infos'=>[],
                                'text_infos'=>[],
								'comment_infos'=>[],
								'sticky_notes'=>[],
                                'update_at'=>$request['update_at'],
                                'hasSignature' => $addSignature
                            ]
                        ]);
                        $resultBody = json_decode((string)$result->getBody());
                        if ($result->getStatusCode() == 200) {
                            return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。', 'data' => $resData->data]);
                        } else {
                            Log::debug("Update circulars response body: " . $result->getBody());
                            return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
                        }
                    }else {
                        Log::error('Log signatureCircular: '. $result->getBody());
                        return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
                    }
                } else {
                    Log::error('Log signatureCircular: '. $result->getBody());
                    return Response::json(['status' => false, 'message' => $resData->message, 'data' => null], 500);
                }
            }else {
                Log::error('Log signatureCircular: '. $result->getBody());
                return Response::json(['status' => false, 'message' => $result->message, 'data' => null], 500);
            }
        }

        return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。']);
    }

    /**
     * プレビュー画面ダウンロード
     * @param DownloadFileRequest $request
     * @return mixed
     */
    public function downloadFile(DownloadFileRequest $request) {
        try {
            $user = $this->getUser();
            $appEnv = null;
            $appServer = null;
            $active_circular_document_id = $request['active_id'];

            $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$userApiClient){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $username = "";
            if($request['usingHash']) {
                $result = $userApiClient->get("userByHashing");
                $resultBody = json_decode((string)$result->getBody());
                if ($result->getStatusCode() == 200) {
                    if($resultBody && $resultBody->user) {
                        $user = $resultBody->user;
                        $appEnv = $user->current_env_flg;
                        $appServer = $user->current_server_flg;
                        $username = $user->name;
                    }
                }else {
                    Log::warning("userByHashing response body: " . $result->getBody());
                    return Response::json(['status' => false, 'message' => '権限がありません。', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
                }
            }else{
                $username = $user->family_name.' '. $user->given_name;
            }

            // 回覧完了日時
            if (isset($request['finishedDate'])) {  // 完了一覧
                $finishedDate = $request['finishedDate'];
                $result = $userApiClient->get("circulars/".$request['circular_id']."/documents/".$active_circular_document_id."?check_add_text_history=".$request['check_add_text_history']."&finishedDate=$finishedDate");
            } else {    // 完了一覧以外
                $result = $userApiClient->get("circulars/".$request['circular_id']."/documents/".$active_circular_document_id."?check_add_text_history=".$request['check_add_text_history']);
            }

            $resultBody = json_decode((string)$result->getBody());
            if ($result->getStatusCode() == 200) {
                if(($request['check_add_stamp_history']) || (!$request['check_add_stamp_history'] && $request['usingTas']) || ($request['check_add_text_history'])){
                    $file = [
                        'circular_document_id' => $active_circular_document_id,
                        'pdf_data'=> $resultBody->data->file_data,
                        'append_pdf_data'=> ($request['check_add_stamp_history'] || $request['check_add_text_history'])?$resultBody->data->append_pdf :null,
                        'stamps'=> [],
                        'texts'=> [],
                        'usingTas'=> 0,
                        'usingDTS' => $request['usingTas']?$request['usingTas']:0,
                    ];

                    $result = $userApiClient->get("setting/getMyCompany");
                    $company= json_decode((string)$result->getBody());
                    if ($result->getStatusCode() == 200) {
                        $addSignature = $company->data->esigned_flg;
                    }else{
                        Log::error('Log signatureAndImpress: '. $result->getBody());
                        return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
                    }
                    $stampApiClient = UserApiUtils::getStampApiClient();
                    $hasSignature = (isset($addSignature) && $addSignature == 1 ) ? (isset($request['signature'])?$request['signature']:1) : 0;

                    // PAC_5-1327 add signature reason
                    $signatureReason = $this->getSignatureReason($username, $user->email);
                    $result = $stampApiClient->post("signatureAndImpress", [
                        RequestOptions::JSON => [
                            'signature' => $hasSignature,
                            'data' => [$file],
                            'signatureReason' => $signatureReason,
                            'signatureKeyFile' => $company->data->certificate_flg?$company->data->certificate_destination:null,
                            'signatureKeyPassword' => $company->data->certificate_flg?$company->data->certificate_pwd:null,
                            'documentTimestampSignatureReason' => $this->getDTSSignatureReason()
                        ]
                    ]);
                    $resData = json_decode((string)$result->getBody());

                    if ($result->getStatusCode() == 200) {
                        if ($resData && $resData->data) {
                            // TODO insert other env
                            $this->storeTimestampInfo($userApiClient, $user, [$file], $appEnv, $appServer);
                            return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。', 'data' => $resData->data]);
                        }else {
                            Log::error('Log signatureAndImpress: '. $result->getBody());
                            return Response::json(['status' => false, 'message' => '印面処理に失敗しました。', 'data' => null], 500);
                        }
                    } else {
                        Log::error('Log signatureAndImpress: '. $result->getBody());
                        return Response::json(['status' => false, 'message' => $resData->message, 'data' => null], 500);
                    }
                }else{
                    $resData=[];
                    $resData[0] = [
                        'circular_document_id' => $active_circular_document_id,
                        'pdf_data'=> $resultBody->data->file_data,
                    ];
                    return Response::json(['status' => true, 'message' => '文書保存処理に成功しました。', 'data' => $resData]);
                }

            }else {
                Log::error('Log getCircularDoc: '. $result->getBody());
                return Response::json(['status' => false, 'message' => $resultBody->message, 'data' => null], 500);
            }

        }catch(\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => '保存処理に失敗しました。', 'data' => null], 500);
        }
    }

    public function userByHashing(BaseFormRequest $request){
        try {
            $request['usingHash'] = true;
            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if (!$client) {
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $result = $client->get("userByHashing");
            if ($result->getStatusCode() == 200) {
                $resultBody = json_decode((string)$result->getBody());
                if ($resultBody->status == StatusCodeUtils::HTTP_PARTIAL_CONTENT) {
                    return Response::json(['status' => false, 'message' => $resultBody->return_url], StatusCodeUtils::HTTP_PARTIAL_CONTENT);
                }
                Session::put('hashUser', $resultBody->user);
            } elseif ($result->getStatusCode() == 404) {
                $resultBody = json_decode((string)$result->getBody());
                abort(404, $resultBody->message);
            }
            return $result;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function verifyMyInfo(\Illuminate\Http\Request $request)
    {
        $data = $request->input('data');
        if (!Hash::check(mb_strtolower($data['email']), urldecode($data['hash']))) {
            return Response::json(['status' => true, 'message' => rtrim(config('app.url'), '/')], 206);
        }
        return Response::json(['status' => true, 'message' => '認識成功しました。']);
    }

    public function loadCircular(LoadCircularRequest $request) {
        try {
            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            // 完了一覧
            if (isset($request['finishedDate'])) {
                // 回覧完了日時
                $finishedDateKey = $request->get('finishedDate');
                $result = $client->get("circulars/".$request['id']."?finishedDate=$finishedDateKey");
            } else {    // 完了一覧以外
                $result = $client->get("circulars/".$request['id']);
            }

            return $this->doLoadCircular($result, $request);

        }catch(\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function loadCircularByHash(LoadCircularByHashRequest $request) {
        try {
            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }

            $result = $client->get("public/circulars/getByHash");
            return $this->doLoadCircular($result, $request);
        }catch(\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    private function doLoadCircular($result, $request) {
        $uniquePath = $this->getUniquePath($request);
        $resData = json_decode((string)$result->getBody());
        if ($result->getStatusCode() == 200) {
            if ($resData && isset($resData->data) && $resData->data) {
                $ret = ['circular'=> $resData->data->circular, 'files'=> [], 'current_viewing_user' => $resData->data->current_viewing_user, 'company_logos' => $resData->data->company_logos, 'title' => $resData->data->title];
                $documents = $resData->data->documents;
                $stored_path = 'uploads/'.$uniquePath.'/';

                $del_flg = $resData->data->circular ? $resData->data->circular->completed_date == null && $resData->data->circular->circular_status == 9 : false;
                $delete_email = $resData->data->circular ? $resData->data->circular->update_user : '';
                $delete_at = $resData->data->circular ? $resData->data->circular->update_at : '';

                foreach ($documents as $document) {
                    if ($document->file_data){
                        $server_file_name = $this->storedPdfDataToFile($stored_path, $document->file_name, $document->file_data);
                    }else{
                        $server_file_name = "";
                    }

                    $currentFile = [
                        'name' => $document->file_name,
                        'path' => $server_file_name?$stored_path.$server_file_name:null,
                        'server_file_name' => $server_file_name,
                        'circular_document_id'=>$document->circular_document_id,
                        'confidential_flg'=>$document->confidential_flg,
                        'mst_company_id'=>$document->create_company_id,
                        'create_user_id'=>$document->create_user_id,
                        'origin_env_flg'=>$document->origin_env_flg,
                        'origin_edition_flg'=>$document->origin_edition_flg,
                        'origin_server_flg'=>$document->origin_server_flg,
                        'total_timestamp'=>$document->total_timestamp,
                        'parent_send_order'=>$document->parent_send_order,
						'comments'=>$document->comments,
                        'create_at' => $document->create_at,
                        'sticky_notes' => $document->sticky_notes,
                    ];
                    $fileInfo = $this->previewFile($currentFile,$del_flg,$delete_email,$delete_at);
                    $ret['files'][] = $fileInfo;
                }
                return Response::json(['status' => true, 'message' => '文書ロード処理に成功しました。', 'data' => $ret]);
            }else {
                Log::debug('Log loadCircular: '. $result->getBody());
                return Response::json(['status' => false, 'message' => '文書ロード処理に失敗しました。', 'data' => null], 500);
            }
        } else {
            Log::debug('Log loadCircular: '. $result->getBody());
            return Response::json(['status' => false, 'message' => $resData->message, 'data' => null], 500);
        }
    }

    public function storedPdfDataToFile($stored_path, $file_name, $pdf_data) {

        $server_file_name = hash('SHA256', $file_name.rand().AppUtils::getUnique());
//        $server_file_name = AppUtils::getUnique();
        Storage::disk('local')->put($stored_path.$server_file_name.'.pdf', base64_decode($pdf_data));

        $imgPath = $stored_path.$server_file_name;
        Storage::disk('local')->makeDirectory($imgPath);

        return $server_file_name.'.pdf';
    }

    private function getFirstPageImage($pdf_data) {

        $stored_path = 'tmp/';

        $server_file_name = $this->storedPdfDataToFile($stored_path, rand(), $pdf_data);

        $userPath = storage_path()."/app/tmp/";
        $pdfFilePath = $userPath.$server_file_name;

        $imgFolderPath = $userPath.pathinfo($pdfFilePath)['filename'];

        $imgFilePath = "$imgFolderPath/1.png";

        $cairo = new PdfToCairo($pdfFilePath);
        $cairo->startFromPage(1)->stopAtPage(1);
        $cairo->setRequireOutputDir(true);
        $cairo->setSubDirRequired(true);
        $cairo->setFlag(Constants::_SINGLE_FILE);
        $cairo->setOutputSubDir(pathinfo($pdfFilePath)['filename']);
        $cairo->setOutputFilenamePrefix(1);
        $cairo->scalePagesTo(1200);
        $resultGenerateMain = $cairo->generatePNG();

        $image = null;
        if (!$resultGenerateMain) {
            $image = base64_encode(file_get_contents($imgFilePath));
        }
        File::delete($imgFilePath);
        File::deleteDirectory($imgFolderPath);

        return $image;

    }

    private function generateUUID(){
	    $mapCharacter = ['A', '!', 'w', '0', 'H','5', '3', 'T', 'c', '='];

	    $current = strval(intval(microtime(true)*10000));
        $chars = array_reverse(str_split($current));
        $uuid = '';
        foreach($chars as $char){
            $uuid .= $mapCharacter[intval($char)];
        }
        $uuid .= Str::random(2);
        return $uuid;
    }

    public function getPageImage($pdf_data, $numPageMin, $numPageMax){
        $stored_path = 'tmp/';

        $server_file_name = $this->storedPdfDataToFile($stored_path, rand(), $pdf_data);

        $userPath = storage_path()."/app/tmp/";
        $pdfFilePath = $userPath.$server_file_name;

        $imgFolderPath = $userPath.pathinfo($pdfFilePath)['filename'];

        $imgFilePath = "$imgFolderPath/1.jpg";

        $cairo = new PdfToCairo($pdfFilePath);

        $arrImage = [];
        for($i = $numPageMin; $i<=$numPageMax; $i++){
            $cairo->startFromPage((int)$i)->stopAtPage((int)$i);
            $cairo->setRequireOutputDir(true);
            $cairo->setSubDirRequired(true);
            $cairo->setFlag(Constants::_SINGLE_FILE);
            $cairo->setOutputSubDir(pathinfo($pdfFilePath)['filename']);
            $cairo->setOutputFilenamePrefix(1);
            $cairo->scalePagesTo(1200);
            $resultGenerateMain = $cairo->generateJPG();

            if (!$resultGenerateMain) {
                $arrImage[] = base64_encode(file_get_contents($imgFilePath));
            }

            File::delete($imgFilePath);
            File::deleteDirectory($imgFolderPath);
        }

        return $arrImage;
    }

    public function convertTemplateExcelToImage($templateId, \Illuminate\Http\Request $request)
    {
        $circular_id = $request->get('circularId');
        if ($request->get('special_sit_flg')) {
            return $this->convertExcelToImage($templateId, AppUtils::TEMPLATE_TYPE_SPECIAL, $request);
        }else if($circular_id != 'undefined'){
            return $this->convertExcelToImage($templateId, AppUtils::TEMPLATE_EDIT, $request);
        }else{
            return $this->convertExcelToImage($templateId, AppUtils::TEMPLATE_TYPE_TEMPLATE, $request);
        }
    }

    public function convertFormExcelToImage($templateId, \Illuminate\Http\Request $request)
    {
        return $this->convertExcelToImage($templateId, AppUtils::TEMPLATE_TYPE_FORM_ISSUANCE, $request);
    }

    private function convertExcelToImage($templateId, $type, \Illuminate\Http\Request $request){
        try {
            $page = $request->get('page') ? $request->get('page') : 0;
            $resDownload = $this->downloadTemplate($templateId, $type, $request);
            if ($resDownload['status']){
                $filePathPdf = $resDownload['data'];
            }else{
                return Response::json($resDownload, 500);
            }
            $pdf_data = base64_encode(\file_get_contents($filePathPdf));

            if ($type == AppUtils::TEMPLATE_TYPE_FORM_ISSUANCE){
                Log::debug("Imprint stamp");
                $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
                if(!$userApiClient){
                    return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
                }
                $result = $userApiClient->get("form-issuances/$templateId/stamp");
                if ($result->getStatusCode() != 200) {
                    Log::error('Log get template stamp: '. $result->getBody());
                    return Response::json(['status' => false, 'message' => 'ファイルの取得が失敗しました。', 'data' => null], 500);
                }
                $stamps = json_decode((string)$result->getBody())->data->stamps;

                if (count($stamps)){
                    $file2Imprint = new \stdClass();
                    $file2Imprint->circular_document_id = 1;
                    $file2Imprint->confidential_flg = 0;
                    $file2Imprint->stamps = $stamps;
                    $file2Imprint->texts = [];
                    $file2Imprint->usingTas = 0;
                    $file2Imprint->pdf_data = $pdf_data;

                    $stampApiClient = UserApiUtils::getStampApiClient();
                    $result = $stampApiClient->post("signatureAndImpress", [
                        RequestOptions::JSON => [
                            'signature' => 0,
                            'data' => [$file2Imprint],
                            'signatureKeyFile' => null,
                            'signatureKeyPassword' => null,
                        ]
                    ]);
                    Log::debug("convertExcelToImage: call api signatureAndImpress add stamp for form template ".$templateId);
                    if ($result->getStatusCode() == 200) {
                        $resData = json_decode((string)$result->getBody());
                        if ($resData && $resData->success && $resData->data) {
                            Log::debug("convertExcelToImage: call api signatureAndImpress add stamp for form template ".$templateId." success");
                            foreach($resData->data as $data){
                                $pdf_data = $data->pdf_data;
                            }
                        }else{
                            Log::error('convertExcelToImage : Log signatureAndImpress: '. $result->getBody());
                        }
                    }else{
                        Log::error('convertExcelToImage : Log signatureAndImpress: '. $result->getBody());
                    }
                }
            }
            $pdf = new PDFInfo($filePathPdf);
            $totalPages = $pdf->pages;

            $numPageMin = $page + 1;
            if($page == 0 ){
                $numPageMax = ($totalPages < PDFUtils::NUMBER_PAGE_LOAD) ? $totalPages : PDFUtils::NUMBER_PAGE_LOAD;
            }else{
                $numPageMax = $totalPages < ($page + PDFUtils::NUMBER_PAGE_LOAD) ? $totalPages : ($page + PDFUtils::NUMBER_PAGE_LOAD) ;
            }

            $dataTemplate['arrImage'] = $this->getPageImage($pdf_data, $numPageMin, $numPageMax);

            $dataTemplate['startPage']= ( ($page + PDFUtils::NUMBER_PAGE_LOAD) < $totalPages ) ? ($page+PDFUtils::NUMBER_PAGE_LOAD) : 0;

            return Response::json(['status' => true, 'message' => 'ロード処理に成功しました。', 'data' => $dataTemplate]);

        } catch (\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return ['status' => \Illuminate\Http\Response::HTTP_BAD_REQUEST, 'message' => "ファイルを取得できませんでした。" ];
        }
    }

    /**
     * User upload image, validate and resize image (if image size large)
     * POST /uploadUserImage
     *
     * @param AcceptUploadUserImageRequest $request
     *
     * @throws Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadUserImage(AcceptUploadUserImageRequest $request)
    {
        $MAX_WITH_HEIGHT_IMAGE = 100;
        $input = $request->all();

        try {
            $image = $input['image'];
            $image = \Intervention\Image\Facades\Image::make($image->getRealPath());

            $widthImage = $image->width();
            $heightImage = $image->height();

            /* Resize image */
            if ($widthImage > $MAX_WITH_HEIGHT_IMAGE || $heightImage > $MAX_WITH_HEIGHT_IMAGE) {
                $ratioX = $widthImage / $MAX_WITH_HEIGHT_IMAGE;
                $ratioY = $heightImage / $MAX_WITH_HEIGHT_IMAGE;
                if ($ratioX > $ratioY) {
                    $image->widen($MAX_WITH_HEIGHT_IMAGE)->save();
                } else {
                    $image->heighten($MAX_WITH_HEIGHT_IMAGE)->save();
                }
            }

            // Get content image base64
            $imageBase64 = (string) $image->encode('data-url');
            $imageBase64 = explode(',', $imageBase64);
            $imageBase64 = $imageBase64[1];

            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if (!$client) {
                return Response::json(['status' => false, 'message' => '', 'data' => null], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
            }
            $result = $client->post("userimage", [
                RequestOptions::JSON => ['image' => $imageBase64]
            ]);
            if ($result->getStatusCode() != 200) {
                Log::error("uploadUserImage response body " . $result->getBody());
                return Response::json(['status'=>false, 'message'=> '画像更新プロセスでエラーが発生しました', 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return Response::json(['status'=>true, 'message'=> '写真の更新プロセスは成功しました。', 'data'=> null]);
        } catch (Exception $ex){
            Log::error('ApplicationController@uploadUserImage:' . $ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status'=>false, 'message'=> '画像更新プロセスでエラーが発生しました', 'data'=> null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PDF の指定された領域から文字列の抽出をする。一行に限る。
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function extractPdfLine(\Illuminate\Http\Request $request) {
        $user = $this->getUser();
        if ($user === null) {
            // これはあり得るため
            throw new AuthenticationException();
        }
        // メモ: もし CheckIfAuthenticated で Session::put されたなら、次の行でエラーが起きる
        $has_permission = optional($user)->checkLongTermFlgAll;
        if (!$has_permission) {
            // この機能を使えるユーザーではない
            throw new AuthorizationException();
        }

        $validated = $request->validate([
            'filename' => ['required', 'string', 'regex:/^[0-9a-zA-Z]+\\.pdf$/'],
            'page' => 'required|integer|min:1',
            'ppi' => 'required|integer|min:1|max:600', // 上限を指定してあるが、600 に意味はない
            'x1' => 'required|integer|min:0',
            'y1' => 'required|integer|min:0',
            'x2' => 'required|integer|min:0',
            'y2' => 'required|integer|min:0',
        ]);

        $pdfPath = storage_path("app/uploads/" . $this->getUniquePath($request) . "/" . $validated['filename']);
        $process = self::generateExtractPdfLineProcess(
            $pdfPath, (int) $validated['page'], (int) $validated['ppi'],
            (int) $validated['x1'], (int) $validated['y1'], (int) $validated['x2'], (int) $validated['y2']);

        $exitCode = $process->run();
        $stdoutJson = json_decode($process->getOutput(), true);

        $isError = $exitCode !== 0;
        if ($isError) {
            $stderr = $process->getErrorOutput();

            if ($stdoutJson === null) {
                // Python モジュールが想定通り動作せず、エラーコードを含むJSONすら返されなかった
                // 動作設定の不備が考えられる
                Log::error('外部プログラムの処理結果を読みだせません' . "\n" . $stderr);
                throw new \RuntimeException('外部プログラムの処理結果を読みだせません');
            }

            // ファイルの存在は、処理中に削除される可能性も考慮し処理後にチェックしている
            if (!file_exists($pdfPath)) {
                Log::error("処理対象のファイルが存在しません: $pdfPath");
                $errorMessage = '処理対象のファイルが存在しません';
            } else {
                $errorMessage = self::extractPdfLineHandleErrorCode($stdoutJson['error'], $stderr);
            }

            return Response::json(['status' => false, 'message' => $errorMessage, 'data' => null], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 成功時
        $data = self::extractPdfLineHandleSuccess($stdoutJson['results']);
        return Response::json(['status' => true, 'message' => 'ok', 'data' => $data]);
    }

    /**
     * PDF 文字列抽出プロセスを生成する
     *
     * @param string $pdfPath
     * @param integer $pageno
     * @param integer $ppi
     * @param integer $x1
     * @param integer $y1
     * @param integer $x2
     * @param integer $y2
     * @return Process
     */
    private static function generateExtractPdfLineProcess(string $pdfPath, int $pageno, int $ppi, int $x1, int $y1, int $x2, int $y2): Process {
        $repositoryPath = base_path('../');
        $interpreter = $repositoryPath . 'one_line_extract/venv/bin/python3';
        $modulePath = realpath($repositoryPath . 'one_line_extract');

        if (!is_file($interpreter) || !is_dir($modulePath)) {
            // セットアップ手順は one_line_extract/README.md を参照
            throw new \RuntimeException('この機能の動作に必要なものが見つかりません');
        }

        $extractParameters = [
            'pdf_path' => $pdfPath,
            'page' => $pageno,
            'dpi' => $ppi,
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x2,
            'y2' => $y2,
        ];

        $command = [
            $interpreter,
            '-m', 'pdf_index',
            'extract', json_encode($extractParameters)
        ];

        return new Process($command, $modulePath);
    }

    /**
     * PDF 文字列抽出処理のエラーコードからエラーメッセージを生成する
     *
     * @param string $pyErrorCode
     * @param string $stderr
     * @return string
     */
    private static function extractPdfLineHandleErrorCode(string $pyErrorCode, string $stderr): string {
        $errorCodeMessage = [
            // エラーコード => エラーメッセージ (クライアントへ送りたいエラーのみ)
            'page_not_exists' => '存在しないページが指定されました',
            'out_of_page_area' => 'ページ外の領域が指定されました (+)',
            'negative_area' => 'ページ外の領域が指定されました (-)',
        ];

        $errorMessage = $errorCodeMessage[$pyErrorCode] ?? null;
        if ($errorMessage === null) {
            // 予期しないエラーを意味する
            // 実行した外部プログラムの動作もしくは PHP 実装に問題があると思われる
            Log::error("予期しないエラーが発生しました: $pyErrorCode\n" . $stderr);

            // ↓とせず、通常通りのレスポンスを返すことを優先した
            // throw new \LogicException('予期しないエラーが発生しました');
            $errorMessage = '予期しないエラーが発生しました';
        }

        return $errorMessage;
    }

    /**
     * PDF 文字列抽出処理成功時のレスポンス生成処理
     *
     * @param array $processResults
     * @return array
     */
    private static function extractPdfLineHandleSuccess(array $processResults): array {
        $results = array_column($processResults, null, 'type');

        // ↓結果を取り出す順
        $resultKeys = ['embedded_workaround', 'ocr', 'embedded'];

        if (count($results) != count($resultKeys)) {
            throw new \LogicException('結果の数が不正です');
        }

        // 出力生成
        $choices = [];
        $isMultilineDetected = false;

        foreach ($resultKeys as $resultKey) {
            $resultItem = $results[$resultKey];

            switch ($resultItem['status']) {
                case 'success':
                    $text = $resultItem['text'];
                    $isPushed = in_array($text, $choices, true);
                    if (!$isPushed) {
                        $choices[] = $text;
                    }
                    break;
                case 'multiline':
                    $isMultilineDetected = true;
                    break;
                case 'empty':
                    // do nothing
                    break;
                default:
                    throw new \LogicException('unexpected value');
            }
        }

        return [
            'choices' => $choices,
            'isMultilineDetected' => $isMultilineDetected,
        ];
    }

    public function loadFormIssuance($templateId, \Illuminate\Http\Request $request) {
        try {
            $noStamp = $request->get('noStamp');
            $client = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$client){
                return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
            }
            $result = $client->get("form-issuances/show/$templateId");

            $resData = json_decode((string)$result->getBody());
            if ($result->getStatusCode() == 200) {
                if ($resData && isset($resData->data) && $resData->data) {
                    $circular = new \stdClass();
                    $circular->id = $templateId;
                    $ret = ['circular'=> $circular, 'files'=> [], 'current_viewing_user' => [], 'company_logos' => [], 'title' => ''];
                    $frmTemplate = $resData->data->frm_template;

                    $request['storage_file_name'] = $frmTemplate->storage_file_name;
                    $resDownload = $this->downloadTemplate($templateId, AppUtils::TEMPLATE_TYPE_FORM_ISSUANCE, $request);
                    if ($resDownload['status']){
                        $pdfFilePath = $resDownload['data'];
                        $server_file_name = $resDownload['stored_basename'];
                    }else{
                        return Response::json($resDownload, 500);
                    }
                    if (!$noStamp){
                        Log::debug("Imprint stamp");
                        $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
                        if(!$userApiClient){
                            return Response::json(['status' => false, 'message' => '', 'data' => null], 401);
                        }
                        $result = $userApiClient->get("form-issuances/$templateId/stamp");
                        if ($result->getStatusCode() != 200) {
                            Log::error('Log get template stamp: '. $result->getBody());
                            return Response::json(['status' => false, 'message' => 'ファイルの取得が失敗しました。', 'data' => null], 500);
                        }
                        $stamps = json_decode((string)$result->getBody())->data->stamps;

                        if (count($stamps)) {
                            $pdf_data = base64_encode(file_get_contents($pdfFilePath));

                            $file2Imprint = new \stdClass();
                            $file2Imprint->circular_document_id = 1;
                            $file2Imprint->confidential_flg = 0;
                            $file2Imprint->stamps = $stamps;
                            $file2Imprint->texts = [];
                            $file2Imprint->usingTas = 0;
                            $file2Imprint->pdf_data = $pdf_data;

                            $stampApiClient = UserApiUtils::getStampApiClient();
                            $result = $stampApiClient->post("signatureAndImpress", [
                                RequestOptions::JSON => [
                                    'signature' => 0,
                                    'data' => [$file2Imprint],
                                    'signatureKeyFile' => null,
                                    'signatureKeyPassword' => null,
                                ]
                            ]);
                            Log::debug("convertExcelToImage: call api signatureAndImpress add stamp for form template " . $templateId);
                            if ($result->getStatusCode() == 200) {
                                $resData = json_decode((string)$result->getBody());
                                if ($resData && $resData->success && $resData->data) {
                                    Log::debug("convertExcelToImage: call api signatureAndImpress add stamp for form template " . $templateId . " success");
                                    foreach ($resData->data as $data) {
                                        $pdf_data = $data->pdf_data;
                                    }
                                    file_put_contents($pdfFilePath, base64_decode($pdf_data));
                                } else {
                                    Log::error('convertExcelToImage : Log signatureAndImpress: ' . $result->getBody());
                                }
                            } else {
                                Log::error('convertExcelToImage : Log signatureAndImpress: ' . $result->getBody());
                            }
                        }
                    }

                    $file = [
                        'name' => $frmTemplate->file_name,
                        'path' => $pdfFilePath,
                        'server_file_name' => $server_file_name,
                        'circular_document_id'=>1,
                        'confidential_flg'=>0,
                        'mst_company_id'=>1,
                        'create_user_id'=>1,
                        'origin_env_flg'=>0,
                        'origin_edition_flg'=>1,
                        'origin_server_flg'=>1,
                        'total_timestamp'=>1,
                        'parent_send_order'=>0,
                        'comments'=>[],
                        'create_at' => Carbon::now()
                    ];

                    $fileInfo = array();
                    if ($file){
                        try{
                            File::chmod($pdfFilePath,0755);

                            $pdf = new PDFUtils($pdfFilePath);
                            $pages = $pdf->getShownPagesInfo();

                            preg_match('/([0-9\.]+) x ([0-9\.]+)/', $pdf->pagesInfo[0]["size"] , $matches);

                            $fileInfo = array(
                                'circular_document_id'=>$file['circular_document_id'],
                                'origin_env_flg'=>$file['origin_env_flg'],
                                'origin_edition_flg'=>$file['origin_edition_flg'],
                                'origin_server_flg'=>$file['origin_server_flg'],
                                'confidential_flg'=>$file['confidential_flg'],
                                'mst_company_id'=>$file['mst_company_id'],
                                'create_user_id'=>$file['create_user_id'],
                                'name' => $file['name'],
                                'server_file_name'=> $file['server_file_name'],
                                'server_file_path'=>$file['path'],
                                'parent_send_order'=>$file['parent_send_order'],
                                'total_timestamp'=>isset($file['total_timestamp'])?$file['total_timestamp']:0,
                                'pages' => $pages,
                                'width_px' => round($matches[1]*1.3333333333333333),
                                'height_px' => round($matches[2]*1.3333333333333333),
                                'comments' => isset($file['comments'])?$file['comments']:[],
                                'create_at' => $file['create_at'],
                            );
                        }catch (\Exception $e){
                            Log::error($e->getMessage().$e->getTraceAsString());
                        }

                        $fileInfo['del_flg'] = false;
                        $fileInfo['delete_watermark'] = null;
                    }
                    $ret['files'][] = $fileInfo;
                    return Response::json(['status' => true, 'message' => '文書ロード処理に成功しました。', 'data' => $ret]);
                }else {
                    Log::error('Log loadFormIssuance: '. $result->getBody());
                    return Response::json(['status' => false, 'message' => '文書ロード処理に失敗しました。', 'data' => null], 500);
                }
            } else {
                Log::error('Log loadFormIssuance: '. $result->getBody());
                return Response::json(['status' => false, 'message' => $resData->message, 'data' => null], 500);
            }
        }catch(\Exception $ex) {
            Log::error($ex->getMessage().$ex->getTraceAsString());
            return Response::json(['status' => false, 'message' => $ex->getMessage(), 'data' => null], 500);
        }
    }

    public function getFormIssuancePage(GetPageRequest $request){
        return $this->processGetPage($request, "/app/form_issuance/");
    }

    private function downloadTemplate($templateId, $type, \Illuminate\Http\Request $request){
        $storage_file_name = $request->get('storage_file_name');

        $circularId = $request->get('circularId');

        if (!$storage_file_name) {
            return ['status' => false, 'message' => 'ファイル名がありません。', 'data' => null];
        }
        $stored_path = 'template';
        if ($type == AppUtils::TEMPLATE_TYPE_FORM_ISSUANCE){
            $stored_path = "form_issuance";
        }
        $tplFolder = "app/$stored_path/";
        if (File::exists(storage_path($tplFolder.$storage_file_name))){
            $realFileExtension = pathinfo(storage_path().$tplFolder.$storage_file_name, PATHINFO_EXTENSION);
            $fileName = chop($storage_file_name, $realFileExtension);
            $stored_basename = $fileName ."pdf";
            $filePathPdf = storage_path($tplFolder .  $fileName ."pdf");
        }else{
            $userApiClient = UserApiUtils::getAuthorizedApiClientWithRequest($request);
            if(!$userApiClient){
                return ['status' => false, 'message' => '', 'data' => null];
            }
            if ($type == AppUtils::TEMPLATE_TYPE_FORM_ISSUANCE){
                $result = $userApiClient->get("form-issuances/".$templateId);
            }else if($type == AppUtils::TEMPLATE_TYPE_SPECIAL){
                $result = $userApiClient->get("templates_special/".$templateId);
            }else if($type == AppUtils::TEMPLATE_EDIT){
                $result = $userApiClient->get("templates_middle_edit/".$circularId);
            } else{
                $result = $userApiClient->get("templates/".$templateId);
            }
            if ($result->getStatusCode() != 200) {
                Log::error('Log get file template: '. $result->getBody());
                return ['status' => false, 'message' => 'ファイルの取得が失敗しました。', 'data' => null];
            }
            $data = json_decode((string)$result->getBody())->data;
            if (!isset($data->file)) {
                Log::error('Log get file template: ' . $result->getBody());
                return ['status' => false, 'message' => 'ファイルの取得が失敗しました。', 'data' => null];
            }

            if (!File::exists(storage_path($tplFolder))){
                File::makeDirectory(storage_path($tplFolder), 0755, true);
            }

            $realFileExtension = pathinfo(storage_path().$tplFolder . $data->file[0]->storage_file_name, PATHINFO_EXTENSION);

            $fileData = base64_decode($data->file_data);
            $uniqueFileName = hash('SHA256', $templateId.round(microtime(true) * 1000).rand().AppUtils::getUnique());
            $filePath = storage_path($tplFolder . $uniqueFileName.'.'.$realFileExtension);

            file_put_contents($filePath, $fileData);

            $stored_basename = $uniqueFileName. '.pdf';
            Log::debug("Convert from $filePath to $stored_basename in folder storage/app/$stored_path");

            $stored_path = "$stored_path/$stored_basename";
            $filePathPdf = storage_path('app/' . $stored_path);

            try {
                $errorResponse = self::tryConvertOfficeToPdf($filePath, $filePathPdf);
                if ($errorResponse) {
                    // 変換失敗
                    File::delete($filePath);
                    return $errorResponse;
                }
            } catch (\Exception $ex) {
                File::delete($filePath);
                throw $ex;
            }
        }

        return ['status' => true, 'message' => 'ファイルの取得が失敗しました。', 'data' => $filePathPdf, 'stored_basename' => $stored_basename];
    }

    private function processGetPage(GetPageRequest $request, $folderPath){
        ini_set("max_execution_time", 3600);
        $validated = $request->validated();

        $file_name = $validated['filename'];
        $page = $validated['page'];
        $isThumbnail = $validated['is_thumbnail'] ?? false;
        if (!$page){
            $page = 1;
        }

        $userPath = storage_path().$folderPath;
        //$folderPath = BoxUtils::getBoxFolder();
        $pdfFilePath = $userPath.$file_name;
        Log::debug("Convert image for $page for $pdfFilePath");

        $imageExists = false;
        if (!file_exists($pdfFilePath)) {
            Log::debug('PDF file is not exist');
        } else {
            $imgFolderPath = $userPath.pathinfo($pdfFilePath)['filename'];

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

            $isOldClient = !isset($validated['is_thumbnail']);
            if ($isOldClient) {
                $pdf = new PDFUtils($pdfFilePath);
                $page = $pdf->pagesInfo[$page - 1];

                preg_match('/([0-9\.]+) x ([0-9\.]+)/', $page["size"], $matches);
                $data['width'] = round($matches[1]*0.3527777778);
                $data['height'] = round($matches[2]*0.3527777778);
                $data['isPortraitPage'] = ($page["rot"] == 0 && $data['width'] < $data['height']);
            }

            $message = 'ロード処理に成功しました。';
        } else {
            $data = [
                'image' => "data:image/png;base64," . base64_encode(file_get_contents(public_path('images/no-preview.png')))
            ];
            $message = '変換処理に失敗しました。'.$page;
            //File::delete($imgFilePath);
        }
        return Response::json(['status' => $imageExists, 'message' => $message, 'data' => $data]);
    }

    private function getSignatureReason($username, $email){
        return sprintf("%s（%s）により%sに署名されています。", $username, $email, Carbon::now()->format("Y-m-d H:i:s.u"));
    }

    private function getShachihataSignatureReason(){
        return sprintf("Shachihata Cloudにより%sに署名されています。", Carbon::now()->format("Y-m-d H:i:s.u"));
    }

    private function getDTSSignatureReason(){
        return sprintf("MIND Timestamp Service DiaStamp A2E01により%sに署名されています", Carbon::now()->format("Y-m-d H:i:s.u"));
    }

    private function printAPICallErrorLog($uri, $result, $param = '')
    {
        $resData = json_decode((string)$result->getBody());
        $error_msg = "リクエストapi呼出失敗しました。 {API:%s; status:%s; message:%s; param:%s}";
        $message = $resData ? ($resData->message ?? ($resData->error->message ?? ' ')) : 'null';
        if($result->getStatusCode() == StatusCodeUtils::HTTP_UNAUTHORIZED || $result->getStatusCode() == StatusCodeUtils::HTTP_FORBIDDEN
            || $result->getStatusCode() == StatusCodeUtils::HTTP_CONFLICT){
            Log::warning(sprintf($error_msg, $uri, $result->getStatusCode(), $message, json_encode($param)));
        }else{
            Log::error(sprintf($error_msg, $uri, $result->getStatusCode(), $message, json_encode($param)));
        }
    }
}
