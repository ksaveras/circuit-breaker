<?php

namespace Ksaveras\CircuitBreaker;

class Circuit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $state = State::CLOSED;

    /**
     * @var int
     */
    private $failureCount = 0;

    /**
     * @var float|null
     */
    private $lastFailure;

    /**
     * Circuit constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     *
     * @return Circuit
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getLastFailure(): ?float
    {
        return $this->lastFailure;
    }

    public function increaseFailure(): void
    {
        ++$this->failureCount;
        $this->lastFailure = microtime(true);
    }

    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailure = null;
    }
}
