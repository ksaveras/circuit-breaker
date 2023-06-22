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
use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\Exception\OpenCircuitException;
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

final class CircuitBreakerTest extends TestCase
{
    private InMemoryStorage $storage;

    private CircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new InMemoryStorage();

        $this->circuitBreaker = new CircuitBreaker(
            'demo',
            2,
            new ConstantRetryPolicy(50),
            $this->storage
        );
    }

    public function testReturnsName(): void
    {
        self::assertEquals('demo', $this->circuitBreaker->getName());
    }

    #[DataProvider('providesCircuitStateChecks')]
    public function testState(Circuit $circuit, State $state): void
    {
        $this->storage->save($circuit);

        self::assertSame($state, $this->circuitBreaker->state());

        $result = match ($state) {
            State::CLOSED => $this->circuitBreaker->isClosed(),
            State::HALF_OPEN => $this->circuitBreaker->isHalfOpen(),
            State::OPEN => $this->circuitBreaker->isOpen(),
        };
        self::assertTrue($result);
    }

    /**
     * @return iterable<string, array<int, Circuit|State>>
     */
    public static function providesCircuitStateChecks(): iterable
    {
        $builder = CircuitBuilder::new()
            ->withFailureCount(0)
            ->withFailureThreshold(3)
            ->withResetTimeout(10)
            ->withLastFailure(microtime(true));

        return [
            'closed' => [$builder->build(), State::CLOSED],
            'threshold not reached' => [$builder->withFailureCount(2)->build(), State::CLOSED],
            'half open' => [
                $builder->withFailureCount(4)->withLastFailure(microtime(true) - 15)->build(),
                State::HALF_OPEN,
            ],
            'open' => [$builder->withFailureCount(4)->build(), State::OPEN],
        ];
    }

    public function testClosedCircuitRemainingDelay(): void
    {
        $this->storage->save(CircuitBuilder::new()->withFailureCount(0)->build());

        self::assertSame(0.0, $this->circuitBreaker->remainingDelay());
    }

    public function testOpenCircuitRemainingDelay(): void
    {
        $this->storage->save(
            CircuitBuilder::new()
                ->withFailureCount(10)
                ->withLastFailure(microtime(true) - 100)
                ->withResetTimeout(60)
                ->build()
        );

        self::assertEqualsWithDelta(60.0, $this->circuitBreaker->remainingDelay(), 1.0);
    }

    public function testFailureCount(): void
    {
        $this->storage->save(
            CircuitBuilder::new()
                ->withFailureCount(10)
                ->build()
        );

        self::assertSame(10, $this->circuitBreaker->getFailureCount());
    }

    public function testSuccessCallbackReturnsResult(): void
    {
        $result = $this->circuitBreaker->call($this->successClosure());

        self::assertEquals('success', $result);
        self::assertTrue($this->circuitBreaker->isClosed());
    }

    public function testOpenCircuitException(): void
    {
        $this->expectException(OpenCircuitException::class);

        $this->storage->save(
            CircuitBuilder::new()
                ->withFailureCount(3)
                ->withLastFailure(microtime(true))
                ->withResetTimeout(60)
                ->build()
        );

        self::assertTrue($this->circuitBreaker->isOpen());

        $this->circuitBreaker->call($this->failingClosure());
    }

    public function testOpenCircuitExceptionResetTimeoutNotExpired(): void
    {
        $this->expectException(OpenCircuitException::class);

        $this->storage->save(
            CircuitBuilder::new()
                ->withFailureCount(3)
                ->withLastFailure(microtime(true))
                ->withResetTimeout(60)
                ->build()
        );

        self::assertTrue($this->circuitBreaker->isOpen());

        $this->circuitBreaker->call($this->successClosure());
    }

    #[RunInSeparateProcess]
    public function testResetPeriod(): void
    {
        ClockMock::register(self::class);
        ClockMock::register(CircuitBreaker::class);
        ClockMock::register(InMemoryStorage::class);
        ClockMock::withClockMock(true);

        try {
            $this->circuitBreaker->call($this->failingClosure());
        } catch (\Exception) {
        }

        self::assertTrue($this->circuitBreaker->isClosed());

        try {
            $this->circuitBreaker->call($this->failingClosure());
        } catch (\Exception) {
        }

        self::assertTrue($this->circuitBreaker->isOpen());

        sleep(100);

        $this->circuitBreaker->call($this->successClosure());

        self::assertTrue($this->circuitBreaker->isClosed());

        ClockMock::withClockMock(false);
    }

    public function testResetWhenServiceBecomesAvailableAndThresholdNotReached(): void
    {
        $this->storage->save(
            CircuitBuilder::new()
                ->withFailureCount(1)
                ->withLastFailure(microtime(true))
                ->withResetTimeout(60)
                ->build()
        );

        $this->circuitBreaker->call($this->successClosure());

        self::assertTrue($this->circuitBreaker->isClosed());
        self::assertSame(0, $this->circuitBreaker->getFailureCount());
    }

    public function testResetHalfOpenWhenServiceBecomesAvailable(): void
    {
        $this->storage->save(
            CircuitBuilder::new()
                ->withFailureCount(10)
                ->withLastFailure(microtime(true) - 100)
                ->withResetTimeout(60)
                ->build()
        );

        $this->circuitBreaker->call($this->successClosure());

        self::assertTrue($this->circuitBreaker->isClosed());
        self::assertSame(0, $this->circuitBreaker->getFailureCount());
    }

    public function testCircuitFunctions(): void
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();

        self::assertTrue($this->circuitBreaker->isOpen());

        $this->circuitBreaker->recordSuccess();

        self::assertTrue($this->circuitBreaker->isClosed());
    }

    private function failingClosure(): \Closure
    {
        return static function (): never {
            throw new \RuntimeException('Runtime error');
        };
    }

    private function successClosure(): \Closure
    {
        return static fn (): string => 'success';
    }
}
