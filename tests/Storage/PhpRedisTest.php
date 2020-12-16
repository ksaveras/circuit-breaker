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
use Ksaveras\CircuitBreaker\Storage\PhpRedis;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PhpRedisTest extends TestCase
{
    /**
     * @var PhpRedis
     */
    private $storage;

    /**
     * @var MockObject&\Redis
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(\Redis::class);
        $this->storage = new PhpRedis($this->client);
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
            ->method('hGetAll')
            ->with('CircuitBreaker|myApi')
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

        $this->client->expects($this->exactly(5))
            ->method('hSet')
            ->withConsecutive(
                ['CircuitBreaker|myApi', 'name', 'myApi'],
                ['CircuitBreaker|myApi', 'state', 'open'],
                ['CircuitBreaker|myApi', 'failureCount', 5],
                ['CircuitBreaker|myApi', 'lastFailure', $now],
                ['CircuitBreaker|myApi', 'resetTimeout', 600]
            );

        $this->client->expects($this->once())
            ->method('expire')
            ->with('CircuitBreaker|myApi', 600);

        $this->storage->saveCircuit($circuit);
    }

    public function testResetCircuit(): void
    {
        $this->client->expects($this->once())
            ->method('del')
            ->with('CircuitBreaker|myApi');

        $this->storage->resetCircuit('myApi');
    }
}
