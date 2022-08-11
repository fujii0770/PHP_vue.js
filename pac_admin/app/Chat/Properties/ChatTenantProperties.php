<?php
namespace App\Chat\Properties;

class ChatTenantProperties extends AbstractProperties {

    // /**
    //  * @return string
    //  */
    // public function mst_company_id(string $value = null) {
    //     return $this->_getset(__FUNCTION__, func_get_args());
    // }

    /**
     * @return string
     */
    public function chat_license_type(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function chat_license_begin(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function chat_lisense_end(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function group_name(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function server_name(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function admin_name(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function admin_email(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return ChatOrganizationProperties
     */
    public function organization(ChatOrganizationProperties $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return ChatServerProperties
     */
    public function server(ChatServerProperties $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }


    /*************** reserved **************/
    /**
     * @return string
     */
    public function cluster_arn(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function image(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function mongo_url(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function tenant_key(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function server_url(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }


    /**
     * @return int
     */
    public function company_id(int $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    // /**
    //  * @return string
    //  */
    // public function subdomain(string $value = null) {
    //     return $this->_getset(__FUNCTION__, func_get_args());
    // }


    /*******************************/

    public function status(int $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * サービスで実行するタスク定義のファミリーとリビジョン（family：revision）または完全なARN。
     * リビジョンが指定されていない場合は、最新のACTIVEリビジョンが使用されます。
     *
     * @return string
     */
    public function task_definition(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "");
    }

    /**
     * 登録したサービスのARN。
     */
    public function service_arn(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "");
    }

    /**
     *
     */
    public function admin_id(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "");
    }

    /**
     *
     */
    public function admin_token(string $value = null) {
        return $this->_getset(__FUNCTION__, func_get_args(), "");
    }


}
