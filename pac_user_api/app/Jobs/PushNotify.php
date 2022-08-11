<?php
namespace App\Jobs;

class PushNotify
{
    public $title;
    public $body;
    public $email;
    public $type;
    public $env_flg;
    public $server_flg;
    public $badge;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct($title, $body, $email, $env_flg, $server_flg, $type, $badge)
    {
        $this->title = $title;
        $this->body = $body;
        $this->email = $email;
        $this->type = $type;
        $this->env_flg = $env_flg;
        $this->server_flg = $server_flg;
        $this->badge = $badge;
    }
}
