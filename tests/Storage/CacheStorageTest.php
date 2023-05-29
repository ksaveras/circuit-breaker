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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CacheStorageTest extends TestCase
{
    private CacheStorage $storage;

    /**
     * @var MockObject|CacheItemPoolInterface
     */
    private MockObject $pool;

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
}
