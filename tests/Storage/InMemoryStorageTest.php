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
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\TestCase;

final class InMemoryStorageTest extends TestCase
{
    public function testStorage(): void
    {
        $storage = new InMemoryStorage();
        $policy = new ConstantRetryPolicy();

        $storage->save(new Circuit('demo1'));

        $circuit = $storage->fetch('demo1');
        self::assertNotNull($circuit);
        self::assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure($policy);
        $storage->save($circuit);

        $circuit = $storage->fetch('demo1');
        self::assertNotNull($circuit);
        self::assertEquals(1, $circuit->getFailureCount());

        $storage->delete('demo1');
        self::assertNull($storage->fetch('demo1'));
    }
}
