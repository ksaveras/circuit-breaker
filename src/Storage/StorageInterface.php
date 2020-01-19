<?php

namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;

/**
 * Interface StorageInterface.
 */
interface StorageInterface
{
    public function getCircuit(string $name): Circuit;

    public function saveCircuit(Circuit $circuit);
}
