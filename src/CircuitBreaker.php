<?php

namespace Ksaveras\CircuitBreaker;

use Ksaveras\CircuitBreaker\Event\StateChangeEvent;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Ksaveras\CircuitBreaker\Storage\AbstractStorage;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CircuitBreaker
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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

    public function __construct(string $name, StorageInterface $storage, int $threshold = 5, float $resetPeriod = 60.0, float $ratio = 1.0)
    {
        $this->name = AbstractStorage::validateKey($name);
        $this->storage = $storage;

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
        return $this->getCircuit()->getState();
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
        $circuit = $this->getCircuit();
        $this->resetTimeout = $this->resetPeriod;
        $circuit->reset();
        $this->saveCircuit($circuit);

        $this->setState(State::CLOSED);
    }

    public function failure(): void
    {
        $circuit = $this->getCircuit();
        $circuit->increaseFailure();
        $this->saveCircuit($circuit);
    }

    private function updateState(): void
    {
        $state = State::CLOSED;

        if ($this->getCircuit()->getFailureCount() >= $this->failureThreshold) {
            if ((microtime(true) - $this->getCircuit()->getLastFailure()) > $this->resetTimeout) {
                $state = State::HALF_OPEN;
            } else {
                $state = State::OPEN;
            }
        }

        $this->setState($state);
    }

    private function setState(string $state): void
    {
        $circuit = $this->getCircuit();
        $currentState = $circuit->getState();

        if (State::OPEN === $currentState && State::HALF_OPEN === $state) {
            $this->resetTimeout *= $this->ratio;
        }

        if ($currentState !== $state && null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new StateChangeEvent($this, $currentState, $state));
        }

        $circuit->setState($state);
        $this->saveCircuit($circuit);
    }

    private function getCircuit(): Circuit
    {
        return $this->storage->getCircuit($this->name);
    }

    private function saveCircuit(Circuit $circuit): void
    {
        $this->storage->saveCircuit($circuit);
    }
}
