<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMfaMail extends Mailable
{
    use Queueable, SerializesModels;

    private $data;
    private $isPlain;

    /**
     * Create a new message instance.
     *
     * @return void
     */
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

        // SESの送信履歴をS3に保存する処理を追加
        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', 'SES_Event');
            $message->getHeaders()->addTextHeader('X-SES-MESSAGE-TAGS', 'campaign=book');
        });
        if (!isset($this->data['company_name'])){
            $this->data['company_name'] = '';
        }
        if (!isset($this->data['user_id'])){
            $this->data['user_id'] = '';
        }
        if($this->isPlain){
            return $this->subject($this->data['mail_subject'])
                ->text('email_plain_template.SendMfaMail',$this->data);
        } else {
            return $this->subject($this->data['mail_subject'])
                ->view('email_template.SendMfaMail',$this->data);
        }
    }
}
