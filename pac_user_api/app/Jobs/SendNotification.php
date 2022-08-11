<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Illuminate\Support\Carbon;
use FCM;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $dataNotify;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PushNotify $data)
    {
        $this->dataNotify = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $notificationBuilder = new PayloadNotificationBuilder($this->dataNotify->title);
            $notificationBuilder->setBody($this->dataNotify->body)
                                ->setBadge($this->dataNotify->badge)
                                ->setSound('default');

            $notification = $notificationBuilder->build();

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData([
                'body' => $this->dataNotify->body,
                'title' => $this->dataNotify->title,
                'type' => $this->dataNotify->type,
                'datetime' => Carbon::now()
            ]);
            $data = $dataBuilder->build();

            $topic = new Topics();
            $topic->topic(preg_replace('/[^A-Za-z0-9\.]/', '%', $this->dataNotify->email) . '_' . $this->dataNotify->env_flg . '_' . $this->dataNotify->server_flg);

            $topicResponse = FCM::sendToTopic($topic, null, $notification, $data);

            $topicResponse->isSuccess();
            $topicResponse->shouldRetry();
            $topicResponse->error();
        } catch (\Exception $e) {
            Log::error('SendNotificationエラー発生しました。' . $e->getMessage() . $e->getTraceAsString());
        }

    }
}
