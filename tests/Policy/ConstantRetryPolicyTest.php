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

class ConstantRetryPolicyTest extends TestCase
{
    public function testNegativeTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout value must be 0 or positive integer.');

        new ConstantRetryPolicy(-1);
    }

    public function testCalculateRetryTtl(): void
    {
        $policy = new ConstantRetryPolicy(600);
        $circuit = CircuitBuilder::builder()->build();

        self::assertEquals(600, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(600, $policy->calculate($circuit));

        $circuit->increaseFailure();

        self::assertEquals(600, $policy->calculate($circuit));
    }
}
