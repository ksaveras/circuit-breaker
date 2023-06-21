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

final class ExponentialRetryPolicyTest extends TestCase
{
    public function testNegativeInitialTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Start sleep value can not be negative number.');

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
        $this->expectExceptionMessage('Maximum sleep value must be positive and greater than initial timeout.');

        new ExponentialRetryPolicy(1, -1);
    }

    public function testInvalidMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum sleep value must be positive and greater than initial timeout.');

        new ExponentialRetryPolicy(10, 2);
    }

    public function testCalculateRetryTtl(): void
    {
        $policy = new ExponentialRetryPolicy(0, 100);

        $circuit = CircuitBuilder::new()
            ->withFailureCount(2)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(1, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(3)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(2, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(4)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(4, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(5)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(8, $policy->calculate($circuit));
    }

    public function testCalculateMaximumRetryTtl(): void
    {
        $policy = new ExponentialRetryPolicy(0, 50);

        $circuit = CircuitBuilder::new()
            ->withFailureCount(6)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(16, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(7)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(32, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(8)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(50, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(9)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(50, $policy->calculate($circuit));
    }
}
