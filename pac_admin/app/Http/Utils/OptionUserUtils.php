<?php


namespace App\Http\Utils;


class OptionUserUtils
{
    const OPTION_USER_DOMAINS = ['inc','com','net','co','work','xyz','org','biz','info','site','online','tech','cloud','art','design','jp','co.jp','ne.jp','gr.jp','or.jp','ac.jp','ed.jp','go.jp','ad.jp','lg.jp'];

    /**
     * ドメイン名を変更する
     * @param $company_domains array 会社の登録できるドメイン
     * @param $replace_word string example('gw')
     * @return array
     */
    public static function replaceDomains($company_domains,$replace_word): array
    {
        $email_domain_company = [];
        $domains = [];

        foreach ($company_domains as $domain){
            $old_domain = ltrim($domain,"@");
            $sub_domain = $old_domain;
            $index = strpos($sub_domain, '.') + 1;
            while(true){
                if (strpos($sub_domain, '.')){
                    $sub_domain = substr($old_domain, $index);
                    if (in_array($sub_domain,self::OPTION_USER_DOMAINS)){
                        $suffix_domain = substr($old_domain,0,$index) . $replace_word;
                        $email_domain_company['@'.$suffix_domain] = $suffix_domain;
                        $domains[] = '@'.$suffix_domain;
                        break;
                    }else{
                        $i = strpos($sub_domain, '.') + 1;
                        $index += $i;
                        continue;
                    }
                }else{
                    $suffix_domain = substr($old_domain,0,strpos($old_domain,'.')) . '.' . $replace_word;
                    $upd_domain = '@' . $suffix_domain;
                    $email_domain_company[$upd_domain] = $suffix_domain;
                    $domains[] = $upd_domain;
                    break;
                }
            }
        }
        return [$email_domain_company, $domains];
    }

    /**
     * ドメイン名を変更する
     * @param $email
     * @param $replace_word string example(gw,wf)
     * @return string
     */
    public static function replaceEmail($email,$replace_word): string
    {
        $old_domain = substr($email, strrpos($email,'@') + 1);
        $domain = $old_domain;
        $index = strpos($domain, '.') + 1;
        while (true){
            if (strpos($domain, '.')){
                $domain = substr($old_domain, $index);
                if (in_array($domain, self::OPTION_USER_DOMAINS)){
                    return str_replace($domain, $replace_word ,$email);
                }else{
                    $i = strpos($domain, '.') + 1;
                    $index += $i;
                    continue;
                }
            }else{
                return substr($email, 0, strrpos($email,'@') + 1) .
                    substr($old_domain,0,strpos($old_domain,'.')) . '.' . $replace_word;
            }
        }
    }

}