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

use Ksaveras\CircuitBreaker\Storage\PredisStorage;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class PredisStorageTest extends TestCase
{
    /**
     * @var PredisStorage
     */
    private $storage;

    /**
     * @var MockObject&ClientInterface
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(ClientInterface::class);
        $this->storage = new PredisStorage($this->client);
    }

    public function testReturnsNullIfNotFound(): void
    {
        $this->client->expects(self::once())
            ->method('__call')
            ->with('hgetall', ['CircuitBreaker|myApi'])
            ->willReturn(null);

        $circuit = $this->storage->getCircuit('myApi');

        self::assertNull($circuit);
    }

    public function testGetCircuit(): void
    {
        $circuitData = [
            'name' => 'myApi',
            'failureCount' => 10,
            'failureThreshold' => 3,
            'lastFailure' => time(),
            'resetTimeout' => 600,
        ];

        $this->client->expects(self::once())
            ->method('__call')
            ->with('hgetall', ['CircuitBreaker|myApi'])
            ->willReturn($circuitData);

        $circuit = $this->storage->getCircuit('myApi');

        self::assertEquals($circuitData, $circuit->toArray());
    }

    public function testSaveCircuit(): void
    {
        $now = time();

        $circuit = CircuitBuilder::builder()->build();

        $this->client
            ->expects(self::exactly(6))
            ->method('__call')
            ->withConsecutive(
                ['hset', ['CircuitBreaker|demo', 'name', 'demo']],
                ['hset', ['CircuitBreaker|demo', 'failureCount', 3]],
                ['hset', ['CircuitBreaker|demo', 'failureThreshold', 2]],
                ['hset', ['CircuitBreaker|demo', 'lastFailure', $now]],
                ['hset', ['CircuitBreaker|demo', 'resetTimeout', 120]],
                ['expire', ['CircuitBreaker|demo', 120]]
            );

        $this->storage->saveCircuit($circuit);
    }

    public function testResetCircuit(): void
    {
        $this->client->expects(self::once())
            ->method('__call')
            ->with('del', [['CircuitBreaker|demo']]);

        $this->storage->resetCircuit('demo');
    }
}
