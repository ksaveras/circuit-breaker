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

final readonly class LinearRetryPolicy implements RetryPolicyInterface
{
    public function __construct(
        private int $startSleepSeconds,
        private int $maxSleepSeconds,
        private int $step,
    ) {
        if ($this->startSleepSeconds < 0) {
            throw new \InvalidArgumentException('Start sleep value value can not be negative number.');
        }

        if ($this->step <= 0) {
            throw new \InvalidArgumentException('Step value must be positive number.');
        }

        if ($this->maxSleepSeconds <= 0 || $this->startSleepSeconds > $this->maxSleepSeconds) {
            throw new \InvalidArgumentException('Maximum sleep value must be positive and greater than start sleep.');
        }
    }

    public function calculate(Circuit $circuit): int
    {
        $retries = max(0, $circuit->getFailureCount() - $circuit->getFailureThreshold());

        return min($this->maxSleepSeconds, $this->startSleepSeconds + ($retries * $this->step));
    }

    public function initialDelay(): int
    {
        return $this->startSleepSeconds;
    }
}
