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

use Ksaveras\CircuitBreaker\Circuit;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Ksaveras\CircuitBreaker\State;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class CircuitTest extends TestCase
{
    /**
     * @dataProvider circuitDataProvider
     */
    public function testFromArray(array $data, array $expected): void
    {
        $circuit = Circuit::fromArray($data);

        self::assertEquals($expected, $circuit->toArray());
    }

    public function circuitDataProvider(): \Generator
    {
        yield [
            [
                'name' => 'demo',
            ],
            [
                'name' => 'demo',
                'state' => 'closed',
                'failureCount' => 0,
                'lastFailure' => null,
                'resetTimeout' => 60,
                'failureThreshold' => 5,
            ],
        ];

        $now = time();
        yield [
            [
                'name' => 'demo',
                'state' => 'open',
                'failureCount' => 10,
                'lastFailure' => $now,
                'resetTimeout' => 120,
                'failureThreshold' => 5,
            ],
            [
                'name' => 'demo',
                'state' => 'open',
                'failureCount' => 10,
                'lastFailure' => $now,
                'resetTimeout' => 120,
                'failureThreshold' => 5,
            ],
        ];
    }

    public function testWithEmptyArray(): void
    {
        $this->expectException(CircuitBreakerException::class);
        $this->expectExceptionMessage('Missing required data field "name"');

        Circuit::fromArray([]);
    }

    public function testReset(): void
    {
        $circuit = Circuit::fromArray(
            [
                'name' => 'demo',
                'state' => 'open',
                'failureCount' => 10,
                'lastFailure' => time(),
                'resetTimeout' => 120,
            ]
        );

        $circuit->reset();

        self::assertEquals(0, $circuit->getFailureCount());
        self::assertNull($circuit->getLastFailure());
        self::assertEquals(120, $circuit->getResetTimeout());
    }

    public function testGetState(): void
    {
        $circuit = new Circuit('demo', 0, 2);

        self::assertEquals(State::CLOSED, $circuit->getState());

        $circuit->increaseFailure();

        self::assertEquals(State::CLOSED, $circuit->getState());

        $circuit->increaseFailure();

        self::assertEquals(State::OPEN, $circuit->getState());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIncreaseFailure(): void
    {
        ClockMock::register(Circuit::class);
        ClockMock::withClockMock(strtotime('2020-12-01 10:00:00'));

        $circuit = new Circuit('demo', 0, 2);

        self::assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure();
        self::assertEquals(1, $circuit->getFailureCount());
        self::assertEquals(ClockMock::time(), $circuit->getLastFailure());

        $circuit->increaseFailure();
        self::assertEquals(2, $circuit->getFailureCount());
        self::assertEquals(ClockMock::time(), $circuit->getLastFailure());

        ClockMock::withClockMock(false);
    }
}
