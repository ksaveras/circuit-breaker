<?php

namespace Ksaveras\CircuitBreaker\Exception;

/**
 * Class OpenCircuitException.
 */
class OpenCircuitException extends CircuitBreakerException
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $message = $message ?? 'Open Circuit';

        parent::__construct($message, $code, $previous);
    }
}
