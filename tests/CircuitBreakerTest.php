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

use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Ksaveras\CircuitBreaker\Factory\CircuitFactory;
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\PhpArray;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

class CircuitBreakerTest extends TestCase
{
    /**
     * @var CircuitBreaker
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CircuitBreaker('demo', new PhpArray(), new CircuitFactory(2));
    }

    public function testReturnsName(): void
    {
        self::assertEquals('demo', $this->service->getName());
    }

    public function testNewCircuitBreakerIsClosed(): void
    {
        self::assertEquals(State::CLOSED, $this->service->getState());
    }

    public function testSuccessCallback(): void
    {
        $result = $this->service->call($this->successClosure());

        self::assertEquals('success', $result);
        self::assertEquals(State::CLOSED, $this->service->getState());
    }

    public function testServiceIsAvailableCallback(): void
    {
        self::assertTrue($this->service->isAvailable());

        $this->service->call($this->successClosure());

        self::assertTrue($this->service->isAvailable());
    }

    public function testFailureThreshold(): void
    {
        try {
            $this->service->call($this->failingClosure());
        } catch (\Exception $exception) {
            self::assertInstanceOf(\RuntimeException::class, $exception);
        }
        self::assertEquals(State::CLOSED, $this->service->getState());

        try {
            $this->service->call($this->failingClosure());
        } catch (\Exception $exception) {
            self::assertInstanceOf(\RuntimeException::class, $exception);
        }
        self::assertEquals(State::OPEN, $this->service->getState());

        try {
            $this->service->call($this->failingClosure());
        } catch (\Exception $exception) {
            self::assertInstanceOf(CircuitBreakerException::class, $exception);
        }
        self::assertEquals(State::OPEN, $this->service->getState());
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
        $service = (new CircuitBreaker('demo', $storage, new CircuitFactory(2, 10)));

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        self::assertEquals(State::CLOSED, $service->getState());

        try {
            $service->call($this->failingClosure());
        } catch (\Exception $exception) {
        }
        self::assertEquals(State::OPEN, $service->getState());

        sleep(11);

        try {
            $service->call($this->successClosure());
        } catch (\Exception $exception) {
        }
        self::assertEquals(State::CLOSED, $service->getState());

        ClockMock::withClockMock(false);
    }

    public function testCircuitFunctions(): void
    {
        $storage = new PhpArray();
        $service = (new CircuitBreaker('demo', $storage, new CircuitFactory(2, 10)));

        self::assertTrue($service->isAvailable());

        $service->failure();

        self::assertTrue($service->isAvailable());

        $service->failure();

        self::assertFalse($service->isAvailable());

        $service->success();

        self::assertTrue($service->isAvailable());
    }

    private function failingClosure(): \Closure
    {
        return static function () {
            throw new \RuntimeException('Runtime error');
        };
    }

    private function successClosure(string $result = 'success'): \Closure
    {
        return static function () use ($result) {
            return $result;
        };
    }
}
