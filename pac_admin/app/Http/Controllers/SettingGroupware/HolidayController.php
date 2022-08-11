<?php


namespace App\Http\Controllers\SettingGroupware;

use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use DB;

/**
 * PAC_14-45 休日設定
 * Class HolidayController
 * @package App\Http\Controllers\SettingGroupware
 */
class HolidayController extends AdminController
{
    private $api_url_index;
    private $limit = 10;

    public function __construct()
    {
        parent::__construct();

        $server = config('app.gw_domain');
        $domain = 'https://'.$server;
        $this->api_url_index = $domain . '/api/v1/admin/holiday-setting';
        
    }
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_HOLIDAY_SETTING_VIEW)){
            return redirect()->route('home');
        }
        $failureMessage = '';
        $page = $request->get('page', '1');
        $limit = $request->get('limit', $this->limit);
        $year = $request->get('year', Carbon::now()->format('Y'));
        $holidays = (object)[];
        try {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                $failureMessage = "Cannot connect to ID App。";
            } else {
                $apiResponse = $client->post($this->api_url_index . '/pageHoliday', $this->createApiParam($user, ['currentPage' => $page, 'year' => Carbon::createFromDate($year)->format('Y-m-d')]));

                if ($apiResponse->getStatusCode() == 200) {
                    $holidays = json_decode($apiResponse->getBody());
                    if ($holidays && isset($holidays->data) && count($holidays->data) === 0 && (int)$page > 1) {
                        return redirect()->route('Holiday.Index', ['page' => (int)$page-1, 'limit' => $limit, 'year' => $year]);
                    }
                } else {
                    Log::error('Search Holiday portalCompanyId:' . $user->mst_company_id . ' failed');
                    $failureMessage = "休日設定を取得できませんでした。";
                }
            }
            
        } catch (\Exception $e) {
            $failureMessage = "休日設定を取得できませんでした。";
        }
        $holidays->data = $holidays->data ?? [];
        $pageHolidays = new LengthAwarePaginator($holidays->data, $holidays->totalCount ?? 0, $limit, $page);
        $pageHolidays->setPath($request->url());
        $pageHolidays->appends(request()->input());
        $this->setMetaTitle('休日設定');
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));
        $this->assign('limit', $limit);
        $this->assign('failureMessage', $failureMessage);
        $this->assign('holidays', $holidays->data);
        $this->assign('pageHolidays', $pageHolidays);
        $this->assign('year', $year);
        $this->assign('max_year', Carbon::parse('+4 year')->format('Y'));
        $this->assign('min_year', Carbon::parse('-1 year')->format('Y'));
        $this->assign('use_angular', true);
        return $this->render('SettingGroupware.Holiday.index');
    }
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_HOLIDAY_SETTING_CREATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $name = $request->get('name');
        $date = $request->get('date');
        $color = $request->get('color');
    
        $name = trim($name);
        if (empty($name)) return response()->json(['status' => false, 'message' => ['祝日名を入力してください。']]);
        $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
        try {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false, 'message' => ['Cannot connect to ID App']]);
            }
    
            $apiResponse = $client->post($this->api_url_index . '/custom-holiday/createCustomHoliday', $this->createApiParam($user,
                [
                    'createUpdateCompanyCustomHolidayRequest' => [
                        'date' => $date,
                        'name' => $name,
                        'color' => $color,
                    ]
                ]
            ));
    
            if ($apiResponse->getStatusCode() !== 200) {
                Log::error('Add Holiday portalCompanyId:' . $user->mst_company_id . ' failed');
                return response()->json(['status' => false, 'message' => ['休日の追加処理に失敗しました。']]);
            }
            $result = json_decode($apiResponse->getBody());
            if ($result && $result->code && $result->code == -1) {
                return response()->json(['status' => false, 'message' => ['この休日はすでに存在します。']]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.create_holiday')],]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.create_holiday')],]);
    }
    
    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_HOLIDAY_SETTING_UPDATE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $name = $request->get('name');
        $date = $request->get('date');
        $color = $request->get('color');

        $name = trim($name);
        if (empty($name)) return response()->json(['status' => false, 'message' => ['祝日名を入力してください。']]);
        $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
        try {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
    
            $apiResponse = $client->post($this->api_url_index . '/custom-holiday/' . $id, $this->createApiParam($user, [
                'companyHolidayId' => $id,
                'createUpdateCompanyCustomHolidayRequest' => [
                    'date' => $date,
                    'name' => $name,
                    'color' => $color,
                ]
            ]));
    
            if ($apiResponse->getStatusCode() !== 200) {
                Log::error('Update Holiday portalCompanyId:' . $user->mst_company_id . ' failed');
                return response()->json(['status' => false,
                    'message' => ['休日の変更処理に失敗しました。']
                ]);
            }
            $result = json_decode($apiResponse->getBody());
            if ($result && $result->code && $result->code == -1) {
                return response()->json(['status' => false, 'message' => ['この休日はすでに存在します。']]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.update_holiday')],]);
        }
        
        return response()->json(['status' => true, 'message' => [__('message.success.update_holiday')]]);
    }
    
    /**
     * @param Request $request
     * @param Array $ids
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $user = \Auth::user();
        if(!$user->can(PermissionUtils::PERMISSION_HOLIDAY_SETTING_DELETE)){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        $customHolidayIdList = $request->get('customHolidayIdList', '');
        $japaneseHolidayIdList = $request->get('japaneseHolidayIdList', '');
        $year = $request->get('year', '');
        try {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $year = Carbon::createFromDate($year ?? Carbon::now()->format('Y'), 10)->format('Y-m-d'); // 10 month ローカル環境での誤ったタイムゾーンの影響を防ぐ
            $apiResponse = $client->delete($this->api_url_index . '/deleteBathHoliday', $this->createApiParam($user,
                [
                    'year' => $year,
                    'customHolidayIdList' => array_filter(explode(',', $customHolidayIdList)),
                    'japaneseHolidayIdList' => array_filter(explode(',', $japaneseHolidayIdList)),
                ]
            ));
            
            if ($apiResponse->getStatusCode() !== 200) {
                Log::error('Delete Holiday portalCompanyId:' . $user->mst_company_id . ' failed');
                return response()->json(['status' => false,
                    'message' => ['休日の削除処理に失敗しました。']
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.delete_holiday')],]);
        }
        return response()->json(['status' => true, 'message' => [__('message.success.delete_holiday')]]);
    }
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        try {
            $user = \Auth::user();
            if(!$user->can(PermissionUtils::PERMISSION_HOLIDAY_SETTING_DELETE)){
                return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
            }
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                //TODO message
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }
            $year = $request->post('year', '');
            $apiResponse = $client->post($this->api_url_index . '/japanese-holiday/Initialization', $this->createApiParam($user, [
                'year' => Carbon::createFromDate($year ?? Carbon::now()->format('Y'), 10)->format('Y-m-d') // 10 month ローカル環境での誤ったタイムゾーンの影響を防ぐ
            ]));
        
            if ($apiResponse->getStatusCode() !== 200) {
                Log::error('Reset Holiday portalCompanyId:' . $user->mst_company_id . ' failed');
                return response()->json(['status' => false,
                    'message' => ['休日の初期化処理に失敗しました。']
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.reset_holiday')],]);
        }
    
        return response()->json(['status' => true, 'message' => [__('message.success.reset_holiday')]]);
    }
    
    /**
     * @param null $params
     * @return array[]
     */
    private function createApiParam($user, $params = null)
    {
        $authParams = [
            "portalEmail" => $user->email,
            "portalCompanyId" => $user->mst_company_id,
            "editionFlg" => config('app.pac_contract_app'),
            "envFlg" => config('app.pac_app_env'),
            "serverFlg" => config('app.pac_contract_server')
        ];
        return [RequestOptions::JSON => empty($params) ? $authParams : array_merge([
            "adminRequest" => $authParams,
        ], $params)];
    }
}
