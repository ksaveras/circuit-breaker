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

class CircuitBuilder
{
    private string $name = 'demo';

    private int $failureCount = 3;

    /**
     * @var int
     */
    private $lastFailure;

    private int $failureThreshold = 2;

    private int $resetTimeout = 120;

    private function __construct()
    {
        $this->lastFailure = time();
    }

    public static function builder(): self
    {
        return new self();
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withFailureCount(int $failureCount): self
    {
        $this->failureCount = $failureCount;

        return $this;
    }

    public function withLastFailure(int $lastFailure): self
    {
        $this->lastFailure = $lastFailure;

        return $this;
    }

    public function withFailureThreshold(int $failureThreshold): self
    {
        $this->failureThreshold = $failureThreshold;

        return $this;
    }

    public function withResetTimeout(int $resetTimeout): self
    {
        $this->resetTimeout = $resetTimeout;

        return $this;
    }

    public function build(): Circuit
    {
        return Circuit::fromArray(
            [
                'name' => $this->name,
                'failureThreshold' => $this->failureThreshold,
                'failureCount' => $this->failureCount,
                'lastFailure' => $this->lastFailure,
                'resetTimeout' => $this->resetTimeout,
            ]
        );
    }
}
