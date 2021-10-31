<?php
declare(strict_types=1);

namespace Fyre\Cache;

use
    Fyre\Cache\Exceptions\CacheException;

use function
    array_key_exists,
    class_exists;

/**
 * Cache
 */
abstract class Cache
{

    protected static array $config = [];

    protected static array $instances = [];

    /**
     * Clear instances.
     */
    public static function clear(): void
    {
        static::$instances = [];
    }

    /**
     * Load a handler.
     * @param array $config Options for the handler.
     * @return Cacher The handler.
     * @throws CacheException if the handler is invalid.
     */
    public static function load(array $config = []): Cacher
    {
        if (!array_key_exists('className', $config)) {
            throw CacheException::forInvalidClass();
        }

        if (!class_exists($config['className'], true)) {
            throw CacheException::forInvalidClass($config['className']);
        }

        return new $config['className']($config);
    }

    /**
     * Set handler config.
     * @param string $key The config key.
     * @param array $config The config options.
     */
    public static function setConfig(string $key, array $config): void
    {
        static::$config[$key] = $config;
    }

    /**
     * Load a shared handler instance.
     * @param string $key The config key.
     * @return Cacher The handler.
     */
    public static function use(string $key = 'default'): Cacher
    {
        return static::$instances[$key] ??= static::load(static::$config[$key] ?? []);
    }

}
