<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController;
use App\Http\Utils\IdAppApiUtils;
use App\Models\Company;
use App\Models\Constraint;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class IpRestrictionController extends AdminController
{
    private $model;

    // PAC_5-1151 ページ表示上限数
    const PAGE_LIMIT = 50;

    public function __construct(Company $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $this->setMetaTitle("接続IP制限設定");

        $user = \Auth::user();
        $company = Company::findOrFail($user->mst_company_id);
        $constraints = Constraint::where('mst_company_id',$user->mst_company_id)->firstOrFail();
        $this->assign('disabled', !$company->ip_restriction_flg);
        $maxIpAddressCount = 0;

        if (!$company->ip_restriction_flg) {
            Log::warning("Company not found");
            return $this->render('GlobalSetting.IpRestriction.index');
        }

        if (!$constraints->max_ip_address_count) {
            Log::warning("Constraint not found");
            return $this->render('GlobalSetting.IpRestriction.index');
        }else{
            //PAC_5-1152 IP登録数上限値をマスタ管理する
            $maxIpAddressCount = $constraints->max_ip_address_count;
        }
        if (!$request->has('refresh') || !$request->session()->has('IpRestriction.items')) {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }

            $params = [
                'company_id' => $user->mst_company_id,
                'contract_app' => config('app.pac_contract_app'),
                'app_env' => config('app.pac_app_env'),
                'contract_server'=> config('app.pac_contract_server'),
            ];

            $result = $client->post('ip_restrictions/list', [
                RequestOptions::JSON => $params
            ]);

            if ($result->getStatusCode() != 200) {
                Log::warning("IP制限リスト取得API失敗: " . $result->getBody());
                $response = json_decode((string)$result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message]
                ]);
            }

            $data = array_map(function($elm) {
                    unset($elm['id']);
                    return $elm;
                }, json_decode($result->getBody(), true)['data']);

            $cids = array_keys($data);
            $items = collect($data);

            $request->session()->put('IpRestriction.items', $items);
            $request->session()->put('IpRestriction.dirty', false);

            $hasrefresh = false;
        } else {
            $items = $request->session()->get('IpRestriction.items');
            $cids = array_keys($items->all());
            $hasrefresh = true;
        }
        $IpAddress = request('ip_address','');
        $IpName = (request('IpName',''));

        //PAC_5-1460 検索機能追加
        if (!is_null($IpName) && trim($IpName) !== "") {
            $items = $items->filter(function ($item) use ($IpName) {
                return strpos($item['name'], $IpName) !== false;
            });
        }
        if (!is_null($IpAddress) && trim($IpAddress) !== "") {
            $items = $items->filter(function ($item) use ($IpAddress) {
                return strpos($item['ip_address'], $IpAddress) !== false;
            });
        }
        $items = $this->paginateItems($items,$hasrefresh);

        $this->assign('cids',$cids);
        $this->assign('items', $items);

        $this->assign('dirty', $request->session()->get('IpRestriction.dirty', false));
        $this->assign('permit_unregistered_ip_flg', $company->permit_unregistered_ip_flg);
        $this->assign('max_count', $maxIpAddressCount);

        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        return $this->render('GlobalSetting.IpRestriction.index');
    }

    public function show(Request $request, int $id)
    {
        $items = $request->session()->get('IpRestriction.items');
        if (empty($items)) {
            return response()->json(['status' => false,'message' => '異常が発生しています。']);
        }

        return response()->json(['status' => true, 'info' => $items[$id]]);
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        $constraints = Constraint::where('mst_company_id',$user->mst_company_id)->firstOrFail();
        $maxIpAddressCount = $constraints->max_ip_address_count;

        $items = $request->session()->get('IpRestriction.items');
        if (is_null($items)) {
            return response()->json(['status' => false,'message' => ['異常が発生しています。']]);
        }
        if (count($items) >= $maxIpAddressCount) {
            return response()->json(['status' => false, 'message' => ['これ以上登録できません。']]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'max:128',
            'ip_address' => ['required', 'regex:/^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])((\/\d)|(\/[1-2]\d)|(\/3[0-2]))?)|((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|(\*))\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|(\*)))$/i',
                function ($attr, $val, $fail) use (&$items) {
                    foreach ($items as $k => $v) {
                        if ($val === $v['ip_address']) {
                            $fail('IPアドレスが重複しています。');
                            break;
                        }
                    }
                }
            ],
            //'subnet_mask' => 'required|ipv4'
        ]);
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $items[] = [
            'name' => $request->get('name'),
            'ip_address' => $request->get('ip_address'),
            'subnet_mask' => '255.255.255.255'
        ];
        $request->session()->put('IpRestriction.items', $items);
        $request->session()->put('IpRestriction.dirty', true);

        return response()->json(['status' => true, 'message' => ['IPアドレスを追加しました。']]);
    }


    public function update(Request $request, int $id)
    {
        $items = $request->session()->get('IpRestriction.items');
        if (empty($items)) {
            return response()->json(['status' => false,'message' => ['異常が発生しています。']]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'max:128',
            'ip_address' => ['required', 'regex:/^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])((\/\d)|(\/[1-2]\d)|(\/3[0-2]))?)|((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|(\*))\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5]|(\*)))$/i',
                function ($attr, $val, $fail) use (&$items, $id) {
                    foreach ($items as $k => $v) {
                        if ($k !== $id && $val === $v['ip_address']) {
                            $fail('IPアドレスが重複しています。');
                            break;
                        }
                    }
                }
            ],
            //'subnet_mask' => 'required|ipv4'
        ]);
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $items[$id] = [
            'name' => $request->get('name'),
            'ip_address' => $request->get('ip_address'),
            'subnet_mask' => '255.255.255.255'
        ];
        $request->session()->put('IpRestriction.items', $items);
        $request->session()->put('IpRestriction.dirty', true);
        return response()->json(['status' => true, 'message' => ['IPアドレスを更新しました。']]);
    }

    public function destroy(Request $request, int $id)
    {
        $items = $request->session()->get('IpRestriction.items');
        if (empty($items)) {
            return response()->json(['status' => false,'message' => ['異常が発生しています。']]);
        }

        unset($items[$id]);
        $request->session()->put('IpRestriction.items', $items);
        $request->session()->put('IpRestriction.dirty', true);

        return response()->json(['status' => true, 'message' => ['IPアドレスを削除しました。']]);
    }

    public function bulkUpdate(Request $request)
    {
        $items = $request->session()->get('IpRestriction.items');
        if (is_null($items)) {
            return response()->json(['status' => false,'message' => ['異常が発生しています。']]);
        }

        $user = \Auth::user();

        try {
            $client = IdAppApiUtils::getAuthorizeClient();
            if (!$client) {
                return response()->json(['status' => false,
                    'message' => ['Cannot connect to ID App']
                ]);
            }

            $company = Company::findOrFail($user->mst_company_id);
            $company->permit_unregistered_ip_flg = $request->input('permit_unregistered_ip_flg');
            $company->save();

            $params = [
                'company_id' => $user->mst_company_id,
                'contract_app' => config('app.pac_contract_app'),
                'app_env' => config('app.pac_app_env'),
                'contract_server'=> config('app.pac_contract_server'),
                'ip_info' => $items
            ];

            $result = $client->post('ip_restrictions/bulkUpdate', [
                RequestOptions::JSON => $params
            ]);

            if ($result->getStatusCode() != 200) {
                Log::warning("Call ID App Api to create company admin failed. Response Body " . $result->getBody());
                $response = json_decode((string)$result->getBody());
                return response()->json(['status' => false,
                    'message' => [$response->message]
                ]);
            }

            $request->session()->forget('IpRestriction.items');
            $request->session()->forget('IpRestriction.dirty');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => ['設定の更新に失敗しました。']]);
        }

        session()->flash('message', '設定を更新しました。');
        return response()->json(['status' => true]);
    }

    public function multiDelete(Request $request)
    {
        $items = $request->session()->get('IpRestriction.items');
        if (empty($items)) {
            return response()->json(['status' => false,'message' => ['異常が発生しています。']]);
        }

        $validator = Validator::make($request->all(), [
            'selected' => 'required|array',
            'selected.*' => 'integer',
        ]);
        if ($validator->fails())
        {
            $message = $validator->messages();
            $message_all = $message->all();
            return response()->json(['status' => false,'message' => $message_all]);
        }

        $selected = $request->get('selected');
        foreach ($selected as $id) {
            unset($items[$id]);
        }
        $request->session()->put('IpRestriction.items', $items);
        $request->session()->put('IpRestriction.dirty', true);

        return response()->json(['status' => true]);
    }

    //PAC_1151 外部API連携のページネーション実装
    public function paginateItems($items,bool $hasrefresh){
        $totalItems = count($items);

        $page   = intval(request('page'));
        $page   = $page > 0?$page:1;
        $start  = ($page - 1) * self::PAGE_LIMIT;
        $items  = $items->slice($start, self::PAGE_LIMIT);

        if($hasrefresh){
            $query = array('refresh'=>'true');
        }else{
            $query = [];
        }

        $items = new LengthAwarePaginator($items, $totalItems,self::PAGE_LIMIT, request('page'), [
            'path'  => request()->url(),
            'query' => $query,
        ]);

        return $items;
    }
}
