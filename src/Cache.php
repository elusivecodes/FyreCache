<?php
declare(strict_types=1);

namespace Fyre\Cache;

use
    Fyre\Cache\Exceptions\CacheException;

use function
    array_key_exists,
    array_search,
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
     * Get the key for a cacher instance.
     * @param Cacher $cacher The Cacher.
     * @return string|null The Cacher key.
     */
    public static function getKey(Cacher $cacher): string|null
    {
        return array_search($cacher, $this->instances, true) ?: null;
    }

    /**
     * Load a handler.
     * @param array $options Options for the handler.
     * @return Cacher The handler.
     * @throws CacheException if the handler is invalid.
     */
    public static function load(array $options = []): Cacher
    {
        if (!array_key_exists('className', $options)) {
            throw CacheException::forInvalidClass();
        }

        if (!class_exists($options['className'], true)) {
            throw CacheException::forInvalidClass($options['className']);
        }

        return new $options['className']($options);
    }

    /**
     * Set handler config.
     * @param string $key The config key.
     * @param array $options The config options.
     */
    public static function setConfig(string $key, array $options): void
    {
        static::$config[$key] = $options;
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
