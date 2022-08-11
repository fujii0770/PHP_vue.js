<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\SanitizingLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingSanitizingController extends Controller
{
    private $sanitizingLine;

    public function __construct(SanitizingLine $sanitizingLine)
    {
        parent::__construct();
        $this->sanitizingLine = $sanitizingLine;
    }

    /**
     * 無害化回線一覧を取得
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //画面からリクエスト情報を取得
        $page       = $request->get('page', 1);
        $limit      = $request->get('limit', 10);
        $orderBy    = $request->get('orderBy', "sanitizing_line_name");
        $orderDir   = $request->get('orderDir', "DESC");

        //一覧取得の条件を作成
        $where = ['1=1'];
        $where_arg = [];

        // 回線名検索
        if ($request->get('sanitizing_line_name')) {
            $where[]     = "sanitizing_line_name like ?";
            $where_arg[] = "%".$request->get('sanitizing_line_name')."%";
        }
        // 無害化ファイル要求上限検索
        if ($request->get('sanitize_request_limit')) {
            $where[]     = 'sanitize_request_limit = ?';
            $where_arg[] = $request->get('sanitize_request_limit');
        }

        // 企業名検索
        $cids = [];
        if($request->get('company_name')){
            $cids = DB::table('mst_company')
                ->select('mst_sanitizing_line_id')
                ->where('company_name', 'like', '%'.$request->get('company_name').'%')
                ->pluck('mst_sanitizing_line_id')
                ->toArray();
        }

        //無害化回線一覧を取得
        $dataItems = $this->sanitizingLine
            ->select('id', 'sanitizing_line_name', 'sanitize_request_limit')
            ->where(function ($dataItems) use ($request, $cids){
                if($request->get('company_name')){
                    $dataItems->whereIn('id', $cids);
                }
            })
            ->whereRaw(implode(" AND ", $where), $where_arg)
            ->orderBy($orderBy, $orderDir)
            ->paginate($limit)->appends(request()->input());

        // 回線設定した企業を取得
        foreach ($dataItems as $dataItem) {
            $companyNames = DB::table('mst_company')
                ->select('company_name')
                ->where('mst_sanitizing_line_id', $dataItem->id)
                ->orderBy('id')
                ->get()
                ->toArray();

            $company_names = '';
            foreach ($companyNames as $name) {
                $company_names = $company_names ? $company_names.';'.$name->company_name : $name->company_name;
            }
            $dataItem->company_names = $company_names;
        }

        $this->assign('itemsLine', $dataItems);
        $this->assign('use_angular', true);
        $this->assign('show_sidebar', true);
        $this->assign('use_contain', true);
        $this->assign('limit', $limit);
        $this->assign('orderBy', $orderBy);
        $this->assign('orderDir', strtolower($orderDir) == "asc" ? "desc" : "asc");
        $this->setMetaTitle("無害化回線設定");
        $this->addStyleSheet('tablesaw', asset("/libs/tablesaw/tablesaw.css"));
        $this->addScript('tablesaw', asset("/libs/tablesaw/tablesaw.jquery.js"));
        $this->addScript('tablesaw-init', asset("/libs/tablesaw/tablesaw-init.js"));

        return $this->render('SettingSanitizing.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * 無害化回線新規作成
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //画面からリクエスト情報を取得
        $user   = $request->user();
        $item_info = $request->get('item');
        //無害化回線新規登録データを作成
        $item = new $this->sanitizingLine;
        $item->fill($item_info);
        $item->create_user = $user->getFullName();
        $item->update_user = $user->getFullName();

        try {
            // 無害化回線新規作成
            $item->save();
            return response()->json(['status' => true, 'message' => [__('message.success.sanitizing_line.create')] ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.sanitizing_line.create')] ]);
        }
    }

    /**
     * 無害化回線詳細情報を取得
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //無害化回線詳細情報を取得
        $item = $this->sanitizingLine->find($id);

        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }
        return response()->json(['status' => true, 'item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 無害化回線更新
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user   = $request->user();
        $item_info = $request->get('item');

        try{

            $item = $this->sanitizingLine->find($id);
            $item->fill($item_info);
            $item->update_user = $user->getFullName();
            $item->save();
            return response()->json(['status' => true, 'id' => $item->id,
                'message' => [__('message.success.sanitizing_line.update')]
            ]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.sanitizing_line.update')] ]);
        }
    }

    /**
     * 無害化回線削除
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 回線存在チェック
        $item = $this->sanitizingLine->find($id);
        if(!$item){
            return response()->json(['status' => false,'message' => [__('message.warning.not_permission_access')]]);
        }

        // 回線使用チェック
        $company = DB::table('mst_company')->where('mst_sanitizing_line_id', $id)->first();
        if($company){
            return response()->json(['status' => false,'message' => [__('message.false.sanitizing_line.cannot_delete')]]);
        }

        try{
            // 無害化回線削除
            DB::table('mst_sanitizing_line')
                ->where('id',$id)
                ->delete();
            return response()->json(['status' => true, 'message' => [__('message.success.sanitizing_line.delete')]]);
        }catch(\Exception $e){
            Log::error($e->getMessage().$e->getTraceAsString());
            return response()->json(['status' => false, 'message' => [__('message.false.sanitizing_line.delete')] ]);
        }
    }
}
