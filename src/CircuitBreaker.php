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
use Ksaveras\CircuitBreaker\HeaderPolicy\HttpHeaderPolicy;
use Ksaveras\CircuitBreaker\HeaderPolicy\PolicyChain;
use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class CircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(
        private string $name,
        private int $failureThreshold,
        private RetryPolicyInterface $retryPolicy,
        private StorageInterface $storage,
        private HttpHeaderPolicy $headerPolicy = new PolicyChain([]),
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
        return State::CLOSED === $this->state();
    }

    public function isHalfOpen(): bool
    {
        return State::HALF_OPEN === $this->state();
    }

    public function isOpen(): bool
    {
        return State::OPEN === $this->state();
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

    public function recordRequestFailure(ResponseInterface $response): void
    {
        if (null === $resetDateTime = $this->headerPolicy->fromResponse($response)) {
            $this->recordFailure();

            return;
        }

        $circuit = new Circuit(
            $this->name,
            1,
            $resetDateTime->getTimestamp() - time(),
            1,
            microtime(true),
        );

        $this->storage->save($circuit);
    }

    private function getState(Circuit $circuit): State
    {
        if (!$circuit->thresholdReached()) {
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
