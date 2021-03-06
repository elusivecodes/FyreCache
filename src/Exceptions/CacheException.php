<?php
declare(strict_types=1);

namespace Fyre\Cache\Exceptions;

use
    RuntimeException;

/**
 * CacheException
 */
class CacheException extends RuntimeException
{

    public static function forAuthFailed()
    {
        return new static('Cache handler authentication failed');
    }

    public static function forConfigExists(string $key)
    {
        return new static('Cache handler config already exists: '.$key);
    }

    public static function forConnectionError(string $message = '')
    {
        return new static('Cache handler connection error: '.$message);
    }

    public static function forConnectionFailed()
    {
        return new static('Cache handler connection failed');
    }

    public static function forInvalidClass(string $className = '')
    {
        return new static('Cache handler class not found: '.$className);
    }

    public static function forInvalidConfig(string $key)
    {
        return new static('Cache handler invalid config: '.$key);
    }

    public static function forInvalidDatabase(string $database)
    {
        return new static('Cache handler invalid database: '.$database);
    }

    public static function forInvalidKey(string $key)
    {
        return new static('Cache handler invalid key: '.$key);
    }

}
