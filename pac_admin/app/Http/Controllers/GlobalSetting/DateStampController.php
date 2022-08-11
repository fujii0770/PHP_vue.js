<?php

namespace App\Http\Controllers\GlobalSetting;

use App\Http\Utils\AppUtils;
use App\Http\Utils\IdAppApiUtils;
use App\Http\Utils\PermissionUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\AdminController; 
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\Company;

class DateStampController extends AdminController
{

    private $model;

    private $model_type;

    private $modelPermission;

    public function __construct(Company $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a setting for DateStamp
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = \Auth::user();
        $company = $this->model->find($user->mst_company_id);
        $this->assign('company', $company);
        
        $this->setMetaTitle("日付印設定");
        return $this->render('GlobalSetting.Settings.date');
    }
 
    /**
     * Store a DateStamp.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        $dstamp_style = $request->get('dstamp_style');
        $company = $this->model->find($user->mst_company_id);
        $company->dstamp_style = $dstamp_style;
        $company->save();

        return response()->json(['status' => true, 'message' => [__('message.success.save_date')]]);
    }
}