<?php

namespace Ksaveras\CircuitBreaker;

/**
 * Class Circuit.
 */
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
     * @var float|null
     */
    private $resetTimeout;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
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

    public function getResetTimeout(): ?float
    {
        return $this->resetTimeout;
    }

    public function setResetTimeout(?float $resetTimeout): self
    {
        $this->resetTimeout = $resetTimeout;

        return $this;
    }

    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailure = null;
        $this->resetTimeout = null;
    }
}
