<?php
/**
 * Created by PhpStorm.
 * User: dongnv
 * Date: 11/13/19
 * Time: 14:45
 */

namespace App\Http\Utils;


class TemplateUtils
{

    const COMPANY_ACCESS_TYPE = 0;
    const DEPARTMENT_ACCESS_TYPE = 1;
    const INDIVIDUAL_ACCESS_TYPE = 2;

    const NUMERIC_TYPE = 1;
    const STRING_TYPE = 2;
    const DATE_TYPE = 0;

    const CREATE_APP = 1;
    const CREATE_ADMIN = 2;

    const RECEIVE_FLG = 1;
    const SEND_FLG = 2;
    const AUTH_FLG = 1;

    /**
     * 一時ファイルパス
     * @param $mst_company_id
     * @param $mst_user_id
     * @return string
     */
    public static function localTemplatePath($mst_company_id, $mst_user_id): string
    {
        return 'template/' .config('app.server_env') . '/' . config('app.edition_flg')
            . '/' . config('app.server_flg'). '/' . $mst_company_id . '/' . $mst_user_id . '/';
    }
}