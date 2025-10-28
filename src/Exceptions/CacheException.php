<?php
declare(strict_types=1);

namespace Fyre\Cache\Exceptions;

use RuntimeException;

/**
 * CacheException
 */
class CacheException extends RuntimeException implements \Psr\SimpleCache\CacheException
{
    public static function forAuthFailed(): static
    {
        return new static('Cache handler authentication failed');
    }

    public static function forConfigExists(string $key): static
    {
        return new static('Cache handler config already exists: '.$key);
    }

    public static function forConnectionError(string $message = ''): static
    {
        return new static('Cache handler connection error: '.$message);
    }

    public static function forConnectionFailed(): static
    {
        return new static('Cache handler connection failed');
    }

    public static function forInvalidClass(string $className = ''): static
    {
        return new static('Cache handler class not found: '.$className);
    }

    public static function forInvalidDatabase(string $database): static
    {
        return new static('Cache handler invalid database: '.$database);
    }

    public static function forInvalidKey(string $key): static
    {
        return new static('Cache handler invalid key: '.$key);
    }
}
