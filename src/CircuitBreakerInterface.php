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

interface CircuitBreakerInterface
{
    public function getName(): string;

    public function getState(): State;

    public function isAvailable(): bool;

    public function call(callable $closure): mixed;

    public function success(): void;

    public function failure(): void;
}
