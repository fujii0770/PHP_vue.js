<?php

namespace App\Chat\Properties;


class ChatTaskRegisterProperties extends AbstractProperties
{

    /**
     * @return ChatTaskEnvironmentValues
     */
    public function environments(ChatTaskEnvironmentValues $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }


    /**
     * @return string
     */
    public function image(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function container_name(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function awslog_driver(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function awslogs_group(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function awslogs_region(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function awslogs_stream_prefix(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return int
     */
    public function memory_reservation(int $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args(), 500);
    }

    /**
     * @return string
     */
    public function execution_role_arn(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function task_role_arn(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function family(string $value = null)
    {
        return $this->_getset(__FUNCTION__, func_get_args());
    }
}
