<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker;

/**
 * Class Circuit.
 */
class Circuit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $state = State::CLOSED;

    /**
     * @var int
     */
    private $failureCount = 0;

    /**
     * @var int|null
     */
    private $lastFailure;

    /**
     * @var int|null
     */
    private $resetTimeout;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function fromArray(array $data): self
    {
        $circuit = new self($data['name']);

        $circuit->setState($data['state'] ?? State::CLOSED);
        $circuit->setFailureCount($data['failureCount'] ?? 0);
        $circuit->setLastFailure($data['lastFailure'] ?? null);
        $circuit->setResetTimeout($data['resetTimeout'] ?? null);

        return $circuit;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function setFailureCount(int $failureCount): self
    {
        $this->failureCount = $failureCount;

        return $this;
    }

    public function getLastFailure(): ?int
    {
        return $this->lastFailure;
    }

    public function setLastFailure(?int $lastFailure): self
    {
        $this->lastFailure = $lastFailure;

        return $this;
    }

    public function increaseFailure(): void
    {
        ++$this->failureCount;
        $this->lastFailure = time();
    }

    public function getResetTimeout(): ?int
    {
        return $this->resetTimeout;
    }

    public function setResetTimeout(?int $resetTimeout): self
    {
        $this->resetTimeout = $resetTimeout;

        return $this;
    }

    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailure = null;
        $this->resetTimeout = null;
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'state' => $this->state,
            'failureCount' => $this->failureCount,
            'lastFailure' => $this->lastFailure,
            'resetTimeout' => $this->resetTimeout,
        ];
    }
}
