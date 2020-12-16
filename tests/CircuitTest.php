<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests;

use Ksaveras\CircuitBreaker\Circuit;
use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use PHPUnit\Framework\TestCase;

class CircuitTest extends TestCase
{
    /**
     * @dataProvider circuitDataProvider
     */
    public function testFromArray(array $data, array $expected): void
    {
        $circuit = Circuit::fromArray($data);

        self::assertEquals($expected, $circuit->toArray());
    }

    public function circuitDataProvider(): \Generator
    {
        yield [
            [
                'name' => 'demo',
            ],
            [
                'name' => 'demo',
                'state' => 'closed',
                'failureCount' => 0,
                'lastFailure' => null,
                'resetTimeout' => 60,
            ],
        ];

        $now = time();
        yield [
            [
                'name' => 'demo',
                'state' => 'open',
                'failureCount' => 10,
                'lastFailure' => $now,
                'resetTimeout' => 120,
            ],
            [
                'name' => 'demo',
                'state' => 'open',
                'failureCount' => 10,
                'lastFailure' => $now,
                'resetTimeout' => 120,
            ],
        ];
    }

    public function testWithEmptyArray(): void
    {
        $this->expectException(CircuitBreakerException::class);
        $this->expectExceptionMessage('Missing required data field "name"');

        Circuit::fromArray([]);
    }

    public function testReset(): void
    {
        $circuit = Circuit::fromArray(
            [
                'name' => 'demo',
                'state' => 'open',
                'failureCount' => 10,
                'lastFailure' => time(),
                'resetTimeout' => 120,
            ]
        );

        $circuit->reset();

        $this->assertEquals(0, $circuit->getFailureCount());
        $this->assertNull($circuit->getLastFailure());
        $this->assertEquals(120, $circuit->getResetTimeout());
    }
}
