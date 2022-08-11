<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AdminController;
use Auth;
use App\Models\Protection;

class   ProtectionController extends AdminController
{
    private $model;

    public function __construct(Protection $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $user = \Auth::user();
        $protection = $this->model->where('mst_company_id', $user->mst_company_id)->first();
        $limit = DB::table('mst_limit')->select('text_append_flg')
            ->where('mst_company_id', $user->mst_company_id)->first();
        $company = DB::table('mst_company')->select('enable_email_thumbnail')
            ->where('id', $user->mst_company_id)->first();
        $this->assign('limit', $limit);
        $this->assign('company', $company);
        $this->assign('protection', $protection);
        $this->setMetaTitle("保護設定");
        return $this->render('GlobalSetting.Settings.Protection');
    }

    /** save_protection
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $setting = $request->all();
        $protection = $this->model->where('mst_company_id', $user->mst_company_id)->first();

        if (!$protection) {
            $protection = new $this->model;
            $protection->mst_company_id = $user->mst_company_id;
            $protection->create_user = $user->getFullName();
        } else {
            $protection->update_user = $user->getFullName();
        }
        $protection->fill($setting);
        $protection->save();

        return response()->json(['status' => true, 'message' => [__('message.success.save_protection')]]);
    }
}
