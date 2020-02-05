<?php

declare(strict_types=1);

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
