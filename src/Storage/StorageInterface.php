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

interface StorageInterface
{
    public function getCircuit(string $name): Circuit;

    public function saveCircuit(Circuit $circuit): void;

    public function resetCircuit(string $name): void;

    public function increaseFailure(string $name): void;
}
