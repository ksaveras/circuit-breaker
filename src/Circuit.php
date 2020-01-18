<?php

namespace Ksaveras\CircuitBreaker;

class Circuit
{
    public const CACHE_PREFIX = 'CircuitBreaker';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $state;

    /**
     * @var int
     */
    private $failureCount = 0;

    /**
     * @var float|null
     */
    private $lastFailure;

    public static function cacheKey(string $name): string
    {
        return static::CACHE_PREFIX.'|'.md5($name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Circuit
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
