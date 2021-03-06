<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\Policy;

use Ksaveras\CircuitBreaker\Policy\ExponentialRetryPolicy;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;

class ExponentialRetryPolicyTest extends TestCase
{
    public function testNegativeInitialTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Initial timeout can not be negative number.');

        new ExponentialRetryPolicy(-1, 10);
    }

    public function testInvalidBaseValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Base value must be greater than 1.');

        new ExponentialRetryPolicy(1, 10, 0);
    }

    public function testNegativeMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum timeout must be positive and greater than initial timeout.');

        new ExponentialRetryPolicy(1, -1);
    }

    public function testInvalidMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum timeout must be positive and greater than initial timeout.');

        new ExponentialRetryPolicy(10, 2);
    }

    public function testCalculateRetryTtl(): void
    {
        $policy = new ExponentialRetryPolicy(0);

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(2)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(1, $policy->calculate($circuit));

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(3)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(2, $policy->calculate($circuit));

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(4)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(4, $policy->calculate($circuit));

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(5)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(8, $policy->calculate($circuit));
    }

    public function testCalculateMaximumRetryTtl(): void
    {
        $policy = new ExponentialRetryPolicy(0, 50);

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(6)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(16, $policy->calculate($circuit));

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(7)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(32, $policy->calculate($circuit));

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(8)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(50, $policy->calculate($circuit));

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(9)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(50, $policy->calculate($circuit));
    }
}
