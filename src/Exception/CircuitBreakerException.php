<?php

namespace Ksaveras\CircuitBreaker\Exception;

class CircuitBreakerException extends \RuntimeException
{
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function openCircuit(): self
    {
        return new self('Open circuit');
    }
}
