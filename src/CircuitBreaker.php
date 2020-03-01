<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker;

use Ksaveras\CircuitBreaker\Event\StateChangeEvent;
use Ksaveras\CircuitBreaker\Exception\OpenCircuitException;
use Ksaveras\CircuitBreaker\Storage\AbstractStorage;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

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
     * @var int
     */
    private $resetTimeout;

    /**
     * @var int
     */
    private $failureThreshold = 5;

    /**
     * @var int
     */
    private $resetPeriod;

    /**
     * @var float
     */
    private $ratio;

    /**
     * @var Circuit|null
     */
    private $circuit;

    public function __construct(string $name, StorageInterface $storage, int $resetPeriod = 60, float $ratio = 1.0)
    {
        $this->name = AbstractStorage::validateKey($name);
        $this->storage = $storage;

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

    public function setFailureThreshold(int $failureThreshold): self
    {
        $this->failureThreshold = $failureThreshold;

        return $this;
    }

    public function getCircuit(): Circuit
    {
        if (null === $this->circuit) {
            $this->circuit = $this->storage->getCircuit($this->name);
        }

        return $this->circuit;
    }

    /**
     * @return mixed
     *
     * @throws \Throwable
     */
    public function call(\Closure $closure)
    {
        $state = $this->updateState();

        switch ($state) {
            case State::OPEN:
                throw new OpenCircuitException();
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
                throw new \LogicException(sprintf('Unsupported Circuit state "%s"', $state));
        }
    }

    public function isAvailable(): bool
    {
        $state = $this->updateState();

        return \in_array($state, [State::CLOSED, State::HALF_OPEN], true);
    }

    public function success(): void
    {
        $state = $this->getCircuit()->getState();
        $this->storage->resetCircuit($this->name);

        if (State::CLOSED !== $state && null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new StateChangeEvent($this, $state, State::CLOSED));
        }

        $this->circuit = null;
    }

    public function failure(): void
    {
        $this->storage->increaseFailure($this->name);
        $this->circuit = null;
    }

    private function updateState(): string
    {
        $state = State::CLOSED;

        if ($this->getCircuit()->getFailureCount() >= $this->failureThreshold) {
            if ((time() - $this->getCircuit()->getLastFailure()) > $this->resetTimeout) {
                $state = State::HALF_OPEN;
            } else {
                $state = State::OPEN;
            }
        }

        $this->setState($state);

        return $state;
    }

    private function setState(string $state): void
    {
        $circuit = $this->getCircuit();
        $currentState = $circuit->getState();

        if (State::OPEN === $currentState && State::HALF_OPEN === $state) {
            $this->resetTimeout = (int) ceil($this->resetTimeout * $this->ratio);
        }

        $circuit->setState($state);

        if ($currentState !== $state && null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new StateChangeEvent($this, $currentState, $state));
        }

        $this->saveCircuit($circuit);
    }

    private function saveCircuit(Circuit $circuit): void
    {
        $this->storage->saveCircuit($circuit);
    }
}
