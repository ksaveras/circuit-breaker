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

final class LinearRetryPolicy implements RetryPolicyInterface
{
    public function __construct(
        private readonly int $initialTtl = 600,
        private readonly int $maximumTtl = 86400,
        private readonly int $step = 600,
    ) {
        if ($this->initialTtl < 0) {
            throw new \InvalidArgumentException('Initial TTL can not be negative number.');
        }

        if ($this->step <= 0) {
            throw new \InvalidArgumentException('Step value must be positive number.');
        }

        if ($this->maximumTtl <= 0 || $this->initialTtl > $this->maximumTtl) {
            throw new \InvalidArgumentException('Maximum TTL must be positive and greater than initial TTL.');
        }
    }

    public function calculate(Circuit $circuit): int
    {
        $retries = max(0, $circuit->getFailureCount() - $circuit->getFailureThreshold());

        return min($this->maximumTtl, $this->initialTtl + ($retries * $this->step));
    }
}
