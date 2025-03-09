<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;
use Psr\Cache\CacheItemPoolInterface;

final class CacheStorage implements StorageInterface
{
    private readonly CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function save(Circuit $circuit): void
    {
        $item = $this->cache->getItem(sha1($circuit->getName()));
        $item->set($circuit);
        $item->expiresAfter($circuit->getResetTimeout());

        $this->cache->save($item);
    }

    public function fetch(string $name): ?Circuit
    {
        $item = $this->cache->getItem(sha1($name));
        $circuit = $item->get();
        if ($circuit instanceof Circuit) {
            return $circuit;
        }

        return null;
    }

    public function delete(string $name): void
    {
        $this->cache->deleteItem(sha1($name));
    }
}
