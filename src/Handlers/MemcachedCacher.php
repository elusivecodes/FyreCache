<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use
    Fyre\Cache\Cacher,
    Fyre\Cache\Exceptions\CacheException,
    Exception,
    Memcached;

/**
 * MemcachedCacher
 */
class MemcachedCacher extends Cacher
{

    protected static array $defaults =[ 
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 1
    ];

    protected Memcached $connection;

    /**
     * New Cacher constructor.
     * @param array $options Options for the handler.
     * @throws CacheException if the path is invalid.
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
     * Cacher destructor.
     */
    public function __destruct()
    {
        $this->connection->quit();
    }

    /**
     * Decrement a cache value.
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
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->delete($key);
    }

    /**
     * Empty the cache.
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function empty(): bool
    {
        return $this->connection->flush();
    }

    /**
     * Retrieve a value from the cache.
     * @param string $key The cache key.
     * @return mixed The cache value.
     */
    public function get(string $key)
    {
        $key = $this->prepareKey($key);

        $value = $this->connection->get($key);

        if ($this->connection->getResultCode() === Memcached::RES_NOTFOUND) {
            return null;
        }

        return $value;
    }

    /**
     * Increment a cache value.
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
     * Save an item in the cache.
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    public function save(string $key, $value, int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->set($key, $value, $expire ?? 0);
    }

    /**
     * Get the size of the cache.
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        $stats = $this->getStats();

        return $stats['bytes'] ?? 0;
    }

    /**
     * Get memcached stats.
     * @return array|null The memcached stats.
     */
    protected function getStats(): array|null
    {
        $stats = $this->connection->getStats();

        $server = $this->config['host'].':'.$this->config['port'];
        return $stats[$server] ?? null;
    }

}
