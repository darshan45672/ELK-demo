<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter;

class JsonLogFormatter
{
    /**
     * Customize the given logger instance to output JSON format.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter(
                JsonFormatter::BATCH_MODE_NEWLINES,
                true,  // appendNewline
                false, // ignoreEmptyContextAndExtra
                true   // includeStacktraces
            ));
        }
    }
}
