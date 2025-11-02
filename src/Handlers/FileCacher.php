<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use Fyre\Cache\Cacher;
use Fyre\FileSystem\Exceptions\FileSystemException;
use Fyre\FileSystem\File;
use Fyre\FileSystem\Folder;
use Fyre\Utility\Path;
use Override;

use function get_object_vars;
use function is_numeric;
use function serialize;
use function time;
use function unserialize;

/**
 * FileCacher
 */
class FileCacher extends Cacher
{
    protected static array $defaults = [
        'path' => '/tmp/cache',
        'mode' => 0640,
    ];

    protected Folder $folder;

    /**
     * New Cacher constructor.
     *
     * @param array $options Options for the handler.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->folder = new Folder($this->config['path'], true);
    }

    /**
     * Get the debug info of the object.
     *
     * @return array The debug info.
     */
    public function __debugInfo(): array
    {
        $data = get_object_vars($this);

        unset($data['folder']);

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
        $this->folder->empty();

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
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->folder->path(), $key);

        $file = new File($filePath);

        if (!$file->exists()) {
            return false;
        }

        $file->delete();

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
        $file = $this->getFile($key);

        if (!$file) {
            return $default;
        }

        $file->open('r');

        $value = $this->readData($file, $default);

        $file->close();

        if ($value === null) {
            $this->delete($key);
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
    #[Override]
    public function increment(string $key, int $amount = 1): int
    {
        $file = $this->getFile($key, true);

        $file->open('c');
        $file->lock();

        $value = $this->readData($file);

        if (!is_numeric($value)) {
            $value = 0;
        }

        $value += $amount;

        $file->truncate();
        $file->rewind();

        $this->writeData($file, $value);

        $file->close();

        return $value;
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
        $file = $this->getFile($key, true);

        $file->open('w');
        $file->lock();

        $this->writeData($file, $data, $expire);

        $file->close();

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
        return $this->folder->size();
    }

    /**
     * Open a cache file (or create it if it doesn't exist).
     *
     * @param string $key The cache key.
     * @param bool $create Whether to create the file.
     * @return File|null The File.
     */
    protected function getFile(string $key, bool $create = false): File|null
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->folder->path(), $key);

        $file = new File($filePath, $create);

        if (!$create && !$file->exists()) {
            return null;
        }

        $file->chmod($this->config['mode']);

        return $file;
    }

    /**
     * Read data from a File.
     *
     * @param File $file The File.
     * @param mixed $default The default value.
     * @return mixed The file data.
     */
    protected function readData(File $file, mixed $default = null): mixed
    {
        try {
            $contents = $file->contents();

            if (!$contents) {
                return $default;
            }

            $data = unserialize($contents);

            if ($data['expires'] !== null && $data['expires'] <= time()) {
                return $default;
            }

            return $data['data'];
        } catch (FileSystemException $e) {
            return $default;
        }
    }

    /**
     * Write data to a file pointer.
     *
     * @param File $file The File.
     * @param mixed $data The data to cache.
     * @param DateInterval|int|null $expire The number of seconds the value will be valid.
     */
    protected function writeData(File $file, mixed $data, DateInterval|int|null $expire = null): void
    {
        $expires = $this->getExpires($expire);

        if ($expires !== null) {
            $expires += time();
        }

        $data = serialize([
            'data' => $data,
            'expires' => $expires,
        ]);

        $file->write($data);
    }
}
