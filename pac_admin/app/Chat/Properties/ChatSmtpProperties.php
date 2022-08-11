<?php
namespace App\Chat\Properties;

class ChatSmtpProperties extends AbstractProperties {

    const PROTOCOL = "SMTP_Protocol";
    
    /**
     * SMTPプロトコル
     * 
     * 'smtp' : 'smtp',
     * 'smtps' : 'smtps',
     */
    public function protocol(string $value = null) {
        return $this->_getset(self::PROTOCOL, func_get_args(), "smtp");
    }

    const HOST = "SMTP_Host";

    /**
     * SMTPホスト
     * 
     * @return string
     */
    public function host(string $value = null) {
        return $this->_getset(self::HOST, func_get_args());
    }

    const PORT = "SMTP_Port";
    
    /**
     * ポート
     * 
     * 
     * @param string $value
     * 
     */
    public function port(string $value = null) {
        return $this->_getset(self::PORT, func_get_args());
    }

    const IGNORE_TLS = "SMTP_IgnoreTLS";

    /**
     * TLSを無視するかどうか
     * 
     * @param bool $value
     */
    public function ignore_tls(bool $value = null) {
        return $this->_getset(self::IGNORE_TLS, func_get_args(), false);
    }


    const POOL = "SMTP_Pool";

    /**
     * プールするかどうか
     * 
     * @param bool $value
     */
    public function pool(bool $value = null) {
        return $this->_getset(self::POOL, func_get_args(), true);
    }


    const USERNAME = "SMTP_Username";

    /**
     * SMTPユーザー名
     * 
     * @param string $value
     */
    public function username(string $value = null) {
        return $this->_getset(self::USERNAME, func_get_args());
    }

    const PASSWORD = "SMTP_Password";

    /**
     * パスワード
     * 
     * @param string $value
     */
    public function password(string $value = null) {
        return $this->_getset(self::PASSWORD, func_get_args());
    }

    const FROM = "From_Email";

    /**
     * 送信元アドレス
     * 
     * @param string $value
     */
    public function from(string $value = null) {
        return $this->_getset(self::FROM, func_get_args());
    }
}
