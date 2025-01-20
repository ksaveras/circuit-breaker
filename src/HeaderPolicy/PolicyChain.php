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

final class PolicyChain implements HttpHeaderPolicy
{
    /**
     * @param iterable<HttpHeaderPolicy> $policies
     */
    public function __construct(
        private readonly iterable $policies,
    ) {
    }

    public function fromResponse(ResponseInterface $response): ?\DateTimeImmutable
    {
        foreach ($this->policies as $policy) {
            if (null !== $result = $policy->fromResponse($response)) {
                return $result;
            }
        }

        return null;
    }
}
