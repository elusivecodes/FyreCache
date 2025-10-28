<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Exception;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Memcached;

use function array_combine;
use function array_keys;
use function array_map;
use function get_debug_info;
use function in_array;
use function iterator_to_array;

/**
 * MemcachedCacher
 */
class MemcachedCacher extends Cacher
{
    protected static array $defaults = [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 1,
    ];

    protected Memcached $connection;

    /**
     * New Cacher constructor.
     *
     * @param array $options Options for the handler.
     *
     * @throws CacheException if the connection is not valid.
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        try {
            $this->connection = new Memcached();

            $this->connection->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

            $this->connection->addServer(
                $this->config['host'],
                (int) $this->config['port'],
                $this->config['weight']
            );

            if (!$this->getStats()) {
                throw CacheException::forConnectionFailed();
            }
        } catch (CacheException $e) {
            throw $e;
        } catch (Exception $e) {
            throw CacheException::forConnectionError($e->getMessage());
        }
    }

    /**
     * Get the debug info of the object.
     *
     * @return array The debug info.
     */
    public function __debugInfo(): array
    {
        $data = get_object_vars($this);

        foreach (['host', 'port'] as $key) {
            if (!$data['config'][$key]) {
                continue;
            }

            $data['config'][$key] = '*****';
        }

        return $data;
    }

    /**
     * Cacher destructor.
     */
    public function __destruct()
    {
        $this->connection->quit();
    }

    /**
     * Clear the cache.
     *
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function clear(): bool
    {
        return $this->connection->flush();
    }

    /**
     * Decrement a cache value.
     *
     * @param string $key The cache key.
     * @param int $amount The amount to decrement.
     * @return int The new value.
     */
    public function decrement(string $key, int $amount = 1): int
    {
        $key = $this->prepareKey($key);

        return $this->connection->decrement($key, $amount, 0);
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

        return $this->connection->delete($key);
    }

    /**
     * Delete multiple items from the cache.
     *
     * @param iterable $keys The cache keys.
     * @return bool TRUE if the items were deleted, otherwise FALSE.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = iterator_to_array($keys);
        $keys = array_map(
            fn(string $key): string => $this->prepareKey($key),
            $keys
        );

        $result = $this->connection->deleteMulti($keys);

        return !in_array(false, $result, true);
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

        $value = $this->connection->get($key);

        if ($this->connection->getResultCode() === Memcached::RES_NOTFOUND) {
            return $default;
        }

        return $value;
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
        $key = $this->prepareKey($key);

        return $this->connection->increment($key, $amount, $amount);
    }

    /**
     * Set an item in the cache.
     *
     * @param string $key The cache key.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @param mixed $data The data to cache.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    public function set(string $key, mixed $value, DateInterval|int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->set($key, $value, $this->getExpires($expire) ?? 0);
    }

    /**
     * Set multiple items in the cache.
     *
     * @param iterable $values The cache values.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the values were saved, otherwise FALSE.
     */
    public function setMultiple(iterable $values, DateInterval|int|null $expire = null): bool
    {
        $values = iterator_to_array($values);
        $keys = array_keys($values);
        $keys = array_map(
            fn(string $key): string => $this->prepareKey($key),
            $keys
        );

        $values = array_combine($keys, $values);

        return $this->connection->setMulti($values, $this->getExpires($expire) ?? 0);
    }

    /**
     * Get the size of the cache.
     *
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        $stats = $this->getStats();

        return $stats['bytes'] ?? 0;
    }

    /**
     * Get memcached stats.
     *
     * @return array|null The memcached stats.
     */
    protected function getStats(): array|null
    {
        $stats = $this->connection->getStats();

        $server = $this->config['host'].':'.$this->config['port'];

        return $stats[$server] ?? null;
    }
}
