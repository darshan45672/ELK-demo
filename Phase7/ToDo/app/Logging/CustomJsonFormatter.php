<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class CustomJsonFormatter extends JsonFormatter
{
    /**
     * Format the log record into a clean JSON structure for ELK
     *
     * @param LogRecord $record
     * @return string
     */
    public function format(LogRecord $record): string
    {
        $normalized = [
            'timestamp' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
            'level' => $record->level->getName(),
            'level_name' => $record->level->getName(),
            'channel' => $record->channel,
            'message' => $record->message,
            'context' => $record->context,
            'extra' => $record->extra,
        ];

        // Add exception details if present
        if (isset($record->context['exception']) && $record->context['exception'] instanceof \Throwable) {
            $exception = $record->context['exception'];
            $normalized['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
            unset($normalized['context']['exception']);
        }

        // Remove empty arrays to keep the JSON clean
        $normalized = array_filter($normalized, function ($value) {
            return !is_array($value) || !empty($value);
        });

        return $this->toJson($normalized) . "\n";
    }
}
