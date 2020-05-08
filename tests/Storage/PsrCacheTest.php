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
use Ksaveras\CircuitBreaker\Storage\AbstractStorage;
use Ksaveras\CircuitBreaker\Storage\PsrCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class PsrCacheTest extends TestCase
{
    public function testStorage(): void
    {
        $adapter = new ArrayAdapter();
        $storage = new PsrCache($adapter);

        $storage->saveCircuit(new Circuit('demo'));

        $circuit = $storage->getCircuit('demo');

        $this->assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure();
        $storage->saveCircuit($circuit);

        $circuit = $storage->getCircuit('demo');

        $this->assertEquals(1, $circuit->getFailureCount());
    }

    public function testResetCircuit(): void
    {
        $adapter = new ArrayAdapter();
        $storage = new PsrCache($adapter);

        $storage->saveCircuit(new Circuit('demo'));

        $this->assertTrue($adapter->hasItem(AbstractStorage::storageKey('demo')));

        $storage->resetCircuit('demo');

        $this->assertFalse($adapter->hasItem(AbstractStorage::storageKey('demo')));
    }
}
