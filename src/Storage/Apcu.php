<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;

/**
 * Class Apcu.
 */
class Apcu extends AbstractStorage
{
    public function getCircuit(string $name): Circuit
    {
        $data = apcu_fetch(static::storageKey($name));
        if (false !== $data) {
            return Circuit::fromArray($data);
        }

        return new Circuit($name);
    }

    public function saveCircuit(Circuit $circuit): void
    {
        apcu_store(static::storageKey($circuit->getName()), $circuit->toArray());
    }

    public function resetCircuit(string $name): void
    {
        apcu_delete(static::storageKey($name));
    }
}
