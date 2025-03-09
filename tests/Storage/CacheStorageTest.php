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

use Ksaveras\CircuitBreaker\Storage\CacheStorage;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(CacheStorage::class)]
final class CacheStorageTest extends TestCase
{
    private CacheStorage $storage;

    private MockObject&CacheItemPoolInterface $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $this->createMock(CacheItemPoolInterface::class);
        $this->storage = new CacheStorage($this->pool);
    }

    public function testFetchNotFound(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('get')->willReturn(null);
        $cacheItem->method('isHit')->willReturn(false);

        $this->pool->expects(self::once())
            ->method('getItem')
            ->with(sha1('myApi'))
            ->willReturn($cacheItem);

        $circuit = $this->storage->fetch('myApi');

        self::assertNull($circuit);
    }

    public function testFetchJunk(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('get')->willReturn('junk');
        $cacheItem->method('isHit')->willReturn(true);

        $this->pool->expects(self::once())
            ->method('getItem')
            ->with(sha1('myApi'))
            ->willReturn($cacheItem);

        $circuit = $this->storage->fetch('myApi');

        self::assertNull($circuit);
    }

    public function testFetchExisting(): void
    {
        $circuit = CircuitBuilder::new()
            ->withFailureCount(2)
            ->build();

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('get')->willReturn($circuit);
        $cacheItem->method('isHit')->willReturn(true);

        $this->pool->expects(self::once())
            ->method('getItem')
            ->with(sha1($circuit->getName()))
            ->willReturn($cacheItem);

        self::assertEquals($circuit, $this->storage->fetch($circuit->getName()));
    }

    public function testSave(): void
    {
        $circuit = CircuitBuilder::new()
            ->withFailureCount(2)
            ->build();

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->expects(self::once())
            ->method('set')
            ->with($circuit);
        $cacheItem->expects(self::once())
            ->method('expiresAfter')
            ->with(120);

        $this->pool->expects(self::once())
            ->method('getItem')
            ->with(sha1($circuit->getName()))
            ->willReturn($cacheItem);

        $this->storage->save($circuit);
    }

    public function testDelete(): void
    {
        $circuit = CircuitBuilder::new()->build();

        $this->pool->expects(self::once())
            ->method('deleteItem')
            ->with(sha1($circuit->getName()));

        $this->storage->delete($circuit->getName());
    }

    public function testClear(): void
    {
        $this->pool
            ->expects(self::once())
            ->method('clear');

        $this->storage->clear();
    }

    public function testGetAll(): void
    {
        $circuit1 = CircuitBuilder::new()
            ->withName('circuit1')
            ->build();
        $cacheItem1 = $this->createMock(CacheItemInterface::class);
        $cacheItem1->method('isHit')->willReturn(true);
        $cacheItem1->method('get')->willReturn($circuit1);

        $circuit2 = CircuitBuilder::new()
            ->withName('circuit2')
            ->build();
        $cacheItem2 = $this->createMock(CacheItemInterface::class);
        $cacheItem2->method('isHit')->willReturn(true);
        $cacheItem2->method('get')->willReturn($circuit2);

        $this->pool
            ->expects(self::once())
            ->method('getItems')
            ->willReturn([$cacheItem1, $cacheItem2]);

        $results = $this->storage->getAll();

        self::assertEquals([$circuit1, $circuit2], $results);
    }

    public function testCleanup(): void
    {
        $cacheItem1 = $this->createMock(CacheItemInterface::class);
        $cacheItem1->method('isHit')->willReturn(false);
        $cacheItem1->method('getKey')->willReturn(sha1('junk1'));

        $cacheItem2 = $this->createMock(CacheItemInterface::class);
        $cacheItem2->method('isHit')->willReturn(false);
        $cacheItem2->method('getKey')->willReturn(sha1('junk2'));

        $circuit = CircuitBuilder::new()
            ->withFailureCount(0)
            ->build();

        $validCacheItem = $this->createMock(CacheItemInterface::class);
        $validCacheItem->method('isHit')->willReturn(true);
        $validCacheItem->method('get')->willReturn($circuit);

        $this->pool
            ->expects(self::once())
            ->method('getItems')
            ->willReturn([$cacheItem1, $cacheItem2, $validCacheItem]);

        $this->pool
            ->expects(self::once())
            ->method('deleteItems')
            ->with([sha1('junk1'), sha1('junk2')])
            ->willReturn(true);

        $this->storage->cleanup();
    }
}
