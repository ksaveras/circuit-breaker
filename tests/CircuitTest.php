<?php declare(strict_types=1);

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

        $this->assertEquals($expected, $circuit->toArray());
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
                'resetTimeout' => null,
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
        $this->assertNull($circuit->getResetTimeout());
    }
}
