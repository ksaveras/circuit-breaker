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

abstract class AbstractStorage implements StorageInterface
{
    public const STORAGE_PREFIX = 'CircuitBreaker';
    private const RESERVED_CHARACTERS = '{}()/\@:';

    public static function storageKey(string $name): string
    {
        return static::STORAGE_PREFIX.'|'.$name;
    }

    public static function validateKey(string $key): string
    {
        if (false !== strpbrk($key, self::RESERVED_CHARACTERS)) {
            throw new \InvalidArgumentException(sprintf('Storage key "%s" contains reserved characters %s', $key, static::RESERVED_CHARACTERS));
        }

        return $key;
    }

    public function increaseFailure(string $name): void
    {
        $circuit = $this->getCircuit($name);
        $circuit->increaseFailure();
        $this->saveCircuit($circuit);
    }
}
