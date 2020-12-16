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

class PhpArray extends AbstractStorage
{
    /**
     * @var Circuit[]|array
     */
    private $circuits = [];

    public function getCircuit(string $name): ?Circuit
    {
        if (!isset($this->circuits[$name])) {
            return null;
        }

        return $this->circuits[$name];
    }

    public function saveCircuit(Circuit $circuit): void
    {
        $this->circuits[$circuit->getName()] = $circuit;
    }

    public function resetCircuit(string $name): void
    {
        unset($this->circuits[$name]);
    }
}
