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
use Ksaveras\CircuitBreaker\Storage\PhpArray;
use PHPUnit\Framework\TestCase;

class PhpArrayTest extends TestCase
{
    public function testStorage(): void
    {
        $storage = new PhpArray();
        $policy = new ConstantRetryPolicy();

        $storage->saveCircuit(new Circuit('demo1'));

        $circuit = $storage->getCircuit('demo1');

        self::assertEquals(0, $circuit->getFailureCount());

        $circuit->increaseFailure($policy);
        $storage->saveCircuit($circuit);

        $circuit = $storage->getCircuit('demo1');

        self::assertEquals(1, $circuit->getFailureCount());
    }
}
