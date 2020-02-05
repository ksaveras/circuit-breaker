<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Tests\Storage;

use Ksaveras\CircuitBreaker\Storage\PhpArray;
use PHPUnit\Framework\TestCase;

class PhpArrayTest extends TestCase
{
    public function testStorage(): void
    {
        $storage = new PhpArray();

        $circuit = $storage->getCircuit('demo1');

        $this->assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure();
        $storage->saveCircuit($circuit);

        $circuit = $storage->getCircuit('demo1');

        $this->assertEquals(1, $circuit->getFailureCount());
    }
}
