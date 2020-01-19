<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Tests\Storage;

use Ksaveras\CircuitBreaker\Storage\PsrCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class PsrCacheTest extends TestCase
{
    public function testStorage(): void
    {
        $adapter = new ArrayAdapter();

        $storage = new PsrCache($adapter);

        $circuit = $storage->getCircuit('demo');

        $this->assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure();
        $storage->saveCircuit($circuit);

        $circuit = $storage->getCircuit('demo');

        $this->assertEquals(1, $circuit->getFailureCount());
    }
}
