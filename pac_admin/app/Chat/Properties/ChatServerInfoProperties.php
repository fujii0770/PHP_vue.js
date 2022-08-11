<?php

namespace App\Chat\Properties;

use DateTime;

class ChatServerInfoProperties extends AbstractProperties
{

    public function id (int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     *
     */
    public function company_id(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function company_name(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     *
     */
    public function server_name(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     *
     */
    public function tenant_key(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     *
     */
    public function server_url(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     *
     */
    public function onetime_key(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /* -------------------- */

    public function admin_username(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function admin_password(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function status(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function admin_id(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function admin_token(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function operation_user(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /* ---*/

    public function user_count(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function user_count_detail(array $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function is_contract(bool $flag = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function is_trial(bool $flag = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function trial_start_date(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function trial_end_date(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function contract_start_date(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function contract_end_date(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function user_max(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function storage_max_mega(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function plan(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function create_at(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function create_user(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function update_at(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function update_user(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function service_status(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function service_status_at(DateTime $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function version(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    public function callback_url(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }
}
