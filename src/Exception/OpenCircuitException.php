<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ksaveras\CircuitBreaker\Exception;

/**
 * Class OpenCircuitException.
 */
class OpenCircuitException extends CircuitBreakerException
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $message = $message ?? 'Open Circuit';

        parent::__construct($message, $code, $previous);
    }
}
