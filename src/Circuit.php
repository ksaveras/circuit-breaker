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

final class Circuit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $failureCount;

    /**
     * @var int|null
     */
    private $lastFailure;

    /**
     * @var int
     */
    private $failureThreshold;

    /**
     * @var int
     */
    private $resetTimeout = 60;

    public function __construct(string $name, int $failureCount = 0, ?int $failureThreshold = null)
    {
        $this->name = $name;
        $this->failureCount = $failureCount;
        $this->failureThreshold = $failureThreshold ?? 5;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['name'])) {
            throw new CircuitBreakerException('Missing required data field "name"');
        }

        $failureCount = (int) ($data['failureCount'] ?? 0);
        $failureThreshold = isset($data['failureThreshold']) ? (int) $data['failureThreshold'] : null;

        $circuit = new self($data['name'], $failureCount, $failureThreshold);

        $circuit->setLastFailure($data['lastFailure'] ?? null);

        if (isset($data['resetTimeout'])) {
            $circuit->setResetTimeout($data['resetTimeout']);
        }

        return $circuit;
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

    public function setLastFailure(?int $lastFailure): self
    {
        $this->lastFailure = $lastFailure;

        return $this;
    }

    public function getResetTimeout(): int
    {
        return $this->resetTimeout;
    }

    public function setResetTimeout(int $resetTimeout): self
    {
        $this->resetTimeout = $resetTimeout;

        return $this;
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
