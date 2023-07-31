<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Redis;
use RedisException;

use function gettype;
use function serialize;
use function time;
use function unserialize;

/**
 * RedisCacher
 */
class RedisCacher extends Cacher
{

    protected static array $defaults =[ 
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'database' => null,
        'timeout' => 0
    ];

    protected Redis $connection;

    /**
     * New Cacher constructor.
     * @param array $options Options for the handler.
     * @throws CacheException if the connection is not valid.
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        try {
            $this->connection = new Redis();
    
            if (!$this->connection->connect($this->config['host'], (int) $this->config['port'], $this->config['timeout'])) {
                throw CacheException::forConnectionFailed();
            }

            if ($this->config['password'] && !$this->connection->auth($this->config['password'])) {
                throw CacheException::forAuthFailed();
            }

            if ($this->config['database'] && !$this->connection->select($this->config['database'])) {
                throw CacheException::forInvalidDatabase($this->config['database']);
            }

        } catch (RedisException $e) {
            throw CacheException::forConnectionError($e->getMessage());
        }
    }

    /**
     * Cacher destructor.
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * Delete an item from the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item was deleted, otherwise FALSE.
     */
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->del($key) === 1;
    }

    /**
     * Empty the cache.
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    public function empty(): bool
    {
        return $this->connection->flushDB();
    }

    /**
     * Retrieve a value from the cache.
     * @param string $key The cache key.
     * @return mixed The cache value.
     */
    public function get(string $key): mixed
    {
        $key = $this->prepareKey($key);

        $data = $this->connection->hMGet($key, ['type', 'value']);

        switch ($data['type']) {
            case 'array':
            case 'object':
                return unserialize($data['value']);
            case 'boolean':
                return (bool) $data['value'];
            case 'double':
                return (float) $data['value'];
            case 'integer':
                return (int) $data['value'];
            case 'string':
                return (string) $data['value'];
            default:
                return null;
        }
    }

    /**
     * Determine if an item exists in the cache.
     * @param string $key The cache key.
     * @return bool TRUE if the item exists, otherwise FALSE.
     */
    public function has(string $key): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->hExists($key, 'value');
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

        // ensure cached value has correct type
        $this->connection->hSetNx($key, 'type', 'integer');

        return $this->connection->hIncrBy($key, 'value', $amount);
    }

    /**
     * Save an item in the cache.
     * @param string $key The cache key.
     * @param mixed $data The data to cache.
     * @param int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    public function save(string $key, mixed $value, int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);

        $type = gettype($value);

        switch ($type) {
            case 'array':
            case 'object':
                $value = serialize($value);
                break;
            case 'boolean':
            case 'double':
            case 'integer':
            case 'string':
            case 'NULL':
                break;
            default:
                return false;
        }

        $this->connection->hMSet($key, ['type' => $type, 'value' => $value]);

        if ($expire) {
            $this->connection->expireAt($key, time() + $expire);
        }

        return true;
    }

    /**
     * Get the size of the cache.
     * @return int The size of the cache (in bytes).
     */
    public function size(): int
    {
        $info = $this->connection->info();

        return $info['used_memory'];
    }

}
