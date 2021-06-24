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

class PsrCacheStorage extends AbstractStorage
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getCircuit(string $name): ?Circuit
    {
        $item = $this->cache->getItem(static::storageKey($name));
        if (!$item->isHit()) {
            return null;
        }

        return Circuit::fromArray($item->get());
    }

    public function saveCircuit(Circuit $circuit): void
    {
        $item = $this->cache->getItem(static::storageKey($circuit->getName()));
        $item->set($circuit->toArray());

        $this->cache->save($item);
    }

    public function resetCircuit(string $name): void
    {
        $this->cache->deleteItem(static::storageKey($name));
    }
}
