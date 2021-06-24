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

use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Storage\AbstractStorage;
use Ksaveras\CircuitBreaker\Storage\PsrCacheStorage;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class PsrCacheStorageTest extends TestCase
{
    public function testReturnsNullIfNotFound(): void
    {
        $adapter = new ArrayAdapter();
        $storage = new PsrCacheStorage($adapter);

        $circuit = $storage->getCircuit('myApi');

        self::assertNull($circuit);
    }

    public function testStorage(): void
    {
        $adapter = new ArrayAdapter();
        $storage = new PsrCacheStorage($adapter);
        $policy = new ConstantRetryPolicy();

        $circuit = CircuitBuilder::builder()
            ->withFailureCount(0)
            ->build();

        $storage->saveCircuit($circuit);

        $circuitB = $storage->getCircuit($circuit->getName());

        self::assertEquals(0, $circuitB->getFailureCount());

        $circuit->increaseFailure($policy);
        $storage->saveCircuit($circuit);

        $circuitB = $storage->getCircuit($circuit->getName());

        self::assertEquals(1, $circuitB->getFailureCount());
    }

    public function testResetCircuit(): void
    {
        $adapter = new ArrayAdapter();
        $storage = new PsrCacheStorage($adapter);

        $circuit = CircuitBuilder::builder()->build();

        $storage->saveCircuit($circuit);

        self::assertTrue($adapter->hasItem(AbstractStorage::storageKey($circuit->getName())));

        $storage->resetCircuit($circuit->getName());

        self::assertFalse($adapter->hasItem(AbstractStorage::storageKey($circuit->getName())));
    }
}
