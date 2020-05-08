<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
