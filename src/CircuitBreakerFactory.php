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

use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\ExponentialRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\LinearRetryPolicy;
use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;
use Ksaveras\CircuitBreaker\Storage\StorageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CircuitBreakerFactory
{
    private array $config;

    private StorageInterface $storage;

    public function __construct(array $config, StorageInterface $storage)
    {
        $options = new OptionsResolver();
        self::configureOptions($options);

        $this->config = $options->resolve($config);
        $this->storage = $storage;
    }

    protected static function configureOptions(OptionsResolver $options): void
    {
        $options->setRequired(['failure_threshold', 'retry_policy']);
        $options->setAllowedTypes('failure_threshold', 'int');

        $options->setDefaults([
            'failure_threshold' => 5,
            'retry_policy' => function (OptionsResolver $policyResolver) {
                $policyResolver->setDefaults([
                    'type' => 'exponential',
                    'options' => function (OptionsResolver $optionsResolver) {
                        $optionsResolver->setDefaults([
                            'reset_timeout' => 60,
                            'maximum_timeout' => 86400,
                        ]);
                        $optionsResolver->setAllowedTypes('reset_timeout', 'int');
                        $optionsResolver->setAllowedTypes('maximum_timeout', 'int');
                    },
                ]);
                $policyResolver->setAllowedValues('type', ['constant', 'exponential', 'linear']);
            },
        ]);
    }

    public function create(string $name): CircuitBreaker
    {
        return new CircuitBreaker($name, $this->config['failure_threshold'], $this->createRetryPolicy(), $this->storage);
    }

    private function createRetryPolicy(): RetryPolicyInterface
    {
        $options = $this->config['retry_policy']['options'];

        switch ($this->config['retry_policy']['type']) {
            case 'constant':
                return new ConstantRetryPolicy($options['reset_timeout']);
            case 'exponential':
                return new ExponentialRetryPolicy($options['reset_timeout'], $options['maximum_timeout']);
            case 'linear':
                return new LinearRetryPolicy($options['reset_timeout']);
            default:
                throw new \LogicException(sprintf('Retry policy "%s" does not exists, it must be one of "constant", "exponential", "linear".', $this->config['retry_policy']));
        }
    }
}
