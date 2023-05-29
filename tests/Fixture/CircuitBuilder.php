<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\Fixture;

use Ksaveras\CircuitBreaker\Circuit;

final class CircuitBuilder
{
    private string $name = 'CB item';

    private int $failureCount = 3;

    private ?float $lastFailure = null;

    private int $failureThreshold = 2;

    private int $resetTimeout = 120;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public function withName(string $name): self
    {
        $builder = clone $this;
        $builder->name = $name;

        return $builder;
    }

    public function withFailureCount(int $failureCount): self
    {
        $builder = clone $this;
        $builder->failureCount = $failureCount;

        return $builder;
    }

    public function withLastFailure(float $lastFailure): self
    {
        $builder = clone $this;
        $builder->lastFailure = $lastFailure;

        return $builder;
    }

    public function withFailureThreshold(int $failureThreshold): self
    {
        $builder = clone $this;
        $builder->failureThreshold = $failureThreshold;

        return $builder;
    }

    public function withResetTimeout(int $resetTimeout): self
    {
        $builder = clone $this;
        $builder->resetTimeout = $resetTimeout;

        return $builder;
    }

    public function build(): Circuit
    {
        return new Circuit(
            $this->name,
            $this->failureThreshold,
            $this->failureCount,
            $this->lastFailure,
            $this->resetTimeout
        );
    }
}
