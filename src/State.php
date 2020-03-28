<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker;

/**
 * @codeCoverageIgnore
 */
final class State
{
    public const OPEN = 'open';
    public const HALF_OPEN = 'half-open';
    public const CLOSED = 'closed';

    private function __construct()
    {
    }
}
