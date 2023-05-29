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
    private int $timeout;

    public function __construct(int $timeout = 600)
    {
        if ($timeout < 0) {
            throw new \InvalidArgumentException('Timeout value must be 0 or positive integer.');
        }
        $this->timeout = $timeout;
    }

    public function calculate(Circuit $circuit): int
    {
        return $this->timeout;
    }
}
