<?php

namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class PsrCache.
 */
class PsrCache extends AbstractStorage
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getCircuit(string $name): Circuit
    {
        $storageKey = static::storageKey($name);

        $item = $this->cache->getItem($storageKey);
        if (!$item->isHit()) {
            $item->set(new Circuit($name));
            $this->cache->save($item);
        }

        return $item->get();
    }

    public function saveCircuit(Circuit $circuit): void
    {
        $storageKey = static::storageKey($circuit->getName());

        $item = $this->cache->getItem($storageKey);
        $item->set($circuit);

        $this->cache->save($item);
    }
}
