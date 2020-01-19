<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Tests;

use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\ArrayStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class CircuitBreakerTest extends TestCase
{
    public function testNewCircuitBreakerIsClosed(): void
    {
        $storage = new ArrayStorage();

        $service = new CircuitBreaker('demo', $storage);

        $this->assertEquals(State::CLOSED, $service->getState());
    }

    public function testCircuitBreaker(): void
    {
        $storage = new ArrayStorage();

        $service = new CircuitBreaker('demo', $storage);
        $result = $service->call($this->successClosure('demo data'));

        $this->assertEquals('demo data', $result);
        $this->assertEquals(State::CLOSED, $service->getState());
    }

    public function testFailureThreshold(): void
    {
        $storage = new ArrayStorage();
        $service = new CircuitBreaker('demo', $storage, 2, 60);

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

        $storage = new ArrayStorage();
        $service = new CircuitBreaker('demo', $storage, 1, 10);

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

        $storage = new ArrayStorage();
        $service = new CircuitBreaker('demo', $storage, 1, 10, 1.5);

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
        $storage = new ArrayStorage();
        $service = new CircuitBreaker('demo', $storage, 2, 10);

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
