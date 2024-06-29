<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\NullCacher;

use function array_key_exists;
use function array_search;
use function class_exists;
use function is_array;

/**
 * Cache
 */
abstract class Cache
{
    public const DEFAULT = 'default';

    protected static array $config = [];

    protected static bool $enabled = true;

    protected static array $instances = [];

    protected static NullCacher $nullCacher;

    /**
     * Clear all instances and configs.
     */
    public static function clear(): void
    {
        static::$config = [];
        static::$instances = [];
    }

    /**
     * Disable the cache.
     */
    public static function disable(): void
    {
        static::$enabled = false;
    }

    /**
     * Enable the cache.
     */
    public static function enable(): void
    {
        static::$enabled = true;
    }

    /**
     * Get the handler config.
     *
     * @param string|null $key The config key.
     */
    public static function getConfig(string|null $key = null): array|null
    {
        if ($key === null) {
            return static::$config;
        }

        return static::$config[$key] ?? null;
    }

    /**
     * Get the key for a cacher instance.
     *
     * @param Cacher $cacher The Cacher.
     * @return string|null The cacher key.
     */
    public static function getKey(Cacher $cacher): string|null
    {
        return array_search($cacher, static::$instances, true) ?: null;
    }

    /**
     * Determine if a config exists.
     *
     * @param string $key The config key.
     * @return bool TRUE if the config exists, otherwise FALSE.
     */
    public static function hasConfig(string $key = self::DEFAULT): bool
    {
        return array_key_exists($key, static::$config);
    }

    /**
     * Determine if the cache is enabled.
     *
     * @return bool TRUE if the cache is enabled, otherwise FALSE.
     */
    public static function isEnabled(): bool
    {
        return static::$enabled;
    }

    /**
     * Determine if a handler is loaded.
     *
     * @param string $key The config key.
     * @return bool TRUE if the handler is loaded, otherwise FALSE.
     */
    public static function isLoaded(string $key = self::DEFAULT): bool
    {
        return array_key_exists($key, static::$instances);
    }

    /**
     * Load a handler.
     *
     * @param array $options Options for the handler.
     * @return Cacher The handler.
     *
     * @throws CacheException if the handler is not valid.
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
     *
     * @param array|string $key The config key.
     * @param array|null $options The config options.
     *
     * @throws CacheException if the config is not valid.
     */
    public static function setConfig(array|string $key, array|null $options = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                static::setConfig($k, $v);
            }

            return;
        }

        if ($options === null) {
            throw CacheException::forInvalidConfig($key);
        }

        if (array_key_exists($key, static::$config)) {
            throw CacheException::forConfigExists($key);
        }

        static::$config[$key] = $options;
    }

    /**
     * Unload a handler.
     *
     * @param string $key The config key.
     * @return bool TRUE if the handler was removed, otherwise FALSE.
     */
    public static function unload(string $key = self::DEFAULT): bool
    {
        if (!array_key_exists($key, static::$config)) {
            return false;
        }

        unset(static::$instances[$key]);
        unset(static::$config[$key]);

        return true;
    }

    /**
     * Load a shared handler instance.
     *
     * @param string $key The config key.
     * @return Cacher The handler.
     */
    public static function use(string $key = self::DEFAULT): Cacher
    {
        if (!static::$enabled) {
            return static::$nullCacher ??= new NullCacher();
        }

        return static::$instances[$key] ??= static::load(static::$config[$key] ?? []);
    }
}
