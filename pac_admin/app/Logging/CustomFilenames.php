<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;

class CustomFilenames
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        $user = \Auth::user();
        $userID = $user?$user->id:0;
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof RotatingFileHandler) {
                $handler->setFilenameFormat("{filename}-$userID-{date}", 'Y-m-d');
            }
        }
    }
}