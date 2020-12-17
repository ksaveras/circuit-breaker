<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Factory;

use Ksaveras\CircuitBreaker\Circuit;

final class CircuitFactory implements CircuitFactoryInterface
{
    /**
     * @var int
     */
    private $resetTimeout;

    /**
     * @var int
     */
    private $failureThreshold;

    public function __construct(int $failureThreshold = 5, int $resetTimeout = 60)
    {
        $this->resetTimeout = $resetTimeout;
        $this->failureThreshold = $failureThreshold;
    }

    public function create(string $name): Circuit
    {
        $circuit = new Circuit($name, 0, $this->failureThreshold);
        $circuit->setResetTimeout($this->resetTimeout);

        return $circuit;
    }
}
