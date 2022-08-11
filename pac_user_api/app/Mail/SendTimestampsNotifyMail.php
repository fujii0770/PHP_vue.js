<?php

namespace App\Mail;

use App\Http\Utils\MailUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTimestampsNotifyMail extends Mailable
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
    
        // SESの送信履歴をS3に保存する処理を追加
        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', 'SES_Event');
            $message->getHeaders()->addTextHeader('X-SES-MESSAGE-TAGS', 'campaign=book');
        });
    
        $codes = array_column(MailUtils::MAIL_DICTIONARY,'CODE');
        $arr = array_values(MailUtils::MAIL_DICTIONARY);
        if($this->isPlain){
            return $this->from(config('mail.from.address'),config('mail.from.name'))
                ->subject($this->data['mail_subject'])
                ->text(str_replace('email_template', 'email_plain_template', $arr[array_search($this->data['mail_code'], $codes)]['TEMPLATE']), $this->data);
        } else {
            return $this->from(config('mail.from.address'),config('mail.from.name'))
                ->subject($this->data['mail_subject'])
                ->view($arr[array_search($this->data['mail_code'], $codes)]['TEMPLATE'], $this->data);
        }
    }
}
