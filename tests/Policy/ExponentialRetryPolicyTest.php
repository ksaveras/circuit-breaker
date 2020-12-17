<?php declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Tests\Policy;

use Ksaveras\CircuitBreaker\Policy\ExponentialRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\LinearRetryPolicy;
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

        $circuit->increaseFailure();

        self::assertEquals(2, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(4, $policy->calculate($circuit));

        $circuit->increaseFailure();

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

        $circuit->increaseFailure();

        self::assertEquals(32, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(50, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(50, $policy->calculate($circuit));
    }
}
