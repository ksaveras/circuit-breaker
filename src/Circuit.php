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

use Ksaveras\CircuitBreaker\Exception\CircuitBreakerException;
use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;

/**
 * @phpstan-type CircuitArray array{
 *      name: string,
 *      failureCount?: int,
 *      failureThreshold?: int,
 *      lastFailure?: int|null,
 *      resetTimeout?: int
 * }
 */
final class Circuit
{
    private string $name;

    private int $failureCount;

    private ?int $lastFailure;

    private int $failureThreshold;

    private int $resetTimeout;

    public function __construct(
        string $name,
        int $failureThreshold = 5,
        int $failureCount = 0,
        int $lastFailure = null,
        int $resetTimeout = 60
    ) {
        $this->name = $name;
        $this->failureThreshold = $failureThreshold;
        $this->failureCount = $failureCount;
        $this->lastFailure = $lastFailure;
        $this->resetTimeout = $resetTimeout;
    }

    /**
     * @param array<string, string|int|null> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['name']) || !\is_string($data['name'])) {
            throw new CircuitBreakerException('Missing required data field "name"');
        }

        $failureCount = (int) ($data['failureCount'] ?? 0);
        $failureThreshold = (int) ($data['failureThreshold'] ?? 5);
        $lastFailure = isset($data['lastFailure']) ? (int) $data['lastFailure'] : null;
        $resetTimeout = (int) ($data['resetTimeout'] ?? 60);

        return new self($data['name'], $failureThreshold, $failureCount, $lastFailure, $resetTimeout);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getState(): string
    {
        if ($this->failureCount < $this->failureThreshold) {
            return State::CLOSED;
        }

        return (time() - $this->getLastFailure()) > $this->getResetTimeout() ? State::HALF_OPEN : State::OPEN;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function increaseFailure(RetryPolicyInterface $policy): void
    {
        ++$this->failureCount;
        $this->lastFailure = time();
        $this->resetTimeout = $policy->calculate($this);
    }

    public function getFailureThreshold(): int
    {
        return $this->failureThreshold;
    }

    public function getLastFailure(): ?int
    {
        return $this->lastFailure;
    }

    public function getResetTimeout(): int
    {
        return $this->resetTimeout;
    }

    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailure = null;
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'failureCount' => $this->failureCount,
            'failureThreshold' => $this->failureThreshold,
            'lastFailure' => $this->lastFailure,
            'resetTimeout' => $this->resetTimeout,
        ];
    }
}
