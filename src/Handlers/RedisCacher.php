<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Override;
use Redis;
use RedisException;

use function array_map;
use function count;
use function get_object_vars;
use function gettype;
use function iterator_to_array;
use function serialize;
use function unserialize;

/**
 * RedisCacher
 */
class RedisCacher extends Cacher
{
    protected static array $defaults = [
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'database' => null,
        'timeout' => 0,
        'persist' => true,
        'tls' => false,
        'ssl' => [
            'key' => null,
            'cert' => null,
            'ca' => null,
        ],
    ];

    protected Redis $connection;

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
            $this->connection = new Redis();

            $tls = $this->config['tls'] ? 'tls://' : '';

            if (!$this->connection->connect(
                $tls.$this->config['host'],
                (int) $this->config['port'],
                (int) $this->config['timeout'],
                $this->config['persist'] ?
                    ($this->config['port'].$this->config['timeout'].$this->config['database']) :
                null,
                0,
                0,
                [
                    'ssl' => [
                        'local_pk' => $this->config['ssl']['key'] ?? null,
                        'local_cert' => $this->config['ssl']['cert'] ?? null,
                        'cafile' => $this->config['ssl']['ca'] ?? null,
                    ],
                ],
            )) {
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
        if (!$this->config['persist']) {
            $this->connection->close();
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

        foreach (['host', 'password', 'port', 'database'] as $key) {
            if (!$data['config'][$key]) {
                continue;
            }

            $data['config'][$key] = '*****';
        }

        unset($data['connection']);

        return $data;
    }

    /**
     * Clear the cache.
     *
     * @return bool TRUE if the cache was cleared, otherwise FALSE.
     */
    #[Override]
    public function clear(): bool
    {
        return $this->connection->flushDB(false);
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
        $key = $this->prepareKey($key);

        return $this->connection->del($key) > 0;
    }

    /**
     * Delete multiple items from the cache.
     *
     * @param iterable $keys The cache keys.
     * @return bool TRUE if the items were deleted, otherwise FALSE.
     */
    #[Override]
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = iterator_to_array($keys);
        $keys = array_map(
            fn(string $key): string => $this->prepareKey($key),
            $keys
        );

        return $this->connection->del($keys) >= count($keys);
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
                return $default;
        }
    }

    /**
     * Determine whether an item exists in the cache.
     *
     * @param string $key The cache key.
     * @return bool TRUE if the item exists, otherwise FALSE.
     */
    #[Override]
    public function has(string $key): bool
    {
        $key = $this->prepareKey($key);

        return $this->connection->hExists($key, 'value');
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
        $key = $this->prepareKey($key);

        // ensure cached value has correct type
        $this->connection->hSetNx($key, 'type', 'integer');

        return $this->connection->hIncrBy($key, 'value', $amount);
    }

    /**
     * set an item in the cache.
     *
     * @param string $key The cache key.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @param mixed $data The data to cache.
     * @return bool TRUE if the value was saved, otherwise FALSE.
     */
    #[Override]
    public function set(string $key, mixed $value, DateInterval|int|null $expire = null): bool
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

        $expires = $this->getExpires($expire);

        if ($expires !== null) {
            $this->connection->expireAt($key, $expires);
        }

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
        $info = $this->connection->info();

        return $info['used_memory'];
    }
}
