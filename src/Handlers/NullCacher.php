<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;
use Override;

/**
 * NullCacher
 */
class NullCacher extends Cacher
{
    /**
     * Clear the cache.
     *
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    #[Override]
    public function clear(): bool
    {
        return true;
    }

    /**
     * Delete an item from the cache.
     *
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    #[Override]
    public function delete(string $key): bool
    {
        return true;
    }

    /**
     * Retrieve a value from the cache.
     *
     * @param string $key The cache key.
     * @param mixed $default The default value.
     * @return mixed The cache value.
     */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        return null;
    }

    /**
     * Increment a cache value.
     *
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return int The new value.
     */
    #[Override]
    public function increment(string $key, int $amount = 1): int
    {
        return 1;
    }

    /**
     * Set an item in the cache.
     *
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    #[Override]
    public function set(string $key, mixed $data, DateInterval|int|null $expire = null): bool
    {
        return true;
    }

    /**
     * Get the size of the cache.
     *
     * @return int The size of the cache (in bytes).
     */
    #[Override]
    public function size(): int
    {
        return 0;
    }
}
