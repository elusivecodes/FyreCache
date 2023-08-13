<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use Fyre\Cache\Cacher;

/**
 * NullCacher
 */
class NullCacher extends Cacher
{

    /**
     * Delete an item from the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    public function delete(string $key): bool
    {
        return true;
    }

    /**
     * Empty the cache.
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function empty(): bool
    {
        return true;
	}

    /**
     * Retrieve a value from the cache.
     * @param string $key The cache key.
     * @return mixed The cache value.
     */
    public function get(string $key): mixed
    {
        return null;
	}

    /**
     * Increment a cache value.
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return int The new value.
     */
    public function increment(string $key, int $amount = 1): int
    {
        return 1;
    }

    /**
     * Save an item in the cache.
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    public function save(string $key, mixed $data, int|null $expire = null): bool
    {
        return true;
    }

    /**
     * Get the size of the cache.
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        return 0;
    }

}
