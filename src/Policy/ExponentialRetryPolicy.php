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
        private readonly int $startSleepSeconds = 10,
        private readonly int $maxSleepSeconds = 86400,
        private readonly float $base = 2.0,
    ) {
        if ($this->startSleepSeconds < 0) {
            throw new \InvalidArgumentException('Start sleep value can not be negative number.');
        }

        if ($this->base <= 1.0) {
            throw new \InvalidArgumentException('Base value must be greater than 1.');
        }

        if ($this->maxSleepSeconds <= 0 || $this->startSleepSeconds > $this->maxSleepSeconds) {
            throw new \InvalidArgumentException('Maximum sleep value must be positive and greater than initial timeout.');
        }
    }

    public function calculate(Circuit $circuit): int
    {
        $retries = max(0, $circuit->getFailureCount() - $circuit->getFailureThreshold());

        return min($this->maxSleepSeconds, (int) ($this->base ** $retries + $this->startSleepSeconds));
    }
}
