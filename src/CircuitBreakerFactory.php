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

use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;

final class CircuitBreakerFactory
{
    private int $failureThreshold;

    private StorageInterface $storage;

    private RetryPolicyInterface $retryPolicy;

    public function __construct(int $failureThreshold, StorageInterface $storage, RetryPolicyInterface $retryPolicy)
    {
        if (0 >= $failureThreshold) {
            throw new \InvalidArgumentException('Failure threshold must be positive non zero number.');
        }

        $this->failureThreshold = $failureThreshold;
        $this->storage = $storage;
        $this->retryPolicy = $retryPolicy;
    }

    public function create(string $name): CircuitBreaker
    {
        return new CircuitBreaker($name, $this->failureThreshold, $this->retryPolicy, $this->storage);
    }
}
