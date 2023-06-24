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

use Ksaveras\CircuitBreaker\HeaderPolicy\HttpHeaderPolicy;
use Ksaveras\CircuitBreaker\HeaderPolicy\PolicyChain;
use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;

final class CircuitBreakerFactory
{
    public function __construct(
        private readonly int $failureThreshold,
        private readonly StorageInterface $storage,
        private readonly RetryPolicyInterface $retryPolicy,
        private readonly HttpHeaderPolicy $headerPolicy = new PolicyChain([]),
    ) {
        if (0 >= $this->failureThreshold) {
            throw new \InvalidArgumentException('Failure threshold must be positive non zero number.');
        }
    }

    public function create(string $name): CircuitBreaker
    {
        return new CircuitBreaker(
            $name,
            $this->failureThreshold,
            $this->retryPolicy,
            $this->storage,
            $this->headerPolicy,
        );
    }
}
