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
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\State;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @phpstan-import-type CircuitArray from Circuit
 */
final class CircuitTest extends TestCase
{
    /**
     * @dataProvider circuitDataProvider
     *
     * @param CircuitArray $data
     * @param CircuitArray $expected
     */
    public function testFromArray(array $data, array $expected): void
    {
        $circuit = Circuit::fromArray($data);

        self::assertEquals($expected, $circuit->toArray());
    }

    /**
     * @return array<int, array<int, CircuitArray>>
     */
    public function circuitDataProvider(): iterable
    {
        yield [
            [
                'name' => 'demo',
            ],
            [
                'name' => 'demo',
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
                'failureCount' => 10,
                'lastFailure' => $now,
                'resetTimeout' => 120,
                'failureThreshold' => 5,
            ],
            [
                'name' => 'demo',
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
        $circuit = new Circuit('demo', 2);
        $policy = new ConstantRetryPolicy();

        self::assertEquals(State::CLOSED, $circuit->getState());

        $circuit->increaseFailure($policy);

        self::assertEquals(State::CLOSED, $circuit->getState());

        $circuit->increaseFailure($policy);

        self::assertEquals(State::OPEN, $circuit->getState());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIncreaseFailure(): void
    {
        ClockMock::register(Circuit::class);
        ClockMock::withClockMock(strtotime('2020-12-01 10:00:00'));

        $circuit = new Circuit('demo', 2);
        $policy = new ConstantRetryPolicy();

        self::assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure($policy);
        self::assertEquals(1, $circuit->getFailureCount());
        self::assertEquals(ClockMock::time(), $circuit->getLastFailure());

        $circuit->increaseFailure($policy);
        self::assertEquals(2, $circuit->getFailureCount());
        self::assertEquals(ClockMock::time(), $circuit->getLastFailure());

        ClockMock::withClockMock(false);
    }
}
