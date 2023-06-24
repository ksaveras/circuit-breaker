<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests;

use Ksaveras\CircuitBreaker\CircuitBreaker;
use Ksaveras\CircuitBreaker\HeaderPolicy\PolicyChain;
use Ksaveras\CircuitBreaker\HeaderPolicy\RateLimitPolicy;
use Ksaveras\CircuitBreaker\HeaderPolicy\RetryAfterPolicy;
use Ksaveras\CircuitBreaker\Policy\ConstantRetryPolicy;
use Ksaveras\CircuitBreaker\Storage\InMemoryStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class CircuitBreakerHttpHeadersTest extends TestCase
{
    private InMemoryStorage $storage;

    private CircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new InMemoryStorage();

        $this->circuitBreaker = new CircuitBreaker(
            'demo',
            2,
            new ConstantRetryPolicy(50),
            $this->storage,
            new PolicyChain([
                new RetryAfterPolicy(),
                new RateLimitPolicy(),
            ]),
        );
    }

    /**
     * @param array<string, list<string>> $headersMap
     */
    #[DataProvider('provideResponseHeaders')]
    public function testRequestFailure(array $headersMap): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeader')->willReturnCallback(
            static fn (string $name): array => $headersMap[$name] ?? []
        );

        $this->circuitBreaker->recordRequestFailure($response);

        self::assertTrue($this->circuitBreaker->isOpen());

        $circuit = $this->storage->fetch($this->circuitBreaker->getName());
        self::assertNotNull($circuit);
        self::assertEquals(1, $circuit->getFailureCount());
        self::assertEquals(1136214245, $circuit->getResetTimeout());
    }

    /**
     * @return iterable<string, list<array<string, list<string>>>>
     */
    public static function provideResponseHeaders(): iterable
    {
        return [
            'retry after' => [
                [
                    'Retry-After' => ['Mon, 02 Jan 2006 15:04:05 GMT'],
                ],
            ],
            'rate limit reset' => [
                [
                    'X-RateLimit-Reset' => ['Mon, 02 Jan 2006 15:04:05 GMT'],
                    'X-RateLimit-Remaining' => ['0'],
                ],
            ],
        ];
    }
}
