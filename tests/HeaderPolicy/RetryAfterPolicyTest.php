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
use Ksaveras\CircuitBreaker\HeaderPolicy\RetryAfterPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Clock\MockClock;

final class RetryAfterPolicyTest extends TestCase
{
    private HttpHeaderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RetryAfterPolicy();
        $this->policy->setClock(new MockClock('2006-01-02 15:04:05', 'GMT'));
    }

    /**
     * @param list<string> $headerValues
     */
    #[DataProvider('providesResponseHeaders')]
    public function testHeaders(array $headerValues): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getHeader')
            ->with('Retry-After')
            ->willReturn($headerValues);

        self::assertNull($this->policy->fromResponse($response));
    }

    /**
     * @return iterable<string, list<list<string>>>
     */
    public static function providesResponseHeaders(): iterable
    {
        return [
            'no headers' => [[]],
            'malformed value' => [['FooBar']],
        ];
    }

    public function testRetryAfterSeconds(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getHeader')
            ->with('Retry-After')
            ->willReturn(['600']);

        self::assertEquals(
            new \DateTimeImmutable('2006-01-02 15:14:05', new \DateTimeZone('GMT')),
            $this->policy->fromResponse($response),
        );
    }

    public function testRetryAfterDateTime(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getHeader')
            ->with('Retry-After')
            ->willReturn([
                'Fri, 23 Jun 2023 08:00:00 GMT',
            ]);

        self::assertEquals(
            new \DateTimeImmutable('2023-06-23 08:00:00', new \DateTimeZone('GMT')),
            $this->policy->fromResponse($response),
        );
    }
}
