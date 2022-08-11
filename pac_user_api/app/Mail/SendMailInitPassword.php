<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMailInitPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    private $data;
    private $isPlain;

    public function __construct($data, $isPlain = false)
    {
        $this->data = $data;
	    $this->isPlain = $isPlain;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        foreach ($this->to as $k => $to){
            if (isset($to['address'])){
                $this->to[$k]['address'] = trim($to['address']);
            }
        }
        if($this->isPlain){
                if($this->data['account_type'] == 'user' || $this->data['account_type'] == 'option') {
                    $view = 'email_plain_template.email_reset_link_user';
                } else if($this->data['account_type'] == 'simple_user'){
                    $view = 'email_plain_template.email_reset_link_simple_user';
                } else if($this->data['account_type'] == 'audit'){
                    $view = 'email_plain_template.email_reset_link_audit';
                } else {
                    $view = 'email_plain_template.email_reset_link_admin';
                }
        }
        else{
                if($this->data['account_type'] == 'user'|| $this->data['account_type'] == 'option') {
                    $view = 'email_template.email_reset_link_user';
                } else if($this->data['account_type'] == 'simple_user'){
                    $view = 'email_template.email_reset_link_simple_user';
                } else if($this->data['account_type'] == 'audit'){
                    $view = 'email_template.email_reset_link_audit';
                } else {
                    $view = 'email_template.email_reset_link_admin';
                }
        }

        // SESの送信履歴をS3に保存する処理を追加
        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', 'SES_Event');
            $message->getHeaders()->addTextHeader('X-SES-MESSAGE-TAGS', 'campaign=book');
        });

        if($this->isPlain){
            return $this->from(config('mail.from.address'),config('mail.from.name'))
                ->subject($this->data['mail_subject'])
                ->text($view, $this->data);
        } else {
            return $this->from(config('mail.from.address'),config('mail.from.name'))
                ->subject($this->data['mail_subject'])
                ->view($view, $this->data);
        }
    }
}
