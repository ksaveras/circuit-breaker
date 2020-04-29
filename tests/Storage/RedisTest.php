<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\Storage;

use Ksaveras\CircuitBreaker\Circuit;
use Ksaveras\CircuitBreaker\Storage\Redis;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class RedisTest extends TestCase
{
    /**
     * @var Redis
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
        $this->storage = new Redis($this->client);
    }

    public function testGetCircuit(): void
    {
        $circuitData = [
            'name' => 'myApi',
            'state' => 'open',
            'failureCount' => 10,
            'lastFailure' => time(),
            'resetTimeout' => 600,
        ];

        $this->client->expects($this->once())
            ->method('__call')
            ->with('hgetall', ['CircuitBreaker|myApi'])
            ->willReturn($circuitData);

        $circuit = $this->storage->getCircuit('myApi');

        $this->assertEquals($circuitData, $circuit->toArray());
    }

    public function testSaveCircuit(): void
    {
        $now = time();

        $circuit = new Circuit('myApi');
        $circuit->setState('open')
            ->setResetTimeout(600)
            ->setFailureCount(5)
            ->setLastFailure($now);

        $this->client
            ->expects($this->exactly(6))
            ->method('__call')
            ->withConsecutive(
                ['hset', ['CircuitBreaker|myApi', 'name', 'myApi']],
                ['hset', ['CircuitBreaker|myApi', 'state', 'open']],
                ['hset', ['CircuitBreaker|myApi', 'failureCount', 5]],
                ['hset', ['CircuitBreaker|myApi', 'lastFailure', $now]],
                ['hset', ['CircuitBreaker|myApi', 'resetTimeout', 600]],
                ['expire', ['CircuitBreaker|myApi', 600]]
            );

        $this->storage->saveCircuit($circuit);
    }

    public function testResetCircuit(): void
    {
        $this->client->expects($this->once())
            ->method('__call')
            ->with('del', [['CircuitBreaker|myApi']]);

        $this->storage->resetCircuit('myApi');
    }
}
