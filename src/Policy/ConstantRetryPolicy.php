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

final class ConstantRetryPolicy implements RetryPolicyInterface
{
    private int $ttlSeconds;

    public function __construct(int $ttlSeconds = 600)
    {
        if ($ttlSeconds < 0) {
            throw new \InvalidArgumentException('TTL value must be a positive integer.');
        }
        $this->ttlSeconds = $ttlSeconds;
    }

    public function calculate(Circuit $circuit): int
    {
        return $this->ttlSeconds;
    }
}
