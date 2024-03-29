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

use Ksaveras\CircuitBreaker\Policy\LinearRetryPolicy;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;

final class LinearRetryPolicyTest extends TestCase
{
    public function testNegativeInitialTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Start sleep value value can not be negative number.');

        new LinearRetryPolicy(-1, 10, 2);
    }

    public function testInvalidStepValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Step value must be positive number.');

        new LinearRetryPolicy(1, 10, 0);
    }

    public function testNegativeMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum sleep value must be positive and greater than start sleep.');

        new LinearRetryPolicy(1, -1, 2);
    }

    public function testInvalidMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum sleep value must be positive and greater than start sleep.');

        new LinearRetryPolicy(10, 2, 2);
    }

    public function testCalculateRetryTtl(): void
    {
        $policy = new LinearRetryPolicy(10, 100, 5);

        $circuit = CircuitBuilder::new()
            ->withFailureCount(1)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(10, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(2)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(10, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(3)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(15, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(4)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(20, $policy->calculate($circuit));
    }

    public function testCalculateMaximumRetryTtl(): void
    {
        $policy = new LinearRetryPolicy(20, 100, 50);

        $circuit = CircuitBuilder::new()
            ->withFailureCount(3)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(70, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(4)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(100, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(5)
            ->withFailureThreshold(2)
            ->build();
        self::assertEquals(100, $policy->calculate($circuit));
    }
}
