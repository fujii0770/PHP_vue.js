<?php
namespace App\Chat\Properties;


class ChatUserDataProperties extends AbstractProperties
{
    /**
     * メールアドレス
     *
     * @return string
     */
    public function email(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * 氏名
     *
     * @return string
     */
    public function name(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * パスワード
     *
     * @return string
     */
    public function password(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * ユーザー名（アカウント名）
     *
     * @return string
     */
    public function username(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }


    /**
     * ユーザーがアクティブかどうか
     *
     * @return string
     */
    public function active(bool $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), true);
    }


    /**
     * ロール
     *
     * @return string
     */
    public function roles(array $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), ["user"]);
    }

    /**
     * ユーザー作成時にデフォルトチャンネルに自動で参加するかどうか
     *
     * @return string
     */
    public function joinDefaultChannels(bool $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), true);
    }


    /**
     * 管理者がパスワードを設定した後に本人によるパスワードの変更を強制するかどうか
     *
     * @return string
     */
    public function requirePasswordChange(bool $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), true);
    }

    /**
     * ユーザー作成時にウェルカムメールを送信するかどうか
     *
     * @return string
     */
    public function sendWelcomeEmail(bool $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), false);
    }

    /**
     * Should the user's email address be verified when created?
     *
     * @return string
     */
    public function verified(bool $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), false);
    }


    /**
     * Set random password and send by email
     */
    public function setRandomPassword(bool $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), true);
    }
}
