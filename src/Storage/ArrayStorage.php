<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;

/**
 * Class ArrayStorage.
 */
class ArrayStorage extends AbstractStorage
{
    /**
     * @var Circuit[]|array
     */
    private $circuits = [];

    public function getCircuit(string $name): Circuit
    {
        if (!isset($this->circuits[$name])) {
            $this->circuits[$name] = new Circuit($name);
        }

        return $this->circuits[$name];
    }

    public function saveCircuit(Circuit $circuit): void
    {
    }
}
