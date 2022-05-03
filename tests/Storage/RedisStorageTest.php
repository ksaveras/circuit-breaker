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

use Ksaveras\CircuitBreaker\Storage\RedisStorage;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RedisStorageTest extends TestCase
{
    private RedisStorage $storage;

    /**
     * @var MockObject&\Redis
     */
    private MockObject $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(\Redis::class);
        $this->storage = new RedisStorage($this->client);
    }

    public function testReturnsNullIfNotFound(): void
    {
        $this->client->expects(self::once())
            ->method('hGetAll')
            ->with('CircuitBreaker|myApi')
            ->willReturn(null);

        $circuit = $this->storage->getCircuit('myApi');

        self::assertNull($circuit);
    }

    public function testGetCircuit(): void
    {
        $circuitData = [
            'name' => 'myApi',
            'failureCount' => 10,
            'lastFailure' => time(),
            'resetTimeout' => 600,
            'failureThreshold' => 5,
        ];

        $this->client->expects(self::once())
            ->method('hGetAll')
            ->with('CircuitBreaker|myApi')
            ->willReturn($circuitData);

        $circuit = $this->storage->getCircuit('myApi');
        self::assertNotNull($circuit);
        self::assertEquals($circuitData, $circuit->toArray());
    }

    public function testSaveCircuit(): void
    {
        $now = time();

        $circuit = CircuitBuilder::builder()->build();

        $this->client->expects(self::exactly(5))
            ->method('hSet')
            ->withConsecutive(
                ['CircuitBreaker|demo', 'name', 'demo'],
                ['CircuitBreaker|demo', 'failureCount', 3],
                ['CircuitBreaker|demo', 'failureThreshold', 2],
                ['CircuitBreaker|demo', 'lastFailure', $now],
                ['CircuitBreaker|demo', 'resetTimeout', 120]
            );

        $this->client->expects(self::once())
            ->method('expire')
            ->with('CircuitBreaker|demo', 120);

        $this->storage->saveCircuit($circuit);
    }

    public function testResetCircuit(): void
    {
        $this->client->expects(self::once())
            ->method('del')
            ->with('CircuitBreaker|myApi');

        $this->storage->resetCircuit('myApi');
    }
}
