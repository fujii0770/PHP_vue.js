<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FaqBbsApiController extends AppBaseController
{


    const BBS_LIST = '0';
    const BBS_DETAIL = '1';
    private $client;

    public function __construct()
    {
        $this->client = IdAppApiUtils::getAuthorizeClient();
    }


    public function getBbsCategories(Request $request)
    {
        $data = $request->all();
        $limitPage = isset($data['limit']) ? $data['limit'] : 10;
        $page = isset($data['page']) ? $data['page'] : 1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        $allflg = isset($data['allflg']) ? $data['allflg'] : '';
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'page' => $page,
            'limit' => $limit,
            'allflg' => $allflg
        ];

        $result = $client->get('bbs/category', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $categories = json_decode((string)$result->getBody(), true)['data'];
            if (!$allflg) {
                unset($categories['first_page_url']);
                unset($categories['last_page_url']);
                unset($categories['next_page_url']);
                unset($categories['path']);
                unset($categories['prev_page_url']);
            }
            return $this->sendResponse($allflg ? ['data' => $categories] : $categories, 'サポート掲示板の権限取得に成功しました。');
        } else {
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addTopic(Request $request)
    {
        $data = $request->all();
        $errmsg = '投稿追加処理で異常が発生しました。';
        $validator = Validator::make($data, [
            'value.bbs_category_id' => 'required|numeric',
            'value.title' => 'required|string',
            'value.content' => 'required|string',
            'value.view_type' => 'required|numeric|min:0|max:1',
            'value.notify_type' => 'required|numeric|min:0|max:1',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'bbs_category_id' => $data['value']['bbs_category_id'],
            'title' => $data['value']['title'],
            'content' => $data['value']['content'],
            'view_type' => $data['value']['view_type'],
            'notify_type' => $data['value']['notify_type'],
        ];
        $result = $client->post('bbs', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $topic_info = json_decode((string)$result->getBody(), true)['data'];

            $files = $request->get('attachment');
            $attachments = [];
            foreach ($files as $file) {
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION) ? '.' . pathinfo($file['name'], PATHINFO_EXTENSION) : '';
                $path = config('filesystems.prefix_path') . '/' . $topic_info['path'] . '/' . Str::uuid() . $file_ext;
                Storage::disk('s3')->put($path, base64_decode($file['file']));
                $attachments[] = [
                    'bbs_id' => $topic_info['bbs_id'],
                    'comment_id' => $topic_info['comment_id'],
                    's3_path' => $path,
                    'file_name' => $file['name'],
                    'size' => $file['size'],
                    'type' => 'add'
                ];
            }
            $params['files'] = $attachments;

            $file_result = $client->post('bbs/attachment', [
                RequestOptions::JSON => $params
            ]);
            if ($file_result->getStatusCode() == Response::HTTP_OK) {
                return $this->sendSuccess('topic add successfully');
            } else {
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } else {
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTopicList(Request $request)
    {
        $input = $request->all();
        $limitPage = isset($input['limit']) ? $input['limit'] : 10;
        $page = isset($input['page']) ? $input['page'] : 1;
        $limit = AppUtils::normalizeLimit($limitPage, 10);
        $keyword = isset($input['keyword']) ? $input['keyword'] : '';
        $categoryId = isset($input['categoryId']) ? $input['categoryId'] : '';
        $bbsId = isset($input['bbsId']) ? $input['bbsId'] : '';
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
        ];
        $procKbn = $request->procKbn;
        switch ($procKbn) {
            case self::BBS_LIST:
                $params['page'] = $page;
                $params['limit'] = $limit;
                $params['keyword'] = $keyword;
                $params['bbs_category_id'] = $categoryId;
                $result = $client->get('bbs', [
                    RequestOptions::JSON => $params
                ]);
                if ($result->getStatusCode() == Response::HTTP_OK) {
                    $bbs = json_decode((string)$result->getBody(), true)['data'];
                    unset($bbs['first_page_url']);
                    unset($bbs['last_page_url']);
                    unset($bbs['next_page_url']);
                    unset($bbs['path']);
                    unset($bbs['prev_page_url']);
                    return $this->sendResponse($bbs, 'サポート掲示板の権限取得に成功しました。');
                } else {
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            case self::BBS_DETAIL:
                $params['id'] = $bbsId;
                $result = $client->get('bbs/show', [
                    RequestOptions::JSON => $params
                ]);
                if ($result->getStatusCode() == Response::HTTP_OK) {
                    $bbs = json_decode((string)$result->getBody(), true)['data'];
                    return $this->sendResponse($bbs, 'サポート掲示板の権限取得に成功しました。');
                } else {
                    return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                }
        }
    }

    public function updateTopic(Request $request)
    {
        $data = $request->all();
        $errmsg = '投稿追加処理で異常が発生しました。';
        $validator = Validator::make($data, [
            'id' => 'required|numeric',
            'value.bbs_category_id' => 'required|numeric',
            'value.title' => 'required|string',
            'value.content' => 'required|string',
            'value.view_type' => 'required|numeric|min:0|max:1',
            'value.notify_type' => 'required|numeric|min:0|max:1',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'bbs_category_id' => $data['value']['bbs_category_id'],
            'id' => $data['id'],
            'title' => $data['value']['title'],
            'content' => $data['value']['content'],
            'view_type' => $data['value']['view_type'],
            'notify_type' => $data['value']['notify_type'],
        ];
        $result = $client->post('bbs/update', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $topic_info = json_decode((string)$result->getBody(), true)['data'];

            $files = $request->get('attachment');
            $attachments = [];
            foreach ($files as $file) {
                $path = '';
                if ($file['type'] == 'add') {
                    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION) ? '.' . pathinfo($file['name'], PATHINFO_EXTENSION) : '';
                    $path = config('filesystems.prefix_path') . '/' . $topic_info['path'] . '/' . Str::uuid() . $file_ext;
                    Storage::disk('s3')->put($path, base64_decode($file['file']));
                }
                $attachments[] = [
                    'id' => $file['id'] ?? 0,
                    'bbs_id' => $topic_info['bbs_id'],
                    'comment_id' => $topic_info['comment_id'],
                    's3_path' => $path,
                    'file_name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type'],
                ];
            }
            $params['files'] = $attachments;

            $file_result = $client->post('bbs/attachment', [
                RequestOptions::JSON => $params
            ]);
            if ($file_result->getStatusCode() == Response::HTTP_OK) {
                return $this->sendSuccess('topic add successfully');
            } else {
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } else {
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addComment(Request $request)
    {
        $data = $request->all();
        $errmsg = 'コメント追加処理で異常が発生しました。';
        $validator = Validator::make($data, [
            'value.bbs_id' => 'required|numeric',
            'value.comment' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'bbs_id' => $data['value']['bbs_id'],
            'content' => $data['value']['comment'],
        ];
        $result = $client->post('bbs/comment', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $topic_info = json_decode((string)$result->getBody(), true)['data'];

            $files = $request->get('attachment');
            $attachments = [];
            foreach ($files as $file) {
                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION) ? '.' . pathinfo($file['name'], PATHINFO_EXTENSION) : '';
                $path = config('filesystems.prefix_path') . '/' . $topic_info['path'] . '/' . Str::uuid() . $file_ext;
                Storage::disk('s3')->put($path, base64_decode($file['file']));
                $attachments[] = [
                    'bbs_id' => $topic_info['bbs_id'],
                    'comment_id' => $topic_info['comment_id'],
                    's3_path' => $path,
                    'file_name' => $file['name'],
                    'size' => $file['size'],
                    'type' => 'add'
                ];
            }
            $params['files'] = $attachments;

            $file_result = $client->post('bbs/attachment', [
                RequestOptions::JSON => $params
            ]);
            if ($file_result->getStatusCode() == Response::HTTP_OK) {
                return $this->sendSuccess('topic add successfully');
            } else {
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } else {
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateComment(Request $request)
    {
        $data = $request->all();
        $errmsg = 'コメント追加処理で異常が発生しました。';
        $validator = Validator::make($data, [
            'value.id' => 'required|numeric',
            'value.content' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'id' => $data['value']['id'],
            'content' => $data['value']['content'],
        ];
        $result = $client->post('bbs/comment/update', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $comment_info = json_decode((string)$result->getBody(), true)['data'];

            $files = $request->get('attachment');
            $attachments = [];
            foreach ($files as $file) {
                $path = '';
                if ($file['type'] == 'add') {
                    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION) ? '.' . pathinfo($file['name'], PATHINFO_EXTENSION) : '';
                    $path = config('filesystems.prefix_path') . '/' . $comment_info['path'] . '/' . Str::uuid() . $file_ext;
                    Storage::disk('s3')->put($path, base64_decode($file['file']));
                }
                $attachments[] = [
                    'id' => $file['id'] ?? 0,
                    'bbs_id' => $comment_info['bbs_id'],
                    'comment_id' => $comment_info['comment_id'],
                    's3_path' => $path,
                    'file_name' => $file['name'],
                    'size' => $file['size'],
                    'type' => 'add',
                    'type' => $file['type'],
                ];
            }
            $params['files'] = $attachments;

            $file_result = $client->post('bbs/attachment', [
                RequestOptions::JSON => $params
            ]);
            if ($file_result->getStatusCode() == Response::HTTP_OK) {
                return $this->sendSuccess('topic add successfully');
            } else {
                return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } else {
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteComment(Request $request)
    {
        $data = $request->all();
        $errmsg = '投稿追加処理で異常が発生しました。';
        $validator = Validator::make($data, [
            'value.id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'id' => $data['value']['id'],
        ];
        $result = $client->post('bbs/comment/delete', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $topic_info = json_decode((string)$result->getBody(), true)['data'];
            return $this->sendSuccess('comment delete successfully');
        } else {
            return $this->sendError(\Illuminate\Http\Response::$statusTexts[\Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteTopic(Request $request)
    {
        $data = $request->all();
        $errmsg = '投稿追加処理で異常が発生しました。';
        $validator = Validator::make($data, [
            'ids' => 'required|array',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->all(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'ids' => $data['ids'],
        ];
        $result = $client->post('bbs/topic/delete', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $topic_info = json_decode((string)$result->getBody(), true)['data'];
            return $this->sendSuccess('topic delete successfully');
        } else {
            return $this->sendError($result->getStatusCode(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getFile(Request $request)
    {
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
            'id' => $request->get('id', ''),
        ];
        $result = $client->post('bbs/file', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $url = json_decode((string)$result->getBody(), true)['url'];
            return $this->sendResponse(['url' => $url], '');
        } else {
            return $this->sendError($result->getStatusCode(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getNoticeList(Request $request)
    {
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
        ];
        $result = $client->get('bbs/notice/list', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $res = json_decode((string)$result->getBody(), true)['data'];
            return $this->sendResponse($res, '');
        } else {
            return $this->sendError((string)$result->getBody(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function unReadCnt(Request $request)
    {
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
        ];
        $result = $client->get('bbs/unread/cnt', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            $res = json_decode((string)$result->getBody(), true);
            return $res;
        } else {
            return $this->sendError((string)$result->getBody(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function makeNoticeRead(Request $request, $notice_id)
    {
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
        ];
        $result = $client->post('bbs/notice/read/' . $notice_id, [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            return $this->sendResponse([], '');
        } else {
            return $this->sendError((string)$result->getBody(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function makeNoticeReadByBbsId(Request $request, $bbs_id)
    {
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
        ];
        $result = $client->post('bbs/notice/readByBbs/' . $bbs_id, [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            return $this->sendResponse([], '');
        } else {
            return $this->sendError((string)$result->getBody(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function makeAllNoticeRead(Request $request)
    {
        $user = $request->user();
        $client = IdAppApiUtils::getAuthorizeClient();
        if (!$client) {
            //TODO message
            return response()->json(['status' => false,
                'message' => ['Cannot connect to ID App']
            ]);
        }
        $params = [
            'email' => $user->email,
            'contract_app' => config('app.edition_flg'),
            'app_env' => config('app.server_env'),
            'contract_server' => config('app.server_flg'),
        ];
        $result = $client->post('bbs/all/notice_read', [
            RequestOptions::JSON => $params
        ]);
        if ($result->getStatusCode() == Response::HTTP_OK) {
            return $this->sendResponse([], '');
        } else {
            return $this->sendError((string)$result->getBody(), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}