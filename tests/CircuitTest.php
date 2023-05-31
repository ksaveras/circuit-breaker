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
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

final class CircuitTest extends TestCase
{
    public function testSerialize(): void
    {
        $initial = CircuitBuilder::new()->build();

        $serialized = serialize($initial);

        $circuit = unserialize($serialized, ['allowed_classes' => [Circuit::class]]);

        self::assertInstanceOf(Circuit::class, $circuit);

        self::assertEquals($initial->getName(), $circuit->getName());
        self::assertEquals($initial->getFailureCount(), $circuit->getFailureCount());
        self::assertEquals($initial->getLastFailure(), $circuit->getLastFailure());
        self::assertEquals($initial->getFailureThreshold(), $circuit->getFailureThreshold());
        self::assertEquals($initial->getResetTimeout(), $circuit->getResetTimeout());
    }

    public function testReset(): void
    {
        $circuit = CircuitBuilder::new()
            ->withFailureCount(10)
            ->withLastFailure(microtime(true))
            ->build();

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
        self::assertEquals(ClockMock::microtime(true), $circuit->getLastFailure());

        $circuit->increaseFailure($policy);
        self::assertEquals(2, $circuit->getFailureCount());
        self::assertEquals(ClockMock::microtime(true), $circuit->getLastFailure());

        ClockMock::withClockMock(false);
    }
}
