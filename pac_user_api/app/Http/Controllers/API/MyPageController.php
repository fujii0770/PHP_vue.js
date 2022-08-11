<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\MstMyPageLayout;
use App\Models\MyPage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\API\CreateMyPageAPIRequest;
use App\Http\Requests\API\DeleteMyPageAPIRequest;
use App\Http\Requests\API\UpdateMyPageAPIRequest;

/**
 * Class MyPageController
 * @package App\Http\Controllers\API
 */
class MyPageController extends AppBaseController
{
    var $table = 'mypage';
    var $model = null;

    public function __construct(MyPage $myPage)
    {
        $this->model = $myPage;
    }

    /**
     * Display a listing of the MyPage.
     * GET|HEAD /favorite
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        try {
            $mstMyPageLayout = new MstMyPageLayout();
            $countMyPage = $this->model
                ->where('mst_user_id', $user->id)
                ->count();

            $countNewMyPage = $this->model
                ->where('mst_user_id', $user->id)
                ->where('layout', 'like', '%available%')
                ->count();

            $countNewMstMyPage = $mstMyPageLayout
                ->where('layout', 'like', '%available%')
                ->count();

            if ($countNewMstMyPage === 4) {
                if ($countMyPage === 0 || ($countNewMyPage > 0 && $countNewMyPage < 4)) {
                    if ($countNewMyPage > 0 && $countNewMyPage < 4) {
                        $this->model->where('mst_user_id', $user->id)->delete();
                    }
                    $layoutList = $mstMyPageLayout::whereIn('id', [1, 2, 3, 4])->get();
                    $default = 1;
                    foreach ($layoutList as $item) {
                        $myPageDefault = [
                            'mst_user_id' => $user->id,
                            'mst_mypage_layout_id' => $item->id,
                            'page_name' => $item->layout_name,
                            'layout' => $item->layout,
                            'default' => $default,
                            'create_at' => Carbon::now(),
                            'create_user' => $user->email
                        ];
                        $this->model->insert($myPageDefault);
                        $default = 0;
                    }
                } else if ($countNewMyPage === 0){
                    $this->transformData($user);
                }
            }

            $mypage = $this->model->where('mst_user_id', $user->id)->where('default', DB::raw(1))->get();
            return $this->sendResponse($mypage, 'マイページデータを取得するのが成功になった');

        } catch (Exception $ex) {
            Log::error('MyPageController@index:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created MyPage in storage.
     * POST /mypage
     *
     * @param CreateMyPageAPIRequest $request
     *
     * @return Response
     * @throws Exception
     *
     */
    public function store(CreateMyPageAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        $data['mst_user_id'] = $user->id;
        $data['create_user'] = $user->email;
        $data['create_at'] = Carbon::now();
        try {
            $mstMyPageLayout = new MstMyPageLayout();
            $countNewMstMyPage = $mstMyPageLayout->where('layout', 'like', '%available%')->count();
            if ($countNewMstMyPage === 4) $this->model->insert($data);
            return $this->sendSuccess('マイページデータを追加するのが成功になった');
        } catch (Exception $ex) {
            Log::error('MyPageController@store:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    /**
     * Update a MyPage in storage.
     * PUT /mypage/{mypage_id}
     *
     * @param UpdateMyPageAPIRequest $request
     *
     * @return Response
     */
    public function update(UpdateMyPageAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        $data['mst_user_id'] = $user->id;
        $data['update_user'] = $user->email;
        $data['update_at'] = Carbon::now();
        try {
            $mstMyPageLayout = new MstMyPageLayout();
            $countNewMstMyPage = $mstMyPageLayout->where('layout', 'like', '%available%')->count();
            $myPage = $this->model->where([
                'mst_user_id' => $data['mst_user_id'],
                'id' => $data['id']
            ])->first();
            if (is_null($myPage) || $countNewMstMyPage !== 4) {
                return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (isset($data['mst_mypage_layout_id'])) {
                $myPage->mst_mypage_layout_id = $data['mst_mypage_layout_id'];
            }
            if (isset($data['page_name'])) {
                $myPage->page_name = $data['page_name'];
            }
            if (isset($data['layout'])) {
                $myPage->layout = $data['layout'];
            }
            if (isset($data['default'])) {
                $myPage->default = $data['default'];
            }
            $myPage->update_user = $data['update_user'];
            $myPage->update_at = $data['update_at'];
            $myPage->save();
            if (isset($data['default']) && $data['default'] === 1) {
                $this->model->where('mst_user_id', $data['mst_user_id'])
                    ->where('id', '<>', $data['id'])->update(['default' => 0]);
            }
            return $this->sendSuccess('マイページの更新に成功しました。');
        } catch (Exception $ex) {
            Log::error('MyPageController@update:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified MyPage from storage.
     * DELETE /mypage/{id}
     *
     * @param DeleteMyPageAPIRequest $request
     *
     * @return Response
     * @throws Exception
     *
     */
    public function destroy(DeleteMyPageAPIRequest $request)
    {
        $data = $request->all();
        $user = $request->user();
        try {
            DB::table($this->table)->where([
                'mst_user_id' => $user->id,
                'id' => $data['id']
            ])->delete();
            return $this->sendSuccess('マイページデータを削除するのが成功になった');
        } catch (Exception $ex) {
            Log::error('MyPageController@destroy:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getMyPageList(Request $request)
    {
        $user = $request->user();
        try {
            $mstMyPageLayout = new MstMyPageLayout();
            $countMyPage = $this->model
                ->where('mst_user_id', $user->id)
                ->count();

            $countNewMyPage = $this->model
                ->where('mst_user_id', $user->id)
                ->where('layout', 'like', '%available%')
                ->count();
    
            $countNewMstMyPage = $mstMyPageLayout
                ->where('layout', 'like', '%available%')
                ->count();

            if ($countNewMstMyPage === 4) {
                if ($countMyPage === 0 || ($countNewMyPage > 0 && $countNewMyPage < 4)) {
                    if ($countNewMyPage > 0 && $countNewMyPage < 4) {
                        $this->model->where('mst_user_id', $user->id)->delete();
                    }
                    $layoutList = $mstMyPageLayout::whereIn('id', [1, 2, 3, 4])->get();
                    $default = 1;
                    foreach ($layoutList as $item) {
                        $myPageDefault = [
                            'mst_user_id' => $user->id,
                            'mst_mypage_layout_id' => $item->id,
                            'page_name' => $item->layout_name,
                            'layout' => $item->layout,
                            'default' => $default,
                            'create_at' => Carbon::now(),
                            'create_user' => $user->email
                        ];
                        $this->model->insert($myPageDefault);
                        $default = 0;
                    }
                } else if ($countNewMyPage === 0){
                    $this->transformData($user);
                }
            }
            $myPageList = DB::table('mypage as m')
                ->join('mst_mypage_layout as mml', 'mml.id', '=', 'm.mst_mypage_layout_id')
                ->where('m.mst_user_id', $user->id)
                ->select('m.*', 'mml.layout_src')
                ->orderBy('m.id', 'ASC')
                ->get();
            return $this->sendResponse($myPageList, 'マイページデータを取得するのが成功になった');
        } catch (Exception $ex) {
            Log::error('MyPageController@index:' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @param $user
     */
    private function transformData($user)
    {
        try {
        $mstMyPageList = DB::table('mst_mypage_layout')->select('id', 'layout_name', 'layout')->orderBy('id', 'ASC')->get();
        $myPage = DB::table('mypage')
            ->select('mst_user_id', 'layout', 'create_user', 'mst_mypage_layout_id')
            ->where('mst_user_id', $user->id)
            ->first();
        $layout = json_decode($myPage->layout);
        switch ($myPage->mst_mypage_layout_id) {
            case 1:
                $defaultPage = clone $mstMyPageList[0];
                break;
            case 2:
                $defaultPage = clone $mstMyPageList[1];
                break;
            case 3:
                $defaultPage = clone $mstMyPageList[2];
                break;
            case 4:
                $defaultPage = clone $mstMyPageList[3];
                break;
            default:
                $defaultPage = clone $mstMyPageList[0];
                break;
        }
        $newLayout = [];
        $mstLayout = json_decode($defaultPage->layout);
        foreach ($mstLayout as $layout1) {
            if (in_array($layout1->name, ['top_menu', 'top_screen', 'notification', 'movie', 'advertisement', 'customize_area'])) {
                $newLayout[] = clone $layout1;
            }
        }
        $x = 0;
        $default_y = 11;
        $max_y = 11;
        if (isset($layout->layout) && is_array($layout->layout)) {
            $tmp = $layout->layout;
            uasort($tmp,function ($a,$b){
                if ($a->width == $b->width) {
                    return 0;
                }
                return ($a->width < $b->width) ? -1 : 1;
            });

            foreach ($tmp as  $layout_item) {
                $y = $default_y;
                switch ($layout_item->width) {
                    case 3:
                        $w = 6;
                        break;
                    case 6:
                        $w = 12;
                        break;
                    case 9:
                        $w = 18;
                        break;
                    case 12:
                        $w = 24;
                        $x =0;
                        $y = $max_y;
                        break;
                    default:
                        break 2;
                }
                foreach ($layout_item->component as $component) {
                    $newLayout[] = (object)[
                        'x' => $x,
                        'y' => $y,
                        'w' => $w,
                        'h' => 28,
                        'i' => (string)count($newLayout),
                        'name' => $component,
                        'static' => false,
                        "minW" => 6,
                        "minH" => 28,
                        "maxW" => 32,
                        "maxH" => 50,
                        "show" => true,
                        "available" => true,
                        "resizing" => false,
                        "hasData" => true,
                        "moved" => false,
                    ];
                    if ($component === 'time_card') {
                        $newLayout[] = (object)[
                            'x' => $x,
                            'y' => $y,
                            'w' => $w,
                            'h' => 28,
                            'i' => (string)count($newLayout),
                            'name' => 'time_card_attendance',
                            'static' => false,
                            "minW" => 6,
                            "minH" => 28,
                            "maxW" => 32,
                            "maxH" => 50,
                            "show" => true,
                            "available" => true,
                            "resizing" => false,
                            "hasData" => true,
                            "moved" => false,
                        ];
                    }
                    $y += 28;
                }
                $max_y = $y > $max_y ? $y : $max_y;
                $x += $w;
                if ($layout_item->width != 24){
                    $y = 11;
                }
                if ($w === 24) $default_y = $y;
            }
        
            foreach ($newLayout as $item) {
                switch ($item->name) {
                    case "favorite":
                        if (isset($layout->show_favorite)) {
                            $item->show = $layout->show_favorite;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "scheduler":
                        if (isset($layout->show_scheduler)) {
                            $item->show = $layout->show_scheduler;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "bulletin_board":
                        if (isset($layout->show_bulletin_board)) {
                            $item->show = $layout->show_bulletin_board;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "circular":
                        if (isset($layout->show_circular)) {
                            $item->show = $layout->show_circular;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "time_card":
                        if (isset($layout->show_time_card)) {
                            $item->show = $layout->show_time_card;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "time_card_attendance":
                        if (isset($layout->show_time_card)) {
                            $item->show = $layout->show_time_card;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "file_mail":
                        if (isset($layout->show_file_mail)) {
                            $item->show = $layout->show_file_mail;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "special":
                        if (isset($layout->show_special)) {
                            $item->show = $layout->show_special;
                        } else {
                            $item->show = true;
                        }
                        break;
                    case "faq_bulletin_board":
                        if (isset($layout->show_faq_bulletin_board)) {
                            $item->show = $layout->show_faq_bulletin_board;
                        } else {
                            $item->show = true;
                        }
                        break;
                    default:
                        break;
                }
            }
            $newLayout[]= (object)[
                'x' => 0,
                'y' => $max_y,
                'w' => 24,
                'h' => 28,
                'i' => (string)count($newLayout),
                'name' => 'receive_plan',
                'static' => false,
                "minW" => 6,
                "minH" => 28,
                "maxW" => 32,
                "maxH" => 50,
                "show" => true,
                "available" => true,
                "resizing" => false,
                "hasData" => true,
                "moved" => false,
            ];
            $max_y += 28; // The new component uses this as the y value

            $newLayout[]= (object)[
                'x' => 0,
                'y' => $max_y,
                'w' => 24,
                'h' => 28,
                'i' => (string)count($newLayout),
                'name' => 'to_do_list',
                'static' => false,
                "minW" => 6,
                "minH" => 28,
                "maxW" => 32,
                "maxH" => 50,
                "show" => true,
                "available" => true,
                "resizing" => false,
                "hasData" => true,
                "moved" => false,
            ];
            $max_y += 28; // The new component uses this as the y value
            $newLayout = json_encode($newLayout);
        } else {
            $newLayout = $defaultPage->layout;
        }
        $data = [
            [
                'mst_user_id' => $myPage->mst_user_id,
                'mst_mypage_layout_id' => $mstMyPageList[0]->id,
                'page_name' => $mstMyPageList[0]->layout_name,
                'layout' => $defaultPage->id === 1 ? $newLayout : $mstMyPageList[0]->layout,
                'default' => $defaultPage->id === 1 ? 1 : 0,
                'create_at' => Carbon::now(),
                'create_user' => $myPage->create_user,
            ],
            [
                'mst_user_id' => $myPage->mst_user_id,
                'mst_mypage_layout_id' => $mstMyPageList[1]->id,
                'page_name' => $mstMyPageList[1]->layout_name,
                'layout' => $defaultPage->id === 2 ? $newLayout : $mstMyPageList[1]->layout,
                'default' => $defaultPage->id === 2 ? 1 : 0,
                'create_at' => Carbon::now(),
                'create_user' => $myPage->create_user,
            ],
            [
                'mst_user_id' => $myPage->mst_user_id,
                'mst_mypage_layout_id' => $mstMyPageList[2]->id,
                'page_name' => $mstMyPageList[2]->layout_name,
                'layout' => $defaultPage->id === 3 ? $newLayout : $mstMyPageList[2]->layout,
                'default' => $defaultPage->id === 3 ? 1 : 0,
                'create_at' => Carbon::now(),
                'create_user' => $myPage->create_user,
            ],
            [
                'mst_user_id' => $myPage->mst_user_id,
                'mst_mypage_layout_id' => $mstMyPageList[3]->id,
                'page_name' => $mstMyPageList[3]->layout_name,
                'layout' => $defaultPage->id === 4 ? $newLayout : $mstMyPageList[3]->layout,
                'default' => $defaultPage->id === 4 ? 1 : 0,
                'create_at' => Carbon::now(),
                'create_user' => $myPage->create_user,
            ],
        ];
        DB::table('mypage')->where('mst_user_id', $myPage->mst_user_id)->delete();
        DB::table('mypage')->insert($data);
        } catch (\Exception $ex) {
            Log::error('MyPageController@transformData: mypage transformData failed 。' . $ex->getMessage() . $ex->getTraceAsString());
            return $this->sendError(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
