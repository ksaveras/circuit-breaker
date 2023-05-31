<?php declare(strict_types=1);

namespace Ksaveras\CircuitBreaker;

interface CircuitBreakerInterface
{
    public function getName(): string;

    public function getState(): State;

    public function isAvailable(): bool;

    public function call(callable $closure): mixed;

    public function success(): void;

    public function failure(): void;
}
