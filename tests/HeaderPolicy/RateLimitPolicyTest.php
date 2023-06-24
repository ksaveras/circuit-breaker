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
use Ksaveras\CircuitBreaker\HeaderPolicy\RateLimitPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Clock\MockClock;

final class RateLimitPolicyTest extends TestCase
{
    private HttpHeaderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RateLimitPolicy();
        $this->policy->setClock(new MockClock('2006-01-02 15:04:05', 'GMT'));
    }

    /**
     * @param list<string> $limitReset
     * @param list<string> $remainingLimit
     */
    #[DataProvider('providesResponseHeaders')]
    public function testHeaders(array $limitReset, array $remainingLimit): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeader')
            ->willReturnMap([
                ['X-RateLimit-Reset', $limitReset],
                ['X-RateLimit-Remaining', $remainingLimit],
            ]);

        self::assertNull($this->policy->fromResponse($response));
    }

    /**
     * @return iterable<string, list<list<string>>>
     */
    public static function providesResponseHeaders(): iterable
    {
        return [
            'no headers' => [[], []],
            'reset header only' => [['600'], []],
            'remaining header only' => [[], ['10']],
            'remaining requests is 0' => [[], ['0']],
            'malformed reset header' => [['FooBar'], ['0']],
            'malformed remaining header' => [['600'], ['No Data']],
            'has remaining requests' => [['600'], ['1']],
        ];
    }

    public function testRateLimitResetInSeconds(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeader')
            ->willReturnMap([
                ['X-RateLimit-Reset', ['600']],
                ['X-RateLimit-Remaining', ['0']],
            ]);

        self::assertEquals(
            new \DateTimeImmutable('2006-01-02 15:14:05', new \DateTimeZone('GMT')),
            $this->policy->fromResponse($response),
        );
    }

    public function testRateLimitResetDateTime(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeader')
            ->willReturnMap([
                ['X-RateLimit-Reset', ['Fri, 23 Jun 2023 08:00:00 GMT']],
                ['X-RateLimit-Remaining', ['0']],
            ]);

        self::assertEquals(
            new \DateTimeImmutable('2023-06-23 08:00:00', new \DateTimeZone('GMT')),
            $this->policy->fromResponse($response),
        );
    }
}
