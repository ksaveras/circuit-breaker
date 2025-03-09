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
use Symfony\Component\Clock\ClockAwareTrait;

final readonly class RateLimitPolicy implements HttpHeaderPolicy
{
    use ClockAwareTrait;

    public function fromResponse(ResponseInterface $response): ?\DateTimeImmutable
    {
        if (false === $reset = current($response->getHeader('X-RateLimit-Reset'))) {
            return null;
        }

        if (false === $remaining = current($response->getHeader('X-RateLimit-Remaining'))) {
            return null;
        }

        if (!is_numeric($remaining)) {
            return null;
        }

        if (0 < (int) $remaining) {
            return null;
        }

        if (is_numeric($reset)) {
            return $this->now()
                ->setTimezone(new \DateTimeZone('GMT'))
                ->add(new \DateInterval('PT'.$reset.'S'));
        }

        if (false === $retryAfter = \DateTimeImmutable::createFromFormat(\DATE_RFC7231, $reset)) {
            return null;
        }

        return $retryAfter;
    }
}
