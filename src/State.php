<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker;

/**
 * Class State.
 *
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
