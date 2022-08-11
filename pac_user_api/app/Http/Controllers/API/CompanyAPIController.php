<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\UpdateUserInfoAPIRequest;
use App\Http\Utils\AppUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Response;

/**
 * Class CompanyAPIController
 * @package App\Http\Controllers\API
 */

class CompanyAPIController extends AppBaseController
{
    /**
     * Display the specified Company.
     * GET|HEAD /getCompany/{company_id}
     *
     * @param int $company_id
     *
     * @return Response
     */
    public function getCompany($company_id)
    {
        $company = DB::table('mst_company')->where('id', $company_id)->first();

        return $this->sendResponse($company, '');
    }

    /**
     * Display the specified company by url_domain_id.
     * GET|HEAD /getCompanyByDomain/{url_domain_id}
     *
     * @param string $url_domain_id
     *
     * @return Response
     */
    public function getCompanyByDomain($url_domain_id) {
        $company = DB::table('mst_company')->leftJoin('branding', 'branding.mst_company_id', '=', 'mst_company.id')
            ->where('mst_company.url_domain_id', $url_domain_id)
            ->where('mst_company.state', AppUtils::STATE_VALID)
            ->select('mst_company.*', 'branding.background_color', 'branding.logo_file_data', 'branding.color')
            ->first();

        return $this->sendResponse($company, '');
    }

    /**
     * Display the specified Timestamp.
     * GET|HEAD /getTimestamp/{company_id}
     *
     * @param int $company_id
     *
     * @return Response
     */
    public function getTimestamp($company_id)
    {
        $timestamp = DB::table('mst_limit')->where('mst_company_id', $company_id)->first();

        return $this->sendResponse($timestamp, '');
    }

    /**
     * Display the specified Timestamps.
     * GET|HEAD /getTimestamps
     *
     * @return Response
     */
    public function getTimestamps(Request $request)
    {
        $timestamps = [];

        if (isset($request['ids']) && $request['ids']){
            $ids = explode(',', $request['ids'] );

            $timestamps = DB::table('mst_limit')
                ->join('mst_company', 'mst_limit.mst_company_id', 'mst_company.id')
                ->select(['mst_limit.*', 'mst_company.time_stamp_issuing_count', 'mst_company.stamp_flg'])
                ->whereIn('mst_limit.mst_company_id', $ids)->get();
        }

        return $this->sendResponse($timestamps, '');
    }

    /**
     * Display the specified Companies.
     * GET|HEAD /getCompanies?ids={company_id}
     *
     * @param ids
     *
     * @return Response
     */
    public function getCompanies(Request $request)
    {
        $companies = [];
        
        if (isset($request['ids']) && $request['ids']){
            $ids = explode(',', $request['ids'] );

            $companies = DB::table('mst_company')->whereIn('id', $ids)->get();
        }

        return $this->sendResponse($companies, '');
    }
}
