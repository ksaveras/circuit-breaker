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

final class CircuitBreaker
{
    private string $name;

    private int $failureThreshold;

    private RetryPolicyInterface $retryPolicy;

    private StorageInterface $storage;

    public function __construct(
        string $name,
        int $failureThreshold,
        RetryPolicyInterface $retryPolicy,
        StorageInterface $storage
    ) {
        $this->name = $name;
        $this->failureThreshold = $failureThreshold;
        $this->retryPolicy = $retryPolicy;
        $this->storage = $storage;
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
    public function call(callable $closure)
    {
        $circuit = $this->getCircuit();
        $state = $circuit->getState();

        switch ($state) {
            case State::OPEN:
                throw new OpenCircuitException();
            case State::CLOSED:
            case State::HALF_OPEN:
                try {
                    $result = $closure();
                    if (0 !== $circuit->getFailureCount()) {
                        $this->success();
                    }

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
        $state = $this->getCircuit()->getState();

        return \in_array($state, [State::CLOSED, State::HALF_OPEN], true);
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

    protected function getCircuit(): Circuit
    {
        if (null === $circuit = $this->storage->fetch($this->name)) {
            $circuit = new Circuit($this->name, $this->failureThreshold);
        }

        return $circuit;
    }
}
