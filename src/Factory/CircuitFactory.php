<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Factory;

use Ksaveras\CircuitBreaker\Circuit;

final class CircuitFactory
{
    /**
     * @var int
     */
    private $resetTimeout;

    public function __construct(int $resetTimeout = 60)
    {
        $this->resetTimeout = $resetTimeout;
    }

    public function create(string $name): Circuit
    {
        $circuit = new Circuit($name);
        $circuit->setResetTimeout($this->resetTimeout);

        return $circuit;
    }
}
