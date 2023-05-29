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
    private int $initialTimeout;

    private int $step;

    private int $maximum;

    public function __construct(int $initialTimeout = 600, int $step = 600, int $maximum = 86400)
    {
        if ($initialTimeout < 0) {
            throw new \InvalidArgumentException('Initial timeout can not be negative number.');
        }

        if ($step <= 0) {
            throw new \InvalidArgumentException('Step value must be positive number.');
        }

        if ($maximum <= 0 || $initialTimeout > $maximum) {
            throw new \InvalidArgumentException('Maximum timeout must be positive and greater than initial timeout.');
        }

        $this->initialTimeout = $initialTimeout;
        $this->step = $step;
        $this->maximum = $maximum;
    }

    public function calculate(Circuit $circuit): int
    {
        $retries = max(0, $circuit->getFailureCount() - $circuit->getFailureThreshold());

        return min($this->maximum, $this->initialTimeout + ($retries * $this->step));
    }
}
