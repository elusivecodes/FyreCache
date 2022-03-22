<?php
declare(strict_types=1);

namespace Fyre\Cache;

use
    Fyre\Cache\Exceptions\CacheException;

use function
    array_key_exists,
    array_search,
    class_exists,
    is_array;

/**
 * Cache
 */
abstract class Cache
{

    protected static array $config = [];

    protected static array $instances = [];

    /**
     * Clear all instances and configs.
     */
    public static function clear(): void
    {
        static::$config = [];
        static::$instances = [];
    }

    /**
     * Get the handler config.
     * @param string|null $key The config key.
     * @return array|null
     */
    public static function getConfig(string|null $key = null): array|null
    {
        if (!$key) {
            return static::$config;
        }

        return static::$config[$key] ?? null;
    }

    /**
     * Get the key for a cacher instance.
     * @param Cacher $cacher The Cacher.
     * @return string|null The cacher key.
     */
    public static function getKey(Cacher $cacher): string|null
    {
        return array_search($cacher, static::$instances, true) ?: null;
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
     * @param string|array $key The config key.
     * @param array|null $options The config options.
     * @throws CacheException if the config is invalid.
     */
    public static function setConfig(string|array $key, array|null $options = null): void
    {
        if (is_array($key)) {
            foreach ($key AS $k => $value) {
                static::setConfig($k, $value);
            }

            return;
        }

        if (!is_array($options)) {
            throw CacheException::forInvalidConfig($key);
        }

        if (array_key_exists($key, static::$config)) {
            throw CacheException::forConfigExists($key);
        }

        static::$config[$key] = $options;
    }

    /**
     * Unload a handler.
     * @param string $key The config key.
     */
    public static function unload(string $key = 'default'): void
    {
        unset(static::$instances[$key]);
        unset(static::$config[$key]);
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
