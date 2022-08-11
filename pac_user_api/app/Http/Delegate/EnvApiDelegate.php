<?php

/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 10/3/19
 * Time: 10:22
 */

namespace App\Http\Delegate;

use App\Http\Utils\EnvApiUtils;
use Illuminate\Support\Facades\Log;

class EnvApiDelegate
{
    /** Get Company information from other environment
     * @param $mapOtherEnvCompanies mapOtherEnvCompanies[env_flg][server_flg][mst_company_id, mst_company_id, .....]
     * @return mixed mapOtherEnvCompanies[env_flg][server_flg][mst_company_id => company, mst_company_id => company, .....]
     */
    public static function getOtherEnvCompanies($mapOtherEnvCompanies){
        if (count($mapOtherEnvCompanies)){
            foreach ($mapOtherEnvCompanies as $envFlg => $servers){
                foreach ($servers as $serverFlg => $companyIds){
                    if (count($companyIds)){
                        $client = EnvApiUtils::getAuthorizeClient($envFlg, $serverFlg);
                        
                        if ($client){    
                            $response = $client->get("getCompanies?ids=".implode(',', array_keys($companyIds)),[]);
    
                            if ($response->getStatusCode() != \Illuminate\Http\Response::HTTP_OK){
                                Log::warning('Cannot get Companies. Response Body '. $response->getBody());
                            }else{
                                $mstCompanies = json_decode((string)$response->getBody())->data;
                                foreach ($mstCompanies as $mstCompany){
                                    $mapOtherEnvCompanies[$envFlg][$serverFlg][$mstCompany->id] = $mstCompany;
                                }
                            }
                        }else{
                            Log::warning('Cannot connect to Env Api to get Companies');
                        }
                    }
                }
            }
        }
        return $mapOtherEnvCompanies;
    }
}