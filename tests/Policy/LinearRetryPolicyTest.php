<?php declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Tests\Policy;

use Ksaveras\CircuitBreaker\Policy\LinearRetryPolicy;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;

class LinearRetryPolicyTest extends TestCase
{
    public function testNegativeInitialTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Initial timeout can not be negative number.');

        new LinearRetryPolicy(-1, 2, 10);
    }

    public function testInvalidStepValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Step value must be positive number.');

        new LinearRetryPolicy(1, 0, 10);
    }

    public function testNegativeMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum timeout must be positive and greater than initial timeout.');

        new LinearRetryPolicy(1, 2, -1);
    }

    public function testInvalidMaximumTimeoutValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum timeout must be positive and greater than initial timeout.');

        new LinearRetryPolicy(10, 2, 2);
    }

    public function testCalculateRetryTtl(): void
    {
        $policy = new LinearRetryPolicy(10, 5, 100);
        $circuit = CircuitBuilder::builder()
            ->withFailureCount(1)
            ->withFailureThreshold(2)
            ->build();

        self::assertEquals(10, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(10, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(15, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(20, $policy->calculate($circuit));
    }

    public function testCalculateMaximumRetryTtl(): void
    {
        $policy = new LinearRetryPolicy(20, 50, 100);
        $circuit = CircuitBuilder::builder()
            ->withFailureCount(3)
            ->withFailureThreshold(2)
            ->build();

        self::assertEquals(70, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(100, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(100, $policy->calculate($circuit));
    }
}
