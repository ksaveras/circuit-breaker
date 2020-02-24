<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker\Tests;

use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\PhpArray;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class CircuitBreakerTest extends TestCase
{
    public function testNewCircuitBreakerIsClosed(): void
    {
        $storage = new PhpArray();

        $service = new CircuitBreaker('demo', $storage);

        $this->assertEquals(State::CLOSED, $service->getState());
    }

    public function testCircuitBreaker(): void
    {
        $storage = new PhpArray();

        $service = new CircuitBreaker('demo', $storage);
        $result = $service->call($this->successClosure('demo data'));

        $this->assertEquals('demo data', $result);
        $this->assertEquals(State::CLOSED, $service->getState());
    }

    public function testFailureThreshold(): void
    {
        $storage = new PhpArray();
        $service = (new CircuitBreaker('demo', $storage, 60))
            ->setFailureThreshold(2);

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\RuntimeException::class, $exception);
        }
        $this->assertEquals(State::CLOSED, $service->getState());

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\RuntimeException::class, $exception);
        }
        $this->assertEquals(State::CLOSED, $service->getState());

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
            $this->assertInstanceOf(CircuitBreakerException::class, $exception);
        }
        $this->assertEquals(State::OPEN, $service->getState());
    }

    /**
     * @runInSeparateProcess
     */
    public function testResetPeriod(): void
    {
        ClockMock::register(__CLASS__);
        ClockMock::register(CircuitBreaker::class);
        ClockMock::withClockMock(true);

        $storage = new PhpArray();
        $service = (new CircuitBreaker('demo', $storage, 10))
            ->setFailureThreshold(1);

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::CLOSED, $service->getState());

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::OPEN, $service->getState());

        sleep(11);

        try {
            $service->call($this->successClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::CLOSED, $service->getState());

        ClockMock::withClockMock(false);
    }

    /**
     * @runInSeparateProcess
     */
    public function testResetPeriodRatio(): void
    {
        ClockMock::register(__CLASS__);
        ClockMock::register(CircuitBreaker::class);
        ClockMock::withClockMock(true);

        $storage = new PhpArray();
        $service = (new CircuitBreaker('demo', $storage, 10, 1.5))
            ->setFailureThreshold(1);

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::CLOSED, $service->getState());

        usleep(100);

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::OPEN, $service->getState());

        sleep(11);

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::HALF_OPEN, $service->getState());

        sleep(11);

        try {
            $service->call($this->successClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::OPEN, $service->getState());

        sleep(6);

        try {
            $service->call($this->successClosure());
        } catch (\Exception $exception) {
        }
        $this->assertEquals(State::CLOSED, $service->getState());

        ClockMock::withClockMock(false);
    }

    public function testCircuitFunctions(): void
    {
        $storage = new PhpArray();
        $service = (new CircuitBreaker('demo', $storage, 10))
            ->setFailureThreshold(2);

        $this->assertTrue($service->isAvailable());

        $service->failure();

        $this->assertTrue($service->isAvailable());

        $service->failure();

        $this->assertFalse($service->isAvailable());

        $service->success();

        $this->assertTrue($service->isAvailable());
    }

    private function failingClosure(): \Closure
    {
        return static function () {
            throw new \RuntimeException('Runtime error');
        };
    }

    /**
     * @param mixed $result
     */
    private function successClosure($result = 'success'): \Closure
    {
        return static function () use ($result) {
            return $result;
        };
    }
}
