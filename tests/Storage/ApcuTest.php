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
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\Apcu;
use PHPUnit\Framework\TestCase;

class ApcuTest extends TestCase
{
    /**
     * @var Apcu
     */
    private $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new Apcu();
    }

    public function testSaveCircuit(): void
    {
        if (!\function_exists('apcu_store')) {
            $this->markTestSkipped();
        }

        $circuit = (new Circuit('demo'))
            ->setState(State::OPEN)
            ->setFailureCount(10)
            ->setLastFailure(1588146000)
            ->setResetTimeout(1588166000);

        $this->storage->saveCircuit($circuit);

        $circuit = $this->storage->getCircuit('demo');
        $this->assertEquals(State::OPEN, $circuit->getState());
        $this->assertEquals(10, $circuit->getFailureCount());
        $this->assertEquals(1588146000, $circuit->getLastFailure());
        $this->assertEquals(1588166000, $circuit->getResetTimeout());
    }

    public function testResetCircuit(): void
    {
        if (!\function_exists('apcu_store')) {
            $this->markTestSkipped();
        }

        $circuit = (new Circuit('demo'))
            ->setState(State::OPEN)
            ->setFailureCount(10)
            ->setLastFailure(1588146000)
            ->setResetTimeout(1588166000);

        $this->storage->saveCircuit($circuit);
        $this->storage->resetCircuit('demo');

        $circuit = $this->storage->getCircuit('demo');
        $this->assertEquals(State::CLOSED, $circuit->getState());
        $this->assertEquals(0, $circuit->getFailureCount());
        $this->assertNull($circuit->getLastFailure());
        $this->assertNull($circuit->getResetTimeout());
    }
}
