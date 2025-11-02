<?php
declare(strict_types=1);

namespace Fyre\Cache;

use Closure;
use DateInterval;
use DateTimeImmutable;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Utility\Traits\MacroTrait;
use Override;
use Psr\SimpleCache\CacheInterface;
use stdClass;

use function array_replace;
use function is_int;
use function strpbrk;

/**
 * Cacher
 */
abstract class Cacher implements CacheInterface
{
    use MacroTrait;

    protected static array $defaults = [
        'expire' => null,
        'prefix' => '',
    ];

    protected array $config;

    /**
     * New Cacher constructor.
     *
     * @param array $options Options for the handler.
     */
    public function __construct(array $options = [])
    {
        $this->config = array_replace(self::$defaults, static::$defaults, $options);
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
        return $this->increment($key, -$amount);
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
        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get the config.
     *
     * @return array The config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Retrieve multiple values from the cache.
     *
     * @param iterable $keys The cache keys.
     * @param mixed $default The default value.
     * @return iterable The cache values.
     */
    #[Override]
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
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
        return $this->get($key) !== null;
    }

    /**
     * Increment a cache value.
     *
     * @param string $key The cache key.
     * @param int $amount The amount to increment.
     * @return int The new value.
     */
    abstract public function increment(string $key, int $amount = 1): int;

    /**
     * Retrieve an item from the cache, or save a new value if it doesn't exist.
     *
     * @param string $key The cache key.
     * @param Closure $callback The callback method to generate the value.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @return mixed The cache value.
     */
    public function remember(string $key, Closure $callback, DateInterval|int|null $expire = null): mixed
    {
        $test = new stdClass();

        $value = $this->get($key, $test);

        if ($value !== $test) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $expire);

        return $value;
    }

    /**
     * Set multiple items in the cache.
     *
     * @param iterable $values The cache values.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @return bool TRUE if the values were saved, otherwise FALSE.
     */
    #[Override]
    public function setMultiple(iterable $values, DateInterval|int|null $expire = null): bool
    {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $expire)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the size of the cache.
     *
     * @return int The size of the cache (in bytes).
     */
    abstract public function size(): int;

    /**
     * Get the expires timestamp.
     *
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     * @return int|null The expires timestamp.
     */
    protected function getExpires(DateInterval|int|null $expire): int|null
    {
        if ($expire === null) {
            return $this->config['expire'];
        }

        if (is_int($expire)) {
            return $expire;
        }

        $start = new DateTimeImmutable();
        $end = $start->add($expire);

        return $end->getTimestamp() - $start->getTimestamp();
    }

    /**
     * Get the real cache key.
     *
     * @param string $key The cache key.
     * @return string The real cache key.
     *
     * @throws CacheException if the handler is not valid.
     */
    protected function prepareKey(string $key): string
    {
        if (strpbrk($key, '{}()/\@:') !== false) {
            throw CacheException::forInvalidKey($key);
        }

        return $this->config['prefix'].$key;
    }
}
