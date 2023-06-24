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

final class RetryAfterPolicy implements HttpHeaderPolicy
{
    use ClockAwareTrait;

    public function fromResponse(ResponseInterface $response): ?\DateTimeImmutable
    {
        if (false === $value = current($response->getHeader('Retry-After'))) {
            return null;
        }

        if (is_numeric($value)) {
            return $this->now()
                ->setTimezone(new \DateTimeZone('GMT'))
                ->add(new \DateInterval('PT'.$value.'S'));
        }

        if (false === $retryAfter = \DateTimeImmutable::createFromFormat(\DATE_RFC7231, $value)) {
            return null;
        }

        return $retryAfter;
    }
}
