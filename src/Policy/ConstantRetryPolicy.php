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

final readonly class ConstantRetryPolicy implements RetryPolicyInterface
{
    public function __construct(
        private int $sleepSeconds,
    ) {
        if ($this->sleepSeconds < 0) {
            throw new \InvalidArgumentException('Sleep seconds value must be a positive integer.');
        }
    }

    public function calculate(Circuit $circuit): int
    {
        return $this->sleepSeconds;
    }

    public function initialDelay(): int
    {
        return $this->sleepSeconds;
    }
}
