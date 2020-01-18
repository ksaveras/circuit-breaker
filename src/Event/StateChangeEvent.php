<?php

namespace Ksaveras\CircuitBreaker\Event;

use Ksaveras\CircuitBreaker\CircuitBreaker;
use Symfony\Contracts\EventDispatcher\Event;

class StateChangeEvent extends Event
{
    private $circuitBreaker;

    private $oldState;

    private $newState;

    public function __construct(CircuitBreaker $circuitBreaker, string $oldState, string $newState)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->oldState = $oldState;
        $this->newState = $newState;
    }

    public function getCircuitBreaker(): CircuitBreaker
    {
        return $this->circuitBreaker;
    }

    public function getName(): string
    {
        return $this->circuitBreaker->getName();
    }

    public function getOldState(): string
    {
        return $this->oldState;
    }

    public function getNewState(): string
    {
        return $this->newState;
    }
}
