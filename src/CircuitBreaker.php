<?php

namespace Ksaveras\CircuitBreaker;

use Ksaveras\CircuitBreaker\Event\StateChangeEvent;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CircuitBreaker
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
     * @var float
     */
    private $resetTimeout;

    /**
     * @var int
     */
    private $failureThreshold;

    /**
     * @var float
     */
    private $resetPeriod;

    /**
     * @var float
     */
    private $ratio;

    /**
     * @var float|null
     */
    private $lastFailure;

    public function __construct(string $name, int $threshold = 5, float $resetPeriod = 60.0, float $ratio = 1.0)
    {
        $this->state = State::CLOSED;
        $this->name = $name;
        $this->failureThreshold = $threshold;
        $this->resetTimeout = $this->resetPeriod = $resetPeriod;
        $this->ratio = $ratio;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return mixed
     *
     * @throws \Throwable
     */
    public function call(\Closure $closure)
    {
        $this->updateState();

        switch ($this->getState()) {
            case State::OPEN:
                throw CircuitBreakerException::openCircuit();
            case State::CLOSED:
            case State::HALF_OPEN:
                try {
                    $result = $closure();
                    $this->success();

                    return $result;
                } catch (\Throwable $exception) {
                    $this->failure();

                    throw $exception;
                }
            default:
                throw new \LogicException('Unreachable code');
        }
    }

    public function isAvailable(): bool
    {
        $this->updateState();

        return \in_array($this->getState(), [State::CLOSED, State::HALF_OPEN], true);
    }

    public function success(): void
    {
        $this->failureCount = 0;
        $this->resetTimeout = $this->resetPeriod;
        $this->lastFailure = null;

        $this->setState(State::CLOSED);
    }

    public function failure(): void
    {
        ++$this->failureCount;
        $this->lastFailure = microtime(true);
    }

    private function updateState(): void
    {
        $state = State::CLOSED;

        if ($this->failureCount >= $this->failureThreshold) {
            if ((microtime(true) - $this->lastFailure) > $this->resetTimeout) {
                $state = State::HALF_OPEN;
            } else {
                $state = State::OPEN;
            }
        }

        if (State::OPEN === $this->state && State::HALF_OPEN === $state) {
            $this->resetTimeout *= $this->ratio;
        }

        $this->setState($state);
    }

    private function setState(string $state): void
    {
        if ($this->state !== $state && null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new StateChangeEvent($this, $this->state, $state));
        }

        $this->state = $state;
    }
}
