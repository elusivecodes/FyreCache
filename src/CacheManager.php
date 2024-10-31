<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\NullCacher;

use function array_key_exists;
use function class_exists;
use function is_subclass_of;

/**
 * CacheManager
 */
class CacheManager
{
    public const DEFAULT = 'default';

    protected array $config = [];

    protected bool $enabled = true;

    protected array $instances = [];

    protected NullCacher $nullCacher;

    /**
     * New Cache constructor.
     *
     * @param array $config The Cache config.
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $options) {
            $this->setConfig($key, $options);
        }
    }

    /**
     * Load a handler.
     *
     * @param array $options Options for the handler.
     * @return Cacher The handler.
     *
     * @throws CacheException if the handler is not valid.
     */
    public function build(array $options = []): Cacher
    {
        if (!array_key_exists('className', $options)) {
            throw CacheException::forInvalidClass();
        }

        if (!class_exists($options['className'], true) || !is_subclass_of($options['className'], Cacher::class)) {
            throw CacheException::forInvalidClass($options['className']);
        }

        return new $options['className']($options);
    }

    /**
     * Clear all instances and configs.
     */
    public function clear(): void
    {
        $this->config = [];
        $this->instances = [];
    }

    /**
     * Disable the cache.
     *
     * @return static The CacheManager.
     */
    public function disable(): static
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Enable the cache.
     *
     * @return static The CacheManager.
     */
    public function enable(): static
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Get the handler config.
     *
     * @param string|null $key The config key.
     */
    public function getConfig(string|null $key = null): array|null
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Determine if a config exists.
     *
     * @param string $key The config key.
     * @return bool TRUE if the config exists, otherwise FALSE.
     */
    public function hasConfig(string $key = self::DEFAULT): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Determine if the cache is enabled.
     *
     * @return bool TRUE if the cache is enabled, otherwise FALSE.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Determine if a handler is loaded.
     *
     * @param string $key The config key.
     * @return bool TRUE if the handler is loaded, otherwise FALSE.
     */
    public function isLoaded(string $key = self::DEFAULT): bool
    {
        return array_key_exists($key, $this->instances);
    }

    /**
     * Set handler config.
     *
     * @param string $key The config key.
     * @param array $options The config options.
     * @return static The CacheManager.
     *
     * @throws CacheException if the config is not valid.
     */
    public function setConfig(string $key, array $options): static
    {
        if (array_key_exists($key, $this->config)) {
            throw CacheException::forConfigExists($key);
        }

        $this->config[$key] = $options;

        return $this;
    }

    /**
     * Unload a handler.
     *
     * @param string $key The config key.
     * @return static The CacheManager.
     */
    public function unload(string $key = self::DEFAULT): static
    {
        unset($this->instances[$key]);
        unset($this->config[$key]);

        return $this;
    }

    /**
     * Load a shared handler instance.
     *
     * @param string $key The config key.
     * @return Cacher The handler.
     */
    public function use(string $key = self::DEFAULT): Cacher
    {
        if (!$this->enabled) {
            return $this->nullCacher ??= new NullCacher();
        }

        return $this->instances[$key] ??= static::build($this->config[$key] ?? []);
    }
}
