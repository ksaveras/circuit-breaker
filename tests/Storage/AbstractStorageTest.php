<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Tests\Storage;

use Ksaveras\CircuitBreaker\Storage\AbstractStorage;
use PHPUnit\Framework\TestCase;

class AbstractStorageTest extends TestCase
{
    /**
     * @dataProvider storageKeyDataProvider
     */
    public function testValidateKey(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AbstractStorage::validateKey($name);
    }

    public function storageKeyDataProvider(): \Generator
    {
        yield ['{demo}'];
        yield ['API(prod)'];
        yield ['master/slave'];
        yield ['master\\slave'];
        yield ['master@slave'];
    }
}
