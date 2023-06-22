<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker;

use Ksaveras\CircuitBreaker\Exception\OpenCircuitException;
use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;

final class CircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(
        private readonly string $name,
        private readonly int $failureThreshold,
        private readonly RetryPolicyInterface $retryPolicy,
        private readonly StorageInterface $storage
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function state(): State
    {
        return $this->getState($this->getCircuit());
    }

    public function remainingDelay(): float
    {
        $circuit = $this->getCircuit();

        return match ($this->getState($circuit)) {
            State::CLOSED => 0.0,
            default => $circuit->getExpirationTime() - microtime(true),
        };
    }

    public function getFailureCount(): int
    {
        return $this->getCircuit()->getFailureCount();
    }

    public function isAvailable(): bool
    {
        return match ($this->state()) {
            State::CLOSED,
            State::HALF_OPEN => true,
            default => false,
        };
    }

    public function isClosed(): bool
    {
        return $this->state() === State::CLOSED;
    }

    public function isHalfOpen(): bool
    {
        return $this->state() === State::HALF_OPEN;
    }

    public function isOpen(): bool
    {
        return $this->state() === State::OPEN;
    }

    /**
     * @throws \Throwable
     */
    public function call(callable $closure): mixed
    {
        $circuit = $this->getCircuit();

        switch ($this->getState($circuit)) {
            case State::CLOSED:
            case State::HALF_OPEN:
                try {
                    $result = $closure();
                    if (0 !== $circuit->getFailureCount()) {
                        $this->recordSuccess();
                    }

                    return $result;
                } catch (\Throwable $throwable) {
                    $this->recordFailure();

                    throw $throwable;
                }
            default:
                throw new OpenCircuitException();
        }
    }

    public function recordSuccess(): void
    {
        $this->storage->delete($this->name);
    }

    public function recordFailure(): void
    {
        $circuit = $this->getCircuit();
        $circuit->increaseFailure($this->retryPolicy);
        $this->storage->save($circuit);
    }

    private function getState(Circuit $circuit): State
    {
        if ($circuit->thresholdReached()) {
            return State::CLOSED;
        }

        return (microtime(true) - ($circuit->getLastFailure() ?? 0.0)) > $circuit->getResetTimeout() ? State::HALF_OPEN : State::OPEN;
    }

    private function getCircuit(): Circuit
    {
        if (null === $circuit = $this->storage->fetch($this->name)) {
            return new Circuit($this->name, $this->failureThreshold, $this->retryPolicy->initialDelay());
        }

        return $circuit;
    }
}
