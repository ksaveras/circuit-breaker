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

use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\ApcuStorage;
use Ksaveras\CircuitBreaker\Tests\Fixture\CircuitBuilder;
use PHPUnit\Framework\TestCase;

class ApcuStorageTest extends TestCase
{
    private ApcuStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new ApcuStorage();
    }

    public function testSaveCircuit(): void
    {
        if (!\function_exists('apcu_store')) {
            self::markTestSkipped('Missing apcu extension');
        }
        if (!apcu_enabled()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $circuit = CircuitBuilder::builder()
            ->withName('demo')
            ->withFailureCount(10)
            ->build();

        $this->storage->saveCircuit($circuit);

        $circuitB = $this->storage->getCircuit('demo');
        self::assertNotNull($circuitB);
        self::assertEquals(State::OPEN, $circuitB->getState());
        self::assertEquals(10, $circuitB->getFailureCount());
        self::assertEquals($circuit->getLastFailure(), $circuitB->getLastFailure());
        self::assertEquals($circuit->getResetTimeout(), $circuitB->getResetTimeout());
    }

    public function testResetCircuit(): void
    {
        if (!\function_exists('apcu_store')) {
            self::markTestSkipped('Missing apcu extension');
        }

        $circuit = CircuitBuilder::builder()
            ->withName('demo')
            ->withFailureCount(10)
            ->build();

        $this->storage->saveCircuit($circuit);
        $this->storage->resetCircuit('demo');

        $circuit = $this->storage->getCircuit('demo');
        self::assertNull($circuit);
    }
}
