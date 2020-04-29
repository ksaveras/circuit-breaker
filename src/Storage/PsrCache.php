<?php declare(strict_types=1);
/*
 * This file is part of ksaveras/circuit-breaker-bundle.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\CircuitBreaker\Storage;

use Ksaveras\CircuitBreaker\Circuit;
use Psr\Cache\CacheItemPoolInterface;

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

    public function resetCircuit(string $name): void
    {
        $this->cache->deleteItem(static::storageKey($name));
    }
}
