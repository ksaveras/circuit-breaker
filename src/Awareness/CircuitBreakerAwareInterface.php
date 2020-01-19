<?php

namespace Ksaveras\CircuitBreaker\Awareness;

use Ksaveras\CircuitBreaker\CircuitBreaker;

/**
 * Interface CircuitBreakerAwareInterface.
 */
interface CircuitBreakerAwareInterface
{
    public function getCircuitBreaker(): CircuitBreaker;
}
