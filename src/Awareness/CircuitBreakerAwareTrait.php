<?php

namespace Ksaveras\CircuitBreaker\Awareness;

use Ksaveras\CircuitBreaker\CircuitBreaker;

/**
 * Trait CircuitBreakerAwareTrait.
 */
trait CircuitBreakerAwareTrait
{
    /**
     * @var CircuitBreaker
     */
    private $circuitBreaker;

    public function getCircuitBreaker(): CircuitBreaker
    {
        if (null === $this->circuitBreaker) {
            throw new \RuntimeException('CircuitBreaker is not set');
        }

        return $this->circuitBreaker;
    }

    public function setCircuitBreaker(CircuitBreaker $circuitBreaker): self
    {
        $this->circuitBreaker = $circuitBreaker;

        return $this;
    }
}
