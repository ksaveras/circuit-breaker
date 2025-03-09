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

use Ksaveras\CircuitBreaker\Policy\RetryPolicyInterface;

final class Circuit
{
    private readonly string $name;

    private int $failureCount;

    private ?float $lastFailure;

    private readonly int $failureThreshold;

    private int $resetTimeout;

    public function __construct(
        string $name,
        int $failureThreshold,
        int $resetTimeout,
        int $failureCount = 0,
        ?float $lastFailure = null,
    ) {
        $this->name = $name;
        $this->failureThreshold = $failureThreshold;
        $this->failureCount = $failureCount;
        $this->lastFailure = $lastFailure;
        $this->resetTimeout = $resetTimeout;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function increaseFailure(RetryPolicyInterface $policy): void
    {
        ++$this->failureCount;
        $this->lastFailure = microtime(true);
        $this->resetTimeout = $policy->calculate($this);
    }

    public function getFailureThreshold(): int
    {
        return $this->failureThreshold;
    }

    public function thresholdReached(): bool
    {
        return $this->failureCount < $this->failureThreshold;
    }

    public function getLastFailure(): ?float
    {
        return $this->lastFailure;
    }

    public function getResetTimeout(): int
    {
        return $this->resetTimeout;
    }

    public function getExpirationTime(): int
    {
        return time() + $this->resetTimeout;
    }

    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailure = null;
    }

    /**
     * @return non-empty-array<string, float|int|null>
     */
    public function __serialize(): array
    {
        return [
            $this->name => $this->lastFailure,
            pack('NN', $this->failureCount, $this->failureThreshold) => $this->resetTimeout,
        ];
    }

    /**
     * @param non-empty-array<string, float|int|null> $data
     */
    public function __unserialize(array $data): void
    {
        [$this->lastFailure, $this->resetTimeout] = array_values($data);
        [$this->name, $pack] = array_keys($data);
        ['a' => $this->failureCount, 'b' => $this->failureThreshold] = unpack('Na/Nb', $pack);
    }
}
