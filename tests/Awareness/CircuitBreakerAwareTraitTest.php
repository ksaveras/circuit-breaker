<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker\Tests\Awareness;

use Ksaveras\CircuitBreaker\Awareness\CircuitBreakerAwareTrait;
use Ksaveras\CircuitBreaker\CircuitBreaker;
use PHPUnit\Framework\TestCase;

class CircuitBreakerAwareTraitTest extends TestCase
{
    public function testTraitMethods(): void
    {
        $circuitBreaker = $this->createMock(CircuitBreaker::class);

        $service = new MockObject();

        $service->setCircuitBreaker($circuitBreaker);
        $this->assertEquals($circuitBreaker, $service->getCircuitBreaker());
    }

    public function testNullReturn(): void
    {
        $this->expectException(\RuntimeException::class);

        $service = new MockObject();
        $service->getCircuitBreaker();
    }
}

class MockObject
{
    use CircuitBreakerAwareTrait;
}
