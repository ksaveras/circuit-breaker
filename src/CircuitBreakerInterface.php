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

use Psr\Http\Message\ResponseInterface;

interface CircuitBreakerInterface
{
    public function getName(): string;

    public function remainingDelay(): float;

    public function getFailureCount(): int;

    public function state(): State;

    public function isAvailable(): bool;

    public function isClosed(): bool;

    public function isHalfOpen(): bool;

    public function isOpen(): bool;

    public function call(callable $closure): mixed;

    public function recordSuccess(): void;

    public function recordFailure(): void;

    public function recordRequestFailure(ResponseInterface $response): void;
}
