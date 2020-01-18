<?php

namespace Ksaveras\CircuitBreaker;

use Ksaveras\CircuitBreaker\Event\StateChangeEvent;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CircuitBreaker
{
    /**
     * @var CacheInterface
     */
    private $cacheAdapter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $name;

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
     * @var Circuit
     */
    private $circuit;

    public function __construct(string $name, int $threshold = 5, float $resetPeriod = 60.0, float $ratio = 1.0)
    {
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
        return $this->getCircuit()->getState();
    }

    public function getCacheAdapter(): CacheInterface
    {
        if (null === $this->cacheAdapter) {
            $this->cacheAdapter = new NullAdapter();
        }

        return $this->cacheAdapter;
    }

    public function setCacheAdapter(CacheInterface $cacheAdapter): self
    {
        $this->cacheAdapter = $cacheAdapter;

        return $this;
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
        $this->resetTimeout = $this->resetPeriod;
        $this->getCircuit()->reset();
        $this->setState(State::CLOSED);
    }

    public function failure(): void
    {
        $this->getCircuit()->increaseFailure();
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
        $currentState = $this->getCircuit()->getState();

        if (State::OPEN === $currentState && State::HALF_OPEN === $state) {
            $this->resetTimeout *= $this->ratio;
        }

        if ($currentState !== $state && null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new StateChangeEvent($this, $currentState, $state));
        }

        $this->getCircuit()->setState($state);
    }

    private function getCircuit(): Circuit
    {
        if (null === $this->circuit) {
            $this->loadCircuit();
        }

        return $this->circuit;
    }

    private function loadCircuit(): void
    {
        $key = Circuit::cacheKey($this->name);

        $this->circuit = $this->getCacheAdapter()->get(
            $key,
            static function () {
                return (new Circuit())
                    ->setState(State::CLOSED);
            },
            0
        );
    }
}
