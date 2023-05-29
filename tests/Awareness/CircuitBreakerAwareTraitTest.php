<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\Awareness;

use Ksaveras\CircuitBreaker\Awareness\CircuitBreakerAwareInterface;
use Ksaveras\CircuitBreaker\Awareness\CircuitBreakerAwareTrait;
use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\TestCase;

final class CircuitBreakerAwareTraitTest extends TestCase
{
    public function testTraitMethods(): void
    {
        $circuitBreaker = new CircuitBreaker('demo', 3, new ConstantRetryPolicy(), new InMemoryStorage());

        $service = new MockObject();

        $service->setCircuitBreaker($circuitBreaker);
        self::assertEquals($circuitBreaker, $service->getCircuitBreaker());
    }

    public function testNullReturn(): void
    {
        $this->expectException(\RuntimeException::class);

        $service = new MockObject();
        $service->getCircuitBreaker();
    }
}

class MockObject implements CircuitBreakerAwareInterface
{
    use CircuitBreakerAwareTrait;
}
