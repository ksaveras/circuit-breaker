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

final class InMemoryStorage implements StorageInterface
{
    /**
     * @var array<string, array{0: int, 1: string}>
     */
    private array $circuits = [];

    public function save(Circuit $circuit): void
    {
        $this->circuits[$circuit->getName()] = [$circuit->getExpirationTime(), serialize($circuit)];
    }

    public function fetch(string $name): ?Circuit
    {
        if (!isset($this->circuits[$name])) {
            return null;
        }

        [$expiresAt, $circuitState] = $this->circuits[$name];
        if ($expiresAt <= microtime(true)) {
            unset($this->circuits[$name]);

            return null;
        }

        $circuit = unserialize($circuitState, ['allowed_classes' => [Circuit::class]]);
        if ($circuit instanceof Circuit) {
            return $circuit;
        }

        return null;
    }

    public function delete(string $name): void
    {
        unset($this->circuits[$name]);
    }
}
