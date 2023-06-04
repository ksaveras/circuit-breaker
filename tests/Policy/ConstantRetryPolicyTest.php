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

use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;

final class ConstantRetryPolicyTest extends TestCase
{
    public function testNegativeTtl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sleep seconds value must be a positive integer.');

        new ConstantRetryPolicy(-1);
    }

    public function testCalculateRetryTtl(): void
    {
        $policy = new ConstantRetryPolicy(600);

        $circuit = CircuitBuilder::new()->withFailureCount(0)->build();
        self::assertEquals(600, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()->withFailureCount(2)->build();
        self::assertEquals(600, $policy->calculate($circuit));

        $circuit = CircuitBuilder::new()->withFailureCount(4)->build();
        self::assertEquals(600, $policy->calculate($circuit));
    }
}
