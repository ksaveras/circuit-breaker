<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker\Event;

use Ksaveras\CircuitBreaker\CircuitBreaker;
use Symfony\Contracts\EventDispatcher\Event;

class StateChangeEvent extends Event
{
    /**
     * @var CircuitBreaker
     */
    private $circuitBreaker;

    /**
     * @var string
     */
    private $oldState;

    /**
     * @var string
     */
    private $newState;

    public function __construct(CircuitBreaker $circuitBreaker, string $oldState, string $newState)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->oldState = $oldState;
        $this->newState = $newState;
    }

    public function getCircuitBreaker(): CircuitBreaker
    {
        return $this->circuitBreaker;
    }

    public function getName(): string
    {
        return $this->circuitBreaker->getName();
    }

    public function getOldState(): string
    {
        return $this->oldState;
    }

    public function getNewState(): string
    {
        return $this->newState;
    }
}
