<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Jobs\TimeCardDownloadCsv;
use App\Models\TimeCard;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Utils\AppUtils;
use App\Http\Utils\DownloadUtils;

class TimeCardController extends AppBaseController
{
    var $table = 'time_card';
    var $model = null;

    public function __construct(TimeCard $timeCard)
    {
        $this->model = $timeCard;
    }

    public function index(Request $request)
    {
        // 最終打刻履歴
        $timeCard = TimeCard::where('mst_user_id', $request->user()->id)
            ->where('num_flg', '!=', 0)
            ->orderBy('punched_at', 'desc')
            ->first();
        if ($timeCard) {
            if ($timeCard->lastPunched()) {
                $arr = $this->responseData($timeCard);
                return $this->sendResponse($arr, '最新履歴を取得しました');
            } else {
                return $this->sendResponse(false, '打刻履歴がなし');
            }
        } else {
            return $this->sendResponse(false, '打刻履歴がなし');
        }
    }

    /**
     * @param Request $request
     * @param $num  最終打刻履歴の件数、必ずしも当日限らない、打刻動作の時点は必ず当日となりますので、注意必要
     * @return mixed
     */
    public function store(Request $request, $num)
    {
        // 当日打刻
        $timeCard = TimeCard::where('mst_user_id', $request->user()->id)
            ->whereBetween('punched_at', [now()->toDateString(), now()])
            ->first();
        // 最終打刻は当日か判定する
        $lastTimeDate = TimeCard::where('mst_user_id', $request->user()->id)
            ->where('num_flg', '!=', 0)
            ->whereBetween('punched_at', [now()->toDateString(), now()->toDateString() . ' 23:59:59'])
            ->orderBy('punched_at', 'desc')
            ->value('punched_at');

        if($lastTimeDate < now()->toDateString()) {
            $num = 0;
        }
        if ($timeCard) {
            // 打刻上限
            if ($timeCard->num_flg >= 10) {
                return $this->sendError('打刻上限に達しました');
            }

            // 打刻次数正確かを判定する
            if ($num != $timeCard->num_flg) {
                return $this->sendError('画面をリフレッシュして、再度試してください');
            }
        } else {
            $timeCard = new TimeCard;
        }

        $punchData = $timeCard->punchDataInit($num + 1);
        $arr = [
            'num_flg' => $num + 1,
            'mst_user_id' => $request->user()->id,
            'punch_data' => $punchData,
            'punched_at' => now()
        ];

        if ($timeCard->id) {
             $timeCard->update($arr);
        } else {
            $timeCard = TimeCard::create($arr);
        }

        $msg = $num % 2 == 1 ? '退勤' : '出勤';
        if ($timeCard) {
            if ($timeCard->lastPunched()) {
                $arr = $this->responseData($timeCard);
            } else {
                $arr = false;
            }
            return $this->sendResponse($arr, $msg . 'に成功しました');
        } else {
            return $this->sendError($msg . '画面をリフレッシュして、再度試してください');
        }
    }

    public function responseData($timeCard)
    {
        if ($timeCard->punched_at < now()->toDateString()) {
            $arr['todayPunched'] = false;
        } else {
            $arr['todayPunched'] = true;
        }
        $arr['lastPunchedTime'] = $timeCard->lastPunched();
        $arr['lastPunchedType'] = $timeCard->last_punched_type;
        $arr['currentNum'] = $timeCard->num_flg;
        return $arr;
    }

    public function searchList(Request $request)
    {
        $search = str_replace(['年', '月'], ['-', ''], $request->search);
        $date = Carbon::parse($search);
        $query = TimeCard::where('mst_user_id', $request->user()->id)->orderBy('id');
        $startTime = $date->toDateTimeString();
        $endTime = $date->endOfMonth()->toDateTimeString();

        if ($search) {
            $timeCards = $query->whereBetween('punched_at', [$startTime, $endTime])->get();
        } else {
            return $this->sendError('日付は必須項目です');
        }

        $tempArr = [];
        // 月の日数を取得する
        $days = $date->daysInMonth;

        for ($i = 1; $i <= $days; $i++) {
            $tempStr = $date->format('Y-m') . '-' . str_pad($i, 2, 0, STR_PAD_LEFT);
            array_push($tempArr, str_replace('-', '/', $tempStr));
        }

        $tempTimeCard = new TimeCard();
        if ($timeCards->isNotEmpty()) {
            $timeCards = $timeCards->groupBy(function ($item) {
                return $item->punched_date;
            });
            foreach ($tempArr as $k => $v) {
                if (!isset($timeCards[$v])) {
                    $tempTimeCard->punch_data = TimeCard::PUNCH_DATA;
                    $timeCards[$v] = $tempTimeCard;
                } else {
                    $timeCards[$v][0]->punch_data = $timeCards[$v][0]->getEveryPunchedTime();
                    $timeCards[$v] = $timeCards[$v][0];
                }
                $vAddWeek = TimeCard::WEEK[Carbon::parse(str_replace('/', '-', $v))->dayOfWeek];
                $timeCards[$v . ' (' . $vAddWeek . ')'] = $timeCards[$v];
                unset($timeCards[$v]);
            }
        } else {
            foreach ($tempArr as $k => $v) {
                $tempTimeCard->punch_data = TimeCard::PUNCH_DATA;
                $timeCards[$v] = $tempTimeCard;
                $vAddWeek = TimeCard::WEEK[Carbon::parse(str_replace('/', '-', $v))->dayOfWeek];
                $timeCards[$v . ' (' . $vAddWeek . ')'] = $timeCards[$v];
                unset($timeCards[$v]);
            }
        }
        $msg = '取得が成功しました';
        return $this->sendResponse($timeCards, $msg);
    }

    public function update(Request $request)
    {
        $data = $request->data;
        $dateIndex = explode(' ', $request->date_index)[0];
        $editDate = str_replace('/', '-', $dateIndex);

        $timeCard = TimeCard::where('mst_user_id', $request->user()->id)->whereBetween('punched_at', [$editDate, $editDate . ' 23:59:59'])->first();
        $num_flg = TimeCard::getNumForEdit($data['punch_data']);

        try {
            if ($timeCard) {
                // 更新
                $timeCard->punch_data = $data['punch_data'];
                $timeCard->num_flg = $num_flg;
                $timeCard->save();
            } else {
                if ($num_flg) {
                    $arr = [
                        'mst_user_id' => $request->user()->id,
                        'punch_data' => $data['punch_data'],
                        'num_flg' => $num_flg,
                        'punched_at' => $editDate
                    ];
                    TimeCard::create($arr);
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage() . $e->getTraceAsString());
            return $this->sendError('画面をリフレッシュして、再度試してください');
        }

        return $this->sendResponse('true', '更新が成功しました');
    }

    public function csvDownload(Request $request)
    {
        try {
            $user = $request->user();
            // timecard_yyyyMMDDHHmmss.csv
            /*PAC_5-3086 S*/
            $nickname = implode(' ', [$user->family_name, $user->given_name]);
            $nickname = preg_replace('/[\x00-\x1F\x7F]/', '', $nickname);
            $nickname = preg_replace('/\.|\\\|\\/|\:|\*|\?|\"|\<|\>|\|/', '', $nickname);
            $nickname = preg_replace('/[\\|\/|\r|\n|\t|\f]/', '', $nickname);
            /*PAC_5-3086 E*/
            $file_name = 'timecard_' . Carbon::now()->format('YmdHis') .'_'.$nickname. '.csv';
            
            // ダウンロードJob登録
            $result = DownloadUtils::downloadRequest(
                $user, 'App\Http\Utils\TimeCardControllerUtils', 'getTimeCardCsvData', $file_name,
                $user, $request->targetMonth
            );
            
            if(!($result === true)){
                return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $result]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            \Log::info('timecard-download-end-' . $file_name);
            return response()->json(['status' => true, 'message' => __('message.success.download_request.download_ordered', ['attribute' => $file_name])]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage() . $e->getTraceAsString());
            return $this->sendError(__('message.false.download_request.download_ordered', ['attribute' => $e->getMessage()]), \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
