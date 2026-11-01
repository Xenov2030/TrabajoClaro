<?php

namespace App\Logging;

use Monolog\LogRecord;

final class SafeLog
{
    public function __invoke($logger): void
    {
        $logger->pushProcessor(function (LogRecord $record): LogRecord {
            $context = $record->context;
            if (($context['exception'] ?? null) instanceof \Throwable) {
                $e = $context['exception'];
                $context = ['exception_type' => get_class($e), 'code' => (string) $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()];

                return $record->with(message: 'Error de aplicación. Revisar tipo y ubicación.', context: $context);
            }

            return $record->with(message: $this->clean($record->message), context: $this->clean($context));
        });
    }

    private function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $v) {
                $value[$key] = preg_match('/password|secret|token|authorization|cookie/i', (string) $key) ? '[oculto]' : $this->clean($v);
            }

            return $value;
        }
        if ($value instanceof \Throwable) {
            return ['type' => get_class($value), 'code' => (string) $value->getCode()];
        }
        if (is_string($value)) {
            return preg_replace('#/p/[a-f0-9]{64}#', '/p/[privado]', $value);
        }

        return $value;
    }
}
