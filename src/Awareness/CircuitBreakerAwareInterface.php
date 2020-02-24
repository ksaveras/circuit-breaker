<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker\Awareness;

use Ksaveras\CircuitBreaker\CircuitBreaker;

/**
 * Interface CircuitBreakerAwareInterface.
 */
interface CircuitBreakerAwareInterface
{
    public function getCircuitBreaker(): CircuitBreaker;
}
