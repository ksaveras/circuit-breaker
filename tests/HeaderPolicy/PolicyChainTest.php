<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\HeaderPolicy;

use Ksaveras\CircuitBreaker\HeaderPolicy\HttpHeaderPolicy;
use Ksaveras\CircuitBreaker\HeaderPolicy\PolicyChain;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class PolicyChainTest extends TestCase
{
    public function testEmptyChain(): void
    {
        $policy = new PolicyChain([]);

        self::assertNull($policy->fromResponse($this->createMock(ResponseInterface::class)));
    }

    public function testReturnsFirstNotNullValue(): void
    {
        $policy1 = $this->createMock(HttpHeaderPolicy::class);
        $policy1->expects(self::once())
            ->method('fromResponse')
            ->willReturn(null);

        $policy2 = $this->createMock(HttpHeaderPolicy::class);
        $policy2->expects(self::once())
            ->method('fromResponse')
            ->willReturn(new \DateTimeImmutable('2006-01-02 15:04:05 GMT'));

        $policy = new PolicyChain([$policy1, $policy2]);

        self::assertEquals(
            new \DateTimeImmutable('2006-01-02 15:04:05 GMT'),
            $policy->fromResponse($this->createMock(ResponseInterface::class))
        );
    }
}
