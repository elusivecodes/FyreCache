<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Closure;
use Fyre\Cache\Exceptions\CacheException;

use function array_replace;
use function call_user_func;
use function strpbrk;

/**
 * Cacher
 */
abstract class Cacher
{

    protected static array $defaults = [
        'expire' => null,
        'prefix' => ''
    ];

    protected array $config;

    /**
     * New Cacher constructor.
     * @param array $options Options for the handler.
     */
    public function __construct(array $options = [])
    {
        $this->config = array_replace(self::$defaults, static::$defaults, $options);
    }

    /**
     * Decrement a cache value.
     * @param string $key The cache key.
     * @param int $amount The amount to decrement.
     * @return int The new value.
     */
    public function decrement(string $key, int $amount = 1): int
    {
        return $this->increment($key, -$amount);
    }

    /**
     * Delete an item from the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    abstract public function delete(string $key): bool;

    /**
     * Empty the cache.
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    abstract public function empty(): bool;

    /**
     * Retrieve a value from the cache.
     * @param string $key The cache key.
     * @return mixed The cache value.
     */
    abstract public function get(string $key): mixed;

    /**
     * Get the config.
     * @return array The config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Determine if an item exists in the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item exists, otherwise FALSE.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Increment a cache value.
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return int The new value.
     */
    abstract public function increment(string $key, int $amount = 1): int;

    /**
     * Retrieve an item from the cache, or save a new value if it doesn't exist.
     * @param string $key The cache key.
     * @param Closure $callback The callback method to generate the value.
     * @param int|null $expire The number of seconds the value will be valid.
     * @return mixed The cache value.
     */
    public function remember(string $key, Closure $callback, int|null $expire = null): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = call_user_func($callback);

        $this->save($key, $value, $expire);

        return $value;
    }

    /**
     * Save an item in the cache.
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    abstract public function save(string $key, mixed $data, int|null $expire = null): bool;

    /**
     * Get the size of the cache.
     * @return int The size of the cache (in bytes).
     */
    abstract public function size(): int;

    /**
     * Get the real cache key.
     * @param string $key The cache key.
     * @return string The real cache key.
     */
    protected function prepareKey(string $key): string
    {
        if (strpbrk($key, '{}()/\@:') !== false) {
            throw CacheException::forInvalidKey($key);
        }

        return $this->config['prefix'].$key;
    }

}
