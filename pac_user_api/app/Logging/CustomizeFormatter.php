<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        $format = "[%datetime%] %channel%.%level_name%: %message% [%user_agent%] %context% %extra%\n";
        $dateFormat = 'Y-m-d H:i:s';
        $lineFormatter = new LineFormatter($format, $dateFormat, true, true);
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(new IntrospectionProcessor(Logger::DEBUG, ["Illuminate\\"]));
            if ($handler->getLevel() == Logger::ERROR){
                $handler->setFormatter($lineFormatter);
                $handler->pushProcessor([$this,'addExtraFields']);
            }
        }
    }

    public function addExtraFields(array $record): array
    {
        $record['user_agent'] = isset($_SERVER) && isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return $record;
    }
}