<?php
namespace App\Chat\Properties;

class ChatServerProperties extends AbstractProperties {

    const API_ID_SITE_NAME = "Site_Name";
    /**
     * サイト名
     * 
     * @return string
     */
    public function site_name(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    const API_ID_LANGUAGE = "Language";
    /**
     * 言語
     * 
     * "default"
     * "ja"
     * 
     * @return string
     */
    public function language(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "ja");
    }

    const API_ID_SERVER_TYPE = "Server_Type";
    /**
     * サーバーの種類
     * 
     * 'privateTeam' : 'Private_Team',
     * 'publicCommunity' : 'Public_Community',
     * 
     * @return string
     */
    public function server_type(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "privateTeam");
    }

    const API_ID_2FA_EMAIL_AUTO = "Accounts_TwoFactorAuthentication_By_Email_Auto_Opt_In";
    /**
     * 電子メールを介した2ファクタ認証の新規ユーザーの自動選択
     * 
     * @param boolean $value
     */
    public function acconts_2fa_email_auto(bool $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), false);
    }

    const API_ID_REGISTER_SERVER = "Register_Server";
    public function register_server() {
        return false;
    }
}
