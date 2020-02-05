<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;

/**
 * Class PhpArray.
 */
class PhpArray extends AbstractStorage
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

    public function resetCircuit(string $name): void
    {
        unset($this->circuits[$name]);
    }
}
