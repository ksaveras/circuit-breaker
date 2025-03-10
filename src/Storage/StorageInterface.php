<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;

interface StorageInterface
{
    public function save(Circuit $circuit): void;

    public function fetch(string $name): ?Circuit;

    public function delete(string $name): void;

    public function clear(): void;

    /**
     * @return Circuit[]
     */
    public function getAll(): array;

    public function cleanup(): void;
}
