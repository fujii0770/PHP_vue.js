<?php
namespace App\Chat\Properties;


class ChatUserProperties extends AbstractProperties
{
    /**
     * チャットサーバー発行の管理ユーザーID
     * 
     * @return string
     */
    public function userId(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * データ
     * 
     * @return ChatUserDataProperties
     */
    public function data(ChatUserDataProperties $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * 
     */
    public function toArray() {
        $ret = [];
        $ret["userId"] = $this->userId;

        $rdata = [];
        $data = $this->data;
        if ($data !== null) {
            $rdata = $data->toArray();
        }
        $ret["data"] = $rdata;
        return $ret;
    }

}
