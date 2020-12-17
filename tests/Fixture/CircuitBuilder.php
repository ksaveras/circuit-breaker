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
use Ksaveras\CircuitBreaker\State;

class CircuitBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $state;

    /**
     * @var int
     */
    private $failureCount;

    /**
     * @var int
     */
    private $lastFailure;

    /**
     * @var int
     */
    private $failureThreshold;

    /**
     * @var int
     */
    private $resetTimeout;

    private function __construct()
    {
        $this->name = 'demo';
        $this->state = State::OPEN;
        $this->failureCount = 3;
        $this->lastFailure = time();
        $this->failureThreshold = 2;
        $this->resetTimeout = 120;
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

    public function withState(string $state): self
    {
        $this->state = $state;

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
                'failureCount' => $this->failureCount,
                'failureThreshold' => $this->failureThreshold,
                'state' => $this->state,
                'lastFailure' => $this->lastFailure,
                'resetTimeout' => $this->resetTimeout,
            ]
        );
    }
}
