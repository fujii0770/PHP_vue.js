<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;/**
 * Class CompanyRepository
 * @package App\Repositories
 * @version November 12, 2019, 3:45 am UTC
*/

class CompanyRepository
{
    /** Get Company information from same environment
     * @param $mapSameEnvCompanies mapSameEnvCompanies[mst_company_id, mst_company_id, ...] 
     * @return mixed $mapSameEnvCompanies mapSameEnvCompanies[mst_company_id => company, mst_company_id => company, ...] 
     */
    public function getSameEnvCompanies($mapSameEnvCompanies){
        if (count($mapSameEnvCompanies)){
            $mstCompanies = DB::table('mst_company')
                ->select('id', 'login_type', 'url_domain_id')
                ->whereIn('id', array_keys($mapSameEnvCompanies))
                ->get();
            foreach ($mstCompanies as $mstCompany){
                $mapSameEnvCompanies[$mstCompany->id] = $mstCompany;
            }
        }
        return $mapSameEnvCompanies;
    }
}
