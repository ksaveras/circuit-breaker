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
use Ksaveras\CircuitBreaker\State;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @phpstan-type ConfigArray array{
 *      failure_threshold?: int,
 *      retry_policy?: array{
 *          type?: string,
 *          options?: array{reset_timeout?: int, maximum_timeout?: int}
 *      }
 * }
 */
final class CircuitBreakerFactoryTest extends TestCase
{
    /**
     * @dataProvider factoryConfigProvider
     *
     * @param ConfigArray $config
     */
    public function testCreate(array $config): void
    {
        $factory = new CircuitBreakerFactory($config, new InMemoryStorage());

        $circuitBreaker = $factory->create('name');

        self::assertEquals('name', $circuitBreaker->getName());
        self::assertEquals(State::CLOSED, $circuitBreaker->getState());
        self::assertTrue($circuitBreaker->isAvailable());
    }

    /**
     * @return array<string, array<int, ConfigArray>>
     */
    public function factoryConfigProvider(): iterable
    {
        return [
            'empty config' => [[]],
            'failure threshold' => [['failure_threshold' => 3]],
            'retry policy' => [['retry_policy' => ['options' => ['reset_timeout' => 4000]]]],
            'retry policy with maximum' => [
                ['retry_policy' => ['options' => ['reset_timeout' => 4000, 'maximum_timeout' => 50000]]],
            ],
            'constant retry policy' => [['retry_policy' => ['type' => 'constant']]],
            'exponential retry policy' => [['retry_policy' => ['type' => 'exponential']]],
            'linear retry policy' => [['retry_policy' => ['type' => 'linear']]],
        ];
    }

    public function testInvalidRetryPolicy(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "type" with value "nonexistent" is invalid. Accepted values are: "constant", "exponential", "linear".');

        $factory = new CircuitBreakerFactory(['retry_policy' => ['type' => 'nonexistent']], new InMemoryStorage());

        $factory->create('name');
    }
}
