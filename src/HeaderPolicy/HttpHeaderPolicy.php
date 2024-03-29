<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\HeaderPolicy;

use Psr\Http\Message\ResponseInterface;

interface HttpHeaderPolicy
{
    public function fromResponse(ResponseInterface $response): ?\DateTimeImmutable;
}
