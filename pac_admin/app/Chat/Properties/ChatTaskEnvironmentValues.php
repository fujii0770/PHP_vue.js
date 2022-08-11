<?php
namespace App\Chat\Properties;


class ChatTaskEnvironmentValues extends AbstractProperties {



    /**
     * @return string
     */
    public function mongo_oplog_url(string $value = null) {
        return $this->_getset("MONGO_OPLOG_URL", func_get_args());
    }

    /**
     * @return string
     */
    public function mongo_url(string $value = null) {
        return $this->_getset("MONGO_URL", func_get_args());
    }

    /**
     * @return string
     */
    public function root_url(string $value = null) {
        return $this->_getset("ROOT_URL", func_get_args());
    }

    /**
     * @return string
     */
    public function virtual_host(string $value = null) {
        return $this->_getset("VIRTUAL_HOST", func_get_args());
    }

    /**
     * @return string
     */
    public function virtual_port(string $value = null) {
        return $this->_getset("VIRTUAL_PORT", func_get_args());
    }

    /**
     * @return string
     */
    public function admin_email(string $value = null) {
        return $this->_getset("ADMIN_EMAIL", func_get_args());
    }


    /**
     * @return string
     */
    public function admin_password(string $value = null) {
        return $this->_getset("ADMIN_PASS", func_get_args());
    }


    /**
     * @return string
     */
    public function admin_username(string $value = null) {
        return $this->_getset("ADMIN_USERNAME", func_get_args());
    }


    public function opening_callback_url(string $value = null) {
        return $this->_getset("OPENING_CALLBACK_URL", func_get_args());
    }

    public function fileupload_s3_bucket(string $value = null) {
        return $this->_getset("FileUpload_S3_Bucket", func_get_args());
    }

    public function timezone(string $value = null) {
        return $this->_getset("TZ", func_get_args());
    }

    public function fileupload_storage_type(string $value = null) {
        return $this->_getset("FileUpload_Storage_Type", func_get_args());
    }

    public function fileupload_s3_awsaccesskeyid(string $value = null) {
        return $this->_getset("FileUpload_S3_AWSAccessKeyId", func_get_args());
    }

    public function fileupload_s3_awssecretaccesskey(string $value = null) {
        return $this->_getset("FileUpload_S3_AWSSecretAccessKey", func_get_args());
    }

    public function fileupload_s3_region(string $value = null) {
        return $this->_getset("FileUpload_S3_Region", func_get_args());
    }

}
