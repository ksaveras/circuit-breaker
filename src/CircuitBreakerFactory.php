<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker;

use Ksaveras\CircuitBreaker\Factory\CircuitFactory;
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\ExponentialRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\LinearRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CircuitBreakerFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var StorageInterface
     */
    private $storage;

    public function __construct(array $config, StorageInterface $storage)
    {
        $options = new OptionsResolver();
        self::configureOptions($options);

        $this->config = $options->resolve($config);
        $this->storage = $storage;
    }

    protected static function configureOptions(OptionsResolver $options): void
    {
        $options->setRequired(['failure_threshold', 'retry_policy', 'reset_timeout_ms', 'maximum_timeout_ms']);
        $options->setAllowedTypes('failure_threshold', 'int');
        $options->setAllowedTypes('reset_timeout_ms', 'int');
        $options->setAllowedTypes('maximum_timeout_ms', 'int');
        $options->setAllowedValues('retry_policy', ['constant', 'exponential', 'linear']);

        $options->setDefaults([
            'failure_threshold' => 5,
            'retry_policy' => 'exponential',
            'reset_timeout_ms' => 600,
            'maximum_timeout_ms' => 86400,
        ]);
    }

    public function create(string $name): CircuitBreaker
    {
        return new CircuitBreaker($name, $this->config['failure_threshold'], $this->createRetryPolicy(), $this->storage);
    }

    private function createRetryPolicy(): RetryPolicyInterface
    {
        switch ($this->config['retry_policy']) {
            case 'constant':
                return new ConstantRetryPolicy($this->config['reset_timeout_ms']);
            case 'exponential':
                return new ExponentialRetryPolicy($this->config['reset_timeout_ms'], $this->config['maximum_timeout_ms']);
            case 'linear':
                return new LinearRetryPolicy($this->config['reset_timeout_ms']);
            default:
                throw new \LogicException(sprintf('Retry policy "%s" does not exists, it must be one of "constant", "exponential", "linear".', $this->config['retry_policy']));
        }
    }
}
