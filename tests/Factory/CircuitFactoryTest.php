<?php declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Tests\Factory;

use Ksaveras\CircuitBreaker\Factory\CircuitFactory;
use PHPUnit\Framework\TestCase;

class CircuitFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new CircuitFactory(600);

        $circuit = $factory->create('demo');

        $this->assertEquals(600, $circuit->getResetTimeout());
    }
}
