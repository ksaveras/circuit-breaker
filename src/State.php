<?php

namespace Ksaveras\CircuitBreaker;

final class State
{
    public const OPEN = 'open';
    public const HALF_OPEN = 'half-open';
    public const CLOSED = 'closed';

    private function __construct()
    {
    }
}
