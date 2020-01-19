<?php

declare(strict_types=1);

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
