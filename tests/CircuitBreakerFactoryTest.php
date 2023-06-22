<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests;

use Ksaveras\CircuitBreaker\CircuitBreakerFactory;
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\TestCase;

final class CircuitBreakerFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new CircuitBreakerFactory(3, new InMemoryStorage(), new ConstantRetryPolicy(10));

        $circuitBreaker = $factory->create('name');

        self::assertEquals('name', $circuitBreaker->getName());
        self::assertEquals(State::CLOSED, $circuitBreaker->state());
        self::assertTrue($circuitBreaker->isAvailable());
    }

    public function testInvalidFailureThreshold(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Failure threshold must be positive non zero number.');

        $factory = new CircuitBreakerFactory(0, new InMemoryStorage(), new ConstantRetryPolicy(10));

        $factory->create('name');
    }
}
