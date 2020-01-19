<?php

declare(strict_types=1);

namespace Ksaveras\CircuitBreaker\Storage;

/**
 * Class AbstractStorage.
 */
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
}
