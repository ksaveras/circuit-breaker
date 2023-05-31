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

    public function getState(): State
    {
        return $this->getCircuit()->getState();
    }

    /**
     * @throws \Throwable
     */
    public function call(callable $closure): mixed
    {
        $circuit = $this->getCircuit();
        $state = $circuit->getState();

        switch ($state) {
            case State::CLOSED:
            case State::HALF_OPEN:
                try {
                    $result = $closure();
                    if (0 !== $circuit->getFailureCount()) {
                        $this->success();
                    }

                    return $result;
                } catch (\Throwable $throwable) {
                    $this->failure();

                    throw $throwable;
                }
            default:
                throw new OpenCircuitException();
        }
    }

    public function isAvailable(): bool
    {
        return match ($this->getCircuit()->getState()) {
            State::CLOSED,
            State::HALF_OPEN => true,
            default => false,
        };
    }

    public function success(): void
    {
        $this->storage->delete($this->name);
    }

    public function failure(): void
    {
        $circuit = $this->getCircuit();
        $circuit->increaseFailure($this->retryPolicy);
        $this->storage->save($circuit);
    }

    private function getCircuit(): Circuit
    {
        if (null === $circuit = $this->storage->fetch($this->name)) {
            return new Circuit($this->name, $this->failureThreshold);
        }

        return $circuit;
    }
}
