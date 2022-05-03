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

use Ksaveras\CircuitBreaker\CircuitBreakerFactory;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class CircuitBreakerFactoryTest extends TestCase
{
    /**
     * @dataProvider factoryConfigProvider
     */
    public function testCreate(array $config): void
    {
        $factory = new CircuitBreakerFactory($config, new InMemoryStorage());

        $circuitBreaker = $factory->create('name');

        self::assertEquals('name', $circuitBreaker->getName());
        self::assertEquals('closed', $circuitBreaker->getState());
        self::assertTrue($circuitBreaker->isAvailable());
    }

    public function factoryConfigProvider(): iterable
    {
        yield [[]];

        yield [[
            'failure_threshold' => 3,
        ]];

        yield [[
            'reset_timeout' => 4000,
        ]];

        yield [[
            'reset_timeout' => 4000,
            'maximum_timeout' => 50000,
        ]];

        yield [[
            'retry_policy' => 'constant',
        ]];

        yield [[
            'retry_policy' => 'exponential',
        ]];

        yield [[
            'retry_policy' => 'linear',
        ]];
    }

    public function testInvalidRetryPolicy(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "retry_policy" with value "nonexistent" is invalid. Accepted values are: "constant", "exponential", "linear".');

        $factory = new CircuitBreakerFactory(['retry_policy' => 'nonexistent'], new InMemoryStorage());

        $factory->create('name');
    }
}
