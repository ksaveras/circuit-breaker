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
use Predis\ClientInterface;

class Redis extends AbstractStorage
{
    /**
     * @var ClientInterface
     */
    protected $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCircuit(string $name): Circuit
    {
        $data = $this->client->hgetall(static::storageKey($name));

        return Circuit::fromArray($data);
    }

    public function saveCircuit(Circuit $circuit): void
    {
        $data = $circuit->toArray();
        $name = static::storageKey($circuit->getName());
        foreach ($data as $key => $datum) {
            $this->client->hset($name, $key, $datum);
        }

        $this->client->expire($name, $circuit->getResetTimeout());
    }

    public function resetCircuit(string $name): void
    {
        $this->client->del([static::storageKey($name)]);
    }
}
