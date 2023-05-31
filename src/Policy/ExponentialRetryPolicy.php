<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Policy;

use Ksaveras\CircuitBreaker\Circuit;

final class ExponentialRetryPolicy implements RetryPolicyInterface
{
    public function __construct(
        private readonly int $initialTtl = 10,
        private readonly int $maximumTtl = 86400,
        private readonly int $base = 2,
    ) {
        if ($this->initialTtl < 0) {
            throw new \InvalidArgumentException('Initial timeout can not be negative number.');
        }

        if ($this->base <= 1) {
            throw new \InvalidArgumentException('Base value must be greater than 1.');
        }

        if ($this->maximumTtl <= 0 || $this->initialTtl > $this->maximumTtl) {
            throw new \InvalidArgumentException('Maximum timeout must be positive and greater than initial timeout.');
        }
    }

    public function calculate(Circuit $circuit): int
    {
        $retries = max(0, $circuit->getFailureCount() - $circuit->getFailureThreshold());

        return min($this->maximumTtl, $this->base ** $retries + $this->initialTtl);
    }
}
