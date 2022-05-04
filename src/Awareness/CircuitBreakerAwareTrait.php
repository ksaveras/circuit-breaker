<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Awareness;

use Ksaveras\CircuitBreaker\CircuitBreaker;

trait CircuitBreakerAwareTrait
{
    private ?CircuitBreaker $circuitBreaker = null;

    public function getCircuitBreaker(): CircuitBreaker
    {
        if (null === $this->circuitBreaker) {
            throw new \RuntimeException('CircuitBreaker is not set');
        }

        return $this->circuitBreaker;
    }

    public function setCircuitBreaker(CircuitBreaker $circuitBreaker): self
    {
        $this->circuitBreaker = $circuitBreaker;

        return $this;
    }
}
