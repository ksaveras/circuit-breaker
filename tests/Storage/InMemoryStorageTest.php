<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\Storage;

use Ksaveras\CircuitBreaker\Circuit;
use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(Circuit::class)]
#[UsesClass(CircuitBreaker::class)]
#[UsesClass(ConstantRetryPolicy::class)]
#[CoversClass(InMemoryStorage::class)]
final class InMemoryStorageTest extends TestCase
{
    public function testStorage(): void
    {
        $storage = new InMemoryStorage();
        $policy = new ConstantRetryPolicy(10);

        $storage->save(new Circuit('demo1', 3, 10));

        $circuit = $storage->fetch('demo1');
        self::assertNotNull($circuit);
        self::assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure($policy);
        $storage->save($circuit);

        $circuit = $storage->fetch('demo1');
        self::assertNotNull($circuit);
        self::assertEquals(1, $circuit->getFailureCount());

        $storage->delete('demo1');
        self::assertNull($storage->fetch('demo1'));
    }

    public function testClear(): void
    {
        $storage = new InMemoryStorage();

        $storage->save(new Circuit('demo1', 3, 10));
        $storage->save(new Circuit('demo2', 3, 10));

        $storage->clear();

        $reflection = new \ReflectionClass(InMemoryStorage::class);
        $property = $reflection->getProperty('circuits');
        $property->setAccessible(true);

        $internalStorage = $property->getValue($storage);

        self::assertIsArray($internalStorage);
        self::assertCount(0, $internalStorage);
    }

    public function testGetAll(): void
    {
        $storage = new InMemoryStorage();

        $storage->save(new Circuit('demo1', 3, 10));
        $storage->save(new Circuit('demo2', 3, 10));
        $storage->save(new Circuit('expired', 3, -10)); // expired circuit

        $allCircuits = $storage->getAll();

        self::assertCount(2, $allCircuits);
        self::assertSame('demo1', $allCircuits[0]->getName());
        self::assertSame('demo2', $allCircuits[1]->getName());
    }

    public function testCleanup(): void
    {
        $storage = new InMemoryStorage();

        $storage->save(new Circuit('demo1', 3, 10));
        $storage->save(new Circuit('demo2', 3, 10));
        $storage->save(new Circuit('expired', 3, -10)); // expired circuit

        $storage->cleanup();

        $reflection = new \ReflectionClass(InMemoryStorage::class);
        $property = $reflection->getProperty('circuits');
        $property->setAccessible(true);

        $internalStorage = $property->getValue($storage);

        self::assertIsArray($internalStorage);
        self::assertCount(2, $internalStorage);
    }
}
