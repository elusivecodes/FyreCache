<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;

use function array_key_exists;
use function array_reduce;
use function mb_strlen;
use function serialize;
use function time;

/**
 * ArrayCacher
 */
class ArrayCacher extends Cacher
{
    protected array $cache = [];

    /**
     * Clear the cache.
     *
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    /**
     * Delete an item from the cache.
     *
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);

        if (!array_key_exists($key, $this->cache)) {
            return false;
        }

        unset($this->cache[$key]);

        return true;
    }

    /**
     * Retrieve a value from the cache.
     *
     * @param string $key The cache key.
     * @param mixed $default The default value.
     * @return mixed The cache value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->prepareKey($key);

        if (!array_key_exists($key, $this->cache)) {
            return $default;
        }

        $data = $this->cache[$key];

        if ($data['expires'] !== null && $data['expires'] < time()) {
            unset($this->cache[$key]);

            return $default;
        }

        return $data['data'];
    }

    /**
     * Increment a cache value.
     *
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return int The new value.
     */
    public function increment(string $key, int $amount = 1): int
    {
        if ($this->get($key) === null) {
            $this->set($key, 0);
        }

        $key = $this->prepareKey($key);
        $this->cache[$key]['data'] += $amount;

        return $this->cache[$key]['data'];
    }

    /**
     * Set an item in the cache.
     *
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    public function set(string $key, mixed $data, DateInterval|int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);

        $expires = $this->getExpires($expire);

        if ($expires !== null) {
            $expires += time();
        }

        $this->cache[$key] = [
            'data' => $data,
            'expires' => $expires,
        ];

        return true;
    }

    /**
     * Get the size of the cache.
     *
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        return array_reduce(
            $this->cache,
            static fn(int $carry, array $item): int => $carry + mb_strlen(serialize($item), '8bit'),
            0
        );
    }
}
